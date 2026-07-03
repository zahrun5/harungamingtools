<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DailyLoginBonus
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $today = Carbon::today();

            // Cek apakah user sudah dapat bonus hari ini
            if (is_null($user->last_daily_bonus_at) || 
                Carbon::parse($user->last_daily_bonus_at)->lt($today)) {
                
                // Tambah 10 poin dan catat waktu bonus
                $user->points += 10;
                $user->last_daily_bonus_at = now();
                $user->save();

                // Simpan notifikasi ke session biar bisa ditampilkan ke user
                session()->flash('daily_bonus', '+10 poin! Bonus login harian kamu sudah ditambahkan.');
            }
        }

        return $next($request);
    }
}