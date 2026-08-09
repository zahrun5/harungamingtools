<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flip_scans', function (Blueprint $table) {
            $table->id();

            // Hash dari sub_category_id + tier + enchant, dipakai buat lookup cooldown/cache
            $table->string('filter_hash', 64)->unique();

            $table->unsignedBigInteger('sub_category_id');
            $table->string('tier')->nullable();    // null = "All"
            $table->string('enchant')->nullable(); // null = "All"

            // Hasil scan yang sudah difilter (profit > 0) & disortir (profit terbesar dulu)
            $table->json('results');

            $table->unsignedInteger('result_count')->default(0);
            $table->timestamp('scanned_at');

            $table->timestamps();

            $table->index('sub_category_id');
            $table->index('scanned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flip_scans');
    }
};
