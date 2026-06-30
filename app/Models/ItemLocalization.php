<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ItemLocalization extends Model
{
    protected $fillable = ['api_id', 'locale', 'name'];

    /**
     * Ambil nama item untuk locale tertentu, fallback ke EN-US,
     * fallback terakhir ke null (caller yang decide mau prettifyApiId() atau apa).
     *
     * Di-cache forever karena data ini cuma berubah saat sync manual dijalankan
     * (bukan data yang sering berubah-ubah).
     */
    public static function nameFor(string $apiId, string $locale = 'EN-US'): ?string
    {
        $cacheKey = "item_localization:{$apiId}:{$locale}";

        return Cache::rememberForever($cacheKey, function () use ($apiId, $locale) {
            $name = static::where('api_id', $apiId)
                ->where('locale', $locale)
                ->value('name');

            if ($name) {
                return $name;
            }

            // Fallback ke EN-US kalau locale yang diminta gak ada translation-nya
            if ($locale !== 'EN-US') {
                return static::where('api_id', $apiId)
                    ->where('locale', 'EN-US')
                    ->value('name');
            }

            return null;
        });
    }

    /**
     * Bulk lookup untuk banyak api_id sekaligus (hindari N+1 query),
     * dipakai misalnya saat render daftar item di Telegram bot.
     *
     * @param array<string> $apiIds
     * @return array<string,string> api_id => name
     */
    public static function namesFor(array $apiIds, string $locale = 'EN-US'): array
    {
        $rows = static::whereIn('api_id', $apiIds)
            ->whereIn('locale', array_unique([$locale, 'EN-US']))
            ->get(['api_id', 'locale', 'name']);

        $result = [];

        // Prioritaskan locale yang diminta, fallback EN-US kalau gak ada
        foreach ($apiIds as $apiId) {
            $exact = $rows->first(fn ($r) => $r->api_id === $apiId && $r->locale === $locale);
            $fallback = $rows->first(fn ($r) => $r->api_id === $apiId && $r->locale === 'EN-US');

            $result[$apiId] = $exact?->name ?? $fallback?->name ?? null;
        }

        return $result;
    }
}
