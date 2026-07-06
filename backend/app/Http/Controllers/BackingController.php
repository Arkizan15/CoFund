<?php

namespace App\Http\Controllers;

use App\Models\Backing;
use App\Models\Campaign;
use App\Models\CampaignTier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail() === false) {
            return response()->json([
                'success' => false,
                'message' => 'Email harus diverifikasi sebelum melakukan backing.',
            ], 403);
        }

        $validated = $request->validate([
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'tier_id' => ['nullable', 'exists:campaign_tiers,id'],
            'amount' => ['required', 'numeric', 'min:10000'],
        ]);

        $campaign = Campaign::findOrFail($validated['campaign_id']);

        if ($campaign->user_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa mendanai kampanye milik sendiri.',
            ], 403);
        }

        if ($campaign->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya kampanye aktif yang dapat didanai.',
            ], 422);
        }

        $amount = (float) $validated['amount'];

        if (isset($validated['tier_id'])) {
            $tier = CampaignTier::findOrFail($validated['tier_id']);

            if ($tier->campaign_id !== $campaign->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tier tidak sesuai dengan kampanye ini.',
                ], 422);
            }

            if ($tier->remaining_quota === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kuota tier ini sudah habis.',
                ], 422);
            }

            if ($amount < (float) $tier->min_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal minimal untuk tier ini adalah Rp ' . number_format($tier->min_amount, 0, ',', '.'),
                ], 422);
            }
        }

        if ($user->balance < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak mencukupi. Silakan top up terlebih dahulu.',
            ], 422);
        }

        $user->balance -= $amount;
        $user->save();

        $backing = Backing::create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'tier_id' => $validated['tier_id'] ?? null,
            'amount' => $amount,
            'status' => 'completed',
        ]);

        if (isset($validated['tier_id'])) {
            $tier->decrement('remaining_quota');
        }

        $campaign->increment('collected_amount', $amount);

        return response()->json([
            'success' => true,
            'message' => 'Backing berhasil! Dana telah masuk ke escrow.',
            'data' => [
                'backing' => $backing,
                'collected_amount' => $campaign->fresh()->collected_amount,
                'balance' => $user->fresh()->balance,
            ],
        ], 201);
    }

    public function history(): JsonResponse
    {
        $user = Auth::user();

        $backings = Backing::with(['user', 'campaign', 'tier'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $backings,
        ], 200);
    }
}
