<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;



class MarketController extends Controller
{
    // Daftar kota yang dipakai buat cache harga (samain urutannya sama CITIES di frontend)
    private const CITIES = ['Caerleon', 'Bridgewatch', 'Fort Sterling', 'Lymhurst', 'Martlock', 'Thetford', 'Brecilien'];

/**
	 * ===========================================================
	 * PATCH v2 - MarketController.php
	 * Fix: pola enchant resource pakai "_LEVELx" (bukan "@x" seperti equipment)
	 * ===========================================================
	 */
	
	// --- KONSTANTA (taruh di atas class, sebelum method index()) ---
	
	private const TIER_PREFIX = [
	    1 => "Beginner's", 2 => "Novice's", 3 => "Journeyman's", 4 => "Adept's",
	    5 => "Expert's", 6 => "Master's", 7 => "Grandmaster's", 8 => "Elder's",
	];
	
	// Key = kode di api_id (PERSIS dari hasil query kamu: ROCK, ORE, HIDE, FIBER, WOOD)
	private const RAW_RESOURCE_NAMES = [
	    'WOOD'  => [1=>'Rough Logs',2=>'Birch Logs',3=>'Chestnut Logs',4=>'Pine Logs',5=>'Cedar Logs',6=>'Bloadoak Logs',7=>'Ashenbark Logs',8=>'Whitewood Logs'],
	    'ROCK'  => [1=>'Rough Stone',2=>'Limestone',3=>'Sandstone',4=>'Travertine',5=>'Granite',6=>'Slate',7=>'Basalt',8=>'Marble'],
	    'HIDE'  => [1=>'Scraps of Hide',2=>'Rugged Hide',3=>'Thin Hide',4=>'Medium Hide',5=>'Heavy Hide',6=>'Robust Hide',7=>'Thick Hide',8=>'Resilient Hide'],
	    'ORE'   => [1=>'Scraps of Ore',2=>'Copper Ore',3=>'Tin Ore',4=>'Iron Ore',5=>'Titanium Ore',6=>'Runite Ore',7=>'Meteorite Ore',8=>'Adamantium Ore'],
	    'FIBER' => [1=>'Scraps of Fiber',2=>'Cotton',3=>'Flax',4=>'Hemp',5=>'Skyflower',6=>'Redleaf Cotton',7=>'Sunflax',8=>'Ghost Hemp'],
	];
	
	// Max enchant level VALID per resource (dari hasil query kamu)
	private const RAW_RESOURCE_MAX_ENC = [
	    'WOOD' => 4, 'ROCK' => 3, 'HIDE' => 4, 'ORE' => 4, 'FIBER' => 4,
	];
	
	private const REFINED_BASE_NAME = [
	    'WOOD' => 'Plank', 'ROCK' => 'Stone Block', 'HIDE' => 'Leather',
	    'ORE'  => 'Metal Bar', 'FIBER' => 'Cloth',
	];
	
	private const SPECIAL_LEVEL_NAMES = [
	    'FISHSAUCE_LEVEL1' => 'Basic Fish Sauce',
	    'FISHSAUCE_LEVEL2' => 'Fancy Fish Sauce',
	    'FISHSAUCE_LEVEL3' => 'Special Fish Sauce',
	];
	
	// --- GANTI prettifyApiId() LAMA DENGAN INI ---
	// Sekarang return array [name, enc] sekaligus, soalnya _LEVELx nentuin enc juga,
	// bukan cuma nama. Method lama cuma return string, jadi pemanggilnya juga perlu disesuaikan.
	
