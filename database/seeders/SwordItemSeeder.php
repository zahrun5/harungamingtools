<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Seeder;

class SwordItemSeeder extends Seeder
{
    private $prefixes = [
        'T4' => "Adept's",
        'T5' => "Expert's",
        'T6' => "Master's",
        'T7' => "Grandmaster's",
        'T8' => "Elder's",
    ];

    private $variants = [
        'Broadsword'    => 'BROADSWORD',
        'Claymore'      => 'CLAYMORE',
        'Dual sword'    => 'DUALSWORD',
        'Clarent blade' => 'CLARENTBLADE',
        'Carving sword' => 'CARVINGSWORD',
        'Galatine pair' => 'GALATINEPAIR',
        'Kingmaker'     => 'KINGMAKER',
        'Infinity Blade'=> 'INFINITYBLADE',
    ];

    public function run(): void
    {
        $swordCategory = Category::where('name', 'Sword')->first();

        if (!$swordCategory) {
            $this->command->error('Kategori "Sword" tidak ditemukan. Pastikan seeder kategori sudah jalan.');
            return;
        }

        foreach ($this->variants as $categoryName => $apiCode) {
            $category = Category::where('parent_id', $swordCategory->id)
                ->where('name', $categoryName)
                ->first();

            if (!$category) {
                $this->command->warn("Kategori turunan \"{$categoryName}\" tidak ditemukan, dilewati.");
                continue;
            }

            foreach ($this->prefixes as $tier => $prefix) {
                Item::create([
                    'category_id' => $category->id,
                    'name' => "{$prefix} {$categoryName}",
                    'api_id' => "{$tier}_2H_{$apiCode}",
                    'tier' => $tier,
                    'enc' => 0,
                    'quality' => null,
                    'desc' => null,
                ]);
            }
        }

        $this->command->info('Item Sword berhasil dimasukkan (perlu verifikasi api_id manual).');
    }
}
