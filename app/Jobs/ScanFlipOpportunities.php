<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\FlipScan;
use App\Models\Item;
use App\Models\ItemPrice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScanFlipOpportunities implements ShouldQueue
{
    use Queueable;

    private const CITIES = ['Caerleon', 'Bridgewatch', 'Fort Sterling', 'Lymhurst', 'Martlock', 'Thetford', 'Brecilien'];
    private const BATCH_SIZE = 50;
    private const AODP_BASE_URL = [
        'americas' => 'https://west.albion-online-data.com',
        'europe'   => 'https://europe.albion-online-data.com',
        'asia'     => 'https://east.albion-online-data.com',
    ];

    public int $timeout = 300; // 5 menit max per job
    public int $tries = 2;

    /**
     * Scan satu leaf category untuk semua server (americas, europe, asia)
     */
    public function __construct(
        public int $leafCategoryId
    ) {}

    public function handle(): void
    {
        $category = Category::find($this->leafCategoryId);
        
        if (!$category) {
            Log::warning('FlipScan: Category not found', ['id' => $this->leafCategoryId]);
            return;
        }

        // Scan untuk setiap server
        foreach (['americas', 'europe', 'asia'] as $server) {
            $this->scanForServer($category, $server);
        }
    }

    private function scanForServer(Category $category, string $server): void
    {
        $items = Item::where('category_id', $category->id)->get();

        if ($items->isEmpty()) {
            return;
        }

        $itemsByApiId = [];
        foreach ($items as $item) {
            $enc = (int) ($item->enc ?? 0);
            $apiIdWithEnc = $enc > 0 ? "{$item->api_id}@{$enc}" : $item->api_id;
            $itemsByApiId[$apiIdWithEnc] = $item;
        }

        $pricesByApiId = $this->fetchBatchPrices(array_keys($itemsByApiId), $server);
        $results = [];
        $now = now();

        foreach ($pricesByApiId as $apiIdWithEnc => $cityPrices) {
            $cityPrices = array_filter($cityPrices, fn ($p) => $p > 0);
            
            if (count($cityPrices) < 2) {
                continue;
            }

            $cityFrom = array_keys($cityPrices, min($cityPrices))[0];
            $cityTo   = array_keys($cityPrices, max($cityPrices))[0];
            $priceFrom = $cityPrices[$cityFrom];
            $priceTo   = $cityPrices[$cityTo];
            $profit = $priceTo - $priceFrom;

            if ($profit <= 0) {
                continue;
            }

            $item = $itemsByApiId[$apiIdWithEnc];

            $results[] = [
                'item_id'    => $item->id,
                'api_id'     => $item->api_id,
                'name'       => $item->name, // Nanti frontend ambil localized_name
                'tier'       => $item->tier,
                'enc'        => $item->enc,
                'img_url'    => "https://render.albiononline.com/v1/item/{$apiIdWithEnc}.png",
                'city_from'  => $cityFrom,
                'price_from' => $priceFrom,
                'city_to'    => $cityTo,
                'price_to'   => $priceTo,
                'profit'     => $profit,
                'percent'    => round(($profit / $priceFrom) * 100, 1),
                'updated_at' => $now->toIso8601String(),
            ];
        }

        usort($results, fn ($a, $b) => $b['profit'] <=> $a['profit']);

        // Simpan ke database dengan hash unik per (category + server)
        $filterHash = hash('sha256', implode('|', [$category->id, 'all', 'all', $server]));

        FlipScan::updateOrCreate(
            ['filter_hash' => $filterHash],
            [
                'sub_category_id' => $category->id,
                'tier'            => null,
                'enchant'         => null,
                'server'          => $server,
                'results'         => $results,
                'result_count'    => count($results),
                'scanned_at'      => $now,
            ]
        );

        Log::info('FlipScan completed', [
            'category' => $category->name,
            'server' => $server,
            'opportunities' => count($results),
        ]);
    }

    private function fetchBatchPrices(array $apiIdsWithEnc, string $server): array
    {
        $baseUrl = self::AODP_BASE_URL[$server] ?? self::AODP_BASE_URL['americas'];
        $citiesParam = implode(',', self::CITIES);
        $pricesByApiId = [];
        $now = now();

        foreach (array_chunk($apiIdsWithEnc, self::BATCH_SIZE) as $chunk) {
            try {
                $response = Http::timeout(15)->get(
                    $baseUrl . '/api/v2/stats/prices/' . implode(',', $chunk),
                    ['locations' => $citiesParam, 'qualities' => 1]
                );

                if (!$response->successful()) {
                    continue;
                }

                $entries = $response->json() ?? [];

                foreach ($entries as $entry) {
                    $itemId = $entry['item_id'] ?? null;
                    $city   = $entry['city'] ?? null;
                    $price  = (int) ($entry['sell_price_min'] ?? 0);

                    if (!$itemId || !$city || $price <= 0) {
                        continue;
                    }

                    $pricesByApiId[$itemId][$city] = $price;

                    // Cache ke item_prices
                    [$baseApiId, $enc] = str_contains($itemId, '@')
                        ? explode('@', $itemId, 2)
                        : [$itemId, 0];

                    try {
                        ItemPrice::updateOrCreate(
                            ['item_api_id' => $baseApiId, 'enc' => (int) $enc, 'city' => $city, 'server' => $server],
                            ['sell_price_min' => $price, 'fetched_at' => $now]
                        );
                    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                        // Race condition, safe to ignore
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('AODP batch fetch failed in job', [
                    'server' => $server,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $pricesByApiId;
    }
}
