<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\CraftingStation;
use Illuminate\Console\Command;

class AssignCraftingStationCategories extends Command
{
    /**
     * php artisan crafting:assign hunters-lodge "Hunter's Lodge" --categories="Leather Hoods,Leather Jackets,Leather Shoes"
     */
    protected $signature = 'crafting:assign
                            {slug : Slug station, contoh: hunters-lodge}
                            {name : Nama tampilan, contoh: "Hunter'."'".'s Lodge"}
                            {--categories= : Nama kategori dipisah koma}
                            {--ids= : ID kategori langsung, dipisah koma (bypass pencarian nama)}';

    protected $description = 'Assign kategori market yang sudah ada ke sebuah crafting station (tanpa duplikasi data)';

    public function handle(): int
    {
        $slug = $this->argument('slug');
        $name = $this->argument('name');
        $categoriesInput = $this->option('categories');
        $idsInput = $this->option('ids');

        if (!$categoriesInput && !$idsInput) {
            $this->error('Wajib isi --categories="Nama Kategori 1,Nama Kategori 2" atau --ids=1,2,3');
            return self::FAILURE;
        }

        $station = CraftingStation::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );

        $attachIds = [];
        $notFound = [];
        $ambiguous = [];

        // Mode --ids: langsung attach ID yang dikasih, tanpa pencarian nama
        if ($idsInput) {
            $ids = array_filter(array_map('trim', explode(',', $idsInput)));
            $found = Category::where('group', 'market')->whereIn('id', $ids)->pluck('id')->all();
            $missing = array_diff($ids, $found);
            $attachIds = array_merge($attachIds, $found);
            foreach ($missing as $m) {
                $notFound[] = "id={$m}";
            }
        }

        $names = $categoriesInput
            ? array_filter(array_map('trim', explode(',', $categoriesInput)))
            : [];

        foreach ($names as $catName) {
            $matches = Category::where('group', 'market')
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($catName)])
                ->get(['id', 'name', 'parent_id']);

            if ($matches->isEmpty()) {
                $notFound[] = $catName;
                continue;
            }

            if ($matches->count() === 1) {
                $attachIds[] = $matches->first()->id;
                continue;
            }

            // Ada lebih dari satu kategori dengan nama sama -> minta user pilih manual
            $ambiguous[$catName] = $matches->map(function ($c) {
                $parent = $c->parent_id ? Category::find($c->parent_id) : null;
                $grandparent = $parent && $parent->parent_id ? Category::find($parent->parent_id) : null;
                $path = collect([$grandparent?->name, $parent?->name, $c->name])
                    ->filter()
                    ->implode(' > ');
                return "  [id={$c->id}] {$path}";
            })->all();
        }

        if (!empty($ambiguous)) {
            $this->warn('Beberapa nama kategori punya lebih dari satu kecocokan. Pilih ID yang benar manual, lalu jalankan ulang command dengan menambahkan --ids=<id1,id2,...> atau ganti nama jadi lebih spesifik:');
            foreach ($ambiguous as $catName => $options) {
                $this->line("Nama: \"{$catName}\"");
                foreach ($options as $line) {
                    $this->line($line);
                }
            }
        }

        if (!empty($notFound)) {
            $this->warn('Kategori berikut tidak ditemukan di group=market (cek ejaan/kapitalisasi):');
            foreach ($notFound as $n) {
                $this->line("  - {$n}");
            }
        }

        if (!empty($attachIds)) {
            $station->categories()->syncWithoutDetaching($attachIds);
            $this->info('Berhasil attach ' . count($attachIds) . ' kategori ke station "' . $station->name . '" (slug: ' . $station->slug . ').');
            $this->line('Category IDs: ' . implode(', ', $attachIds));
        } else {
            $this->error('Tidak ada kategori yang berhasil di-attach.');
        }

        return self::SUCCESS;
    }
}