<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kill_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('server', 20); // americas | europe | asia

            $table->string('killer_id')->nullable();
            $table->string('killer_name')->nullable();
            $table->string('killer_guild_id')->nullable();
            $table->string('killer_guild_name')->nullable();
            $table->json('killer_equipment')->nullable();

            $table->string('victim_id')->nullable();
            $table->string('victim_name')->nullable();
            $table->string('victim_guild_id')->nullable();
            $table->string('victim_guild_name')->nullable();

            $table->unsignedBigInteger('kill_fame')->default(0);
            $table->timestamp('event_timestamp')->nullable();
            $table->timestamp('fetched_at')->nullable();

            $table->timestamps();

            // event_id unik per server, bukan global
            $table->unique(['event_id', 'server']);
            $table->index(['server', 'event_timestamp']);
            $table->index(['server', 'killer_guild_id']);
            $table->index(['server', 'killer_id']);
        });

        Schema::create('guild_leaderboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('server', 20);
            $table->string('guild_id');
            $table->string('guild_name');
            $table->string('period_type', 20); // daily | weekly | seasonal
            $table->date('period_start');
            $table->unsignedBigInteger('kill_fame')->default(0);
            $table->unsignedBigInteger('death_fame')->default(0);
            $table->unsignedInteger('rank')->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['server', 'guild_id', 'period_type', 'period_start'], 'guild_snap_unique');
            $table->index(['server', 'period_type', 'period_start', 'rank']);
        });

        Schema::create('player_leaderboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('server', 20);
            $table->string('player_id');
            $table->string('player_name');
            $table->string('guild_id')->nullable();
            $table->string('guild_name')->nullable();
            $table->string('period_type', 20);
            $table->date('period_start');
            $table->unsignedBigInteger('kill_fame')->default(0);
            $table->unsignedBigInteger('death_fame')->default(0);
            $table->unsignedInteger('rank')->nullable();
            $table->string('avatar_ring')->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['server', 'player_id', 'period_type', 'period_start'], 'player_snap_unique');
            $table->index(['server', 'period_type', 'period_start', 'rank']);
        });

        Schema::create('top_build_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('server', 20);
            $table->string('period_type', 20);
            $table->date('period_start');
            $table->json('equipment'); // kombinasi slot: MainHand, OffHand, Head, dst
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->index(['server', 'period_type', 'period_start', 'usage_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('top_build_snapshots');
        Schema::dropIfExists('player_leaderboard_snapshots');
        Schema::dropIfExists('guild_leaderboard_snapshots');
        Schema::dropIfExists('kill_events');
    }
};
