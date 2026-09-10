<?php

namespace App\Console\Commands;

use App\Models\CraftingStation;
use App\Models\Item;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScanCraftingMaterials extends Command
{
    protected $signature = 'crafting:scan-materials {station?}';
    protected $description = 'Scan crafting materials needed for each station';

    public function handle()
    {
        $stationSlug = $this->argument('station');
        
        $stations = $stationSlug 
            ? [CraftingStation::where('slug', $stationSlug)->firstOrFail()]
            : CraftingStation::all();
        
        foreach ($stations as $station) {
            $this->info("🔍 Scanning {$station->name} ({$station->slug})...");
            $this->scanStation($station);
        }
        
        $this->info('✅ Scan complete!');
        
        return Command::SUCCESS;
    }
    
    private function scanStation($station)
    {
        // Get station category IDs
        $categoryIds = $station->categories()->pluck('categories.id')->toArray();
        
        if (empty($categoryIds)) {
            $this->warn("  No categories found for {$station->name}");
            return;
        }
        
        // Get all T4.0 items for this station
        $items = Item::whereIn('category_id', $categoryIds)
            ->where('tier', 4)
            ->where('enc', 0)
            ->get();
        
        $this->info("  Found " . $items->count() . " T4.0 items");
        
        $materials = [];
        
        // Get recipes for each item
        foreach ($items as $item) {
            $recipes = DB::table('item_recipes')
                ->where('item_api_id', $item->api_id)
                ->where('enchantment_level', 0)
                ->get();
            
            foreach ($recipes as $recipe) {
                $materialBase = $this->extractBaseName($recipe->resource_api_id);
                
                if (!isset($materials[$materialBase])) {
                    $type = $this->detectType($materialBase);
                    $materials[$materialBase] = [
                        'station_slug' => $station->slug,
                        'material_base' => $materialBase,
                        'min_tier' => $this->getMinTier($materialBase, $type),
                        'max_tier' => $this->getMaxTier($materialBase, $type),
                        'max_enchant' => $this->getMaxEnchant($type),
                        'type' => $type,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        if (empty($materials)) {
            $this->warn("  No materials found");
            return;
        }
        
        // Clear existing materials for this station
        DB::table('crafting_station_materials')
            ->where('station_slug', $station->slug)
            ->delete();
        
        // Insert new materials
        DB::table('crafting_station_materials')->insert(array_values($materials));
        
        $this->info("  ✅ Saved " . count($materials) . " unique materials");
        
        // Show summary
        foreach ($materials as $mat) {
            $this->line("    - {$mat['material_base']} (T{$mat['min_tier']}-T{$mat['max_tier']}, enc 0-{$mat['max_enchant']}) [{$mat['type']}]");
        }
    }
    
    private function extractBaseName($apiId)
    {
        // Remove tier prefix (T2_, T3_, etc)
        $base = preg_replace('/^T[2-8]_/', '', $apiId);
        
        // Remove enchantment suffix (_LEVEL1@1, etc)
        $base = preg_replace('/_LEVEL\d+@\d+$/', '', $base);
        
        return $base;
    }
    
    private function detectType($materialBase)
    {
        // Base materials
        if (in_array($materialBase, ['METALBAR', 'PLANKS', 'LEATHER', 'CLOTH', 'STONEBLOCK'])) {
            return 'base';
        }
        
        // Runes
        if (str_contains($materialBase, 'RUNE')) {
            return 'rune';
        }
        
        // Souls
        if (str_contains($materialBase, 'SOUL') || str_contains($materialBase, 'ESSENCE')) {
            return 'soul';
        }
        
        // Crystals
        if (str_contains($materialBase, 'CRYSTAL') || str_contains($materialBase, 'SHARD')) {
            return 'crystal';
        }
        
        // Hearts
        if (str_contains($materialBase, 'HEART')) {
            return 'heart';
        }
        
        // Default to artifact
        return 'artifact';
    }
    
    private function getMinTier($materialBase, $type)
    {
        // Base materials start from T2
        if ($type === 'base') {
            return 2;
        }
        
        // Most special items start from T4
        return 4;
    }
    
    private function getMaxTier($materialBase, $type)
    {
        // All go up to T8
        return 8;
    }
    
    private function getMaxEnchant($type)
    {
        // Base materials can be enchanted up to .4
        if ($type === 'base') {
            return 4;
        }
        
        // Artifacts, runes, souls usually don't have enchantment
        return 0;
    }
}
