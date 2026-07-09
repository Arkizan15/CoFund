<?php

namespace App\Http\Controllers;

use App\Enums\BackingStatus;
use App\Enums\CampaignStatus;
use App\Models\Backing;
use App\Models\Campaign;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function creatorStats(): JsonResponse
    {
        $user = Auth::user();

        $campaigns = Campaign::where('user_id', $user->id)->get();
        $totalCampaigns = $campaigns->count();
        $activeCampaigns = $campaigns->where('status', CampaignStatus::ACTIVE)->count();

        $campaignIds = $campaigns->pluck('id');
        $totalBackers = Backing::whereIn('campaign_id', $campaignIds)
            ->where('status', BackingStatus::COMPLETED)
            ->distinct('user_id')
            ->count('user_id');

        $totalCollected = (float) $campaigns->sum('collected_amount');

        $perCampaign = $campaigns->map(function ($c) {
            return [
                'id' => $c->id,
                'title' => $c->title,
                'slug' => $c->slug,
                'status' => $c->status,
                'target_amount' => (float) $c->target_amount,
                'collected_amount' => (float) $c->collected_amount,
                'progress' => $c->target_amount > 0
                    ? min(100, round(($c->collected_amount / $c->target_amount) * 100, 1))
                    : 0,
                'deadline' => $c->deadline,
                'backer_count' => Backing::where('campaign_id', $c->id)
                    ->where('status', BackingStatus::COMPLETED)
                    ->distinct('user_id')
                    ->count('user_id'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'total_campaigns' => $totalCampaigns,
                'active_campaigns' => $activeCampaigns,
                'total_backers' => $totalBackers,
                'total_collected' => $totalCollected,
                'campaigns' => $perCampaign,
            ],
        ]);
    }

    public function fundingChart(): JsonResponse
    {
        $user = Auth::user();
        $campaignIds = Campaign::where('user_id', $user->id)->pluck('id');

        if ($campaignIds->isEmpty()) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $daily = Backing::whereIn('campaign_id', $campaignIds)
            ->where('status', BackingStatus::COMPLETED)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $cumulative = 0;
        $chart = $daily->map(function ($item) use (&$cumulative) {
            $cumulative += (float) $item->total;
            return [
                'date' => $item->date,
                'amount' => (float) $item->total,
                'cumulative' => $cumulative,
            ];
        });

        return response()->json(['success' => true, 'data' => $chart]);
    }

    public function backerStats(): JsonResponse
    {
        $user = Auth::user();

        $completedBackings = Backing::where('user_id', $user->id)
            ->where('status', BackingStatus::COMPLETED)->get();
        $totalBacked = (float) $completedBackings->sum('amount');
        $backingCount = $completedBackings->count();

        $refundedBackings = Backing::where('user_id', $user->id)
            ->where('status', BackingStatus::REFUNDED)->get();
        $totalRefund = (float) $refundedBackings->sum('amount');
        $refundCount = $refundedBackings->count();

        $backings = Backing::where('user_id', $user->id)
            ->with(['campaign', 'tier'])
            ->latest()
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'campaign_title' => $b->campaign?->title,
                    'campaign_slug' => $b->campaign?->slug,
                    'amount' => (float) $b->amount,
                    'status' => $b->status,
                    'tier_name' => $b->tier?->name,
                    'tier_reward' => $b->tier?->reward_description,
                    'created_at' => $b->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_backed' => $totalBacked,
                'backing_count' => $backingCount,
                'total_refund' => $totalRefund,
                'refund_count' => $refundCount,
                'backings' => $backings,
            ],
        ]);
    }
}
