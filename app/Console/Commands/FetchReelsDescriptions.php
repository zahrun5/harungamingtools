<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

/**
 * Fetch YouTube video descriptions for reels that don't have one yet.
 *
 * Why: Gemini needs title + description to accurately classify Albion vs non-Albion.
 *      We use YouTube Data API v3 (50 IDs per call) — cheap, fast.
 *
 * Usage:
 *   php artisan reels:fetch-descriptions              # all pending + auto_approved without desc
 *   php artisan reels:fetch-descriptions --batch=50  # batch size (default 50, max 50 per YouTube API)
 *   php artisan reels:fetch-descriptions --limit=500 # only first 500 reels (for testing)
 *   php artisan reels:fetch-descriptions --dry-run   # don't save, just show what would fetch
 */
class FetchReelsDescriptions extends Command
{
    protected $signature = 'reels:fetch-descriptions
                            {--batch=50 : Videos per YouTube API call (max 50)}
                            {--limit=0 : Max reels to process (0 = no limit)}
                            {--dry-run : Show what would happen, do not save}';

    protected $description = 'Fetch YouTube descriptions for reels (used by Gemini reviewer)';

    public function handle(): int
    {
        $apiKey = env('YOUTUBE_API_KEY');
        if (!$apiKey) {
            $this->error('YOUTUBE_API_KEY not set in .env');
            return self::FAILURE;
        }

        $batch = min((int) $this->option('batch'), 50);
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        // We need to add a column for description if not exists
        if (!$this->columnExists('reels', 'youtube_description')) {
            $this->info('Adding youtube_description column to reels table...');
            if ($dryRun) {
                $this->warn('DRY RUN: would add column');
            } else {
                DB::statement('ALTER TABLE reels ADD COLUMN youtube_description TEXT');
            }
        }
        if (!$this->columnExists('reels', 'youtube_description_fetched_at')) {
            if ($dryRun) {
                $this->warn('DRY RUN: would add youtube_description_fetched_at column');
            } else {
                DB::statement('ALTER TABLE reels ADD COLUMN youtube_description_fetched_at DATETIME');
            }
        }

        // Get reels needing descriptions (skip ones already fetched in last 7 days)
        $query = DB::table('reels')
            ->whereNotNull('youtube_id')
            ->where(function ($q) {
                $q->whereNull('youtube_description_fetched_at')
                  ->orWhere('youtube_description_fetched_at', '<', now()->subDays(7));
            })
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $reels = $query->get(['id', 'youtube_id', 'title']);
        $total = $reels->count();
        $this->info("Found {$total} reels to fetch");

        if ($total === 0) {
            $this->info('Nothing to do');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;
        $processed = 0;

        foreach ($reels->chunk($batch) as $chunk) {
            $ids = $chunk->pluck('youtube_id')->all();
            $response = Http::timeout(30)
                ->get('https://www.googleapis.com/youtube/v3/videos', [
                    'part' => 'snippet',
                    'id' => implode(',', $ids),
                    'key' => $apiKey,
                    'maxResults' => 50,
                ]);

            if (!$response->successful()) {
                $this->newLine();
                $this->error("YouTube API call failed: HTTP {$response->status()}");
                $failed += count($chunk);
                $bar->advance(count($chunk));
                continue;
            }

            $items = $response->json()['items'] ?? [];
            $byId = collect($items)->keyBy('id');

            foreach ($chunk as $reel) {
                $yt = $byId[$reel->youtube_id] ?? null;
                if ($yt) {
                    $desc = $yt['snippet']['description'] ?? '';
                    $title = $yt['snippet']['title'] ?? $reel->title;
                    if (!$dryRun) {
                        DB::table('reels')
                            ->where('id', $reel->id)
                            ->update([
                                'youtube_description' => $desc,
                                'title' => $title, // also refresh title in case changed
                                'youtube_description_fetched_at' => now(),
                            ]);
                    }
                    $success++;
                } else {
                    $failed++;
                }
                $processed++;
                $bar->advance();
            }

            // Respect YouTube API quota: 1 unit per video fetched, free 10K units/day
            // We do ~50 fetches per call, 200 calls = 10000 units. Should be fine.
            usleep(200_000); // small delay between batches
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Success: {$success}, Failed: {$failed}");

        return self::SUCCESS;
    }

    private function columnExists(string $table, string $column): bool
    {
        $columns = DB::select("PRAGMA table_info({$table})");
        foreach ($columns as $col) {
            if ($col->name === $column) return true;
        }
        return false;
    }
}
