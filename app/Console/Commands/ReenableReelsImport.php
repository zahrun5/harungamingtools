<?php

namespace App\Console\Commands;

use App\Models\Reel;
use Illuminate\Console\Command;

/**
 * Re-enable reels import scheduler with Gemini auto-review.
 *
 * This is a placeholder — the actual implementation would:
 * 1. Re-enable Schedule::command('reels:import-keyword') in routes/console.php
 * 2. Add a chain that auto-reviews new imports via Gemini before they go pending
 *
 * For now, this command just shows the recommended schedule config.
 */
class ReenableReelsImport extends Command
{
    protected $signature = 'reels:reenable-import {--dry-run}';

    protected $description = 'Show schedule config to re-enable reels import with Gemini auto-review';

    public function handle(): int
    {
        $this->info('To re-enable reels import with Gemini auto-review, add to routes/console.php:');
        $this->newLine();

        $snippet = <<<'PHP'

// Re-enabled 2026-10-09 with Gemini auto-review gate
// New imports go through Gemini before hitting pending_review queue
Schedule::command('reels:import-keyword --limit=200')
    ->dailyAt('03:30')
    ->withoutOverlapping()
    ->onOneServer()
    ->then(function () {
        // Auto-review any new imports via Gemini
        \Illuminate\Support\Facades\Artisan::call('reels:review-with-gemini', [
            '--status' => 'pending_review',
            '--batch' => 50,
        ]);
    });

Schedule::command('reels:import-channels')
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->then(function () {
        \Illuminate\Support\Facades\Artisan::call('reels:review-with-gemini', [
            '--status' => 'pending_review',
            '--batch' => 50,
        ]);
    });
PHP;

        $this->line($snippet);
        $this->newLine();

        $this->info('This will:');
        $this->line('  1. Daily at 03:30, import 200 new reels via keyword search');
        $this->line('  2. Immediately auto-review them via Gemini (3.7 Flash Low)');
        $this->line('  3. Non-Albion reels go to rejected; Albion reels to auto_approved');
        $this->line('  4. Same for channel crawl at 04:00');
        $this->newLine();
        $this->warn('Note: requires GEMINI_API_KEY set in .env');

        return self::SUCCESS;
    }
}
