<?php

namespace App\Services;

use App\Enums\BackingStatus;
use App\Enums\CampaignStatus;
use App\Mail\NotifikasiEmail;
use App\Models\Backing;
use App\Models\Campaign;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CampaignSettlementService
{
    private const PLATFORM_FEE_PERCENT = 5;

    public function settleExpiredCampaigns(): array
    {
        $campaigns = Campaign::where('status', CampaignStatus::ACTIVE)
            ->whereNull('settled_at')
            ->where(function ($q) {
                $q->where('deadline', '<', now()->startOfDay())
                  ->orWhere('collected_amount', '>=', DB::raw('target_amount'));
            })
            ->get();

        $successCount = 0;
        $failedCount = 0;

        foreach ($campaigns as $campaign) {
            try {
                DB::beginTransaction();

                $collected = (float) $campaign->collected_amount;
                $target = (float) $campaign->target_amount;

                if ($collected >= $target) {
                    self::processDisbursement($campaign);
                    $successCount++;
                } else {
                    self::processRefund($campaign);
                    $failedCount++;
                }

                $campaign->settled_at = now();
                $campaign->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Gagal settle campaign #{$campaign->id}: {$e->getMessage()}", [
                    'campaign_id' => $campaign->id,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return ['success' => $successCount, 'failed' => $failedCount];
    }

    public static function processDisbursement(Campaign $campaign): void
    {
        DB::transaction(function () use ($campaign) {
            $collected = (float) $campaign->collected_amount;
            $platformFee = round($collected * self::PLATFORM_FEE_PERCENT / 100, 2);
            $creatorAmount = $collected - $platformFee;

            $campaign->status = CampaignStatus::SUCCESS;
            $campaign->save();

            $creator = $campaign->user;
            $creator->balance += $creatorAmount;
            $creator->save();

            $disbursementRef = 'DBS-' . strtoupper(uniqid());
            WalletTransaction::create([
                'user_id' => $creator->id,
                'type' => 'disbursement',
                'amount' => $creatorAmount,
                'status' => 'success',
                'reference' => $disbursementRef,
                'description' => 'Pencairan dana kampanye "' . $campaign->title . '" — Rp ' .
                    number_format($creatorAmount, 0, ',', '.'),
            ]);

            $feeRef = 'FEE-' . strtoupper(uniqid());
            WalletTransaction::create([
                'user_id' => $creator->id,
                'type' => 'platform_fee',
                'amount' => $platformFee,
                'status' => 'success',
                'reference' => $feeRef,
                'description' => 'Biaya platform 5% untuk kampanye "' . $campaign->title . '" — Rp ' .
                    number_format($platformFee, 0, ',', '.'),
            ]);

            Transaction::create([
                'user_id' => $creator->id,
                'type' => 'disbursement',
                'amount' => $creatorAmount,
                'status' => 'success',
                'reference' => $disbursementRef,
            ]);

            Transaction::create([
                'user_id' => $creator->id,
                'type' => 'platform_fee',
                'amount' => $platformFee,
                'status' => 'success',
                'reference' => $feeRef,
            ]);

            Notification::create([
                'user_id' => $creator->id,
                'type' => 'disbursement_success',
                'title' => 'Pencairan Dana Berhasil!',
                'body' => 'Kampanye "' . $campaign->title . '" telah mencapai target! Dana Rp ' .
                    number_format($creatorAmount, 0, ',', '.') .
                    ' telah masuk ke saldo Anda (setelah potongan biaya platform 5%).',
                'data' => [
                    'campaign_id' => $campaign->id,
                    'campaign_slug' => $campaign->slug,
                    'amount' => $creatorAmount,
                    'platform_fee' => $platformFee,
                ],
                'created_at' => now(),
            ]);
        });

        try {
            $creator = $campaign->user;
            $collected = (float) $campaign->collected_amount;
            $platformFee = round($collected * self::PLATFORM_FEE_PERCENT / 100, 2);
            $creatorAmount = $collected - $platformFee;

            Mail::to($creator->email)->send(new NotifikasiEmail(
                'Pencairan Dana: ' . $campaign->title,
                'Halo ' . $creator->name . '!',
                'Selamat! Kampanye "' . $campaign->title . '" Anda telah mencapai target pendanaan!'
                    . "\n\nTotal terkumpul: Rp " . number_format($collected, 0, ',', '.')
                    . "\nBiaya platform (5%): Rp " . number_format($platformFee, 0, ',', '.')
                    . "\nDana yang dicairkan: Rp " . number_format($creatorAmount, 0, ',', '.')
                    . "\n\nDana telah masuk ke saldo CoFund Anda dan dapat digunakan untuk campaign berikutnya atau ditarik.",
                'Lihat Kampanye',
                url('/campaigns/' . $campaign->slug)
            ));
        } catch (\Exception $e) {
            Log::warning('Gagal kirim email disbursement: ' . $e->getMessage());
        }
    }

    public static function processRefund(Campaign $campaign): void
    {
        DB::transaction(function () use ($campaign) {
            $campaign->status = CampaignStatus::FAILED;
            $campaign->save();

            $completedBackings = Backing::where('campaign_id', $campaign->id)
                ->where('status', BackingStatus::COMPLETED)
                ->with('user')
                ->get();

            foreach ($completedBackings as $backing) {
                $backer = $backing->user;
                $amount = (float) $backing->amount;

                $backer->balance += $amount;
                $backer->save();

                $backing->status = BackingStatus::REFUNDED;
                $backing->save();

                $refundRef = 'RFD-' . strtoupper(uniqid());

                Transaction::create([
                    'user_id' => $backer->id,
                    'backing_id' => $backing->id,
                    'type' => 'refund',
                    'amount' => $amount,
                    'status' => 'success',
                    'reference' => $refundRef,
                ]);

                WalletTransaction::create([
                    'user_id' => $backer->id,
                    'type' => 'refund',
                    'amount' => $amount,
                    'status' => 'success',
                    'reference' => $refundRef,
                    'description' => 'Refund dana kampanye "' . $campaign->title . '" — Rp ' .
                        number_format($amount, 0, ',', '.'),
                ]);

                Notification::create([
                    'user_id' => $backer->id,
                    'type' => 'refund_success',
                    'title' => 'Pengembalian Dana (Refund)',
                    'body' => 'Kampanye "' . $campaign->title . '" gagal mencapai target. Dana Rp ' .
                        number_format($amount, 0, ',', '.') . ' telah dikembalikan ke saldo Anda.',
                    'data' => [
                        'campaign_id' => $campaign->id,
                        'campaign_slug' => $campaign->slug,
                        'amount' => $amount,
                        'backing_id' => $backing->id,
                    ],
                    'created_at' => now(),
                ]);
            }
        });

        $completedBackings = Backing::where('campaign_id', $campaign->id)
            ->where('status', BackingStatus::COMPLETED)
            ->with('user')
            ->get();

        foreach ($completedBackings as $backing) {
            $backer = $backing->user;
            $amount = (float) $backing->amount;

            try {
                Mail::to($backer->email)->send(new NotifikasiEmail(
                    'Refund Dana: ' . $campaign->title,
                    'Halo ' . ($backer->name ?? 'Backer') . '!',
                    'Kampanye "' . $campaign->title . '" yang Anda dukung gagal mencapai target pendanaan.'
                        . "\n\nDana Anda sebesar Rp " . number_format($amount, 0, ',', '.')
                        . ' telah dikembalikan ke saldo CoFund Anda dan dapat digunakan untuk mendukung kampanye lain.',
                    'Jelajahi Kampanye Lain',
                    url('/campaigns')
                ));
            } catch (\Exception $e) {
                Log::warning('Gagal kirim email refund: ' . $e->getMessage());
            }
        }
    }
}
