<?php
namespace Database\Seeders;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Seeder;

class FarmingItemSeeder extends Seeder
{
    public function run(): void
    {
        // Farm Seeds
        $farmSeeds = Category::where('name', 'seeds')
            ->whereHas('parent', fn($q) => $q->where('name', 'Farm'))
            ->first();

        $farmSeedItems = [
            ['name' => 'Carrot Seeds',  'api_id' => 'T1_FARM_CARROT_SEED', 'tier' => 'T1'],
            ['name' => 'Bean Seeds',    'api_id' => 'T2_FARM_BEAN_SEED',   'tier' => 'T2'],
            ['name' => 'Wheat Seeds',   'api_id' => 'T3_FARM_WHEAT_SEED',  'tier' => 'T3'],
            ['name' => 'Turnip Seeds',  'api_id' => 'T4_FARM_TURNIP_SEED', 'tier' => 'T4'],
            ['name' => 'Cabbage Seeds', 'api_id' => 'T5_FARM_CABBAGE_SEED','tier' => 'T5'],
            ['name' => 'Potato Seeds',  'api_id' => 'T6_FARM_POTATO_SEED', 'tier' => 'T6'],
            ['name' => 'Corn Seeds',    'api_id' => 'T7_FARM_CORN_SEED',   'tier' => 'T7'],
            ['name' => 'Pumpkin Seeds', 'api_id' => 'T8_FARM_PUMPKIN_SEED','tier' => 'T8'],
        ];

        foreach ($farmSeedItems as $item) {
            Item::updateOrCreate(
                ['api_id' => $item['api_id']],
                ['category_id' => $farmSeeds->id, 'name' => $item['name'], 'tier' => $item['tier']]
            );
        }

        // Farm Plants
        $farmPlants = Category::where('name', 'plants')
            ->whereHas('parent', fn($q) => $q->where('name', 'Farm'))
            ->first();

        $farmPlantItems = [
            ['name' => 'Carrots',        'api_id' => 'T1_CARROT', 'tier' => 'T1'],
            ['name' => 'Beans',          'api_id' => 'T2_BEAN',   'tier' => 'T2'],
            ['name' => 'Sheaf of Wheat', 'api_id' => 'T3_WHEAT',  'tier' => 'T3'],
            ['name' => 'Turnips',        'api_id' => 'T4_TURNIP', 'tier' => 'T4'],
            ['name' => 'Cabbage',        'api_id' => 'T5_CABBAGE','tier' => 'T5'],
            ['name' => 'Potatoes',       'api_id' => 'T6_POTATO', 'tier' => 'T6'],
            ['name' => 'Bundle of Corn', 'api_id' => 'T7_CORN',   'tier' => 'T7'],
            ['name' => 'Pumpkin',        'api_id' => 'T8_PUMPKIN','tier' => 'T8'],
        ];

        foreach ($farmPlantItems as $item) {
            Item::updateOrCreate(
                ['api_id' => $item['api_id']],
                ['category_id' => $farmPlants->id, 'name' => $item['name'], 'tier' => $item['tier']]
            );
        }

        // Herb Seeds
        $herbSeeds = Category::where('name', 'seeds')
            ->whereHas('parent', fn($q) => $q->where('name', 'Herb garden'))
            ->first();

        $herbSeedItems = [
            ['name' => 'Arcane Agaric Seeds',      'api_id' => 'T2_FARM_AGARIC_SEED',  'tier' => 'T2'],
            ['name' => 'Brightleaf Comfrey Seeds', 'api_id' => 'T3_FARM_COMFREY_SEED', 'tier' => 'T3'],
            ['name' => 'Crenellated Burdock Seeds','api_id' => 'T4_FARM_BURDOCK_SEED', 'tier' => 'T4'],
            ['name' => 'Dragon Teasel Seeds',      'api_id' => 'T5_FARM_TEASEL_SEED',  'tier' => 'T5'],
            ['name' => 'Elusive Foxglove Seeds',   'api_id' => 'T6_FARM_FOXGLOVE_SEED','tier' => 'T6'],
            ['name' => 'Firetouched Mullein Seeds','api_id' => 'T7_FARM_MULLEIN_SEED', 'tier' => 'T7'],
            ['name' => 'Ghoul Yarrow Seeds',       'api_id' => 'T8_FARM_YARROW_SEED',  'tier' => 'T8'],
        ];

        foreach ($herbSeedItems as $item) {
            Item::updateOrCreate(
                ['api_id' => $item['api_id']],
                ['category_id' => $herbSeeds->id, 'name' => $item['name'], 'tier' => $item['tier']]
            );
        }

        // Herbs
        $herbs = Category::where('name', 'herbs')
            ->whereHas('parent', fn($q) => $q->where('name', 'Herb garden'))
            ->first();

        $herbItems = [
            ['name' => 'Arcane Agaric',       'api_id' => 'T2_AGARIC',  'tier' => 'T2'],
            ['name' => 'Brightleaf Comfrey',  'api_id' => 'T3_COMFREY', 'tier' => 'T3'],
            ['name' => 'Crenellated Burdock', 'api_id' => 'T4_BURDOCK', 'tier' => 'T4'],
            ['name' => 'Dragon Teasel',       'api_id' => 'T5_TEASEL',  'tier' => 'T5'],
            ['name' => 'Elusive Foxglove',    'api_id' => 'T6_FOXGLOVE','tier' => 'T6'],
            ['name' => 'Firetouched Mullein', 'api_id' => 'T7_MULLEIN', 'tier' => 'T7'],
            ['name' => 'Ghoul Yarrow',        'api_id' => 'T8_YARROW',  'tier' => 'T8'],
        ];

        foreach ($herbItems as $item) {
            Item::updateOrCreate(
                ['api_id' => $item['api_id']],
                ['category_id' => $herbs->id, 'name' => $item['name'], 'tier' => $item['tier']]
            );
        }
    }
}
