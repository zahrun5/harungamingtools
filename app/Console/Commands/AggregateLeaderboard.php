<?php

namespace App\Console\Commands;

use App\Models\GuildLeaderboardSnapshot;
use App\Models\KillEvent;
use App\Models\PlayerLeaderboardSnapshot;
use App\Services\AlbionApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AggregateLeaderboard extends Command
{
    /**
     * php artisan leaderboard:aggregate
     * php artisan leaderboard:aggregate --period=weekly --top=50
     * php artisan leaderboard:aggregate --server=asia --period=daily
     */
    protected $signature = 'leaderboard:aggregate
        {--server= : Agregasi cuma 1 server. Default: semua server}
        {--period=daily : daily|weekly}
        {--top=100 : Jumlah baris top rank yang disimpan per server per periode}';

    protected $description = 'Agregasi kill_events jadi guild & player leaderboard snapshot (rank, kill_fame, death_fame)';

    public function handle(AlbionApiService $albion): int
    {
        $servers = $this->option('server')
            ? [$this->option('server')]
            : $albion->supportedServers();

        $period = $this->option('period');
        $top = (int) $this->option('top');

        if (! in_array($period, ['daily', 'weekly'], true)) {
            $this->error('Period harus daily atau weekly.');

            return self::FAILURE;
        }

        [$periodStart, $rangeStart, $rangeEnd] = $this->resolvePeriodRange($period);

        foreach ($servers as $server) {
            $this->info("Agregasi {$server} ({$period}, mulai {$periodStart->toDateString()})...");

            $guildCount = $this->aggregateGuilds($server, $period, $periodStart, $rangeStart, $rangeEnd, $top);
            $playerCount = $this->aggregatePlayers($server, $period, $periodStart, $rangeStart, $rangeEnd, $top);

            $this->line("  guild: {$guildCount} baris, player: {$playerCount} baris disimpan");
        }

        $this->info('Agregasi selesai.');

        return self::SUCCESS;
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: Carbon} [periodStart (buat kolom period_start), rangeStart, rangeEnd]
     */
    private function resolvePeriodRange(string $period): array
    {
        if ($period === 'weekly') {
            $start = Carbon::now()->startOfWeek();

            return [$start, $start, $start->copy()->endOfWeek()];
        }

        // daily
        $start = Carbon::now()->startOfDay();

        return [$start, $start, $start->copy()->endOfDay()];
    }

    private function aggregateGuilds(string $server, string $period, Carbon $periodStart, Carbon $rangeStart, Carbon $rangeEnd, int $top): int
    {
        // Kill fame: dijumlah dari sisi killer_guild_id
        $killFame = KillEvent::query()
            ->where('server', $server)
            ->whereNotNull('killer_guild_id')
            ->whereBetween('event_timestamp', [$rangeStart, $rangeEnd])
            ->select('killer_guild_id as guild_id', 'killer_guild_name as guild_name', DB::raw('SUM(kill_fame) as kill_fame'))
            ->groupBy('killer_guild_id', 'killer_guild_name')
            ->get()
            ->keyBy('guild_id');

        // Death fame: dijumlah dari sisi victim_guild_id (fame yang sama dicatat sebagai "kematian" buat guild korban)
        $deathFame = KillEvent::query()
            ->where('server', $server)
            ->whereNotNull('victim_guild_id')
            ->whereBetween('event_timestamp', [$rangeStart, $rangeEnd])
            ->select('victim_guild_id as guild_id', 'victim_guild_name as guild_name', DB::raw('SUM(kill_fame) as death_fame'))
            ->groupBy('victim_guild_id', 'victim_guild_name')
            ->get()
            ->keyBy('guild_id');

        // Gabungkan semua guild_id yang muncul di salah satu sisi
        $allGuildIds = $killFame->keys()->merge($deathFame->keys())->unique();

        $merged = $allGuildIds->map(function ($guildId) use ($killFame, $deathFame) {
            $k = $killFame->get($guildId);
            $d = $deathFame->get($guildId);

            return [
                'guild_id' => $guildId,
                'guild_name' => $k->guild_name ?? $d->guild_name ?? 'Unknown',
                'kill_fame' => (int) ($k->kill_fame ?? 0),
                'death_fame' => (int) ($d->death_fame ?? 0),
            ];
        })->sortByDesc('kill_fame')->values()->take($top);

        $now = now();
        $rank = 0;

        foreach ($merged as $row) {
            $rank++;

            GuildLeaderboardSnapshot::updateOrCreate(
                [
                    'server' => $server,
                    'guild_id' => $row['guild_id'],
                    'period_type' => $period,
                    'period_start' => $periodStart->toDateString(),
                ],
                [
                    'guild_name' => $row['guild_name'],
                    'kill_fame' => $row['kill_fame'],
                    'death_fame' => $row['death_fame'],
                    'rank' => $rank,
                    'computed_at' => $now,
                ]
            );
        }

        return $merged->count();
    }

    private function aggregatePlayers(string $server, string $period, Carbon $periodStart, Carbon $rangeStart, Carbon $rangeEnd, int $top): int
    {
        $killFame = KillEvent::query()
            ->where('server', $server)
            ->whereNotNull('killer_id')
            ->whereBetween('event_timestamp', [$rangeStart, $rangeEnd])
            ->select(
                'killer_id as player_id',
                'killer_name as player_name',
                'killer_guild_id as guild_id',
                'killer_guild_name as guild_name',
                DB::raw('SUM(kill_fame) as kill_fame')
            )
            ->groupBy('killer_id', 'killer_name', 'killer_guild_id', 'killer_guild_name')
            ->get()
            ->keyBy('player_id');

        $deathFame = KillEvent::query()
            ->where('server', $server)
            ->whereNotNull('victim_id')
            ->whereBetween('event_timestamp', [$rangeStart, $rangeEnd])
            ->select('victim_id as player_id', 'victim_name as player_name', DB::raw('SUM(kill_fame) as death_fame'))
            ->groupBy('victim_id', 'victim_name')
            ->get()
            ->keyBy('player_id');

        $allPlayerIds = $killFame->keys()->merge($deathFame->keys())->unique();

        $merged = $allPlayerIds->map(function ($playerId) use ($killFame, $deathFame) {
            $k = $killFame->get($playerId);
            $d = $deathFame->get($playerId);

            return [
                'player_id' => $playerId,
                'player_name' => $k->player_name ?? $d->player_name ?? 'Unknown',
                'guild_id' => $k->guild_id ?? null,
                'guild_name' => $k->guild_name ?? null,
                'kill_fame' => (int) ($k->kill_fame ?? 0),
                'death_fame' => (int) ($d->death_fame ?? 0),
            ];
        })->sortByDesc('kill_fame')->values()->take($top);

        $now = now();
        $rank = 0;

        foreach ($merged as $row) {
            $rank++;

            PlayerLeaderboardSnapshot::updateOrCreate(
                [
                    'server' => $server,
                    'player_id' => $row['player_id'],
                    'period_type' => $period,
                    'period_start' => $periodStart->toDateString(),
                ],
                [
                    'player_name' => $row['player_name'],
                    'guild_id' => $row['guild_id'],
                    'guild_name' => $row['guild_name'],
                    'kill_fame' => $row['kill_fame'],
                    'death_fame' => $row['death_fame'],
                    'rank' => $rank,
                    'computed_at' => $now,
                ]
            );
        }

        return $merged->count();
    }
}
