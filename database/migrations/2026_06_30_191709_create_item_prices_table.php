<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_prices', function (Blueprint $table) {
            $table->id();
            $table->string('item_api_id');
            $table->unsignedTinyInteger('enc')->default(0);
            $table->string('city');
            $table->unsignedBigInteger('sell_price_min')->default(0);
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            // satu baris per kombinasi item + enchant + kota
            $table->unique(['item_api_id', 'enc', 'city']);
            $table->index('item_api_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_prices');
    }
};
