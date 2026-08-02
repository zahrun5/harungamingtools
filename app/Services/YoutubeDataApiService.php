<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Import massal video dari 1 channel YouTube, pakai YouTube Data API v3.
 *
 * Kenapa bukan search.list: search.list biayanya 100 unit/panggilan (dari
 * kuota gratis 10.000/hari), sedangkan playlistItems.list & videos.list
 * cuma 1 unit/panggilan. Karena kita cuma butuh "video-video dari channel
 * tertentu", bukan "cari video berdasarkan kata kunci", playlistItems.list
 * jauh lebih murah & lebih terkontrol hasilnya.
 *
 * Catatan soal filter Shorts: YouTube gak nyediain field resmi semacam
 * `isShort`. Proxy yang dipakai di sini adalah DURASI video (<= 3 menit,
 * sesuai batas Shorts resmi sejak Oktober 2024). Ini heuristik, bukan
 * jaminan 100% — video horizontal pendek juga bisa lolos filter durasi.
 * Kalau butuh lebih ketat, bisa ditambah pengecekan lain di kemudian hari.
 */
class YoutubeDataApiService
{
    private const API_BASE = 'https://www.googleapis.com/youtube/v3';

    /** Batas durasi Shorts resmi YouTube (detik) sejak Oktober 2024. */
    private const DEFAULT_MAX_DURATION_SECONDS = 180;

