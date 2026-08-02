<?php

namespace App\Console\Commands;

use App\Models\Reel;
use App\Models\YoutubeChannel;
use App\Services\YoutubeDataApiService;
use Illuminate\Console\Command;

class ImportReelsFromChannels extends Command
{
    protected $signature = 'reels:import-channels {--max=20 : Maksimal video baru per channel}';
    protected $description = 'Crawl channel YouTube berstatus aktif untuk video Shorts terbaru';

    public function handle(YoutubeDataApiService $service): int
    {
        $maxPerChannel = (int) $this->option('max');
        $channels = YoutubeChannel::where('status', 'active')->get();

        if ($channels->isEmpty()) {
            $this->info('Belum ada channel berstatus aktif.');
            return Command::SUCCESS;
        }

        $totalImported = 0;
        $totalSkipped = 0;

        foreach ($channels as $channel) {
            $this->info("Crawling: {$channel->channel_title}");

            try {
                $shorts = $service->getChannelShorts($channel->channel_id, $maxPerChannel);
            } catch (\RuntimeException $e) {
                $this->error("Gagal crawl {$channel->channel_title}: {$e->getMessage()}");
                continue;
            }

            $imported = 0;
            $relevantCount = 0;

            foreach ($shorts as $video) {
                if (Reel::where('youtube_id', $video['youtube_id'])->exists()) {
                    $totalSkipped++;
                    continue;
                }

                // Lapisan aman: walau channel sudah dipercaya, video tanpa
                // sinyal "albion" di judul tetap masuk pending_review dulu,
                // jaga-jaga channel mulai upload konten di luar Albion.
                $isRelevant = str_contains(strtolower($video['title'] ?? ''), 'albion');
                if ($isRelevant) {
                    $relevantCount++;
                }

                $reviewStatus = $isRelevant ? 'auto_approved' : 'pending_review';

                Reel::create([
                    'youtube_url' => "https://www.youtube.com/shorts/{$video['youtube_id']}",
                    'youtube_id' => $video['youtube_id'],
                    'title' => $video['title'],
                    'channel_name' => $video['channel_name'],
                    'thumbnail_url' => $video['thumbnail_url'],
                    'added_by' => null,
                    'is_active' => $reviewStatus === 'auto_approved',
                    'review_status' => $reviewStatus,
                    'found_via' => 'channel_crawl',
                    'youtube_channel_id' => $channel->id,
                ]);

                $imported++;
            }

            $healthScore = count($shorts) > 0
                ? (int) round(($relevantCount / count($shorts)) * 100)
                : null;

            $channel->update([
                'last_crawled_at' => now(),
                'health_score' => $healthScore,
            ]);

            $totalImported += $imported;
        }

        $this->info("Selesai. Imported: {$totalImported}, Skipped (duplikat): {$totalSkipped}");
        return Command::SUCCESS;
    }
}
