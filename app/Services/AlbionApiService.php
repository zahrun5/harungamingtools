<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlbionApiService
{
    /**
     * Domain gameinfo API per server. Beda server = beda base URL,
     * bukan cuma parameter — lihat catatan di planning-profil-publik.md.
     */
    private const DOMAINS = [
        'americas' => 'https://gameinfo.albiononline.com',
        'europe'   => 'https://gameinfo-ams.albiononline.com',
        'asia'     => 'https://gameinfo-sgp.albiononline.com',
    ];

    /**
     * Resolve nama server ('americas' | 'europe' | 'asia') ke base URL API-nya.
     *
     * @throws \InvalidArgumentException kalau nama server nggak dikenali
     */
    public function resolveDomain(string $server): string
    {
        $server = strtolower($server);

        if (! isset(self::DOMAINS[$server])) {
            throw new \InvalidArgumentException("Server Albion tidak dikenali: {$server}");
        }

        return self::DOMAINS[$server];
    }

    /**
     * Nama semua server yang didukung, buat di-loop scheduler/command.
     *
     * @return string[]
     */
    public function supportedServers(): array
    {
        return array_keys(self::DOMAINS);
    }

    /**
     * Cari player berdasarkan IGN persis (case-insensitive) di server tertentu.
     * Return data player pertama yang namanya cocok PERSIS, bukan hasil fuzzy
     * search pertama — API-nya suka ngasih banyak hasil mirip (lihat contoh
     * response waktu search "sodagarpelit" balikin banyak nama "Laran*").
     *
     * @return array{Id: string, Name: string, GuildName: ?string, ...}|null
     */
    public function searchPlayer(string $ign, string $server): ?array
    {
        $domain = $this->resolveDomain($server);

        $response = Http::timeout(10)->get("{$domain}/api/gameinfo/search", [
            'q' => $ign,
        ]);

        if (! $response->successful()) {
            Log::warning('AlbionApiService: search gagal', [
                'ign' => $ign,
                'server' => $server,
                'status' => $response->status(),
            ]);

            return null;
        }

        $players = $response->json('players', []);

        foreach ($players as $player) {
            if (isset($player['Name']) && strcasecmp($player['Name'], $ign) === 0) {
                return $player;
            }
        }

        return null; // ketemu hasil mirip tapi nggak ada yang PERSIS sama
    }

    /**
     * General search — dipakai fitur pencarian player/guild di halaman leaderboard
     * & statistik player. Beda dari searchPlayer(): ini balikin SEMUA hasil mentah
     * (guilds[] & players[]) tanpa filter exact-match, biar UI yang nampilin pilihan.
     *
     * @return array{guilds: array, players: array}
     */
    public function generalSearch(string $query, string $server): array
    {
        $domain = $this->resolveDomain($server);

        $response = Http::timeout(10)->get("{$domain}/api/gameinfo/search", [
            'q' => $query,
        ]);

        if (! $response->successful()) {
            Log::warning('AlbionApiService: generalSearch gagal', [
                'query' => $query,
                'server' => $server,
                'status' => $response->status(),
            ]);

            return ['guilds' => [], 'players' => []];
        }

        return [
            'guilds' => $response->json('guilds', []),
            'players' => $response->json('players', []),
        ];
    }

    /**
     * Ambil detail lengkap seorang player (termasuk LifetimeStatistics)
     * berdasarkan player_id yang sudah diketahui.
     *
     * @return array{
     *     Name: string,
     *     GuildName: ?string,
     *     AllianceName: ?string,
     *     AllianceTag: ?string,
     *     Avatar: ?string,
     *     AvatarRing: ?string,
     *     KillFame: int,
     *     DeathFame: int,
     *     AverageItemPower: float,
     *     LifetimeStatistics: array,
     * }|null
     */
    public function getPlayerDetail(string $playerId, string $server): ?array
    {
        $domain = $this->resolveDomain($server);

        $response = Http::timeout(10)->get("{$domain}/api/gameinfo/players/{$playerId}");

        if (! $response->successful()) {
            Log::warning('AlbionApiService: getPlayerDetail gagal', [
                'player_id' => $playerId,
                'server' => $server,
                'status' => $response->status(),
            ]);

            return null;
        }

        return $response->json();
    }

    /**
     * Ambil raw kill events dari endpoint events/killfame.
     *
     * PENTING (lihat planning-leaderboard-guild-2.md): endpoint ini kena CDN cache
     * yang tidak konsisten — request dengan parameter identik bisa balikin jumlah
     * event yang jauh berbeda antar waktu. Method ini SENGAJA tidak mencoba
     * "menjamin" data lengkap dalam 1 panggilan; itu tanggung jawab command yang
     * memanggilnya berulang dengan jeda + dedup by EventId.
     *
     * events/guildfame dan events/playerfame TIDAK dipakai sama sekali karena
     * terbukti selalu kosong di semua range (day/week/month) — sudah dites manual.
     *
     * @return array<int, array> list of raw event objects (bisa kosong)
     */
    public function getKillFameEvents(string $server, string $range = 'week', int $limit = 51, int $offset = 0): array
    {
        $domain = $this->resolveDomain($server);

        $response = Http::timeout(15)->get("{$domain}/api/gameinfo/events/killfame", [
            'range' => $range,
            'limit' => $limit,
            'offset' => $offset,
        ]);

        if (! $response->successful()) {
            Log::warning('AlbionApiService: getKillFameEvents gagal', [
                'server' => $server,
                'range' => $range,
                'limit' => $limit,
                'offset' => $offset,
                'status' => $response->status(),
            ]);

            return [];
        }

        $events = $response->json();

        return is_array($events) ? $events : [];
    }

    /**
     * Helper: dari detail player mentah (hasil getPlayerDetail), susun array
     * siap-pakai buat di-simpan ke kolom User. Dipisah dari getPlayerDetail()
     * biar gampang dites/dipanggil ulang tanpa hit API lagi.
     */
    public function mapToUserAttributes(array $detail): array
    {
        return [
            'albion_guild_name'        => $detail['GuildName'] ?: null,
            'albion_alliance_name'     => $detail['AllianceName'] ?: null,
            'albion_alliance_tag'      => $detail['AllianceTag'] ?: null,
            'albion_avatar_code'       => $detail['Avatar'] ?: null,
            'albion_avatar_ring'       => $detail['AvatarRing'] ?: null,
            'kill_fame'                => $detail['KillFame'] ?? 0,
            'death_fame'               => $detail['DeathFame'] ?? 0,
            'average_item_power'       => $detail['AverageItemPower'] ?? 0,
            'lifetime_statistics'      => $detail['LifetimeStatistics'] ?? null,
            'albion_data_refreshed_at' => now(),
        ];
    }
}
