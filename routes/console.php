<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Schedule::command('market:refresh-prices')
	->everyTwoHours();
Schedule::command('albion:refresh-profiles')
	->daily();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('leaderboard:fetch-kill-events --range=week --pages=3')
    ->everyFifteenMinutes()
    ->withoutOverlapping() // kalau run sebelumnya masih jalan (lambat/API lelet), skip run baru
    ->onOneServer(); // jaga-jaga kalau nanti HGT di-deploy multi-instance
    
    Schedule::command('leaderboard:aggregate --period=weekly')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->onOneServer();

// ─── Flip Scan Background Job ───────────────────────────────────────
// Scan semua leaf categories bertahap untuk flip opportunities.
// Jalan setiap 6 jam karena scan 1 cycle butuh waktu lama (banyak kategori).
Schedule::command('flip:scan-all')
    ->everySixHours()
    ->withoutOverlapping()
    ->onOneServer();

// DIMATIKAN SEMENTARA (2026-08-09) — backlog approve reels masih banyak
// (700+ channel, 11.000+ video pending). Uncomment lagi kalau backlog udah
// beres & mau lanjut auto-import.
// Schedule::command('reels:import-keyword --limit=200')
//     ->dailyAt('03:30')
//     ->withoutOverlapping()
//     ->onOneServer();
//
// Schedule::command('reels:import-channels')
//     ->dailyAt('04:00')
//     ->withoutOverlapping()
//     ->onOneServer();
    
