<?php

namespace App\Jobs;

use App\Mail\NotifikasiEmail;
use App\Models\Campaign;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DisburseCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const PLATFORM_FEE_PERCENT = 5;

    public Campaign $campaign;

    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $campaign = $this->campaign;
            $collected = (float) $campaign->collected_amount;
            $platformFee = round($collected * self::PLATFORM_FEE_PERCENT / 100, 2);
            $creatorAmount = $collected - $platformFee;

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

            $campaign->settled_at = now();
            $campaign->save();
        });

        try {
            $campaign = $this->campaign;
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
}
