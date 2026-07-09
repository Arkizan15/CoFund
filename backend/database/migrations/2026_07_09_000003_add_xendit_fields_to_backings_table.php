<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backings', function (Blueprint $table) {
            $table->string('external_id', 100)->nullable()->unique()->after('status');
            $table->text('invoice_url')->nullable()->after('external_id');
        });
    }

    public function down(): void
    {
        Schema::table('backings', function (Blueprint $table) {
            $table->dropColumn(['external_id', 'invoice_url']);
        });
    }
};
