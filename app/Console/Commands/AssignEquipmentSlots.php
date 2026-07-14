<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class AssignEquipmentSlots extends Command
{
    protected $signature = 'items:assign-slot {--dry-run : Cuma preview, gak nyimpen ke DB}';
    protected $description = 'Auto-assign equipment_slot ke categories berdasarkan keyword di nama kategori (untuk fitur Build)';

    // urutan penting: yang lebih spesifik dicek duluan biar gak salah match
    // (misal "Off-hand" harus dicek sebelum "Weapons" umum)
    private array $rules = [
        'OffHand' => ['off-hand', 'off hand', 'offhand', 'tome of spells', 'shield', 'orb', 'horn', 'totem', 'book'],
        'Head' => ['head armor', 'helmet', 'hood', 'cowl'],
        'Armor' => ['chest armor', 'armor', 'robe', 'jacket', 'plate armor'],
        'Shoes' => ['foot armor', 'boots', 'sandals', 'shoes'],
        'Cape' => ['cape', 'cloak'],
        'Bag' => ['bag', 'satchel'],
        'Mount' => ['mount', 'horse', 'ox', 'direwolf', 'direboar', 'mammoth', 'swiftclaw'],
        'Potion' => ['potion'],
        'Food' => ['meal', 'food', 'stew', 'pie', 'omelette', 'soup', 'salad'],
        // MainHand dicek paling akhir sebagai fallback untuk top-level "Weapons"
        'MainHand' => ['weapons', 'sword', 'axe', 'bow', 'crossbow', 'dagger', 'spear', 'mace', 'hammer', 'staff', 'quarterstaff'],
    ];

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $categories = Category::all();

        $matched = 0;
        $unmatched = [];

        foreach ($categories as $category) {
            // gabungin nama kategori sendiri + nama parent (kalau ada relasi parent)
            // biar keyword matching bisa baca konteks top-level (misal "Weapons > Fire staff > Dawnsong")
            $haystack = strtolower($this->buildNameChain($category));

            $slot = $this->detectSlot($haystack);

            if ($slot) {
                $matched++;
                if (! $isDryRun) {
                    $category->update(['equipment_slot' => $slot]);
                }
                $this->line("[{$slot}] {$category->name}");
            } else {
                $unmatched[] = $category->name;
            }
        }

        $this->newLine();
        $this->info("Matched: {$matched} / {$categories->count()}");

        if (count($unmatched) > 0) {
            $this->newLine();
            $this->warn('Kategori yang PERLU DI-ASSIGN MANUAL (kemungkinan bukan equipment, atau nama tidak ke-detect):');
            foreach ($unmatched as $name) {
                $this->line("  - {$name}");
            }
        }

        if ($isDryRun) {
            $this->newLine();
            $this->comment('Ini cuma DRY RUN, belum ada yang disimpan. Jalankan tanpa --dry-run untuk apply.');
        }

        return self::SUCCESS;
    }

    private function detectSlot(string $haystack): ?string
    {
        foreach ($this->rules as $slot => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($haystack, $keyword)) {
                    return $slot;
                }
            }
        }

        return null;
    }

    // rekursif naik ke parent, biar keyword top-level ("Weapons", "Off-hands") ikut kebaca
    // asumsi Category punya relasi parent()/kolom parent_id -- sesuaikan kalau nama relasi beda
    private function buildNameChain(Category $category): string
    {
        $chain = [$category->name];
        $current = $category;

        // batasi 5 level ke atas biar gak infinite loop kalau ada data aneh
        for ($i = 0; $i < 5; $i++) {
            $parent = $current->parent ?? null; // sesuaikan nama relasi kalau bukan 'parent'
            if (! $parent) {
                break;
            }
            $chain[] = $parent->name;
            $current = $parent;
        }

        return implode(' ', $chain);
    }
}
