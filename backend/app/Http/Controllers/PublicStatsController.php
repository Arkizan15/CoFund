<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\User;
use App\Enums\CampaignStatus;
use App\Enums\RoleEnum;
use Illuminate\Http\JsonResponse;

class PublicStatsController extends Controller
{
    public function index(): JsonResponse
    {
        $totalCampaigns = Campaign::whereIn('status', [
            CampaignStatus::ACTIVE,
            CampaignStatus::SUCCESS,
            CampaignStatus::FAILED,
        ])->count();

        $totalBackers = User::where('role', RoleEnum::BACKER->value)->count();
        $totalCollected = Campaign::sum('collected_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total_campaigns' => $totalCampaigns,
                'total_backers' => $totalBackers,
                'total_collected' => (float) $totalCollected,
            ],
        ]);
    }
}
