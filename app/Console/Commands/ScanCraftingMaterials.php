<?php

namespace App\Console\Commands;

use App\Models\CraftingStation;
use App\Models\CraftingStationMaterial;
use App\Models\Item;
use App\Services\CraftingQualityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScanCraftingMaterials extends Command
{
    protected $signature = 'crafting:scan-materials {station?}';
    protected $description = 'Generate advance-mode material mapping per station dari item_recipes. Jalankan ulang setiap kali data recipe/item berubah.';

    public function handle(CraftingQualityService $qualityService)
    {
        $stationSlug = $this->argument('station');

        $stations = $stationSlug
            ? [CraftingStation::where('slug', $stationSlug)->firstOrFail()]
            : CraftingStation::all();

        $totalInserted = 0;

        foreach ($stations as $station) {
            $this->info("Scanning {$station->name} ({$station->slug})...");
            $count = $this->scanStation($station, $qualityService);
            $totalInserted += $count;
        }

        $this->newLine();
        $this->info("Selesai! Total {$totalInserted} material rows di-generate untuk " . count($stations) . " station.");

        return Command::SUCCESS;
    }

    private function scanStation(CraftingStation $station, CraftingQualityService $qualityService): int
    {
        // 1. Ambil semua category IDs milik station ini
        $categoryIds = $station->categories()->pluck('categories.id')->toArray();

        if (empty($categoryIds)) {
            $this->warn("  Tidak ada kategori untuk {$station->name}, skip.");
            return 0;
        }

        // 2. Ambil semua api_id item target (yang bisa di-craft di station ini)
        $targetItems = Item::whereIn('category_id', $categoryIds)
            ->with('category')
            ->get();

        $targetApiIds = $targetItems->pluck('api_id')->unique()->values();

        if ($targetApiIds->isEmpty()) {
            $this->warn("  Tidak ada item craftable untuk {$station->name}, skip.");
            return 0;
        }

        // 3. Bangun peta: api_id => quality_supported (dari item target, bukan bahan)
        //    Bahan mewarisi quality_supported dari item yang mereka dukung craft-nya.
        //    Kalau SALAH SATU item craftable di station ini quality-supported,
        //    bahan yang dipakai di recipe itu juga dianggap quality-supported.
        $qualitySupportedApiIds = $targetItems
            ->filter(function ($item) use ($qualityService) {
                return $qualityService->isCategoryQualitySupported(
                    (int) $item->category_id,
                    $item->category?->name
                );
            })
            ->pluck('api_id')
            ->flip(); // flip buat lookup O(1)

        // 4. Ambil semua resource unik dari item_recipes (1 query)
        $resourcePairs = DB::table('item_recipes')
            ->whereIn('item_api_id', $targetApiIds)
            ->select('resource_api_id', 'resource_enchantment_level', 'item_api_id')
            ->get();

        if ($resourcePairs->isEmpty()) {
            $this->warn("  Tidak ada recipe resource untuk {$station->name}, skip.");
            return 0;
        }

        // 5. Bangun mapping: resource_api_id => apakah dipakai untuk item quality-supported
        $resourceQualityMap = [];
        foreach ($resourcePairs as $pair) {
            $key = $pair->resource_api_id . '|' . $pair->resource_enchantment_level;
            // Kalau item target-nya quality-supported, bahan ini juga dianggap quality-supported
            if ($qualitySupportedApiIds->has($pair->item_api_id)) {
                $resourceQualityMap[$key] = true;
            } elseif (!isset($resourceQualityMap[$key])) {
                $resourceQualityMap[$key] = false;
            }
        }

        // 6. Bangun set pair keys untuk filter presisi (api_id + enc)
        $pairKeys = collect($resourcePairs)
            ->map(fn($p) => $p->resource_api_id . '|' . $p->resource_enchantment_level)
            ->unique()
            ->flip();

        $uniqueApiIds = collect($resourcePairs)->pluck('resource_api_id')->unique()->values();

        // 7. Ambil semua item bahan yang cocok dari tabel items (1 query)
        $materialItems = Item::whereIn('api_id', $uniqueApiIds)
            ->get()
            ->filter(fn($item) => $pairKeys->has($item->api_id . '|' . (int) $item->enc))
            ->values();

        if ($materialItems->isEmpty()) {
            $this->warn("  Tidak ada item bahan yang ter-mapping di tabel items untuk {$station->name}, skip.");
            return 0;
        }

        // 8. Hapus mapping lama untuk station ini
        CraftingStationMaterial::where('station_id', $station->id)->delete();

        // 9. Insert semua bahan sekaligus (chunk biar aman)
        $qualityCount = 0;
        $rows = $materialItems->map(function ($item) use ($station, $resourceQualityMap, &$qualityCount) {
            $key = $item->api_id . '|' . (int) $item->enc;
            $isQualitySupported = $resourceQualityMap[$key] ?? false;
            if ($isQualitySupported) $qualityCount++;

            return [
                'station_id'        => $station->id,
                'item_id'           => $item->id,
                'category_id'       => $item->category_id,
                'api_id'            => $item->api_id,
                'enc'               => (int) $item->enc,
                'tier'              => $item->tier,
                'quality_supported' => $isQualitySupported ? 1 : 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
        })->toArray();

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('crafting_station_materials')->insert($chunk);
        }

        // 10. Summary per station
        $catCount = $materialItems->pluck('category_id')->unique()->count();
        $this->info("  -> {$materialItems->count()} bahan, {$catCount} kategori, {$qualityCount} quality-supported");

        return count($rows);
    }
}
