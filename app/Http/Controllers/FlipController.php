<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FlipScan;
use App\Models\Item;
use App\Models\ItemPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FlipController extends Controller
{
    // Cooldown per kombinasi filter (menit). Ubah sesuai kebutuhan.
    private const COOLDOWN_MINUTES = 10;

    // Samain persis sama MarketController biar hasil cache item_prices konsisten.
    private const CITIES = ['Caerleon', 'Bridgewatch', 'Fort Sterling', 'Lymhurst', 'Martlock', 'Thetford', 'Brecilien'];

    private const AODP_BASE_URL = [
        'americas' => 'https://west.albion-online-data.com',
        'europe'   => 'https://europe.albion-online-data.com',
        'asia'     => 'https://east.albion-online-data.com',
    ];

    // Jumlah item per request batch ke AODP (URL gak boleh kepanjangan)
    private const BATCH_SIZE = 50;

    private function aodpBaseUrl(): string
    {
        $server = session('server', 'americas');
        return self::AODP_BASE_URL[$server] ?? self::AODP_BASE_URL['americas'];
    }

    /**
     * Halaman mode Advance flip (view saja, browse harga tetap pakai endpoint market yang ada).
     */
    public function advance()
    {
        return view('flip.advance');
    }

    /**
     * Trigger scan profit untuk 1 kombinasi filter.
     * sub_category_id WAJIB. tier & enchant opsional (null = "All").
     */
    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sub_category_id' => ['required', 'integer', 'exists:categories,id'],
            'tier'            => ['nullable', 'integer', 'min:1', 'max:8'],
            'enchant'         => ['nullable', 'integer', 'min:0', 'max:4'],
        ]);

        $subCategoryId = $validated['sub_category_id'];
        $tier          = $validated['tier'] ?? null;
        $enchant       = $validated['enchant'] ?? null;

        $filterHash = $this->buildFilterHash($subCategoryId, $tier, $enchant);

        // Cek cache/cooldown dulu — kalau masih fresh, langsung balikin tanpa hitung ulang
        $existing = FlipScan::where('filter_hash', $filterHash)->first();

        if ($existing && $existing->scanned_at->gt(now()->subMinutes(self::COOLDOWN_MINUTES))) {
            return response()->json([
                'from_cache'   => true,
                'scanned_at'   => $existing->scanned_at,
                'next_scan_at' => $existing->scanned_at->addMinutes(self::COOLDOWN_MINUTES),
                'result_count' => $existing->result_count,
                'results'      => $existing->results,
            ]);
        }

        $results = $this->computeProfitOpportunities($subCategoryId, $tier, $enchant);

        $scan = FlipScan::updateOrCreate(
            ['filter_hash' => $filterHash],
            [
                'sub_category_id' => $subCategoryId,
                'tier'            => $tier,
                'enchant'         => $enchant,
                'results'         => $results,
                'result_count'    => count($results),
                'scanned_at'      => now(),
            ]
        );

        return response()->json([
            'from_cache'   => false,
            'scanned_at'   => $scan->scanned_at,
            'next_scan_at' => $scan->scanned_at->addMinutes(self::COOLDOWN_MINUTES),
            'result_count' => $scan->result_count,
            'results'      => $scan->results,
        ]);
    }

    /**
     * Ambil hasil scan tersimpan dengan pagination — dipakai buat ganti halaman (10-15/halaman)
     * tanpa hitung ulang di server.
     */
    public function results(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sub_category_id' => ['required', 'integer', 'exists:categories,id'],
            'tier'            => ['nullable', 'integer', 'min:1', 'max:8'],
            'enchant'         => ['nullable', 'integer', 'min:0', 'max:4'],
            'page'            => ['nullable', 'integer', 'min:1'],
            'per_page'        => ['nullable', 'integer', 'min:1', 'max:15'],
        ]);

        $filterHash = $this->buildFilterHash(
            $validated['sub_category_id'],
            $validated['tier'] ?? null,
            $validated['enchant'] ?? null
        );

        $scan = FlipScan::where('filter_hash', $filterHash)->first();

        if (!$scan) {
            return response()->json([
                'message' => 'Belum ada hasil scan untuk filter ini. Klik Scan dulu.',
                'results' => [],
            ], 404);
        }

        $perPage = $validated['per_page'] ?? 15;
        $page    = $validated['page'] ?? 1;

        $paged = collect($scan->results)
            ->forPage($page, $perPage)
            ->values();

        return response()->json([
            'scanned_at'   => $scan->scanned_at,
            'result_count' => $scan->result_count,
            'page'         => $page,
            'per_page'     => $perPage,
            'results'      => $paged,
        ]);
    }

    private function buildFilterHash(int $subCategoryId, ?int $tier, ?int $enchant): string
    {
        return hash('sha256', implode('|', [
            $subCategoryId,
            $tier ?? 'all',
            $enchant ?? 'all',
        ]));
    }

    /**
     * Ambil item dalam scope, fetch harga live ke AODP (batch), hitung selisih
     * harga termurah->termahal antar kota, filter profit>0, sort desc.
     */
    private function computeProfitOpportunities(int $subCategoryId, ?int $tier, ?int $enchant): array
    {
        $query = Item::where('category_id', $subCategoryId);

        if ($tier !== null) {
            $query->where('tier', $tier);
        }
        if ($enchant !== null) {
            $query->where('enc', $enchant);
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            return [];
        }

        // apiIdWithEnc => Item, dipakai buat mapping balik hasil AODP ke item aslinya
        $itemsByApiId = [];
        foreach ($items as $item) {
            $enc = (int) ($item->enc ?? 0);
            $apiIdWithEnc = $enc > 0 ? "{$item->api_id}@{$enc}" : $item->api_id;
            $itemsByApiId[$apiIdWithEnc] = $item;
        }

        $pricesByApiId = $this->fetchBatchPrices(array_keys($itemsByApiId));

        $now = now();
        $server = session('server', 'americas');
        $results = [];

        foreach ($pricesByApiId as $apiIdWithEnc => $cityPrices) {
            // Buang harga 0/kosong, butuh minimal 2 kota buat ada selisih
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
                'item_id'   => $item->id,
                'api_id'    => $item->api_id,
                'name'      => $item->localized_name,
                'tier'      => $item->tier,
                'enc'       => $item->enc,
                'img_url'   => "https://render.albiononline.com/v1/item/{$apiIdWithEnc}.png",
                'city_from' => $cityFrom,
                'price_from'=> $priceFrom,
                'city_to'   => $cityTo,
                'price_to'  => $priceTo,
                'profit'    => $profit,
                'percent'   => round(($profit / $priceFrom) * 100, 1),
                'updated_at'=> $now->toIso8601String(),
            ];
        }

        usort($results, fn ($a, $b) => $b['profit'] <=> $a['profit']);

        return $results;
    }

    /**
     * Fetch harga banyak item sekaligus ke AODP (di-chunk), simpan ke cache item_prices
     * juga, balikin array [apiIdWithEnc => [city => sell_price_min]].
     */
    private function fetchBatchPrices(array $apiIdsWithEnc): array
    {
        $server = session('server', 'americas');
        $citiesParam = implode(',', self::CITIES);
        $now = now();

        $pricesByApiId = [];

        foreach (array_chunk($apiIdsWithEnc, self::BATCH_SIZE) as $chunk) {
            try {
                $response = Http::timeout(15)->get(
                    $this->aodpBaseUrl() . '/api/v2/stats/prices/' . implode(',', $chunk),
                    ['locations' => $citiesParam, 'qualities' => 1]
                );
            } catch (\Throwable $e) {
                \Log::warning('AODP batch fetch exception (flip scan)', [
                    'error' => $e->getMessage(),
                    'chunk_size' => count($chunk),
                ]);
                continue;
            }

            if (!$response->successful()) {
                \Log::warning('AODP batch fetch gagal (flip scan)', ['status' => $response->status()]);
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

                // Isi cache item_prices sekalian, konsisten sama pola refreshPrices()
                [$baseApiId, $enc] = str_contains($itemId, '@')
                    ? explode('@', $itemId, 2)
                    : [$itemId, 0];

                try {
                    ItemPrice::updateOrCreate(
                        ['item_api_id' => $baseApiId, 'enc' => (int) $enc, 'city' => $city, 'server' => $server],
                        ['sell_price_min' => $price, 'fetched_at' => $now]
                    );
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    // Race condition, aman diabaikan
                }
            }
        }

        return $pricesByApiId;
    }
}
