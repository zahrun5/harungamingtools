<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * ===========================================================
 * CraftingController — halaman "Mages Tower"
 * ===========================================================
 * Sama persis alurnya dengan MarketController (index -> categories
 * -> items -> itemDetail -> refreshPrices), BEDANYA cuma satu:
 * categories() di sini di-filter group='crafting' (Weapons, Chest
 * armor, Head armor, Foot armor, Off-hands), bukan group='market'
 * (Wood/Ore/Hide/dll).
 *
 * Tabel & Model yang dipakai (items, item_prices, categories) SAMA
 * dengan MarketController — gak ada tabel baru buat item/harga,
 * cuma kategori yang dipisah lewat kolom `group`.
 *
 * Endpoint admin (saveItems, updateItemCategory, savedItems,
 * recipeItems) SENGAJA TIDAK diduplikat di sini — tetap pakai yang
 * ada di MarketController, karena endpoint itu generic (bisa
 * assign item ke category_id manapun, termasuk kategori crafting
 * yang baru ini).
 */
class CraftingController extends Controller
{
    private const CITIES = ['Caerleon', 'Bridgewatch', 'Fort Sterling', 'Lymhurst', 'Martlock', 'Thetford', 'Brecilien'];

    public function index($station = 'mage-tower')
    {
        $craftingStation = \App\Models\CraftingStation::where('slug', $station)->first();
        $stationName = $craftingStation->name ?? ucwords(str_replace('-', ' ', $station));

        return view('mage-tower', [
            'station'     => $station,
            'stationName' => $stationName,
        ]);
    }

    /**
     * Ambil daftar category_id untuk sebuah station — PERSIS apa yang
     * di-attach ke pivot (tanpa expand ke descendant asli di tabel market,
     * karena itu bisa narik kategori di luar cakupan station, misal
     * "Weapons" narik Bow/Axe/Dagger yang bukan bagian Mage's Tower).
     */
    private function resolveCategoryIds(string $station): array
    {
        $craftingStation = \App\Models\CraftingStation::where('slug', $station)->first();
        if (!$craftingStation) {
            return [];
        }

        return $craftingStation->categories()->pluck('categories.id')->all();
    }


    // Ambil kategori nested untuk dropdown — generic untuk semua station.
    // Kategori yang di-attach ke pivot + semua descendant-nya jadi "whitelist".
    // Tree dibangun dari whitelist itu (bukan dari real tree market yang lebih luas),
    // dan otomatis "loncat" (collapse) kalau sebuah node cuma punya 1 anak —
    // supaya user gak perlu klik kategori pembungkus yang percuma
    // (contoh: "Off-hands" yang isinya cuma "Mage" doang).
    public function categories(string $station = 'mage-tower')
    {
        $craftingStation = \App\Models\CraftingStation::where('slug', $station)->first();
        if (!$craftingStation) {
            return response()->json([]);
        }

        $attachedIds = $craftingStation->categories()->pluck('categories.id')->all();
        if (empty($attachedIds)) {
            return response()->json([]);
        }

        // Whitelist = PERSIS kategori yang di-attach ke pivot (semua level
        // sudah eksplisit ada di sana), TIDAK di-expand ke descendant asli
        // market — supaya "Weapons" gak ikut narik Bow/Axe/Dagger dkk.
        $whitelist = $attachedIds;

        $allCats = Category::whereIn('id', $whitelist)->orderBy('id')->get(['id', 'name', 'parent_id'])->keyBy('id');

        // Anak per parent, dibatasi ke whitelist saja
        $childrenByParent = [];
        foreach ($allCats as $cat) {
            if ($cat->parent_id && $allCats->has($cat->parent_id)) {
                $childrenByParent[$cat->parent_id][] = $cat->id;
            }
        }

        // Node "root" tampilan = kategori attached yang parent-nya TIDAK ada di whitelist
        // (kalau parent-nya ikut ke-attach juga, dia bukan root display, biar gak dobel)
        $displayRootIds = array_filter($attachedIds, function ($id) use ($allCats, $whitelist) {
            $cat = $allCats->get($id);
            return !$cat || !$cat->parent_id || !in_array($cat->parent_id, $whitelist);
        });

        $buildNode = function ($id) use (&$buildNode, &$childrenByParent, $allCats) {
            $cat = $allCats->get($id);
            $childIds = $childrenByParent[$id] ?? [];

            // Auto-collapse: kalau node ini cuma punya 1 anak, tampilkan anaknya
            // langsung sebagai pengganti (loncat terus sampai ketemu percabangan/leaf).
            while (count($childIds) === 1) {
                $id = $childIds[0];
                $cat = $allCats->get($id);
                $childIds = $childrenByParent[$id] ?? [];
            }

            return [
                'id'       => $cat->id,
                'name'     => $cat->name,
                'children' => collect($childIds)->map(fn($cid) => $buildNode($cid))->values(),
            ];
        };

        $roots = collect($displayRootIds)->map(fn($id) => $buildNode($id))->values();

        return response()->json($roots);
    }

