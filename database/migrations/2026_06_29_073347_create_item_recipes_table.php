<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel item_recipes menyimpan recipe crafting semua item Albion Online.
     * Data di-parse dari storage/app/albion/items.xml (ao-bin-dumps).
     *
     * Satu item bisa punya banyak baris (satu per resource bahan).
     * Contoh: T4_2H_BROADSWORD membutuhkan T4_METALBAR (20) + T4_LEATHER (12)
     *         → 2 baris di tabel ini dengan item_api_id yang sama.
     *
     * Enchantment level 0 = item normal (tanpa enchantment)
     * Enchantment level 1-4 = item .1 sampai .4
     */
    public function up(): void
    {
        Schema::create('item_recipes', function (Blueprint $table) {
            $table->id();

            // API ID item yang di-craft, contoh: T4_2H_BROADSWORD, T4_METALBAR
            $table->string('item_api_id');

            // Enchantment level item yang di-craft (0 = normal, 1-4 = enchanted)
            $table->unsignedTinyInteger('enchantment_level')->default(0);

            // API ID resource/bahan yang dibutuhkan, contoh: T4_METALBAR, T4_LEATHER
            $table->string('resource_api_id');

            // Enchantment level resource yang dibutuhkan (0 = normal, 1-4 = enchanted)
            $table->unsignedTinyInteger('resource_enchantment_level')->default(0);

            // Jumlah resource yang dibutuhkan
            $table->unsignedInteger('count');

            // Silver cost untuk crafting (biasanya 0, tapi beberapa item ada silver fee)
            $table->unsignedBigInteger('silver_cost')->default(0);

            // Crafting focus yang dibutuhkan
            $table->unsignedInteger('crafting_focus')->default(0);

            $table->timestamps();

            // Index untuk query cepat berdasarkan item_api_id
            $table->index(['item_api_id', 'enchantment_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_recipes');
    }
};
