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
        Schema::table('reels', function (Blueprint $table) {
            $table->boolean('is_sponsored')->default(false)->after('is_active');
            $table->string('sponsor_name')->nullable()->after('is_sponsored');
            $table->text('sponsor_url')->nullable()->after('sponsor_name');
            $table->unsignedInteger('impressions')->default(0)->after('sponsor_url');
            $table->timestamp('sponsored_at')->nullable()->after('impressions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reels', function (Blueprint $table) {
            $table->dropColumn(['is_sponsored', 'sponsor_name', 'sponsor_url', 'impressions', 'sponsored_at']);
        });
    }
};
