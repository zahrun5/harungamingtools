<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YoutubeOembedService
{
    protected const OEMBED_ENDPOINT = 'https://www.youtube.com/oembed';

    /**
     * Ambil video ID dari berbagai format link YouTube:
     * - https://www.youtube.com/watch?v=XXXXXXXXXXX
     * - https://youtu.be/XXXXXXXXXXX
     * - https://www.youtube.com/shorts/XXXXXXXXXXX
     * - https://www.youtube.com/embed/XXXXXXXXXXX
     */
    public function extractVideoId(string $url): ?string
    {
        $pattern = '/(?:youtu\.be\/|v=|\/embed\/|\/shorts\/)([a-zA-Z0-9_-]{11})/';

        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Panggil oEmbed endpoint YouTube, tidak butuh API key.
     * Return null kalau video tidak ditemukan / private / dihapus.
     *
     * @return array{title: ?string, channel_name: ?string, thumbnail_url: ?string}|null
     */
    public function fetch(string $url): ?array
    {
        try {
            $response = Http::timeout(8)->get(self::OEMBED_ENDPOINT, [
                'url'    => $url,
                'format' => 'json',
            ]);

            if ($response->failed()) {
                Log::warning('YoutubeOembedService: gagal fetch metadata', [
                    'url'    => $url,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $data = $response->json();

            return [
                'title'         => $data['title'] ?? null,
                'channel_name'  => $data['author_name'] ?? null,
                'thumbnail_url' => $data['thumbnail_url'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('YoutubeOembedService: exception saat fetch metadata', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Gabungan: extract ID + fetch metadata sekaligus.
     * Dipakai langsung di controller store().
     *
     * @return array{youtube_id: string, title: ?string, channel_name: ?string, thumbnail_url: ?string}|null
     */
    public function resolve(string $url): ?array
    {
        $videoId = $this->extractVideoId($url);

        if (!$videoId) {
            return null;
        }

        $meta = $this->fetch($url) ?? [];

        return [
            'youtube_id'    => $videoId,
            'title'         => $meta['title'] ?? null,
            'channel_name'  => $meta['channel_name'] ?? null,
            'thumbnail_url' => $meta['thumbnail_url'] ?? null,
        ];
    }
}
