<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('death_search_cursor', function (Blueprint $table) {
            $table->id();
            $table->string('character_id')->unique();
            $table->unsignedInteger('kills_offset')->default(0);
            $table->unsignedInteger('deaths_offset')->default(0);
            $table->boolean('kills_exhausted')->default(false);
            $table->boolean('deaths_exhausted')->default(false);
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('death_search_cursor');
    }
};
