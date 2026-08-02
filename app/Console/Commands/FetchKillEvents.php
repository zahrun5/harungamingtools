<?php

namespace App\Console\Commands;

use App\Models\KillEvent;
use App\Services\AlbionApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchKillEvents extends Command
{
    /**
     * php artisan leaderboard:fetch-kill-events
     * php artisan leaderboard:fetch-kill-events --server=asia
     * php artisan leaderboard:fetch-kill-events --range=day --pages=5
     */
    protected $signature = 'leaderboard:fetch-kill-events
        {--server= : Fetch cuma 1 server (americas|europe|asia). Default: semua server}
        {--range=week : Range API (day|week|month)}
        {--pages=3 : Jumlah halaman (offset) yang ditembak per server, buat menembus variasi CDN cache}
        {--limit=51 : Jumlah event per halaman}';

    protected $description = 'Fetch kill events dari events/killfame per server, dedup by EventId, simpan ke kill_events';

    public function handle(AlbionApiService $albion): int
    {
        $servers = $this->option('server')
            ? [$this->option('server')]
            : $albion->supportedServers();

        $range = $this->option('range');
        $pages = (int) $this->option('pages');
        $limit = (int) $this->option('limit');

        $totalNew = 0;
        $totalSeen = 0;

        foreach ($servers as $server) {
            $this->info("Fetching {$server} (range={$range})...");

            for ($page = 0; $page < $pages; $page++) {
                $offset = $page * $limit;

                $events = $albion->getKillFameEvents($server, $range, $limit, $offset);

                if (empty($events)) {
                    $this->line("  offset={$offset}: kosong (mungkin kena cache atau habis data)");
                    continue;
                }

                $totalSeen += count($events);

                foreach ($events as $event) {
                    $eventId = $event['EventId'] ?? null;

                    if (! $eventId) {
                        continue; // event nggak valid, skip
                    }

                    $killer = $event['Killer'] ?? [];
                    $victim = $event['Victim'] ?? [];

                    $created = KillEvent::firstOrCreate(
                        [
                            'event_id' => $eventId,
                            'server' => $server,
                        ],
                        [
                            'killer_id' => $killer['Id'] ?? null,
                            'killer_name' => $killer['Name'] ?? null,
                            'killer_guild_id' => $killer['GuildId'] ?: null,
                            'killer_guild_name' => $killer['GuildName'] ?: null,
                            'killer_equipment' => $killer['Equipment'] ?? null,
                            'victim_id' => $victim['Id'] ?? null,
                            'victim_name' => $victim['Name'] ?? null,
                            'victim_guild_id' => $victim['GuildId'] ?: null,
                            'victim_guild_name' => $victim['GuildName'] ?: null,
                            'kill_fame' => $killer['KillFame'] ?? ($event['TotalFame'] ?? 0),
                            'event_timestamp' => $event['TimeStamp'] ?? null,
                            'fetched_at' => now(),
                        ]
                    );

                    // firstOrCreate: kalau record sudah ada (dedup jalan), wasRecentlyCreated = false
                    if ($created->wasRecentlyCreated) {
                        $totalNew++;
                    }
                }

                $this->line("  offset={$offset}: ".count($events)." event diterima");
            }
        }

        $this->info("Selesai. Event dilihat: {$totalSeen}, event baru disimpan: {$totalNew}.");

        Log::info('FetchKillEvents selesai', [
            'servers' => $servers,
            'range' => $range,
            'total_seen' => $totalSeen,
            'total_new' => $totalNew,
        ]);

        return self::SUCCESS;
    }
}
