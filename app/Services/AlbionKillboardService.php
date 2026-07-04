<?php

namespace App\Services;

use App\Models\DeathEvent;
use App\Models\DeathSearchCursor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AlbionKillboardService
{
    protected string $baseUrl = 'https://gameinfo-sgp.albiononline.com/api/gameinfo';

    protected int $batchSize = 51;

    public function searchPlayer(string $name): ?array
    {
        $response = Http::timeout(10)->get("{$this->baseUrl}/search", ['q' => $name]);

        if (! $response->ok()) {
            Log::warning('Albion search API gagal', ['status' => $response->status()]);
            return null;
        }

        $players = $response->json('players') ?? [];

        foreach ($players as $p) {
            if (strtolower($p['Name']) === strtolower($name)) {
                return $p;
            }
        }

        return $players[0] ?? null;
    }

    public function getRecentEvents(string $characterId, string $characterName, int $limit = 5): array
    {
        $this->ensureCached($characterId, $characterName, 'kill');
        $this->ensureCached($characterId, $characterName, 'death');

        $events = DeathEvent::where('character_id', $characterId)
            ->orderByDesc('event_timestamp')
            ->limit($limit)
            ->get();

        return $events->map(fn ($e) => $this->formatForList($e, $characterName))->toArray();
    }

    public function loadMoreEvents(string $characterId, string $characterName, int $offset, int $limit = 5): array
    {
        $cachedCount = DeathEvent::where('character_id', $characterId)->count();

        if ($offset + $limit > $cachedCount) {
            $this->fetchAndCache($characterId, $characterName, 'kill');
            $this->fetchAndCache($characterId, $characterName, 'death');
        }

        $events = DeathEvent::where('character_id', $characterId)
            ->orderByDesc('event_timestamp')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $events->map(fn ($e) => $this->formatForList($e, $characterName))->toArray();
    }

    public function getEventDetail(int $eventId): ?array
    {
        $event = DeathEvent::where('event_id', $eventId)->first();

        return $event?->event_data;
    }

    protected function formatForList(DeathEvent $e, string $characterName): array
    {
        $data = $e->event_data;
        $isDeath = $e->type === 'death';

        $opponent = $isDeath ? $data['Killer'] : $data['Victim'];
        $self = $isDeath ? $data['Victim'] : $data['Killer'];

        $participantCount = $data['numberOfParticipants'] ?? 1;

        return [
            'event_id' => $e->event_id,
            'type' => $e->type,
            'timestamp' => $e->event_timestamp,
            'self_name' => $self['Name'] ?? $characterName,
            'self_guild' => $self['GuildName'] ?? null,
            'self_ip' => round($self['AverageItemPower'] ?? 0),
            'self_weapon_type' => $self['Equipment']['MainHand']['Type'] ?? null,
            'opponent_name' => $opponent['Name'] ?? '???',
            'opponent_guild' => $opponent['GuildName'] ?? null,
            'opponent_ip' => round($opponent['AverageItemPower'] ?? 0),
            'opponent_weapon_type' => $opponent['Equipment']['MainHand']['Type'] ?? null,
            'fame' => $e->total_fame,
            'fight_type' => $participantCount <= 2 ? 'Solo' : 'Group',
        ];
    }

    protected function ensureCached(string $characterId, string $characterName, string $type): void
    {
        $exists = DeathEvent::where('character_id', $characterId)->where('type', $type)->exists();

        $cursor = DeathSearchCursor::where('character_id', $characterId)->first();
        $isStale = ! $cursor || $cursor->last_fetched_at?->lt(now()->subMinutes(10));

        if (! $exists || $isStale) {
            $this->fetchAndCache($characterId, $characterName, $type);
        }
    }

    protected function fetchAndCache(string $characterId, string $characterName, string $type): void
    {
        $cursor = DeathSearchCursor::firstOrCreate(['character_id' => $characterId]);
        $offsetField = "{$type}s_offset";
        $exhaustedField = "{$type}s_exhausted";

        if ($cursor->$exhaustedField) {
            return;
        }

        $endpoint = $type === 'death' ? 'deaths' : 'kills';

        try {
            $response = Http::timeout(15)->get(
                "{$this->baseUrl}/players/{$characterId}/{$endpoint}",
                ['limit' => $this->batchSize, 'offset' => $cursor->$offsetField]
            );
        } catch (\Throwable $e) {
            Log::error('Albion killboard API error', ['message' => $e->getMessage()]);
            return;
        }

        if (! $response->ok()) {
            Log::warning('Albion killboard API non-200', ['status' => $response->status()]);
            return;
        }

        $events = $response->json() ?? [];

        if (empty($events)) {
            $cursor->update([$exhaustedField => true, 'last_fetched_at' => now()]);
            return;
        }

        foreach ($events as $event) {
            DeathEvent::updateOrCreate(
                ['event_id' => $event['EventId']],
                [
                    'character_name' => $characterName,
                    'character_id' => $characterId,
                    'type' => $type,
                    'event_timestamp' => Carbon::parse($event['TimeStamp']),
                    'event_data' => $event,
                    'total_fame' => $event['TotalVictimKillFame'] ?? 0,
                ]
            );
        }

        $cursor->update([
            $offsetField => $cursor->$offsetField + count($events),
            'last_fetched_at' => now(),
            $exhaustedField => count($events) < $this->batchSize,
        ]);
    }
}