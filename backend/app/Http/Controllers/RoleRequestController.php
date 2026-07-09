<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\CreatorRequest;
use App\Models\Notification;
use App\Services\ActivityLoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleRequestController extends Controller
{
    public function myRequests(): JsonResponse
    {
        $requests = CreatorRequest::where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests,
        ], 200);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $user = Auth::user();

        $existing = CreatorRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memiliki permintaan yang menunggu verifikasi.',
            ], 422);
        }

        $validated = $request->validated();

        $creatorRequest = CreatorRequest::create([
            'user_id' => $user->id,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan upgrade role berhasil dikirim. Menunggu verifikasi admin.',
            'data' => $creatorRequest,
        ], 201);
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== RoleEnum::ADMIN) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Hanya admin yang dapat mengakses.',
            ], 403);
        }

        $requests = CreatorRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests,
        ], 200);
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== RoleEnum::ADMIN) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $validated = $request->validated();

        $creatorRequest = CreatorRequest::findOrFail($id);

        if ($creatorRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan sudah diproses sebelumnya.',
            ], 422);
        }

        $creatorRequest->status = $validated['status'];

        if ($validated['status'] === 'approved') {
            $creatorRequest->user->update(['role' => RoleEnum::CREATOR->value]);

            Notification::create([
                'user_id' => $creatorRequest->user_id,
                'type' => 'creator_approved',
                'title' => 'Permintaan Kreator Disetujui!',
                'body' => 'Selamat! Permintaan upgrade akun Anda menjadi Kreator telah disetujui oleh admin. Anda sekarang dapat membuat kampanye crowdfunding.',
                'data' => ['approved_at' => now()->toISOString()],
                'created_at' => now(),
            ]);

            ActivityLoggerService::log(
                Auth::id(),
                'creator_request.approve',
                'creator_request',
                $creatorRequest->id,
                "Menyetujui permintaan creator dari user: {$creatorRequest->user->name} ({$creatorRequest->user->email})"
            );

        } elseif ($validated['status'] === 'rejected') {
            $creatorRequest->rejection_reason = $validated['rejection_reason'] ?? 'Permintaan ditolak oleh admin.';

            Notification::create([
                'user_id' => $creatorRequest->user_id,
                'type' => 'creator_rejected',
                'title' => 'Permintaan Kreator Ditolak',
                'body' => "Permintaan upgrade akun Anda menjadi Kreator ditolak oleh admin.\nAlasan: {$creatorRequest->rejection_reason}",
                'data' => ['rejection_reason' => $creatorRequest->rejection_reason],
                'created_at' => now(),
            ]);

            ActivityLoggerService::log(
                Auth::id(),
                'creator_request.reject',
                'creator_request',
                $creatorRequest->id,
                "Menolak permintaan creator dari user: {$creatorRequest->user->name} ({$creatorRequest->user->email}), alasan: {$creatorRequest->rejection_reason}"
            );
        }

        $creatorRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Status permintaan berhasil diperbarui.',
            'data' => $creatorRequest->load('user'),
        ], 200);
    }
}
