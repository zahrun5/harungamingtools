<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Nambah kolom identitas Albion Online ke tabel users, buat Tahap 2
     * (planning-profil-publik.md). Semua kolom nullable karena mayoritas user
     * belum punya identitas Albion terverifikasi.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Identitas karakter & server
            $table->string('albion_ign')->nullable()->after('id');
            $table->string('albion_player_id')->nullable()->unique()->after('albion_ign');
            $table->string('albion_server')->nullable()->after('albion_player_id'); // americas | europe | asia

            // Guild & alliance
            $table->string('albion_guild_name')->nullable()->after('albion_server');
            $table->string('albion_alliance_name')->nullable()->after('albion_guild_name');
            $table->string('albion_alliance_tag')->nullable()->after('albion_alliance_name');

            // Avatar (kode asset, BUKAN url — lihat catatan di planning-profil-publik.md)
            $table->string('albion_avatar_code')->nullable()->after('albion_alliance_tag');
            $table->string('albion_avatar_ring')->nullable()->after('albion_avatar_code');

            // Fame & statistik
            $table->unsignedBigInteger('kill_fame')->nullable()->after('albion_avatar_ring');
            $table->unsignedBigInteger('death_fame')->nullable()->after('kill_fame');
            $table->decimal('average_item_power', 8, 2)->nullable()->after('death_fame');
            $table->json('lifetime_statistics')->nullable()->after('average_item_power');
            $table->timestamp('albion_data_refreshed_at')->nullable()->after('lifetime_statistics');

            // Verifikasi
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])
                ->nullable()
                ->after('albion_data_refreshed_at');
            $table->string('verification_code', 10)->nullable()->after('verification_status');
            $table->timestamp('verification_code_expires_at')->nullable()->after('verification_code');
            $table->timestamp('verified_at')->nullable()->after('verification_code_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'albion_ign',
                'albion_player_id',
                'albion_server',
                'albion_guild_name',
                'albion_alliance_name',
                'albion_alliance_tag',
                'albion_avatar_code',
                'albion_avatar_ring',
                'kill_fame',
                'death_fame',
                'average_item_power',
                'lifetime_statistics',
                'albion_data_refreshed_at',
                'verification_status',
                'verification_code',
                'verification_code_expires_at',
                'verified_at',
            ]);
        });
    }
};
