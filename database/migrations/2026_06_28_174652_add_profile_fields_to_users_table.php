<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('custom_name')->nullable()->after('name');
            $table->string('avatar_seed')->nullable()->after('custom_name');
            $table->string('avatar_style')->default('pixel-art')->after('avatar_seed');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['custom_name', 'avatar_seed', 'avatar_style']);
        });
    }
};
