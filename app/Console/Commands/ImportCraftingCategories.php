<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Import kategori Mages Tower (Weapons, Chest armor, dll) dari file JSON
 * ke tabel `categories`, dengan group = 'crafting' biar kepisah dari
 * kategori resource gathering yang dipakai di /market (group = 'market').
 *
 * Cara pakai:
 *   1. Taruh file JSON di storage/app/data/crafting-categories.json
 *   2. php artisan crafting:import-categories
 *
 * Command ini AMAN dijalankan berkali-kali (idempotent) — kalau nama
 * kategori di level yang sama & parent yang sama sudah ada, gak bikin
 * duplikat, cuma dipakai ulang id-nya.
 */
class ImportCraftingCategories extends Command
{
    protected $signature = 'crafting:import-categories {--file=data/crafting-categories.json}';
    protected $description = 'Import pohon kategori crafting (Weapons/Armor/dll) dari JSON ke tabel categories (group=crafting)';

    public function handle(): int
    {
        $path = storage_path('app/' . $this->option('file'));

        if (!File::exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            $this->line('Taruh file JSON-nya di storage/app/data/crafting-categories.json dulu.');
            return self::FAILURE;
        }

        $tree = json_decode(File::get($path), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('JSON tidak valid: ' . json_last_error_msg());
            return self::FAILURE;
        }

        $countCreated = 0;
        $this->importLevel($tree, null, $countCreated);

        $this->info("Selesai. Total kategori baru dibuat: {$countCreated}");
        $this->line('Kategori root yang ke-import: ' . collect($tree)->pluck('name')->implode(', '));

        return self::SUCCESS;
    }

    /**
     * Rekursif: bikin/reuse category per level, jalan ke children-nya.
     * Level 3 (leaf paling dalam, misal "Arcane staff" individual item)
     * tetap dibuat sebagai row category juga, PERSIS seperti pola market
     * (3 level: root -> sub -> leaf), biar reuse categories()/items()
     * endpoint yang sudah ada tanpa perlu logic baru.
     */
    private function importLevel(array $nodes, ?int $parentId, int &$countCreated): void
    {
        foreach ($nodes as $node) {
            $category = Category::firstOrCreate(
                [
                    'name'      => $node['name'],
                    'parent_id' => $parentId,
                    'group'     => 'crafting',
                ]
            );

            if ($category->wasRecentlyCreated) {
                $countCreated++;
            }

            if (!empty($node['children'])) {
                $this->importLevel($node['children'], $category->id, $countCreated);
            }
        }
    }
}
