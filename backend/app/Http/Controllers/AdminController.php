<?php

namespace App\Http\Controllers;

use App\Enums\BackingStatus;
use App\Enums\CampaignStatus;
use App\Enums\RoleEnum;
use App\Mail\NotifikasiEmail;
use App\Models\Backing;
use App\Models\Campaign;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\CampaignSettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function platformOverview(): JsonResponse
    {
        $totalCampaigns = Campaign::count();
        $activeCampaigns = Campaign::where('status', CampaignStatus::ACTIVE)->count();
        $successCampaigns = Campaign::where('status', CampaignStatus::SUCCESS)->count();
        $failedCampaigns = Campaign::where('status', CampaignStatus::FAILED)->count();

        $totalCollected = Campaign::sum('collected_amount');
        $totalPlatformFee = WalletTransaction::where('type', 'platform_fee')
            ->where('status', 'success')
            ->sum('amount');

        $totalUsers = User::count();
        $totalCreators = User::where('role', RoleEnum::CREATOR->value)->count();
        $totalBackers = User::where('role', RoleEnum::BACKER->value)->count();

        $totalBackings = Backing::where('status', BackingStatus::COMPLETED)->count();
        $totalBackingAmount = Backing::where('status', BackingStatus::COMPLETED)->sum('amount');

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
            ->where('status', CampaignStatus::REVIEW)
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

        if ($campaign->status !== CampaignStatus::REVIEW) {
            return response()->json([
                'success' => false,
                'message' => 'Kampanye tidak dalam status review.',
            ], 422);
        }

        $campaign->status = CampaignStatus::ACTIVE;
        $campaign->save();

        $creator = $campaign->user;

        Notification::create([
            'user_id' => $creator->id,
            'type' => 'campaign_approved',
            'title' => 'Kampanye Disetujui!',
            'body' => "Kampanye \"{$campaign->title}\" telah disetujui oleh admin dan sekarang aktif. Kampanye Anda dapat mulai menerima pendanaan.",
            'data' => [
                'campaign_id' => $campaign->id,
                'campaign_slug' => $campaign->slug,
            ],
            'created_at' => now(),
        ]);

        try {
            Mail::to($creator->email)->send(new NotifikasiEmail(
                'Kampanye Disetujui: ' . $campaign->title,
                'Halo ' . $creator->name . '!',
                'Selamat! Kampanye "' . $campaign->title . '" telah disetujui oleh admin dan sekarang aktif.'
                    . "\n\nKampanye Anda sekarang dapat menerima pendanaan dari para backer."
                    . "\n\nJangan lupa untuk mempromosikan kampanye Anda agar semakin banyak yang tahu!",
                'Lihat Kampanye',
                url('/campaigns/' . $campaign->slug)
            ));
        } catch (\Exception $e) {
            Log::warning('Gagal kirim email approve campaign: ' . $e->getMessage());
        }

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

        if ($campaign->status !== CampaignStatus::REVIEW) {
            return response()->json([
                'success' => false,
                'message' => 'Kampanye tidak dalam status review.',
            ], 422);
        }

        $campaign->status = CampaignStatus::DRAFT;
        $campaign->rejection_reason = $validated['reason'];
        $campaign->save();

        $creator = $campaign->user;

        Notification::create([
            'user_id' => $creator->id,
            'type' => 'campaign_rejected',
            'title' => 'Kampanye Ditolak',
            'body' => "Kampanye \"{$campaign->title}\" ditolak oleh admin.\nAlasan: {$validated['reason']}",
            'data' => [
                'campaign_id' => $campaign->id,
                'campaign_slug' => $campaign->slug,
                'reason' => $validated['reason'],
            ],
            'created_at' => now(),
        ]);

        try {
            Mail::to($creator->email)->send(new NotifikasiEmail(
                'Kampanye Ditolak: ' . $campaign->title,
                'Halo ' . $creator->name . '!',
                'Kampanye "' . $campaign->title . '" yang Anda ajukan ditolak oleh admin.'
                    . "\n\nAlasan penolakan:\n" . $validated['reason']
                    . "\n\nSilakan perbaiki sesuai alasan di atas dan ajukan kembali.",
                'Perbaiki Kampanye',
                url('/campaigns/' . $campaign->slug . '/edit')
            ));
        } catch (\Exception $e) {
            Log::warning('Gagal kirim email reject campaign: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Kampanye ditolak. Alasan: ' . $validated['reason'],
            'data' => $campaign,
        ], 200);
    }

    public function allCampaigns(): JsonResponse
    {
        $campaigns = Campaign::with(['user', 'category', 'images'])
            ->whereNotIn('status', [CampaignStatus::REVIEW->value, CampaignStatus::DRAFT->value])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ], 200);
    }

    public function banCampaign(int $id): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);

        if (!in_array($campaign->status, [CampaignStatus::ACTIVE, CampaignStatus::REVIEW])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya kampanye aktif atau dalam review yang bisa diban.',
            ], 422);
        }

        CampaignSettlementService::processRefund($campaign);

        $campaign->settled_at = now();
        $campaign->save();

        return response()->json([
            'success' => true,
            'message' => 'Kampanye berhasil diban. Dana telah dikembalikan ke seluruh backer.',
            'data' => $campaign->fresh(),
        ], 200);
    }

    public function activeCampaigns(): JsonResponse
    {
        $campaigns = Campaign::with(['user', 'category', 'backings'])
            ->where('status', CampaignStatus::ACTIVE)
            ->latest()
            ->get()
            ->map(function ($campaign) {
                $deadline = \Carbon\Carbon::parse($campaign->deadline);
                return [
                    'id' => $campaign->id,
                    'title' => $campaign->title,
                    'slug' => $campaign->slug,
                    'user' => $campaign->user,
                    'category' => $campaign->category,
                    'target_amount' => $campaign->target_amount,
                    'collected_amount' => $campaign->collected_amount,
                    'progress' => $campaign->target_amount > 0
                        ? min(100, round(($campaign->collected_amount / $campaign->target_amount) * 100, 1))
                        : 0,
                    'backer_count' => $campaign->backings->where('status', BackingStatus::COMPLETED)->count(),
                    'deadline' => $campaign->deadline,
                    'days_remaining' => max(0, $deadline->diffInDays(now(), false)),
                    'status' => $campaign->status,
                    'created_at' => $campaign->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ], 200);
    }

    public function endedCampaigns(): JsonResponse
    {
        $campaigns = Campaign::with(['user', 'category', 'backings'])
            ->whereIn('status', [CampaignStatus::SUCCESS->value, CampaignStatus::FAILED->value])
            ->latest()
            ->get()
            ->map(function ($campaign) {
                $deadline = \Carbon\Carbon::parse($campaign->deadline);
                return [
                    'id' => $campaign->id,
                    'title' => $campaign->title,
                    'slug' => $campaign->slug,
                    'user' => $campaign->user,
                    'category' => $campaign->category,
                    'target_amount' => $campaign->target_amount,
                    'collected_amount' => $campaign->collected_amount,
                    'progress' => $campaign->target_amount > 0
                        ? min(100, round(($campaign->collected_amount / $campaign->target_amount) * 100, 1))
                        : 0,
                    'backer_count' => $campaign->backings->where('status', BackingStatus::COMPLETED)->count(),
                    'deadline' => $campaign->deadline,
                    'ended_at' => $campaign->updated_at,
                    'status' => $campaign->status,
                    'conclusion' => $campaign->status === CampaignStatus::SUCCESS
                        ? 'Mencapai target sebelum deadline'
                        : 'Gagal mencapai target',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ], 200);
    }

    // ===== User Management =====

    public function users(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $users = $query->withCount(['campaigns', 'backings'])
            ->latest()
            ->paginate(20);

        $users->getCollection()->transform(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'balance' => (float) $u->balance,
                'email_verified_at' => $u->email_verified_at,
                'suspended_at' => $u->suspended_at,
                'campaigns_count' => $u->campaigns_count,
                'backings_count' => $u->backings_count,
                'created_at' => $u->created_at,
            ];
        });

        return response()->json(['success' => true, 'data' => $users]);
    }

    public function userDetail(int $id): JsonResponse
    {
        $user = User::withCount(['campaigns', 'backings'])->findOrFail($id);

        $walletTransactions = WalletTransaction::where('user_id', $id)
            ->latest()->take(50)->get();

        $transactions = Transaction::where('user_id', $id)
            ->latest()->take(50)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'balance' => (float) $user->balance,
                    'email_verified_at' => $user->email_verified_at,
                    'suspended_at' => $user->suspended_at,
                    'campaigns_count' => $user->campaigns_count,
                    'backings_count' => $user->backings_count,
                    'created_at' => $user->created_at,
                ],
                'wallet_transactions' => $walletTransactions,
                'transactions' => $transactions,
            ],
        ]);
    }

    public function toggleUserStatus(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->suspended_at) {
            $user->suspended_at = null;
            $message = 'Akun user berhasil diaktifkan kembali.';
        } else {
            $user->suspended_at = now();
            $user->tokens()->delete();
            $message = 'Akun user berhasil dinonaktifkan.';
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'id' => $user->id,
                'suspended_at' => $user->suspended_at,
            ],
        ]);
    }
}
