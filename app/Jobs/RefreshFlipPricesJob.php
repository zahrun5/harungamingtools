<?php

namespace App\Jobs;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetch harga bulk dari AODP buat semua kota (7 kota Royal + Caerleon)
 * DAN Black Market sekaligus, lalu upsert ke tabel flip_city_prices.
 *
 * PENTING soal SQLite:
 * - Semua baris per batch di-upsert dalam SATU query (bukan loop query
 *   satu-satu), biar gak lama pegang write-lock dan gak macetin
 *   halaman lain (market, refine, dst) yang lagi baca DB bersamaan.
 * - Pastikan SQLite jalan di WAL mode (biasanya di-set sekali di
 *   AppServiceProvider atau lewat `PRAGMA journal_mode=WAL;`), biar
 *   baca & tulis gak saling blokir selama job ini jalan.
 *
 * PENTING soal AODP:
 * - qualities=1,2,3,4,5 dikirim SEKALIGUS dalam satu request, bukan
 *   5 request terpisah — AODP balikin banyak entry (satu per quality)
 *   dari satu call. Jumlah request tetap ceil(item_count / 200) per city-batch.
 * - locations juga digabung dalam satu request (comma-separated),
 *   termasuk 'Black Market', jadi gak perlu request terpisah buat itu.
 */
class RefreshFlipPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Samain dengan CITIES di market_blade.php — sesuaikan kalau beda
    private const CITIES = [
        'Caerleon', 'Martlock', 'Bridgewatch', 'Lymhurst',
        'Fort Sterling', 'Thetford', 'Brecilien',
    ];

    private const QUALITIES = [1, 2, 3, 4, 5];
    private const AODP_BASE = 'https://west.albion-online-data.com'; // sesuaikan server (west/europe/east)
    private const BATCH_SIZE = 200; // limit AODP per request

    public function handle(): void
    {
        $items = Item::query()
            ->whereNotNull('api_id')
            ->get(['id', 'api_id', 'enc']);

        if ($items->isEmpty()) {
            Log::info('[RefreshFlipPricesJob] Tidak ada item dengan api_id, skip.');
            return;
        }

        // Map aodp_id (api_id + @enc kalau perlu) -> item_id, biar gampang dicocokkan balik
        $idMap = $items->mapWithKeys(function (Item $item) {
            $aodpId = $item->enc > 0 ? "{$item->api_id}@{$item->enc}" : $item->api_id;
            return [$aodpId => $item->id];
        });

        $uniqueAodpIds = $idMap->keys()->unique()->values();
        $locations     = implode(',', array_merge(self::CITIES, ['Black Market']));
        $qualities     = implode(',', self::QUALITIES);

        $rows = [];

        foreach ($uniqueAodpIds->chunk(self::BATCH_SIZE) as $batch) {
            $url = self::AODP_BASE . '/api/v2/stats/prices/' . $batch->implode(',');

            try {
                $response = Http::timeout(15)->get($url, [
                    'locations' => $locations,
                    'qualities' => $qualities,
                ]);

                if (! $response->successful()) {
                    Log::warning('[RefreshFlipPricesJob] AODP gagal, batch dilewati.', [
                        'status' => $response->status(),
                    ]);
                    continue;
                }

                foreach ($response->json() ?? [] as $entry) {
                    $itemId = $idMap[$entry['item_id']] ?? null;
                    if (! $itemId) {
                        continue; // aodp_id gak ketemu di map, lewati
                    }

                    $rows[] = [
                        'item_id'        => $itemId,
                        'city'           => $entry['city'],
                        'quality'        => $entry['quality'] ?? 1,
                        'sell_price_min' => $entry['sell_price_min'] ?: null,
                        'buy_price_max'  => $entry['buy_price_max'] ?: null,
                        'fetched_at'     => now(),
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }
            } catch (\Throwable $e) {
                Log::error('[RefreshFlipPricesJob] Exception saat fetch batch AODP.', [
                    'message' => $e->getMessage(),
                ]);
                continue; // lanjut ke batch berikutnya, jangan gagalin seluruh job
            }
        }

        if (empty($rows)) {
            Log::info('[RefreshFlipPricesJob] Tidak ada data harga yang berhasil diambil.');
            return;
        }

        // Upsert SEMUA baris sekaligus per chunk (bukan satu-satu), biar
        // write-lock SQLite cuma kepegang sebentar per chunk.
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('flip_city_prices')->upsert(
                $chunk,
                ['item_id', 'city', 'quality'], // kolom unique buat deteksi konflik
                ['sell_price_min', 'buy_price_max', 'fetched_at', 'updated_at']
            );
        }

        Log::info('[RefreshFlipPricesJob] Selesai. ' . count($rows) . ' baris di-upsert.');
    }
}
