<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            // id (BigIncrements / PK)
            $table->id();

            // user_id (Foreign Key ke tabel users, jenis bigint)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // category_id (Foreign Key ke tabel categories, jenis bigint)
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');

            // title (String)
            $table->string('title');

            // slug (String, Unique)
            $table->string('slug')->unique();

            // description (Text)
            $table->text('description');

            // target_amount (Decimal 15,2)
            $table->decimal('target_amount', 15, 2);

            // collected_amount (Decimal 15,2, default: 0)
            $table->decimal('collected_amount', 15, 2)->default(0);

            // deadline (Date)
            $table->date('deadline');

            // video_url (String, Nullable)
            $table->string('video_url')->nullable();

            // status (Enum: 'draft', 'review', 'active', 'success', 'failed', default: 'draft')
            $table->enum('status', ['draft', 'review', 'active', 'success', 'failed'])->default('draft');

            // Timestamps (created_at & updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};