<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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
                $query->where('status', 'completed');
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
            ],
        ]);
    }

    public function verify(Request $request, int $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'success' => false,
                'message' => 'Link verifikasi tidak valid.',
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email sudah diverifikasi sebelumnya.',
            ]);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'success' => true,
            'message' => 'Email berhasil diverifikasi.',
        ]);
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
