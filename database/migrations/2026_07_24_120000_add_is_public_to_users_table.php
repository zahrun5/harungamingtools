<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Default true: semua profil yang sudah ada tetap publik seperti sekarang,
            // nggak ada yang tiba-tiba ke-private begitu migration ini jalan.
            $table->boolean('is_public')->default(true)->after('custom_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
};