	private function parseApiId(string $apiId): array
	{
    // 0) Cek dulu ke ItemLocalization - sumber resmi & paling akurat
    $locName = \App\Models\ItemLocalization::nameFor($apiId, 'EN-US');
    if ($locName) {
        $enc = 0;
        if (preg_match('/_LEVEL(\d)$/', $apiId, $m)) {
            $enc = (int) $m[1];
        }
        return ['name' => $locName, 'enc' => $enc];
    }
	    $withoutTier = preg_replace('/^T\d_/', '', $apiId);
	
	    // 0) Special non-tiered names (Fish Sauce dkk) - levelnya bukan enchant, item beda total
	    if (isset(self::SPECIAL_LEVEL_NAMES[$withoutTier])) {
	        return ['name' => self::SPECIAL_LEVEL_NAMES[$withoutTier], 'enc' => 0];
	    }
	
	    $parts = explode('_', $apiId);
	    $tier  = null;
	    if (isset($parts[0]) && preg_match('/^T(\d)$/', $parts[0], $m)) {
	        $tier = (int) $m[1];
	        array_shift($parts);
	    }
	    if (isset($parts[0]) && $parts[0] === '2H') {
	        array_shift($parts);
	    }
	
	    // 1) Cek suffix _LEVEL{n} di parts terakhir -> ini enchant resource, BUKAN bagian nama
	    $enc = 0;
	    if (!empty($parts) && preg_match('/^LEVEL(\d)$/', end($parts), $m)) {
	        $enc = (int) $m[1];
	        array_pop($parts);
	    }
	
	    $key = strtoupper(implode('_', $parts)); // RAW resource code: ROCK, ORE, HIDE, FIBER, WOOD
	
	    // 2) RAW resource (gathering)
	    if ($tier && isset(self::RAW_RESOURCE_NAMES[$key])) {
	        return [
	            'name' => self::RAW_RESOURCE_NAMES[$key][$tier] ?? ucwords(strtolower($key)),
	            'enc'  => $enc,
	        ];
	    }
	
	    // 3) REFINED resource (PLANK, METALBAR, LEATHER, CLOTH) - ikut prefix tier biasa
	    $refinedCodeMap = ['PLANK' => 'WOOD', 'METALBAR' => 'ORE', 'LEATHER' => 'HIDE', 'CLOTH' => 'FIBER'];
	    if ($tier && isset($refinedCodeMap[$key])) {
	        $base = self::REFINED_BASE_NAME[$refinedCodeMap[$key]];
	        return ['name' => self::TIER_PREFIX[$tier] . ' ' . $base, 'enc' => $enc];
	    }
	
	    // 4) Equipment / item umum -> prefix tier standar, enchant pakai @x (sudah ditangani caller)
	    $name = ucwords(strtolower(implode(' ', $parts)));
	    if ($tier && isset(self::TIER_PREFIX[$tier])) {
	        $name = self::TIER_PREFIX[$tier] . ' ' . $name;
	    }
	    return ['name' => $name, 'enc' => $enc];
	}
	
	// Wrapper biar kompatibel kalau ada pemanggil lama yang cuma butuh string nama
	private function prettifyApiId(string $apiId): string
	{
	    return $this->parseApiId($apiId)['name'];
	}

	// ============================================================
	// GROUPING HELPER: sama persis logikanya dengan getBaseKey()
	// di frontend (test.blade.php), biar konsisten. Dipakai buat
	// ngelompokin item per base-name (T1..T8 + enchant) sebelum
	// dipaginate, jadi satu base item gak pernah kepotong halaman.
	// ============================================================
	private function getBaseKey(string $apiId): string
	{
	    $key = preg_replace('/@\d$/', '', $apiId);       // buang suffix enchant equipment @1..@4
	    $key = preg_replace('/_LEVEL[1-4]$/', '', $key); // buang suffix enchant resource _LEVEL1..4
	    $key = preg_replace('/^T[1-8]_/', '', $key);     // buang prefix tier T1_...T8_
	    $key = preg_replace('/^2H_/', '', $key);         // buang prefix 2H_
	    return $key;
	}

	// --- GANTI recipeItems() DENGAN INI (versi grouping per-base-item) ---

