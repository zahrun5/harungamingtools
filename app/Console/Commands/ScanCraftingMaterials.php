<?php

namespace App\Console\Commands;

use App\Models\CraftingStation;
use App\Models\CraftingStationMaterial;
use App\Models\Item;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScanCraftingMaterials extends Command
{
    protected $signature = 'crafting:scan-materials {station?}';
    protected $description = 'Generate advance-mode material mapping per station dari item_recipes. Jalankan ulang setiap kali data recipe/item berubah.';

    public function handle()
    {
        $stationSlug = $this->argument('station');

        $stations = $stationSlug
            ? [CraftingStation::where('slug', $stationSlug)->firstOrFail()]
            : CraftingStation::all();

        $totalInserted = 0;

        foreach ($stations as $station) {
            $this->info("Scanning {$station->name} ({$station->slug})...");
            $count = $this->scanStation($station);
            $totalInserted += $count;
        }

        $this->newLine();
        $this->info("Selesai! Total {$totalInserted} material rows di-generate untuk " . count($stations) . " station.");

        return Command::SUCCESS;
    }

    private function scanStation(CraftingStation $station): int
    {
        // 1. Ambil semua category IDs milik station ini
        $categoryIds = $station->categories()->pluck('categories.id')->toArray();

        if (empty($categoryIds)) {
            $this->warn("  Tidak ada kategori untuk {$station->name}, skip.");
            return 0;
        }

        // 2. Ambil semua api_id item target (yang bisa di-craft di station ini)
        $targetApiIds = Item::whereIn('category_id', $categoryIds)
            ->pluck('api_id')
            ->unique()
            ->values();

        if ($targetApiIds->isEmpty()) {
            $this->warn("  Tidak ada item craftable untuk {$station->name}, skip.");
            return 0;
        }

        // 3. Ambil semua resource unik dari item_recipes (1 query)
        $resourcePairs = DB::table('item_recipes')
            ->whereIn('item_api_id', $targetApiIds)
            ->select('resource_api_id', 'resource_enchantment_level')
            ->distinct()
            ->get();

        if ($resourcePairs->isEmpty()) {
            $this->warn("  Tidak ada recipe resource untuk {$station->name}, skip.");
            return 0;
        }

        // 4. Bangun set pair keys untuk filter presisi (api_id + enc)
        $pairKeys = $resourcePairs
            ->map(fn($p) => $p->resource_api_id . '|' . $p->resource_enchantment_level)
            ->flip();

        $uniqueApiIds = $resourcePairs->pluck('resource_api_id')->unique()->values();

        // 5. Ambil semua item bahan yang cocok dari tabel items (1 query)
        $materialItems = Item::whereIn('api_id', $uniqueApiIds)
            ->get()
            ->filter(fn($item) => $pairKeys->has($item->api_id . '|' . (int) $item->enc))
            ->values();

        if ($materialItems->isEmpty()) {
            $this->warn("  Tidak ada item bahan yang ter-mapping di tabel items untuk {$station->name}, skip.");
            return 0;
        }

        // 6. Hapus mapping lama untuk station ini
        CraftingStationMaterial::where('station_id', $station->id)->delete();

        // 7. Insert semua bahan sekaligus (chunk biar aman)
        $rows = $materialItems->map(fn($item) => [
            'station_id'  => $station->id,
            'item_id'     => $item->id,
            'category_id' => $item->category_id,
            'api_id'      => $item->api_id,
            'enc'         => (int) $item->enc,
            'tier'        => $item->tier,
            'created_at'  => now(),
            'updated_at'  => now(),
        ])->toArray();

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('crafting_station_materials')->insert($chunk);
        }

        // 8. Summary per station
        $catCount = $materialItems->pluck('category_id')->unique()->count();
        $this->info("  -> {$materialItems->count()} bahan, {$catCount} kategori");

        return count($rows);
    }
}
