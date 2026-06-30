<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_localizations', function (Blueprint $table) {
            $table->id();
            $table->string('api_id')->index(); // contoh: T1_FACTION_FOREST_TOKEN_1
            $table->string('locale', 8)->index(); // contoh: EN-US, ID-ID
            $table->string('name');
            $table->timestamps();

            $table->unique(['api_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_localizations');
    }
};
