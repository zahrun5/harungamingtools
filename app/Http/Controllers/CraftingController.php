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
 *
 * --- Fame/Journal calculator (FITUR-FAME-JOURNAL-CRAFTING) ---
 * getItemType() dan computeFame() dipakai di itemDetail() untuk
 * menghitung F_C (fame total per craft) sesuai formula resmi di
 * wiki.albiononline.com/wiki/Fame. Konstanta terkait (tier_multiplier,
 * journal_requirement, harga journal, base_amount, laborer_ratio)
 * ada di config/albion.php, BUKAN hardcode di sini, supaya gampang
 * diupdate kalau ada balancing patch dari game.
 */
class CraftingController extends Controller
{
    private const CITIES = ['Caerleon', 'Bridgewatch', 'Fort Sterling', 'Lymhurst', 'Martlock', 'Thetford', 'Brecilien'];

    public function index($station = 'mage-tower')
    {
        $craftingStation = \App\Models\CraftingStation::where('slug', $station)->first();
        $stationName = $craftingStation->name ?? ucwords(str_replace('-', ' ', $station));

        return view('kalkulator.crafting', [
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

        // Nama item terlokalisasi (sumber resmi: tabel item_localizations,
        // fallback EN-US lalu ke name di tabel items) — pola sama kayak
        // MarketController::items().
        $apiLocale = \App\Models\Item::currentApiLocale();
        $localizedNames = \App\Models\ItemLocalization::namesFor(
            $items->pluck('api_id')->filter()->unique()->values()->all(),
            $apiLocale
        );

        $items = $items->map(function ($item) use ($localizedNames) {
            $enc = (int) ($item->enc ?? 0);
            $apiIdWithEnc = $item->api_id
                ? ($enc > 0 ? "{$item->api_id}@{$enc}" : $item->api_id)
                : null;

            return [
                'id'       => $item->id,
                'name'     => $localizedNames[$item->api_id] ?? $item->name,
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

    // Pisah $recipes (flat array dari DB) jadi grup per-alternatif resep.
    // PORTING dari heuristik JS groupRecipeResources() di mage-tower.blade.php:
    // iterasi berurutan, begitu ketemu resource_api_id yang UDAH MUNCUL di
    // grup aktif, mulai grup baru. Ini perlu karena backend belum punya
    // kolom pembeda resep (misal Royal Jacket punya 3 alternatif armor
    // dasar, Avalon bow punya 2 alternatif cara dapetin artifact piece —
    // semua ke-flatten jadi 1 array oleh query DB, urutannya identik
    // sama urutan insert/ID, sama seperti yang dibaca frontend).
    private function groupRecipeResources($recipes): array
    {
        if ($recipes->isEmpty()) {
            return [];
        }

        // Hitung frekuensi tiap kombinasi resource+enchant di SELURUH baris.
        // Asumsi: kalau item punya N alternatif resep, minimal ada 1 resource
        // yang dipakai bersama di semua alternatif (misal Quest Token Royal),
        // jadi resource itu muncul tepat N kali di data. N = frekuensi
        // maksimum. Kalau semua baris habis terbagi rata oleh N, potong jadi
        // N grup ukuran sama — ini FIX buat kasus di mana resource bersama
        // (TOKEN) nyelip di antara resource pembeda tiap resep (SET1/SET2/SET3),
        // yang bikin heuristik lama (deteksi "udah pernah muncul") salah motong
        // batas grup satu baris lebih awal/telat.
        $freq = [];
        foreach ($recipes as $r) {
            $key = $r->resource_api_id . '@' . $r->resource_enchantment_level;
            $freq[$key] = ($freq[$key] ?? 0) + 1;
        }
        $maxFreq = max($freq);
        $total   = $recipes->count();

        if ($maxFreq > 1 && $total % $maxFreq === 0) {
            $chunkSize = intdiv($total, $maxFreq);
            return $recipes->values()->chunk($chunkSize)->values()->all();
        }

        // Fallback: heuristik lama (deteksi duplikat berurutan) — dipakai
        // kalau datanya gak simetris rata / gak ada resource bersama yang
        // berulang (misal cuma 1 resep, atau pola gak terduga).
        $groups  = [];
        $current = collect();
        $seen    = [];

        foreach ($recipes as $r) {
            $key = $r->resource_api_id;
            if (in_array($key, $seen, true)) {
                $groups[] = $current;
                $current  = collect();
                $seen     = [];
            }
            $seen[] = $key;
            $current->push($r);
        }

        if ($current->isNotEmpty()) {
            $groups[] = $current;
        }

        return $groups;
    }

    // Tentukan tipe item: Standard / Royal / Artifact.
    // Aturan (dikonfirmasi lewat query manual ke DB HGT, lihat
    // FITUR-FAME-JOURNAL-CRAFTING.md):
    //   1. api_id berakhiran "_ROYAL" -> Royal
    //   2. Kalau tidak, cek item_recipes: ada bahan yang mengandung
    //      "ARTEFACT" di resource_api_id -> Artifact
    //   3. Sisanya -> Standard
    private function getItemType(string $apiId): string
    {
        if (str_ends_with($apiId, '_ROYAL')) {
            return 'Royal';
        }

        $hasArtifact = DB::table('item_recipes')
            ->where('item_api_id', $apiId)
            ->where('resource_api_id', 'like', '%ARTEFACT%')
            ->exists();

        return $hasArtifact ? 'Artifact' : 'Standard';
    }

    // Hitung F_B (fame dasar) & F_C (fame total per craft) sesuai formula
    // resmi wiki.albiononline.com/wiki/Fame. Return null kalau tier < 4,
    // karena formula belum terkonfirmasi resmi untuk T1-T3.
    //
    //   F_B = A x tier_multiplier[tier]
    //   Standard : F_C = F_B + E_L x (F_B - 7.5 x A)
    //   Royal T<6: F_C = F_B + 2.5 x A x (tier - 3)
    //   Royal T>=6: F_C = F_B + 2.5 x A x 4
    //   Artifact  : F_C = F_B + 500
    // Nilai silver dari resource yang balik pas 1 journal PENUH diserahkan
    // ke laborer, di asumsi yield 100% (yield% laborer beneran dikaliin
    // belakangan di frontend, soalnya itu tergantung happiness laborer
    // masing-masing pemain — data manual, gak ada di DB).
    //
    // Resource yang balik itu BUKAN bahan resep item yang lagi di-craft —
    // itu resource generic (cloth/leather/metalbar/planks) di TIER journal
    // itu sendiri, proporsinya beda-beda per jenis laborer (config
    // albion.journal.laborer_ratio). Harganya di-lookup dari tabel items+
    // item_prices yang SUDAH ADA (sama persis kayak resource resep biasa),
    // BUKAN data baru.
    //
    // Generalist's Journal belum ada proporsi resource yang dikonfirmasi
    // (return null) — item_type-nya beda dari 4 laborer profesi, jadi
    // sengaja gak ditebak.
    private function journalResourceValue(string $journalName, int $tier): ?float
    {
        $laborerKey = match ($journalName) {
            "Blacksmith's Journal" => 'Blacksmith',
            "Fletcher's Journal"   => 'Fletcher',
            "Imbuer's Journal"     => 'Imbuer',
            "Tinker's Journal"     => 'Tinker',
            default                => null, // Generalist's Journal, dll — belum diketahui proporsinya
        };
        if (!$laborerKey) {
            return null;
        }

        $ratio      = config("albion.journal.laborer_ratio.$laborerKey");
        $baseAmount = config("albion.journal.base_amount.$tier");
        if (!$ratio || !$baseAmount) {
            return null;
        }

        $typeToApiPrefix = [
            'cloth'    => 'CLOTH',
            'leather'  => 'LEATHER',
            'metalbar' => 'METALBAR',
            'planks'   => 'PLANKS',
        ];

        $totalUnitValue = 0;
        foreach ($ratio as $type => $pct) {
            if ($pct <= 0) {
                continue;
            }
            $prefix = $typeToApiPrefix[$type] ?? null;
            if (!$prefix) {
                continue;
            }

            $item = Item::where('api_id', 'like', "T{$tier}_{$prefix}%")
                ->where('tier', $tier)
                ->first();
            if (!$item) {
                continue; // resource ini belum ke-sync di DB HGT — dilewatin, bukan dianggap 0
            }

            $avgPrice = ItemPrice::where('item_api_id', $item->api_id)
                ->where('enc', 0)
                ->avg('sell_price_min');

            $totalUnitValue += $pct * (float) ($avgPrice ?? 0);
        }

        return round($baseAmount * $totalUnitValue, 2);
    }

    // Max fame buat isi 1 journal penuh, per tier — diambil dari tabel
    // journal_requirements (hasil sync items.xml), BUKAN config manual.
    // Beda journal_name bisa punya kurva fame beda (contoh: Generalist's
    // Journal T4 = 5400, sedangkan Fletcher's/Imbuer's/Blacksmith's/
    // Tinker's Journal T4 = 3600) — makanya di-lookup per nama, bukan
    // pakai satu tabel global per tier kayak sebelumnya.
    private function journalMaxFameByTier(string $journalName, array $tierRange): \Illuminate\Support\Collection
    {
        $rows = \App\Models\JournalRequirement::where('journal_name', $journalName)
            ->whereIn('tier', $tierRange)
            ->pluck('max_fame', 'tier');

        return collect($tierRange)->mapWithKeys(fn($t) => [$t => $rows[$t] ?? null]);
    }

    private function computeFame(int $tier, int $encLevel, float $a, string $itemType): ?array
    {
        $multiplier = config("albion.fame.tier_multiplier.$tier");
        if ($multiplier === null) {
            return null;
        }

        $fB = $a * $multiplier;

        $fC = match ($itemType) {
            'Artifact' => $fB + 500,
            'Royal'    => $tier >= 6
                ? $fB + 2.5 * $a * 4
                : $fB + 2.5 * $a * ($tier - 3),
            default    => $fB + $encLevel * ($fB - 7.5 * $a),
        };

        return [
            'A'   => $a,
            'F_B' => round($fB, 2),
            'F_C' => round($fC, 2),
        ];
    }

    // Detail 1 item + recipe + harga cache — identik dengan itemDetail() di MarketController
    public function itemDetail(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $encLevel = (int) ($item->enc ?? 0);
        $apiIdWithEnc = $encLevel > 0 ? "{$item->api_id}@{$encLevel}" : $item->api_id;

        // orderBy('id') eksplisit — groupRecipeResources() di bawah bergantung
        // urutan baris buat misahin varian resep, jangan andalkan default
        // urutan SQLite yang gak dijamin.
        $recipes = DB::table('item_recipes')
            ->where('item_api_id', $item->api_id)
            ->where('enchantment_level', $encLevel)
            ->orderBy('id')
            ->get();

        $resourceApiIds = $recipes->pluck('resource_api_id')->unique()->values();
        $resourceItems  = Item::whereIn('api_id', $resourceApiIds)->get()->keyBy('api_id');

        // Nama terlokalisasi untuk item utama + semua resource resep sekaligus
        // (bulk, hindari N+1) — pola sama kayak MarketController.
        $apiLocale = \App\Models\Item::currentApiLocale();
        $localizedNames = \App\Models\ItemLocalization::namesFor(
            $resourceApiIds->push($item->api_id)->filter()->unique()->values()->all(),
            $apiLocale
        );

        // Resep 1 (grup pertama) dipakai sebagai default, konsisten sama
        // tab "Resep 1" yang aktif duluan di frontend.
        $recipeGroups = $this->groupRecipeResources($recipes);
        $mainRecipe   = $recipeGroups[0] ?? collect();

        // Mapper resource dipakai bareng buat mainRecipe (backward-compat,
        // field 'resources') MAUPUN buat semua grup (field baru 'recipe_groups'
        // yang dipakai frontend buat render tab Resep 1/2/3).
        $mapResource = function ($r) use ($resourceItems, $localizedNames) {
            $resItem   = $resourceItems->get($r->resource_api_id);
            $encSuffix = $r->resource_enchantment_level > 0 ? "@{$r->resource_enchantment_level}" : '';
            return [
                'resource_api_id'            => $r->resource_api_id,
                'resource_enchantment_level' => $r->resource_enchantment_level,
                'count'   => $r->count,
                'name'    => $localizedNames[$r->resource_api_id] ?? $resItem?->name ?? $r->resource_api_id,
                'item_id' => $resItem?->id,
                'img_url' => "https://render.albiononline.com/v1/item/{$r->resource_api_id}{$encSuffix}.png",
            ];
        };

        $resources = $mainRecipe->map($mapResource)->values();

        $recipeGroupsForResponse = collect($recipeGroups)
            ->map(fn($group) => $group->map($mapResource)->values())
            ->values();

        $cachedPrices = ItemPrice::where('item_api_id', $item->api_id)
            ->where('enc', $encLevel)
            ->get()
            ->keyBy('city');

        $prices = collect(self::CITIES)->mapWithKeys(
            fn($city) => [$city => (int) ($cachedPrices[$city]->sell_price_min ?? 0)]
        );

        // --- Data journal (checkbox "Gunakan Jurnal" di halaman crafting) ---
        // Station dikirim frontend lewat query string ?station={slug}, karena
        // itemDetail() dipanggil tanpa route segment station.
        $stationSlug = $request->query('station');
        $journalOptions = [];

        if ($stationSlug) {
            $craftingStation = \App\Models\CraftingStation::where('slug', $stationSlug)->first();
            if ($craftingStation && $craftingStation->journal_name) {
                $minTier = max(2, $item->tier - 2);
                $tierRange = range($minTier, $item->tier);

                $journalOptions[] = [
                    'name'  => $craftingStation->journal_name,
                    'tiers' => $tierRange,
                    'resource_value_by_tier' => collect($tierRange)->mapWithKeys(
                        fn($t) => [$t => $this->journalResourceValue($craftingStation->journal_name, $t)]
                    ),
                    'max_fame_by_tier' => $this->journalMaxFameByTier($craftingStation->journal_name, $tierRange),
                ];
                $journalOptions[] = [
                    'name'  => "Generalist's Journal",
                    'tiers' => $tierRange,
                    'resource_value_by_tier' => collect($tierRange)->mapWithKeys(
                        fn($t) => [$t => $this->journalResourceValue("Generalist's Journal", $t)]
                    ),
                    'max_fame_by_tier' => $this->journalMaxFameByTier("Generalist's Journal", $tierRange),
                ];
            }
        }

        // --- Data fame (Kalkulator Fame & Journal Crafting) ---
        // A = total material non-artifact yang dipakai (SUM count resep utama).
        // F_C dihitung sesuai tipe item (Standard/Royal/Artifact) dan
        // enchant level. Null kalau tier < 4 (formula belum terkonfirmasi).
        $itemType      = $this->getItemType($item->api_id);
        $totalMaterial = (float) $mainRecipe->sum('count');
        $fame          = $this->computeFame((int) $item->tier, $encLevel, $totalMaterial, $itemType);

        return response()->json([
            'id'        => $item->id,
            'name'      => $localizedNames[$item->api_id] ?? $item->name,
            'api_id'    => $item->api_id,
            'tier'      => $item->tier,
            'enc'       => $encLevel,
            'img_url'   => "https://render.albiononline.com/v1/item/{$apiIdWithEnc}.png",
            'resources'     => $resources,        // backward-compat, sama dengan recipe_groups[0]
            'recipe_groups' => $recipeGroupsForResponse, // semua alternatif resep, buat tab Resep 1/2/3
            'prices'    => $prices,
            'journal_options'      => $journalOptions,
            'default_journal'      => $journalOptions[0]['name'] ?? null,
            'default_journal_tier' => $item->tier,
            'item_type' => $itemType,
            'fame'      => $fame,
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

    /**
     * Advance Mode: Get all materials (bahan crafting) for a station
     * Query dari crafting_station_materials, lalu generate all tier+enchant variants
     */
    public function advanceMaterials(Request $request, string $station = 'mage-tower')
    {
        $request->validate([
            'tier' => 'nullable|integer|min:2|max:8',
            'enc' => 'nullable|integer|min:0|max:4',
        ]);

        // Get material definitions for this station
        $materials = DB::table('crafting_station_materials')
            ->where('station_slug', $station)
            ->get();

        if ($materials->isEmpty()) {
            return response()->json([]);
        }

        $items = [];
        $tierFilter = $request->input('tier');
        $encFilter = $request->input('enc');

        foreach ($materials as $mat) {
            // Generate all tier + enchant variants
            for ($tier = $mat->min_tier; $tier <= $mat->max_tier; $tier++) {
                // Apply tier filter
                if ($tierFilter && $tier != $tierFilter) {
                    continue;
                }

                for ($enc = 0; $enc <= $mat->max_enchant; $enc++) {
                    // Apply enchantment filter
                    if ($encFilter !== null && $enc != $encFilter) {
                        continue;
                    }

                    $apiId = "T{$tier}_{$mat->material_base}";
                    if ($enc > 0) {
                        $apiId .= "_LEVEL{$enc}@{$enc}";
                    }

                    // Get item from database
                    $item = Item::where('api_id', $apiId)->first();

                    if ($item) {
                        // Generate img_url for Albion render service
                        $apiIdWithEnc = $item->api_id;
                        if ($enc > 0) {
                            $apiIdWithEnc .= "@{$enc}";
                        }
                        
                        $items[] = [
                            'id' => $item->id,
                            'api_id' => $item->api_id,
                            'name' => $item->name,
                            'icon' => $item->icon,
                            'img_url' => "https://render.albiononline.com/v1/item/{$apiIdWithEnc}.png",
                            'tier' => $tier,
                            'enc' => $enc,
                            'type' => $mat->type,
                        ];
                    }
                }
            }
        }

        return response()->json($items);
    }

    /**
     * Advance Mode: Check which items can be crafted with given materials
     * Receives array of material api_ids from inventory, returns craftable items
     */
    public function checkCraftable(Request $request, string $station = 'mage-tower')
    {
        $request->validate([
            'materials' => 'required|array',
            'materials.*.api_id' => 'required|string',
            'materials.*.qty' => 'required|integer|min:1',
        ]);

        $stationCategoryIds = $this->resolveCategoryIds($station);
        if (empty($stationCategoryIds)) {
            return response()->json([]);
        }

        $materialsInv = collect($request->input('materials'))->keyBy('api_id');
        
        // Get all items for this station
        $items = Item::whereIn('category_id', $stationCategoryIds)->get();
        
        $craftable = [];
        
        foreach ($items as $item) {
            // Get recipes for this item
            $recipes = DB::table('item_recipes')
                ->where('item_api_id', $item->api_id)
                ->where('enchantment_level', $item->enc)
                ->get();
            
            if ($recipes->isEmpty()) continue;
            
            // Check if all required materials are in inventory
            $canCraft = true;
            $maxQty = PHP_INT_MAX;
            
            foreach ($recipes as $recipe) {
                $requiredApiId = $recipe->resource_api_id;
                if ($recipe->resource_enchantment_level > 0) {
                    $requiredApiId .= '_LEVEL' . $recipe->resource_enchantment_level . '@' . $recipe->resource_enchantment_level;
                }
                
                if (!$materialsInv->has($requiredApiId)) {
                    $canCraft = false;
                    break;
                }
                
                $available = $materialsInv->get($requiredApiId)['qty'];
                $required = $recipe->count;
                
                if ($available < $required) {
                    $canCraft = false;
                    break;
                }
                
                $maxQty = min($maxQty, floor($available / $required));
            }
            
            if ($canCraft) {
                $enc = (int) ($item->enc ?? 0);
                $apiIdWithEnc = $item->api_id . ($enc > 0 ? "@{$enc}" : '');
                
                $craftable[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'api_id' => $item->api_id,
                    'img_url' => "https://render.albiononline.com/v1/item/{$apiIdWithEnc}.png",
                    'tier' => $item->tier,
                    'enc' => $item->enc,
                    'maxQty' => $maxQty,
                ];
            }
        }
        
        return response()->json($craftable);
    }

    /**
     * Advance Mode: Get recipe details for a specific item
     * Returns all materials needed and their quantities
     */
    public function advanceRecipe(Request $request, string $station = 'mage-tower')
    {
        $request->validate([
            'item_id' => 'required|integer',
        ]);

        $itemId = $request->input('item_id');
        $stationCategoryIds = $this->resolveCategoryIds($station);
        
        // Verify item belongs to this station
        $item = Item::where('id', $itemId)
            ->whereIn('category_id', $stationCategoryIds)
            ->first();
        
        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }
        
        // Get recipes for this item
        $recipes = DB::table('item_recipes')
            ->where('item_api_id', $item->api_id)
            ->where('enchantment_level', $item->enc)
            ->get();
        
        if ($recipes->isEmpty()) {
            return response()->json(['error' => 'No recipe found'], 404);
        }
        
        // Group recipes by resource (some items have multiple recipe rows for same material)
        $materialsMap = [];
        foreach ($recipes as $recipe) {
            $key = $recipe->resource_api_id . '@' . $recipe->resource_enchantment_level;
            
            if (!isset($materialsMap[$key])) {
                $resourceItem = Item::where('api_id', $recipe->resource_api_id)
                    ->where('enc', $recipe->resource_enchantment_level)
                    ->first();
                
                if ($resourceItem) {
                    $enc = (int) ($resourceItem->enc ?? 0);
                    $apiIdWithEnc = $resourceItem->api_id . ($enc > 0 ? "@{$enc}" : '');
                    
                    $materialsMap[$key] = [
                        'id' => $resourceItem->id,
                        'api_id' => $resourceItem->api_id,
                        'name' => $resourceItem->name,
                        'img_url' => "https://render.albiononline.com/v1/item/{$apiIdWithEnc}.png",
                        'tier' => $resourceItem->tier,
                        'enc' => $resourceItem->enc,
                        'count' => $recipe->count,
                    ];
                }
            } else {
                // Sum the count if same material appears multiple times
                $materialsMap[$key]['count'] += $recipe->count;
            }
        }
        
        $materials = array_values($materialsMap);
        
        // Build img_url for result item
        $itemEnc = (int) ($item->enc ?? 0);
        $itemApiIdWithEnc = $item->api_id . ($itemEnc > 0 ? "_LEVEL{$itemEnc}@{$itemEnc}" : '');
        
        return response()->json([
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'api_id' => $item->api_id,
                'img_url' => "https://render.albiononline.com/v1/item/{$itemApiIdWithEnc}.png",
                'tier' => $item->tier,
                'enc' => $item->enc,
            ],
            'materials' => $materials,
            'silver_cost' => $recipes->first()->silver_cost ?? 0,
        ]);
    }

    /**
     * Get item detail for advance mode (used for restoring inventory)
     */
    public function advanceItemDetail(Request $request, string $station = 'mage-tower')
    {
        $request->validate([
            'item_id' => 'required|integer',
        ]);

        $item = Item::find($request->input('item_id'));
        
        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }
        
        $enc = (int) ($item->enc ?? 0);
        $apiIdWithEnc = $item->api_id . ($enc > 0 ? "_LEVEL{$enc}@{$enc}" : '');
        
        return response()->json([
            'id' => $item->id,
            'name' => $item->name,
            'api_id' => $item->api_id,
            'img_url' => "https://render.albiononline.com/v1/item/{$apiIdWithEnc}.png",
            'tier' => $item->tier,
            'enc' => $item->enc,
        ]);
    }
}
