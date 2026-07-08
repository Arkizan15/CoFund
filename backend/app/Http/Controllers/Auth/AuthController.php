<?php

namespace App\Http\Controllers\Auth;

use App\Enums\BackingStatus;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => RoleEnum::BACKER->value,
        ]);

        $user->sendEmailVerificationNotification();

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
                'avatar_url' => $user->avatar_url,
            ],
        ]);
    }

    public function verify(Request $request, int $id, string $hash): RedirectResponse|JsonResponse
    {
        $user = User::findOrFail($id);
        $frontendUrl = config('app.frontend_url');

        // Invalid hash
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            $errorUrl = $frontendUrl . '/verify-email?error=' . urlencode('Link verifikasi tidak valid atau telah kedaluwarsa.');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Link verifikasi tidak valid.',
                ], 403);
            }

            return redirect($errorUrl);
        }

        // Already verified — still generate a token so they can log in
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

        // Mark email as verified
        $user->markEmailAsVerified();

        // Generate Sanctum token so user is automatically logged in on the frontend
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
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah diverifikasi.',
            ], 400);
        }

        $user->sendEmailVerificationNotification();

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
