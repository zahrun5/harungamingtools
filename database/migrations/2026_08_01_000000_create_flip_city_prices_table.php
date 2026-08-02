<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flip_city_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();

            // 'Caerleon', 'Lymhurst', ..., atau 'Black Market'
            $table->string('city');

            // 1..5, sinkron sama Item::QUALITY_MAP (1=Normal .. 5=Masterpiece)
            $table->unsignedTinyInteger('quality');

            // Harga sell order termurah aktif (dipakai buat "instant buy")
            $table->unsignedBigInteger('sell_price_min')->nullable();

            // Harga buy order tertinggi aktif (dipakai buat "instant sell")
            $table->unsignedBigInteger('buy_price_max')->nullable();

            $table->timestamp('fetched_at'); // kapan data ini diambil dari AODP
            $table->timestamps();

            // Satu baris unik per kombinasi item+kota+quality — upsert nimpa baris ini
            $table->unique(['item_id', 'city', 'quality'], 'flip_city_prices_unique');

            // Query flip nanti sering filter/scan per kota, jadi diindex terpisah juga
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flip_city_prices');
    }
};
