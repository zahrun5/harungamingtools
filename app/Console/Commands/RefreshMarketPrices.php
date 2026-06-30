<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\ItemPrice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RefreshMarketPrices extends Command
{
    /**
     * php artisan market:refresh-prices
     * Isi/refresh cache harga (tabel item_prices) untuk SEMUA item sekaligus,
     * dipecah jadi beberapa request batch (bukan 1 request per item) biar gak
     * kelamaan & gak nge-spam API Albion Online Data Project.
     */
    protected $signature = 'market:refresh-prices {--batch=60 : Jumlah item per request batch}';

    protected $description = 'Warm-up / refresh cache harga semua item di tabel item_prices';

    private const CITIES = ['Caerleon', 'Bridgewatch', 'Fort Sterling', 'Lymhurst', 'Martlock', 'Thetford', 'Brecilien'];

    public function handle(): int
    {
        $batchSize = (int) $this->option('batch');

        // Ambil semua kombinasi api_id + enc yang unik dari tabel items
        $pairs = Item::whereNotNull('api_id')
            ->get(['api_id', 'enc'])
            ->map(fn($i) => ['api_id' => $i->api_id, 'enc' => (int) ($i->enc ?? 0)])
            ->unique(fn($p) => $p['api_id'] . '@' . $p['enc'])
            ->values();

        $total = $pairs->count();
        if ($total === 0) {
            $this->warn('Gak ada item di tabel items. Skip.');
            return self::SUCCESS;
        }

        $this->info("Mau refresh harga buat {$total} kombinasi item+enchant, batch {$batchSize} per request...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $citiesParam = implode(',', self::CITIES);
        $updated = 0;
        $failed  = 0;

        foreach ($pairs->chunk($batchSize) as $chunk) {
            // ID yang dikirim ke API: pakai format apiId@enc kalau enc > 0
            $idsCsv = $chunk->map(fn($p) => $p['enc'] > 0 ? "{$p['api_id']}@{$p['enc']}" : $p['api_id'])
                ->implode(',');

            try {
                $response = Http::timeout(15)->get(
                    "https://west.albion-online-data.com/api/v2/stats/prices/{$idsCsv}",
                    ['locations' => $citiesParam, 'qualities' => 1]
                );

                if ($response->successful()) {
                    $now = now();
                    foreach ($response->json() as $row) {
                        $price = (int) ($row['sell_price_min'] ?? 0);
                        if ($price <= 0) continue; // jangan timpa cache lama pakai 0

                        // item_id dari API formatnya "T4_BAG@1" atau "T4_BAG" (tanpa enchant)
                        [$apiId, $enc] = str_contains($row['item_id'], '@')
                            ? explode('@', $row['item_id'], 2)
                            : [$row['item_id'], '0'];

                        ItemPrice::updateOrCreate(
                            ['item_api_id' => $apiId, 'enc' => (int) $enc, 'city' => $row['city']],
                            ['sell_price_min' => $price, 'fetched_at' => $now]
                        );
                        $updated++;
                    }
                } else {
                    $failed += $chunk->count();
                }
            } catch (\Throwable $e) {
                $failed += $chunk->count();
            }

            $bar->advance($chunk->count());
            usleep(300_000); // jeda dikit antar batch, biar sopan ke API-nya
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Selesai. {$updated} baris harga ke-update/insert.");
        if ($failed > 0) {
            $this->warn("{$failed} item gagal di-fetch (batch error/timeout) — cache lama buat item itu tetap dipertahankan.");
        }

        return self::SUCCESS;
    }
}
