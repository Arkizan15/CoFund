<?php

namespace App\Services;

use App\Enums\BackingStatus;
use App\Mail\NotifikasiEmail;
use App\Models\Backing;
use App\Models\Campaign;
use App\Models\CampaignTier;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BackingService
{
    public function processWalletBacking(User $user, Campaign $campaign, float $amount, ?int $tierId): array
    {
        $backing = null;

        DB::transaction(function () use ($user, $campaign, $amount, $tierId, &$backing) {
            $user->balance -= $amount;
            $user->save();

            $backing = Backing::create([
                'user_id' => $user->id,
                'campaign_id' => $campaign->id,
                'tier_id' => $tierId,
                'amount' => $amount,
                'status' => BackingStatus::COMPLETED,
            ]);

            $paymentRef = 'PAY-' . strtoupper(uniqid());
            Transaction::create([
                'user_id' => $user->id,
                'backing_id' => $backing->id,
                'type' => 'payment',
                'amount' => $amount,
                'status' => 'success',
                'reference' => $paymentRef,
            ]);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'payment',
                'amount' => $amount,
                'status' => 'success',
                'reference' => $paymentRef,
                'description' => 'Pendanaan untuk kampanye "' . $campaign->title . '" — Rp ' .
                    number_format($amount, 0, ',', '.'),
            ]);

            if ($tierId) {
                CampaignTier::where('id', $tierId)->decrement('remaining_quota');
            }

            $campaign->increment('collected_amount', $amount);
        });

        $this->sendNotifications($user, $campaign, $amount, $backing);

        if ((float) $campaign->fresh()->collected_amount >= (float) $campaign->target_amount) {
            CampaignSettlementService::processDisbursement($campaign);
        }

        return [
            'backing' => $backing->fresh(),
            'balance' => $user->fresh()->balance,
        ];
    }

    private function sendNotifications(User $user, Campaign $campaign, float $amount, Backing $backing): void
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'backing_success',
                'title' => 'Pendanaan Berhasil!',
                'body' => 'Dana sebesar Rp ' . number_format($amount, 0, ',', '.') . ' untuk "' . $campaign->title . '" telah masuk ke escrow.',
                'data' => [
                    'campaign_id' => $campaign->id,
                    'campaign_slug' => $campaign->slug,
                    'amount' => $amount,
                    'backing_id' => $backing->id,
                ],
                'created_at' => now(),
            ]);

            if ($campaign->user_id !== $user->id) {
                Notification::create([
                    'user_id' => $campaign->user_id,
                    'type' => 'backing_received',
                    'title' => 'Pendanaan Baru Masuk!',
                    'body' => 'Kampanye "' . $campaign->title . '" menerima pendanaan sebesar Rp ' .
                        number_format($amount, 0, ',', '.') .
                        ' dari ' . $user->name . '.',
                    'data' => [
                        'campaign_id' => $campaign->id,
                        'campaign_slug' => $campaign->slug,
                        'amount' => $amount,
                        'backer_name' => $user->name,
                        'backing_id' => $backing->id,
                    ],
                    'created_at' => now(),
                ]);
            }

            Mail::to($user->email)->send(new NotifikasiEmail(
                'Konfirmasi Pendanaan',
                'Halo ' . $user->name . '!',
                'Terima kasih! Pendanaan Anda sebesar Rp ' . number_format($amount, 0, ',', '.') .
                ' untuk kampanye "' . $campaign->title . '" telah berhasil diproses.',
                'Lihat Detail Kampanye',
                url('/campaigns/' . $campaign->slug)
            ));
        } catch (\Exception $e) {
            Log::error('Backing notification/disbursement/response failed', [
                'backing_id' => $backing->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
