<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemPrice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

/**
 * ===========================================================
 * RefreshCategoryPricesJob
 * ===========================================================
 * Isinya PERSIS logika lama dari MarketController::refreshCategoryPrices(),
 * cuma dipindah ke sini biar bisa dijalankan lewat QUEUE (background worker),
 * bukan langsung di dalam HTTP request.
 *
 * Kenapa ini penting: proses ini manggil API luar (Albion Data Project)
 * berkali-kali per chunk 60 item + ada usleep(300ms) antar chunk, jadi bisa
 * makan waktu belasan detik sampai semenitan buat kategori yang isinya banyak.
 * Kalau ini dijalankan sinkron di controller (seperti sebelumnya), request
 * itu nyandera 1 worker PHP-FPM/artisan-serve selama itu, dan (karena SQLite
 * cuma 1 writer aktif) bisa juga nge-block request BACA lain yang lagi butuh
 * SQLite di waktu bersamaan (misal fetchItems() punya user).
 *
 * Dengan job ini, controller cuma dispatch() lalu langsung balikin response.
 * Kerjaan berat ini akan dieksekusi oleh proses queue:work terpisah.
 * ===========================================================
 */
class RefreshCategoryPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const CITIES = ['Caerleon', 'Bridgewatch', 'Fort Sterling', 'Lymhurst', 'Martlock', 'Thetford', 'Brecilien'];

    // Kalau gagal (misal API luar down), jangan retry berkali-kali percuma.
    public int $tries = 1;

    // Batas waktu maksimum job ini boleh jalan (detik), biar gak nyangkut selamanya
    // kalau API luar hang. Sesuaikan kalau kategorinya besar & butuh lebih lama.
    public int $timeout = 180;

    public function __construct(private readonly int $categoryId)
    {
    }

    public function handle(): void
    {
        $cat = Category::find($this->categoryId);
        if (!$cat) {
            return; // kategori udah kehapus / ID invalid, gak usah lanjut
        }

        $ids   = $this->getAllDescendantIds($cat);
        $ids[] = $cat->id;

        $items = Item::whereIn('category_id', $ids)
            ->whereNotNull('api_id')
            ->get(['api_id', 'enc'])
            ->unique(fn ($i) => $i->api_id . '@' . (int) ($i->enc ?? 0))
            ->values();

        if ($items->isEmpty()) {
            return;
        }

        $citiesParam = implode(',', self::CITIES);

        // Batch setiap 60 item buat kurangi beban API
        foreach ($items->chunk(60) as $chunk) {
            $idsCsv = $chunk->map(fn ($i) => ($i->enc > 0 ? "{$i->api_id}@{$i->enc}" : $i->api_id))
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
                        if ($price <= 0) {
                            continue;
                        }

                        [$apiId, $enc] = str_contains($row['item_id'], '@')
                            ? explode('@', $row['item_id'], 2)
                            : [$row['item_id'], '0'];

                        ItemPrice::updateOrCreate(
                            ['item_api_id' => $apiId, 'enc' => (int) $enc, 'city' => $row['city']],
                            ['sell_price_min' => $price, 'fetched_at' => $now]
                        );
                    }
                }
            } catch (\Throwable $e) {
                // gagal (timeout/API down) -> biarkan, lanjut ke chunk berikutnya.
                // Cache harga lama tetap dipakai sampai job berikutnya berhasil.
            }

            usleep(300_000); // jeda antar batch, biar gak digangu rate-limit API luar
        }
    }

    // Rekursif ambil semua child category id — sama persis dengan
    // MarketController::getAllDescendantIds()
    private function getAllDescendantIds(Category $cat): array
    {
        $ids      = [];
        $children = Category::where('parent_id', $cat->id)->get();
        foreach ($children as $child) {
            $ids[]  = $child->id;
            $ids    = array_merge($ids, $this->getAllDescendantIds($child));
        }
        return $ids;
    }
}
