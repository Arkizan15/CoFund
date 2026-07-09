<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'top_up'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type ENUM('top_up','payment','refund','disbursement','platform_fee') NOT NULL DEFAULT 'top_up'");
    }
};
