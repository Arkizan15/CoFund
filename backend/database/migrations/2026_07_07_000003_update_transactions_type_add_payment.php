<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: alter enum to add 'payment'
        // PostgreSQL/SQLite: change column type to string
        DB::statement('ALTER TABLE transactions MODIFY COLUMN type VARCHAR(50) NOT NULL');
    }

    public function down(): void
    {
        // Warning: Rolling back may lose data if types outside the original enum exist
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('topup', 'backing', 'disbursement', 'refund') NOT NULL");
    }
};
