<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('death_events', function (Blueprint $table) {
            $table->id();
            $table->string('character_name')->index();
            $table->string('character_id');
            $table->unsignedBigInteger('event_id')->unique();
            $table->enum('type', ['kill', 'death']);
            $table->timestamp('event_timestamp');
            $table->json('event_data');
            $table->unsignedInteger('total_fame')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('death_events');
    }
};