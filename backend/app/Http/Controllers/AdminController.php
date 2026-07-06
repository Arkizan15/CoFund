<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\Backing;
use App\Models\Campaign;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function platformOverview(): JsonResponse
    {
        $totalCampaigns = Campaign::count();
        $activeCampaigns = Campaign::where('status', 'active')->count();
        $successCampaigns = Campaign::where('status', 'success')->count();
        $failedCampaigns = Campaign::where('status', 'failed')->count();

        $totalCollected = Campaign::sum('collected_amount');
        $totalPlatformFee = WalletTransaction::where('type', 'platform_fee')
            ->where('status', 'success')
            ->sum('amount');

        $totalUsers = User::count();
        $totalCreators = User::where('role', RoleEnum::CREATOR->value)->count();
        $totalBackers = User::where('role', RoleEnum::BACKER->value)->count();

        $totalBackings = Backing::where('status', 'completed')->count();
        $totalBackingAmount = Backing::where('status', 'completed')->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'campaigns' => [
                    'total' => $totalCampaigns,
                    'active' => $activeCampaigns,
                    'success' => $successCampaigns,
                    'failed' => $failedCampaigns,
                ],
                'finance' => [
                    'total_collected' => (float) $totalCollected,
                    'total_platform_fee' => (float) $totalPlatformFee,
                ],
                'users' => [
                    'total' => $totalUsers,
                    'creators' => $totalCreators,
                    'backers' => $totalBackers,
                ],
                'backings' => [
                    'total' => $totalBackings,
                    'total_amount' => (float) $totalBackingAmount,
                ],
            ],
        ], 200);
    }

    public function pendingApprovals(): JsonResponse
    {
        $campaigns = Campaign::with(['user', 'category'])
            ->where('status', 'review')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ], 200);
    }

    public function approveCampaign(int $id): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->status !== 'review') {
            return response()->json([
                'success' => false,
                'message' => 'Kampanye tidak dalam status review.',
            ], 422);
        }

        $campaign->status = 'active';
        $campaign->save();

        return response()->json([
            'success' => true,
            'message' => 'Kampanye berhasil disetujui dan sekarang aktif.',
            'data' => $campaign,
        ], 200);
    }

    public function rejectCampaign(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $campaign = Campaign::findOrFail($id);

        if ($campaign->status !== 'review') {
            return response()->json([
                'success' => false,
                'message' => 'Kampanye tidak dalam status review.',
            ], 422);
        }

        $campaign->status = 'draft';
        $campaign->save();

        return response()->json([
            'success' => true,
            'message' => 'Kampanye ditolak. Alasan: ' . $validated['reason'],
            'data' => $campaign,
        ], 200);
    }

    public function banCampaign(int $id): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);

        if (!in_array($campaign->status, ['active', 'review'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya kampanye aktif atau dalam review yang bisa diban.',
            ], 422);
        }

        $campaign->status = 'failed';
        $campaign->save();

        return response()->json([
            'success' => true,
            'message' => 'Kampanye berhasil diban. Status diubah menjadi failed.',
            'data' => $campaign,
        ], 200);
    }
}
