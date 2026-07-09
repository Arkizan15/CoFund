<?php

namespace App\Http\Controllers\Auth;

use App\Enums\BackingStatus;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\NotifikasiEmail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Hapus pending registration sebelumnya jika ada (email sama)
        $existingToken = Cache::get('pending_email_' . $data['email']);
        if ($existingToken) {
            Cache::forget('pending_registration_' . $existingToken);
            Cache::forget('pending_email_' . $data['email']);
        }

        // Generate token unik dan simpan data registrasi ke cache (60 menit)
        $token = Str::random(60);
        Cache::put('pending_registration_' . $token, [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ], now()->addMinutes(60));

        Cache::put('pending_email_' . $data['email'], $token, now()->addMinutes(60));

        // Kirim email verifikasi
        $verificationUrl = url('/api/auth/email/verify/' . $token);

        Mail::to($data['email'])->send(new NotifikasiEmail(
            subject: 'Verifikasi Email — CoFund',
            greeting: 'Halo ' . $data['name'] . '!',
            messageContent: "Terima kasih telah mendaftar di CoFund!\n\nSilakan klik tombol di bawah untuk memverifikasi alamat email Anda. Link ini akan kedaluwarsa dalam 60 menit.\n\nJika Anda tidak mendaftar di CoFund, abaikan email ini.",
            actionText: 'Verifikasi Email',
            actionUrl: $verificationUrl,
        ));

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil. Silakan cek email untuk verifikasi.',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $user = Auth::user();

        if ($user->suspended_at) {
            Auth::logout();

            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan oleh admin. Silakan hubungi admin untuk informasi lebih lanjut.',
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
            'role' => $user->role,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->loadCount([
            'backings as total_backings' => function ($query) {
                $query->where('status', BackingStatus::COMPLETED);
            },
        ]);

        $totalDonated = (float) $user->backings()
            ->where('status', BackingStatus::COMPLETED)
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'balance' => $user->balance,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'total_backings' => $user->total_backings,
                'total_donated' => $totalDonated,
                'avatar_url' => $user->avatar_url,
            ],
        ]);
    }

    public function verify(Request $request, string $token): RedirectResponse|JsonResponse
    {
        $frontendUrl = config('app.frontend_url');
        $pendingData = Cache::get('pending_registration_' . $token);

        if (!$pendingData) {
            $errorUrl = $frontendUrl . '/verify-email?error=' . urlencode('Link verifikasi tidak valid atau telah kedaluwarsa.');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Link verifikasi tidak valid atau telah kedaluwarsa.',
                ], 403);
            }

            return redirect($errorUrl);
        }

        // Cek apakah user sudah ada (misalnya link diklik dua kali)
        $existingUser = User::where('email', $pendingData['email'])->first();
        if ($existingUser) {
            Cache::forget('pending_registration_' . $token);
            Cache::forget('pending_email_' . $pendingData['email']);

            $sanctumToken = $existingUser->createToken('auth-token')->plainTextToken;
            $successUrl = $frontendUrl . '/verify-email?token=' . $sanctumToken . '&verified=already';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email sudah diverifikasi sebelumnya.',
                    'token' => $sanctumToken,
                    'user' => $existingUser,
                ]);
            }

            return redirect($successUrl);
        }

        // Buat user di database
        $user = User::create([
            'name' => $pendingData['name'],
            'email' => $pendingData['email'],
            'password' => $pendingData['password'],
            'role' => RoleEnum::BACKER->value,
        ]);

        // Hapus cache
        Cache::forget('pending_registration_' . $token);
        Cache::forget('pending_email_' . $pendingData['email']);

        // Tandai email sebagai terverifikasi
        $user->markEmailAsVerified();

        // Generate Sanctum token untuk auto-login
        $sanctumToken = $user->createToken('auth-token')->plainTextToken;

        $successUrl = $frontendUrl . '/verify-email?token=' . $sanctumToken;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email berhasil diverifikasi.',
                'token' => $sanctumToken,
                'user' => $user,
            ]);
        }

        return redirect($successUrl);
    }

    public function verifyLegacy(Request $request, int $id, string $hash): RedirectResponse|JsonResponse
    {
        $frontendUrl = config('app.frontend_url');
        $user = User::find($id);

        if (!$user || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            $errorUrl = $frontendUrl . '/verify-email?error=' . urlencode('Link verifikasi tidak valid atau telah kedaluwarsa.');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Link verifikasi tidak valid.',
                ], 403);
            }

            return redirect($errorUrl);
        }

        if ($user->hasVerifiedEmail()) {
            $token = $user->createToken('auth-token')->plainTextToken;
            $successUrl = $frontendUrl . '/verify-email?token=' . $token . '&verified=already';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email sudah diverifikasi sebelumnya.',
                    'token' => $token,
                    'user' => $user,
                ]);
            }

            return redirect($successUrl);
        }

        $user->markEmailAsVerified();
        $token = $user->createToken('auth-token')->plainTextToken;
        $successUrl = $frontendUrl . '/verify-email?token=' . $token;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email berhasil diverifikasi.',
                'token' => $token,
                'user' => $user,
            ]);
        }

        return redirect($successUrl);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        // Delete old avatar if exists
        if ($user->avatar_url) {
            $oldPath = str_replace('/storage/', 'public/', $user->avatar_url);
            if (Storage::exists($oldPath)) {
                Storage::delete($oldPath);
            }
        }

        $file = $request->file('avatar');
        $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->extension();
        $path = $file->storeAs('public/avatars', $filename);

        if (!$path) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah avatar.',
            ], 500);
        }

        $avatarUrl = url(Storage::url($path));
        $user->avatar_url = $avatarUrl;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Avatar berhasil diperbarui.',
            'data' => [
                'avatar_url' => $avatarUrl,
            ],
        ], 200);
    }

    public function resend(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'string', 'email']]);
        $email = $request->email;

        $token = Cache::get('pending_email_' . $email);
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada registrasi tertunda untuk email ini.',
            ], 404);
        }

        $pendingData = Cache::get('pending_registration_' . $token);
        if (!$pendingData) {
            return response()->json([
                'success' => false,
                'message' => 'Data registrasi tidak ditemukan atau telah kedaluwarsa.',
            ], 404);
        }

        $verificationUrl = url('/api/auth/email/verify/' . $token);

        Mail::to($email)->send(new NotifikasiEmail(
            subject: 'Verifikasi Email — CoFund',
            greeting: 'Halo ' . $pendingData['name'] . '!',
            messageContent: "Terima kasih telah mendaftar di CoFund!\n\nSilakan klik tombol di bawah untuk memverifikasi alamat email Anda. Link ini akan kedaluwarsa dalam 60 menit.\n\nJika Anda tidak mendaftar di CoFund, abaikan email ini.",
            actionText: 'Verifikasi Email',
            actionUrl: $verificationUrl,
        ));

        return response()->json([
            'success' => true,
            'message' => 'Email verifikasi telah dikirim ulang.',
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Link reset password telah dikirim ke email Anda.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status),
        ], 422);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset($data, function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->setRememberToken(Str::random(60));

            $user->save();
        });

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil direset.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status),
        ], 422);
    }
}
