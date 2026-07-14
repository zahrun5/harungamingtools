<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // nilai yang valid: MainHand, OffHand, Head, Armor, Shoes, Cape, Bag, Mount, Potion, Food
            // null berarti kategori itu bukan equipment (misal resource/material)
            $table->string('equipment_slot')->nullable();
            $table->index('equipment_slot');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('equipment_slot');
        });
    }
};
