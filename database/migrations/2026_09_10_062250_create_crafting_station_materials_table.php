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
        Schema::create('crafting_station_materials', function (Blueprint $table) {
            $table->id();
            $table->string('station_slug');  // 'mage-tower', 'hunters-lodge', dll
            $table->string('material_base'); // 'METALBAR', 'LEATHER', 'CURSEDSKULL'
            $table->unsignedTinyInteger('min_tier')->default(2);  // 2-8
            $table->unsignedTinyInteger('max_tier')->default(8);  // 2-8
            $table->unsignedTinyInteger('max_enchant')->default(4); // 0-4
            $table->string('type')->default('base'); // 'base', 'artifact', 'rune', 'soul', 'crystal'
            $table->timestamps();
            
            $table->unique(['station_slug', 'material_base']);
            $table->index('station_slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crafting_station_materials');
    }
};
