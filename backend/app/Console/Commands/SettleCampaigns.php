<?php

namespace App\Console\Commands;

use App\Services\CampaignSettlementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SettleCampaigns extends Command
{
    protected $signature = 'campaign:settle';
    protected $description = 'Auto-settle campaigns past their deadline: disburse successful campaigns, refund failed ones';

    public function handle(): int
    {
        $this->info('Memulai proses settlement kampanye...');

        $service = new CampaignSettlementService();
        $result = $service->settleExpiredCampaigns();

        $this->info("Selesai! {$result['success']} disbursement, {$result['failed']} refund.");
        return Command::SUCCESS;
    }
}
