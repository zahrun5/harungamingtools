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
        Schema::table('flip_scans', function (Blueprint $table) {
            $table->string('server', 20)->default('americas')->after('enchant');
            $table->index(['server', 'sub_category_id', 'scanned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flip_scans', function (Blueprint $table) {
            $table->dropIndex(['server', 'sub_category_id', 'scanned_at']);
            $table->dropColumn('server');
        });
    }
};