	public function recipeItems(Request $request)
	{
	    $perPageGroups = 20; // jumlah GROUP (base item) per halaman, bukan jumlah row
	    $page   = max(1, (int) $request->get('page', 1));
	    $search = trim($request->get('search', ''));

	    $baseQuery = function () {
	        return DB::table('item_recipes')
	            ->select('item_api_id')
	            ->distinct()
	            ->whereNotIn('item_api_id', function ($q) {
	                $q->select('api_id')->from('items');
	            })
	            ->where('item_api_id', 'not like', 'QUESTITEM%')
	            ->where('item_api_id', 'not like', 'UNIQUE%');
	    };

	    // Ambil SEMUA api_id yang lolos filter dasar (belum di-DB, bukan quest/unique).
	    // Wajib diambil semua (bukan per-halaman) karena grouping & pagination
	    // sekarang dilakukan per BASE-ITEM, bukan per-row, supaya satu base item
	    // (T1..T8 + enchant) selalu utuh dalam satu halaman yang sama.
	    $allApiIds = $baseQuery()->orderBy('item_api_id')->pluck('item_api_id');

	    if ($search !== '') {
	        // "nama item" itu hasil olahan parseApiId(), BUKAN kolom asli di DB,
	        // jadi gak bisa di-LIKE langsung di SQL. Solusinya: cocokkan di PHP
	        // ke api_id ATAU nama hasil parse, baru di-group & di-paginate.
	        $searchLower = mb_strtolower($search);
	        $allApiIds = $allApiIds->filter(function ($apiId) use ($searchLower) {
	            if (str_contains(mb_strtolower($apiId), $searchLower)) {
	                return true;
	            }
	            $name = mb_strtolower($this->parseApiId($apiId)['name']);
	            return str_contains($name, $searchLower);
	        })->values();
	    }

	    // Group berdasarkan base key (strip tier + enchant) — logikanya sama
	    // persis dengan getBaseKey() di test.blade.php, biar konsisten.
	    $groups = [];
	    foreach ($allApiIds as $apiId) {
	        $baseKey = $this->getBaseKey($apiId);
	        $groups[$baseKey][] = $apiId;
	    }
	    ksort($groups); // urut alfabetis per base key biar stabil & predictable

	    $groupKeys   = array_keys($groups);
	    $totalGroups = count($groupKeys);
	    $totalItems  = $allApiIds->count();

	    $pageKeys = array_slice($groupKeys, ($page - 1) * $perPageGroups, $perPageGroups);

	    $apiIds = collect();
	    foreach ($pageKeys as $key) {
	        foreach ($groups[$key] as $id) {
	            $apiIds->push($id);
	        }
	    }

	    // Ambil distinct enchantment_level per item_api_id dari item_recipes (1 query, hindari N+1)
	    $encLevels = DB::table('item_recipes')
	        ->whereIn('item_api_id', $apiIds)
	        ->select('item_api_id', 'enchantment_level')
	        ->distinct()
	        ->get()
	        ->groupBy('item_api_id')
	        ->map(fn($rows) => $rows->pluck('enchantment_level')->sort()->values());

	    $items = $apiIds->map(function ($apiId) use ($encLevels) {
	        $parsed = $this->parseApiId($apiId);
	        $parts  = explode('_', $apiId);
	        $tier   = isset($parts[0]) && preg_match('/^T(\d)$/', $parts[0], $m) ? (int) $m[1] : null;

	        $levels = $encLevels->get($apiId, collect([0]));

	        return [
	            'api_id'     => $apiId,
	            'name'       => $parsed['name'],
	            'tier'       => $tier,
	            'enc'        => $parsed['enc'],       // enc hasil parse dari _LEVELx (kalau resource)
	            'max_enc'    => (int) ($levels->max() ?? 0),
	            'enc_levels' => $levels->values(),
	            'img_url'    => "https://render.albiononline.com/v1/item/{$apiId}.png",
	        ];
	    });

	    return response()->json([
	        'data'         => $items,
	        'total'        => $totalItems,
	        'total_groups' => $totalGroups,
	        'page'         => $page,
	        'per_page'     => $perPageGroups,
	        'last_page'    => (int) ceil($totalGroups / $perPageGroups),
	    ]);
	}

