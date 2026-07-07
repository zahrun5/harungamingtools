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

    public function index()
    {
        return view('mage-tower');
    }

    // Ambil kategori nested (3 level) khusus group='crafting' untuk dropdown
    public function categories()
    {
        $roots = Category::where('group', 'crafting')
            ->whereNull('parent_id')
            ->orderBy('id')
            ->with(['children' => function ($q) {
                $q->where('group', 'crafting')
                  ->orderBy('id')
                  ->with(['children' => function ($q2) {
                      $q2->where('group', 'crafting')->orderBy('id');
                  }]);
            }])
            ->get()
            ->map(fn($root) => [
                'id'       => $root->id,
                'name'     => $root->name,
                'children' => $root->children->map(fn($sub) => [
                    'id'       => $sub->id,
                    'name'     => $sub->name,
                    'children' => $sub->children->map(fn($leaf) => [
                        'id'   => $leaf->id,
                        'name' => $leaf->name,
                    ])->values(),
                ])->values(),
            ]);

        return response()->json($roots);
    }

    // Cari item berdasarkan filter (category_id/tier/enc) — identik logic-nya
    // dengan MarketController::items(), cuma sumber category_id-nya beda pohon.
    public function items(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'tier'        => 'nullable|integer|min:1|max:8',
            'enc'         => 'nullable|integer|min:0|max:4',
        ]);

        $query = Item::with('category');

        if ($request->filled('category_id')) {
            $cat = Category::where('group', 'crafting')->find($request->category_id);
            $ids = $this->getAllDescendantIds($cat);
            $ids[] = $cat->id;
            $query->whereIn('category_id', $ids);
        } else {
            // "All" — tetep batasin ke semua kategori crafting, jangan ambil semua item
            $craftingCategoryIds = Category::where('group', 'crafting')->pluck('id');
            $query->whereIn('category_id', $craftingCategoryIds);
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
        Category::where('group', 'crafting')->findOrFail($categoryId);

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