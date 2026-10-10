<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom quality_supported ke crafting_station_materials.
     * Diisi otomatis saat `crafting:scan-materials` berdasarkan
     * apakah kategori item target mendukung sistem kualitas Albion.
     */
    public function up(): void
    {
        Schema::table('crafting_station_materials', function (Blueprint $table) {
            $table->boolean('quality_supported')->default(false)->after('tier');
        });
    }

    public function down(): void
    {
        Schema::table('crafting_station_materials', function (Blueprint $table) {
            $table->dropColumn('quality_supported');
        });
    }
};