    public function index()
    {
        return view('market');
    }

    // Ambil kategori nested (3 level) untuk dropdown
	public function categories()
	{
	    $roots = Category::whereNull('parent_id')
	        ->orderBy('id')
	        ->with(['children' => function ($q) {
	            $q->orderBy('id')
	              ->with(['children' => function ($q2) {
	                  $q2->orderBy('id');
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

    // Cari item berdasarkan filter
    public function items(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'tier'        => 'nullable|integer|min:1|max:8',
            'enc'         => 'nullable|integer|min:0|max:4',
        ]);

        $query = Item::with('category');

        if ($request->filled('category_id')) {
            $cat = Category::find($request->category_id);

            // Kumpulkan semua descendant category_id
            $ids = $this->getAllDescendantIds($cat);
            $ids[] = $cat->id;

            $query->whereIn('category_id', $ids);
        }

        if ($request->filled('tier')) {
            // PENTING: kolom 'tier' di DB isinya angka (1-8), BUKAN string "T1".."T8"
            $query->where('tier', (int) $request->tier);
        }

        if ($request->filled('enc')) {
            $query->where('enc', $request->enc);
        }

        $items = $query->orderBy('tier')->orderBy('name')->get();

        // List sengaja TIDAK ikut ambil harga sama sekali (dulu sempat join ke
        // item_prices di sini, tapi bikin berat kalau list-nya panjang). Harga
        // cuma di-fetch pas user buka popup item (real-time-first, lihat
        // refreshPrices() / itemDetail()).
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

    // Rekursif ambil semua child category id
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
                'name'    => $resItem?->name ?? $this->prettifyApiId($r->resource_api_id),
                'item_id' => $resItem?->id,
                'img_url' => "https://render.albiononline.com/v1/item/{$r->resource_api_id}{$encSuffix}.png",
            ];
        })->values();

        // Harga dari cache (item_prices) — yang fresh-nya di-trigger lewat endpoint refreshPrices()
        // pas popup dibuka, bukan fetch langsung dari sini.
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

    // Fetch harga terbaru dari Albion Online Data Project, simpan ke cache, balikin hasilnya.
    // Dipanggil pas user buka popup item (bukan tiap render list).
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
            $data = null; // API gagal/timeout -> di bawah kita fallback ke cache lama
        }

        if (is_array($data)) {
            $now = now();
            foreach (self::CITIES as $city) {
                $entry = collect($data)->firstWhere('city', $city);
                $price = (int) ($entry['sell_price_min'] ?? 0);

                // Cuma update cache kalau API benar-benar ngasih harga > 0.
                // Kalau API kosong/0 untuk kota itu, BIARKAN data cache lama tetap
                // (jangan ditimpa 0), biar gak kehilangan harga terakhir yang valid.
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
            'stale'  => $data === null, // true kalau API gagal & ini cuma balikin cache lama
        ]);
    }

    // ============================================================
    // FETCH BATCH per kategori — dijalankan saat user klik kategori
    // (level 2 atau 3), buat pre-cache harga semua item di kategori itu.
    // Kalau berhasil, item-item di kategori itu udah di-cache semuanya
    // sebelum user buka popup. Kalau gagal, fallback ke per-item nantinya.
    // ============================================================
    public function refreshCategoryPrices($categoryId)
    {
        $cat = Category::findOrFail($categoryId);

        // Ambil semua item di kategori ini + descendant-nya
        $ids = $this->getAllDescendantIds($cat);
        $ids[] = $cat->id;

        $items = Item::whereIn('category_id', $ids)
            ->whereNotNull('api_id')
            ->get(['api_id', 'enc'])
            ->unique(fn($i) => $i->api_id . '@' . (int) ($i->enc ?? 0))
            ->values();

        if ($items->isEmpty()) {
            return response()->json(['updated' => 0, 'failed' => 0]);
        }

        $citiesParam = implode(',', self::CITIES);
        $updated = 0;
        $failed = 0;

        // Batch setiap 60 item buat kurangi beban API
        foreach ($items->chunk(60) as $chunk) {
            $idsCsv = $chunk->map(fn($i) => ($i->enc > 0 ? "{$i->api_id}@{$i->enc}" : $i->api_id))
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
                        if ($price <= 0) continue;

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

            usleep(300_000); // jeda antar batch
        }

        return response()->json([
            'updated' => $updated,
            'failed'  => $failed,
            'category_id' => $categoryId,
        ]);
    }

    // ============================================================
    // FETCH per-item tunggal — dipakai kalau kategori batch belum
    // keselesai atau user langsung klik item tanpa klik kategori dulu.
    // ============================================================
    public function refreshItemPriceSingle($id)
    {
        // Bisa pakai item ID dari DB atau api_id string kalau lebih gampang di frontend
        $item = Item::findOrFail($id);
        // Simpel, panggil method refreshPrices yang udah ada
        return $this->refreshPrices($id);
    }

// Fetch item yang SUDAH ada di tabel items (untuk koreksi kategori)
public function savedItems(Request $request)
{
    $perPage = 50;
    $page    = max(1, (int) $request->get('page', 1));
    $search  = $request->get('search', '');

    $query = Item::with('category');

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('api_id', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%");
        });
    }

