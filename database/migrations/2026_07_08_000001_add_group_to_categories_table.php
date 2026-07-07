<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Nambahin kolom `group` di tabel categories, buat misahin
     * pohon kategori "market" (resource gathering: Wood/Ore/Hide/dll,
     * dipakai di halaman /market) sama pohon kategori "crafting"
     * (Weapons/Chest armor/dll, dipakai di halaman /mages-tower).
     *
     * Semua kategori LAMA otomatis di-set 'market' di step down(),
     * biar halaman market gak keganggu sama sekali.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('group')->default('market')->after('parent_id');
        });

        // Jaga-jaga: pastikan semua row lama (yang sudah ada sebelum migration ini)
        // eksplisit ke-set 'market', bukan cuma mengandalkan default kolom.
        DB::table('categories')->update(['group' => 'market']);
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};
