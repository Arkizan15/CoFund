<?php

namespace App\Jobs;

use App\Enums\BackingStatus;
use App\Mail\NotifikasiEmail;
use App\Models\Backing;
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

class RefundBackersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Campaign $campaign;

    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function handle(): void
    {
        $campaign = $this->campaign;

        $completedBackings = Backing::where('campaign_id', $campaign->id)
            ->where('status', BackingStatus::COMPLETED)
            ->with('user')
            ->get();

        DB::transaction(function () use ($campaign, $completedBackings) {
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

            $campaign->settled_at = now();
            $campaign->save();
        });

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
