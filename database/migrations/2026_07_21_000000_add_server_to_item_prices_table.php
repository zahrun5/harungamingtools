<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_prices', function (Blueprint $table) {
            $table->string('server', 20)->default('americas')->after('city');
        });

        // SQLite gak bisa langsung ubah unique index yang udah ada, jadi:
        // drop index lama (item_api_id+enc+city) kalau ada, buat yang baru include server.
        // Kalau nama index lamamu beda, sesuaikan di bawah.
        Schema::table('item_prices', function (Blueprint $table) {
            try {
                $table->dropUnique(['item_api_id', 'enc', 'city']);
            } catch (\Throwable $e) {
                // index lama mungkin gak ada / namanya beda, aman diabaikan
            }
            $table->unique(['item_api_id', 'enc', 'city', 'server'], 'item_prices_unique_per_server');
        });
    }

    public function down(): void
    {
        Schema::table('item_prices', function (Blueprint $table) {
            $table->dropUnique('item_prices_unique_per_server');
            $table->dropColumn('server');
        });
    }
};
