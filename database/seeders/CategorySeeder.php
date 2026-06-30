<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $json = file_get_contents(__DIR__ . '/data/categories.json');
        $tree = json_decode($json, true);

        if (!$tree) {
            $this->command->error('Gagal baca JSON kategori.');
            return;
        }

        $this->insertTree($tree, null);
        $this->command->info('Kategori berhasil dimasukkan.');
    }

    private function insertTree(array $nodes, ?int $parentId): void
    {
        foreach ($nodes as $node) {
            $category = Category::create([
                'parent_id' => $parentId,
                'name' => $node['name'],
            ]);

            if (!empty($node['children'])) {
                $this->insertTree($node['children'], $category->id);
            }
        }
    }
}
