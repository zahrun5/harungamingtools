<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('builds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->text('notes')->nullable();
            $table->boolean('is_favorite')->default(false);

            // 10 slot equipment, semua nullable & FK ke tabel items yang udah ada
            // kalau item dihapus dari database, slot ini otomatis null (nullOnDelete)
            // biar build lama gak ikut kehapus
            $table->foreignId('main_hand_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('off_hand_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('head_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('armor_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('shoes_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('cape_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('bag_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('mount_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('potion_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('food_id')->nullable()->constrained('items')->nullOnDelete();

            $table->timestamps();

            // index buat query build favorit per user (dipakai di tab Overview profil)
            $table->index(['user_id', 'is_favorite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('builds');
    }
};