    private readonly ?string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? config('services.youtube.key');
    }

    /**
     * Titik masuk utama: kasih input channel (handle/@handle, link channel,
     * atau channel ID mentah), dapetin balik daftar video yang KEMUNGKINAN
     * Shorts (durasi <= $maxDurationSeconds), siap disimpan ke tabel `reels`.
     *
     * @return array<int, array{
     *   youtube_id: string, title: ?string, channel_name: ?string,
     *   thumbnail_url: ?string, duration_seconds: int
     * }>
     */
    public function getChannelShorts(
        string $channelInput,
        int $max = 10,
        int $maxDurationSeconds = self::DEFAULT_MAX_DURATION_SECONDS,
    ): array {
        $channelId = $this->resolveChannelId($channelInput);
        if (!$channelId) {
            throw new \RuntimeException('Channel gak ketemu. Cek lagi handle/link channel-nya.');
        }

        $uploadsPlaylistId = $this->getUploadsPlaylistId($channelId);
        if (!$uploadsPlaylistId) {
            throw new \RuntimeException('Gagal ambil daftar upload channel ini.');
        }

        // Ambil lebih banyak dari $max, karena sebagian bakal ke-skip
        // pas filter durasi (bukan Shorts / lebih dari 3 menit).
        $candidateIds = $this->getChannelVideoIds($uploadsPlaylistId, $max * 3);
        if (empty($candidateIds)) {
            return [];
        }

        $videos = $this->getVideoDetails($candidateIds);

        $shorts = array_values(array_filter($videos, function ($video) use ($maxDurationSeconds) {
            return $video['duration_seconds'] > 0 && $video['duration_seconds'] <= $maxDurationSeconds;
        }));

        return array_slice($shorts, 0, $max);
    }

    /**
     * Terima berbagai format input channel dan kembalikan channel ID (UC...).
     * Support: link "youtube.com/channel/UCxxx", link/handle "youtube.com/@nama",
     * "@nama" polos, atau channel ID mentah yang udah "UCxxx".
     */
    public function resolveChannelId(string $input): ?string
    {
        $input = trim($input);

        // Udah berupa channel ID mentah.
        if (preg_match('/^UC[a-zA-Z0-9_-]{22}$/', $input)) {
            return $input;
        }

        // Link /channel/UCxxx
        if (preg_match('#youtube\.com/channel/(UC[a-zA-Z0-9_-]{22})#', $input, $m)) {
            return $m[1];
        }

        // Handle: "@nama", atau link "youtube.com/@nama"
        $handle = null;
        if (preg_match('#youtube\.com/@([a-zA-Z0-9_.-]+)#', $input, $m)) {
            $handle = $m[1];
        } elseif (str_starts_with($input, '@')) {
            $handle = substr($input, 1);
        }

        if ($handle) {
            $response = $this->request('channels', [
                'part' => 'id',
                'forHandle' => $handle,
            ]);
            return $response['items'][0]['id'] ?? null;
        }

        // Fallback terakhir: coba anggap ini "custom URL"/username lama.
        if (preg_match('#youtube\.com/c/([a-zA-Z0-9_.-]+)#', $input, $m)) {
            $input = $m[1];
        }
        $response = $this->request('channels', [
            'part' => 'id',
            'forUsername' => $input,
        ]);

        return $response['items'][0]['id'] ?? null;
    }

    /** Ambil ID playlist "uploads" bawaan channel (1 unit). */
    private function getUploadsPlaylistId(string $channelId): ?string
    {
        $response = $this->request('channels', [
            'part' => 'contentDetails',
            'id' => $channelId,
        ]);

        return $response['items'][0]['contentDetails']['relatedPlaylists']['uploads'] ?? null;
    }

    /**
     * Ambil ID video-video terbaru dari playlist uploads, paginated
     * (1 unit per halaman, maksimal 50 video per halaman).
     *
     * @return string[]
     */
    private function getChannelVideoIds(string $uploadsPlaylistId, int $max): array
    {
        $ids = [];
        $pageToken = null;

        do {
            $response = $this->request('playlistItems', array_filter([
                'part' => 'contentDetails',
                'playlistId' => $uploadsPlaylistId,
                'maxResults' => min(50, max(1, $max - count($ids))),
                'pageToken' => $pageToken,
            ]));

            foreach ($response['items'] ?? [] as $item) {
                if (isset($item['contentDetails']['videoId'])) {
                    $ids[] = $item['contentDetails']['videoId'];
                }
            }

            $pageToken = $response['nextPageToken'] ?? null;
        } while ($pageToken && count($ids) < $max);

        return $ids;
    }

    /**
     * Ambil detail video (durasi, judul, channel, thumbnail) buat sekumpulan
     * video ID sekaligus. videos.list bisa nerima sampai 50 ID per panggilan,
     * tetap cuma 1 unit walau ID-nya banyak.
     *
     * @param string[] $videoIds
     * @return array<int, array{youtube_id: string, title: ?string, channel_name: ?string, thumbnail_url: ?string, duration_seconds: int}>
     */
    private function getVideoDetails(array $videoIds): array
    {
        $results = [];

        foreach (array_chunk($videoIds, 50) as $chunk) {
            $response = $this->request('videos', [
                'part' => 'snippet,contentDetails',
                'id' => implode(',', $chunk),
            ]);

            foreach ($response['items'] ?? [] as $item) {
                $snippet = $item['snippet'] ?? [];
                $thumbnails = $snippet['thumbnails'] ?? [];
                $thumbnail = $thumbnails['high']['url']
                    ?? $thumbnails['medium']['url']
                    ?? $thumbnails['default']['url']
                    ?? null;

                $results[] = [
                    'youtube_id' => $item['id'],
                    'title' => $snippet['title'] ?? null,
                    'channel_name' => $snippet['channelTitle'] ?? null,
                    'thumbnail_url' => $thumbnail,
                    'duration_seconds' => $this->parseIso8601Duration($item['contentDetails']['duration'] ?? 'PT0S'),
                ];
            }
        }

        return $results;
    }

    private function parseIso8601Duration(string $iso8601): int
    {
        try {
            $interval = new \DateInterval($iso8601);
        } catch (\Exception) {
            return 0;
        }

        return ($interval->d * 86400) + ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
    }

    /** @return array<string, mixed> */
    private function request(string $endpoint, array $params): array
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('YOUTUBE_API_KEY belum diset di .env (config: services.youtube.key).');
        }

        $response = Http::get(self::API_BASE . '/' . $endpoint, [
            ...$params,
            'key' => $this->apiKey,
        ]);

        if ($response->failed()) {
            $reason = $response->json('error.message', 'Gagal menghubungi YouTube API.');
            throw new \RuntimeException("YouTube API error ({$response->status()}): {$reason}");
        }

        return $response->json() ?? [];
    }
}

