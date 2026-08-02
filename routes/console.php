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

Schedule::command('reels:import-keyword --limit=200')
    ->dailyAt('03:30')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('reels:import-channels')
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->onOneServer();
    
 
Schedule::job(new \App\Jobs\RefreshFlipPricesJob)
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();