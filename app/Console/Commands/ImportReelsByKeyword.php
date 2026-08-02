<?php

namespace App\Console\Commands;

use App\Models\Reel;
use App\Models\YoutubeChannel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportReelsByKeyword extends Command
{
    protected $signature = 'reels:import-keyword {--limit=200}';
    protected $description = 'Import YouTube Shorts baru berdasarkan keyword pencarian';

    protected array $keywords = [
        // Ekonomi
        'Albion Online gathering',
        'Albion Online refining',
        'Albion Online crafting',
        'Albion Online profit',
        'Albion Online silver farm',
        'Albion Online market flip',

        // PvP
        'Albion Online PvP',
        'Albion Online ZvZ',
        'Albion Online ganking',
        'Albion Online small scale',
        'Albion Online faction warfare',
        'Albion Online arena',

        // PvE / konten spesifik
        'Albion Online Hellgate',
        'Albion Online dungeon',
        'Albion Online Mists',
        'Albion Online Roads of Avalon',
        'Albion Online solo PvE',

        // Build & progression
        'Albion Online build guide',
        'Albion Online beginner guide',
        'Albion Online gameplay',
    ];

    public function handle(): int
    {
        $apiKey = config('services.youtube.key');
        $limit = (int) $this->option('limit');
        $imported = 0;
        $skipped = 0;

        foreach ($this->keywords as $keyword) {
            if ($imported >= $limit) {
                break;
            }

            $this->info("Searching: {$keyword}");

            $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
                'key' => $apiKey,
                'q' => $keyword,
                'part' => 'snippet',
                'type' => 'video',
                'videoDuration' => 'short',
                'order' => 'date',
                'maxResults' => 50,
            ]);

            if (!$response->successful()) {
                $this->error("Gagal fetch keyword: {$keyword}");
                continue;
            }

            $items = $response->json('items', []);

            foreach ($items as $item) {
                if ($imported >= $limit) {
                    break;
                }

                $videoId = $item['id']['videoId'] ?? null;
                if (!$videoId) {
                    continue;
                }

                if (Reel::where('youtube_id', $videoId)->exists()) {
                    $skipped++;
                    continue;
                }

                $snippet = $item['snippet'];
                $channelId = $snippet['channelId'];

                $channel = YoutubeChannel::firstOrCreate(
                    ['channel_id' => $channelId],
                    [
                        'channel_title' => $snippet['channelTitle'],
                        'status' => 'pending',
                        'source' => 'keyword_discovery',
                    ]
                );

                $reviewStatus = $channel->status === 'active' ? 'auto_approved' : 'pending_review';

                Reel::create([
                    'youtube_url' => "https://www.youtube.com/shorts/{$videoId}",
                    'youtube_id' => $videoId,
                    'title' => $snippet['title'],
                    'channel_name' => $snippet['channelTitle'],
                    'thumbnail_url' => $snippet['thumbnails']['high']['url'] ?? null,
                    'added_by' => null,
                    'is_active' => $reviewStatus === 'auto_approved',
                    'review_status' => $reviewStatus,
                    'found_via' => 'keyword_search',
                    'youtube_channel_id' => $channel->id,
                ]);

                $imported++;
            }
        }

        $this->info("Selesai. Imported: {$imported}, Skipped (duplikat): {$skipped}");
        return Command::SUCCESS;
    }
}
