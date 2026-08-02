<?php

namespace App\Http\Controllers;

use App\Models\GuildLeaderboardSnapshot;
use App\Models\PlayerLeaderboardSnapshot;
use App\Services\AlbionApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaderboardController extends Controller
{
    /**
     * Rank penanda "di luar top rank yang tersimpan" — bukan rank pasti,
     * murni keputusan produk (lihat planning-leaderboard-guild-2.md).
     */
    private const OUT_OF_RANK = 999;

    public function index(Request $request, AlbionApiService $albion)
    {
        $mode = $request->query('mode', 'guild'); // guild | player
        $mode = in_array($mode, ['guild', 'player'], true) ? $mode : 'guild';

        $period = $request->query('period', 'weekly'); // daily | weekly
        $period = in_array($period, ['daily', 'weekly'], true) ? $period : 'weekly';

        $user = Auth::user();
        $server = $request->query('server', $user?->albion_server ?? 'asia');

        $search = trim((string) $request->query('q', ''));

        $periodStart = $this->resolvePeriodStart($period);

        if ($mode === 'guild') {
            $rows = GuildLeaderboardSnapshot::query()
                ->server($server)
                ->period($period, $periodStart)
                ->topRank()
                ->get();

            $searchResult = $search !== ''
                ? $this->searchGuild($search, $server, $rows, $albion)
                : null;
        } else {
            $rows = PlayerLeaderboardSnapshot::query()
                ->server($server)
                ->period($period, $periodStart)
                ->topRank()
                ->get();

            $searchResult = $search !== ''
                ? $this->searchPlayer($search, $server, $rows, $albion)
                : null;
        }

        return view('leaderboard.index', [
            'mode' => $mode,
            'period' => $period,
            'server' => $server,
            'search' => $search,
            'rows' => $rows,
            'searchResult' => $searchResult,
            'userGuildName' => $user?->albion_guild_name,
            'userIgn' => $user?->albion_ign,
        ]);
    }

    private function resolvePeriodStart(string $period): string
    {
        return $period === 'weekly'
            ? Carbon::now()->startOfWeek()->toDateString()
            : Carbon::now()->startOfDay()->toDateString();
    }

    /**
     * Cari guild: cek dulu di top rank yang tersimpan (match substring nama),
     * kalau nggak ketemu, fallback ke API search langsung (rank = OUT_OF_RANK).
     */
    private function searchGuild(string $query, string $server, $topRows, AlbionApiService $albion): ?array
    {
        $match = $topRows->first(function ($row) use ($query) {
            return stripos($row->guild_name, $query) !== false;
        });

        if ($match) {
            return [
                'in_top_rank' => true,
                'rank' => $match->rank,
                'guild_name' => $match->guild_name,
                'kill_fame' => $match->kill_fame,
                'death_fame' => $match->death_fame,
            ];
        }

        // Fallback: cari langsung ke API, guild results sudah termasuk KillFame/DeathFame
        $result = $albion->generalSearch($query, $server);
        $guild = $result['guilds'][0] ?? null;

        if (! $guild) {
            return null;
        }

        return [
            'in_top_rank' => false,
            'rank' => self::OUT_OF_RANK,
            'guild_name' => $guild['Name'] ?? $query,
            'kill_fame' => $guild['KillFame'] ?? 0,
            'death_fame' => $guild['DeathFame'] ?? 0,
        ];
    }

    /**
     * Cari player: sama pola dengan searchGuild — cek top rank dulu, fallback ke
     * general search API kalau nggak ketemu (butuh detail tambahan via getPlayerDetail
     * karena hasil /search nggak selalu lengkap AvatarRing-nya).
     */
    private function searchPlayer(string $query, string $server, $topRows, AlbionApiService $albion): ?array
    {
        $match = $topRows->first(function ($row) use ($query) {
            return stripos($row->player_name, $query) !== false;
        });

        if ($match) {
            return [
                'in_top_rank' => true,
                'rank' => $match->rank,
                'player_name' => $match->player_name,
                'guild_name' => $match->guild_name,
                'kill_fame' => $match->kill_fame,
                'death_fame' => $match->death_fame,
                'avatar_ring' => $match->avatar_ring,
            ];
        }

        $result = $albion->generalSearch($query, $server);
        $player = $result['players'][0] ?? null;

        if (! $player) {
            return null;
        }

        // Detail tambahan (AvatarRing dkk) — hasil /search kadang kosong di field ini
        $detail = $albion->getPlayerDetail($player['Id'], $server);

        return [
            'in_top_rank' => false,
            'rank' => self::OUT_OF_RANK,
            'player_name' => $detail['Name'] ?? $player['Name'] ?? $query,
            'guild_name' => $detail['GuildName'] ?? $player['GuildName'] ?? null,
            'kill_fame' => $detail['KillFame'] ?? $player['KillFame'] ?? 0,
            'death_fame' => $detail['DeathFame'] ?? $player['DeathFame'] ?? 0,
            'avatar_ring' => $detail['AvatarRing'] ?? null,
        ];
    }
}
