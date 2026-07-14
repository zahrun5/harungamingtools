<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crafting_station_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crafting_station_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['crafting_station_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crafting_station_category');
    }
};
