<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    /**
     * PENTING — jalankan ini SETELAH bersihin duplikat build manual lewat tinker.
     * Kalau masih ada user yang punya >1 build, unique constraint di bawah bakal
     * gagal pas migrate. Cara bersihin (contoh, sesuaikan ID build yang mau dibuang):
     *
     *   php artisan tinker
     *   >>> App\Models\Build::where('user_id', 1)->orderByDesc('id')->skip(1)->delete();
     *
     * (Perintah di atas nyimpen 1 build terbaru per user, hapus sisanya)
     */
    public function up(): void
    {
        // 1) Drop index gabungan dulu, karena masih mereferensikan is_favorite
        Schema::table('builds', function (Blueprint $table) {
            $table->dropIndex('builds_user_id_is_favorite_index');
        });

        // 2) Baru drop kolomnya, setelah index yang menahannya sudah hilang
        Schema::table('builds', function (Blueprint $table) {
            $table->dropColumn('is_favorite');
        });

        // 3) Tambahkan unique constraint baru (satu build per user)
        Schema::table('builds', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('builds', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });

        Schema::table('builds', function (Blueprint $table) {
            $table->boolean('is_favorite')->default(false);
        });

        Schema::table('builds', function (Blueprint $table) {
            $table->index(['user_id', 'is_favorite']);
        });
    }
};
