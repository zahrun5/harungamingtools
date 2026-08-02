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

            $table->string('name', 100);
            $table->text('notes')->nullable();

            // 10 slot equipment, sama kayak skema lama — tapi sekarang nempel ke
            // baris build tertentu, bukan ke user langsung.
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

            // nandain build mana yang "dipasang" ke profil publik user.
            // Enforced max 1 true per user di level controller (BuildController@activate),
            // bukan DB constraint, karena SQLite gak gampang bikin partial unique index.
            $table->boolean('is_active')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('builds');
    }
};
