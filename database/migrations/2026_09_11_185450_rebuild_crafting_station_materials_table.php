<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rebuild crafting_station_materials: ganti dari format "material_base + range"
     * ke per-item langsung, dengan relasi ke items dan categories.
     * Data lama di-drop karena akan di-generate ulang via artisan command.
     */
    public function up(): void
    {
        Schema::dropIfExists('crafting_station_materials');

        Schema::create('crafting_station_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('crafting_stations')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('api_id');           // items.api_id (denormalisasi untuk query cepat)
            $table->unsignedTinyInteger('enc')->default(0);  // enchantment level
            $table->string('tier')->nullable();  // T2, T3, dst
            $table->timestamps();

            $table->unique(['station_id', 'item_id']);
            $table->index(['station_id', 'category_id']);
            $table->index('api_id');
        });
    }

    /**
     * Reverse: kembalikan ke struktur lama.
     */
    public function down(): void
    {
        Schema::dropIfExists('crafting_station_materials');

        Schema::create('crafting_station_materials', function (Blueprint $table) {
            $table->id();
            $table->string('station_slug');
            $table->string('material_base');
            $table->unsignedTinyInteger('min_tier')->default(2);
            $table->unsignedTinyInteger('max_tier')->default(8);
            $table->unsignedTinyInteger('max_enchant')->default(4);
            $table->string('type')->default('base');
            $table->timestamps();

            $table->unique(['station_slug', 'material_base']);
            $table->index('station_slug');
        });
    }
};
