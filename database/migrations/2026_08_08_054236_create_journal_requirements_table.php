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
        Schema::create('journal_requirements', function (Blueprint $table) {
            $table->id();
            // Nama tampilan journal, sama persis dengan crafting_stations.journal_name
            // (contoh: "Fletcher's Journal", "Imbuer's Journal")
            $table->string('journal_name');
            $table->unsignedTinyInteger('tier');
            // uniquename asli dari items.xml, contoh: T4_JOURNAL_HUNTER
            $table->string('unique_name')->nullable();
            $table->unsignedInteger('max_fame');
            $table->decimal('base_loot_amount', 10, 4)->nullable();
            $table->timestamps();

            $table->unique(['journal_name', 'tier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_requirements');
    }
};
