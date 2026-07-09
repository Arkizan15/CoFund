<?php

namespace App\Console\Commands;

use App\Enums\CampaignStatus;
use App\Jobs\DisburseCampaignJob;
use App\Jobs\RefundBackersJob;
use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckExpiredCampaigns extends Command
{
    protected $signature = 'campaign:check-expired';
    protected $description = 'Check and settle expired campaigns past their deadline';

    public function handle(): int
    {
        $this->info('Memeriksa kampanye yang sudah lewat deadline...');

        $campaigns = Campaign::where('status', CampaignStatus::ACTIVE)
            ->whereNull('settled_at')
            ->where('deadline', '<', now()->startOfDay())
            ->get();

        $disbursed = 0;
        $refunded = 0;

        foreach ($campaigns as $campaign) {
            try {
                DB::beginTransaction();

                $collected = (float) $campaign->collected_amount;
                $target = (float) $campaign->target_amount;

                if ($collected >= $target) {
                    $campaign->status = CampaignStatus::SUCCESS;
                    $campaign->save();

                    DisburseCampaignJob::dispatch($campaign);
                    $disbursed++;
                } else {
                    $campaign->status = CampaignStatus::FAILED;
                    $campaign->save();

                    RefundBackersJob::dispatch($campaign);
                    $refunded++;
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Gagal proses campaign #{$campaign->id}: {$e->getMessage()}", [
                    'campaign_id' => $campaign->id,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info("Selesai! {$disbursed} kampanye sukses (disbursement), {$refunded} kampanye gagal (refund).");
        return Command::SUCCESS;
    }
}
