<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Salin custom_name yang sudah ada ke username
        DB::table('users')->whereNotNull('custom_name')->orderBy('id')->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update(['username' => $user->custom_name]);
        });

        // 2. Generate username buat user yang belum punya (custom_name kosong)
        DB::table('users')->whereNull('username')->orderBy('id')->each(function ($user) {
            $base = 'user' . $user->id;
            DB::table('users')->where('id', $user->id)->update(['username' => $base]);
        });
    }

    public function down(): void
    {
        // Tidak perlu revert data, kolom di-drop di migration add_username
    }
};