    // Cari item berdasarkan filter (category_id/tier/enc) — station-aware
    public function items(Request $request, string $station = 'mage-tower')
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'tier'        => 'nullable|integer|min:1|max:8',
            'enc'         => 'nullable|integer|min:0|max:4',
        ]);

        $stationCategoryIds = $this->resolveCategoryIds($station);

        $query = Item::with('category');

        if ($request->filled('category_id')) {
            $cat = Category::find($request->category_id);
            if (!$cat || !in_array($cat->id, $stationCategoryIds)) {
                return response()->json([]);
            }
            $ids = $this->getAllDescendantIds($cat);
            $ids[] = $cat->id;
            // Batasi ke kategori yang memang milik station ini saja,
            // jangan ikut narik descendant asli di luar cakupan station.
            $ids = array_intersect($ids, $stationCategoryIds);
            $query->whereIn('category_id', $ids);
        } else {
            // "All" — tetep batasin ke kategori milik station ini, jangan ambil semua item
            $query->whereIn('category_id', $stationCategoryIds);
        }

        if ($request->filled('tier')) {
            $query->where('tier', (int) $request->tier);
        }

        if ($request->filled('enc')) {
            $query->where('enc', $request->enc);
        }

        $items = $query->orderBy('tier')->orderBy('name')->get();

        $items = $items->map(function ($item) {
            $enc = (int) ($item->enc ?? 0);
            $apiIdWithEnc = $item->api_id
                ? ($enc > 0 ? "{$item->api_id}@{$enc}" : $item->api_id)
                : null;

            return [
                'id'       => $item->id,
                'name'     => $item->name,
                'api_id'   => $item->api_id,
                'tier'     => $item->tier,
                'enc'      => $item->enc,
                'quality'  => $item->quality,
                'category' => $item->category->name ?? '-',
                'img_url'  => $apiIdWithEnc
                    ? "https://render.albiononline.com/v1/item/{$apiIdWithEnc}.png"
                    : null,
            ];
        });

        return response()->json($items);
    }

    private function getAllDescendantIds(Category $cat): array
    {
        $ids = [];
        $children = Category::where('parent_id', $cat->id)->get();
        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getAllDescendantIds($child));
        }
        return $ids;
    }

    // Detail 1 item + recipe + harga cache — identik dengan itemDetail() di MarketController
    public function itemDetail($id)
    {
        $item = Item::findOrFail($id);
        $encLevel = (int) ($item->enc ?? 0);
        $apiIdWithEnc = $encLevel > 0 ? "{$item->api_id}@{$encLevel}" : $item->api_id;

        $recipes = DB::table('item_recipes')
            ->where('item_api_id', $item->api_id)
            ->where('enchantment_level', $encLevel)
            ->get();

        $resourceApiIds = $recipes->pluck('resource_api_id')->unique()->values();
        $resourceItems  = Item::whereIn('api_id', $resourceApiIds)->get()->keyBy('api_id');

        $mainRecipe = $recipes->groupBy('silver_cost')->first() ?? collect();

        $resources = $mainRecipe->map(function ($r) use ($resourceItems) {
            $resItem   = $resourceItems->get($r->resource_api_id);
            $encSuffix = $r->resource_enchantment_level > 0 ? "@{$r->resource_enchantment_level}" : '';
            return [
                'resource_api_id'            => $r->resource_api_id,
                'resource_enchantment_level' => $r->resource_enchantment_level,
                'count'   => $r->count,
                'name'    => $resItem?->name ?? $r->resource_api_id,
                'item_id' => $resItem?->id,
                'img_url' => "https://render.albiononline.com/v1/item/{$r->resource_api_id}{$encSuffix}.png",
            ];
        })->values();

        $cachedPrices = ItemPrice::where('item_api_id', $item->api_id)
            ->where('enc', $encLevel)
            ->get()
            ->keyBy('city');

        $prices = collect(self::CITIES)->mapWithKeys(
            fn($city) => [$city => (int) ($cachedPrices[$city]->sell_price_min ?? 0)]
        );

        return response()->json([
            'id'        => $item->id,
            'name'      => $item->name,
            'api_id'    => $item->api_id,
            'tier'      => $item->tier,
            'enc'       => $encLevel,
            'img_url'   => "https://render.albiononline.com/v1/item/{$apiIdWithEnc}.png",
            'resources' => $resources,
            'prices'    => $prices,
        ]);
    }

    // Fetch harga terbaru real-time — identik dengan refreshPrices() di MarketController
    public function refreshPrices($id)
    {
        $item = Item::findOrFail($id);
        $enc  = (int) ($item->enc ?? 0);
        $apiIdWithEnc = $enc > 0 ? "{$item->api_id}@{$enc}" : $item->api_id;
        $citiesParam  = implode(',', self::CITIES);

        $data = null;
        try {
            $response = Http::timeout(8)->get(
                "https://west.albion-online-data.com/api/v2/stats/prices/{$apiIdWithEnc}",
                ['locations' => $citiesParam, 'qualities' => 1]
            );
            if ($response->successful()) {
                $data = $response->json();
            }
        } catch (\Throwable $e) {
            $data = null;
        }

        if (is_array($data)) {
            $now = now();
            foreach (self::CITIES as $city) {
                $entry = collect($data)->firstWhere('city', $city);
                $price = (int) ($entry['sell_price_min'] ?? 0);
                if ($price > 0) {
                    ItemPrice::updateOrCreate(
                        ['item_api_id' => $item->api_id, 'enc' => $enc, 'city' => $city],
                        ['sell_price_min' => $price, 'fetched_at' => $now]
                    );
                }
            }
        }

        $cachedPrices = ItemPrice::where('item_api_id', $item->api_id)
            ->where('enc', $enc)
            ->get()
            ->keyBy('city');

        $prices = collect(self::CITIES)->mapWithKeys(
            fn($city) => [$city => (int) ($cachedPrices[$city]->sell_price_min ?? 0)]
        );

        return response()->json([
            'prices' => $prices,
            'stale'  => $data === null,
        ]);
    }

    // Pre-cache harga semua item di 1 kategori — identik dengan refreshCategoryPrices()
    public function refreshCategoryPrices($categoryId)
    {
        Category::findOrFail($categoryId);

        \App\Jobs\RefreshCategoryPricesJob::dispatch((int) $categoryId);

        return response()->json([
            'queued'      => true,
            'category_id' => $categoryId,
        ]);
    }

    // Fetch per-item tunggal — identik dengan refreshItemPriceSingle()
    public function refreshItemPriceSingle($id)
    {
        return $this->refreshPrices($id);
    }
}