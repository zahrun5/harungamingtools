<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('builds', function (Blueprint $table) {
            // JSON: { "head_id": 3, "armor_id": 1, ... } — key = nama kolom slot,
            // value = quality integer 1-5 (1=Normal/default, 2=Good, 3=Outstanding,
            // 4=Excellent, 5=Masterpiece). Item di DB tetap quality Normal semua,
            // kolom ini cuma nentuin quality VISUAL yang dipilih user buat build ini.
            $table->json('qualities')->nullable()->after('food_id');
        });
    }

    public function down(): void
    {
        Schema::table('builds', function (Blueprint $table) {
            $table->dropColumn('qualities');
        });
    }
};