    $total = $query->count();
    $items = $query->orderBy('api_id')->orderBy('enc')
                   ->offset(($page - 1) * $perPage)
                   ->limit($perPage)
                   ->get()
                   ->map(fn($item) => [
                       'id'          => $item->id,
                       'api_id'      => $item->api_id,
                       'name'        => $item->name,
                       'tier'        => $item->tier,
                       'enc'         => $item->enc,
                       'category_id' => $item->category_id,
                       'category'    => $item->category->name ?? '-',
                       'img_url'     => "https://render.albiononline.com/v1/item/{$item->api_id}.png",
                   ]);

    return response()->json([
        'data'      => $items,
        'total'     => $total,
        'page'      => $page,
        'per_page'  => $perPage,
        'last_page' => (int) ceil($total / $perPage),
    ]);
}

// Update category_id satu item yang sudah tersimpan
public function updateItemCategory(Request $request, $id)
{
    $request->validate([
        'category_id' => 'required|integer|exists:categories,id',
    ]);

    $item = Item::findOrFail($id);
    $item->category_id = $request->category_id;
    $item->save();

    return response()->json(['ok' => true, 'category_id' => $item->category_id]);
}




// Save batch items ke tabel items
public function saveItems(Request $request)
{
    $request->validate([
        'items'               => 'required|array|min:1',
        'items.*.api_id'      => 'required|string',
        'items.*.name'        => 'required|string',
        'items.*.tier'        => 'nullable|integer',
        'items.*.enc'         => 'nullable|integer',
        'items.*.category_id' => 'required|integer|exists:categories,id',
    ]);

    $inserted = 0;
    foreach ($request->items as $row) {
        $enc = $row['enc'] ?? 0;

        // Skip jika kombinasi api_id + enc sudah ada
        $exists = DB::table('items')
            ->where('api_id', $row['api_id'])
            ->where('enc', $enc)
            ->exists();
        if ($exists) continue;

        DB::table('items')->insert([
            'api_id'      => $row['api_id'],
            'name'        => $row['name'],
            'tier'        => $row['tier'] ?? null,
            'enc'         => $enc,
            'quality'     => 'Normal',
            'category_id' => $row['category_id'],
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $inserted++;
    }

    return response()->json(['saved' => $inserted]);
}
}