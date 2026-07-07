<?php

namespace App\Console\Commands;

use App\Enums\BackingStatus;
use App\Enums\CampaignStatus;
use App\Mail\NotifikasiEmail;
use App\Models\Backing;
use App\Models\Campaign;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyDeadlineApproaching extends Command
{
    protected $signature = 'campaign:notify-deadline';
    protected $description = 'Notify backers of campaigns whose deadline is H-3 or H-1';

    public function handle(): int
    {
        $this->info('Mengirim notifikasi deadline mendekat...');

        $targetDates = [
            now()->addDays(3)->startOfDay(),
            now()->addDays(1)->startOfDay(),
        ];

        $campaigns = Campaign::where('status', CampaignStatus::ACTIVE)
            ->whereIn('deadline', $targetDates)
            ->get();

        $notified = 0;

        foreach ($campaigns as $campaign) {
            $daysLeft = now()->startOfDay()->diffInDays($campaign->deadline);
            $isH1 = $daysLeft === 1;

            $backers = User::whereIn('id', Backing::where('campaign_id', $campaign->id)
                ->where('status', BackingStatus::COMPLETED)
                ->pluck('user_id')
            )->get();

            foreach ($backers as $backer) {
                try {
                    Notification::create([
                        'user_id' => $backer->id,
                        'type' => 'deadline_approaching',
                        'title' => 'Deadline Kampanye Mendekat!',
                        'body' => "Kampanye \"{$campaign->title}\" akan berakhir dalam {$daysLeft} hari. Jangan lewatkan kesempatan untuk mendukung!",
                        'data' => [
                            'campaign_id' => $campaign->id,
                            'campaign_slug' => $campaign->slug,
                            'days_left' => $daysLeft,
                        ],
                        'created_at' => now(),
                    ]);

                    if ($isH1) {
                        Mail::to($backer->email)->send(new NotifikasiEmail(
                            'Deadline H-1: ' . $campaign->title,
                            'Halo ' . ($backer->name ?? 'Backer') . '!',
                            'Kampanye "' . $campaign->title . '" yang Anda dukung akan berakhir besok!'
                                . "\n\nTotal terkumpul: Rp " . number_format((float) $campaign->collected_amount, 0, ',', '.')
                                . "\nTarget: Rp " . number_format((float) $campaign->target_amount, 0, ',', '.')
                                . "\n\nDukung sekarang sebelum kampanye berakhir!",
                            'Lihat Kampanye',
                            url('/campaigns/' . $campaign->slug)
                        ));
                    }

                    $notified++;
                } catch (\Exception $e) {
                    Log::error("Gagal kirim notifikasi deadline untuk user #{$backer->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Selesai! {$notified} notifikasi terkirim.");
        return Command::SUCCESS;
    }
}
