<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlbionParseRecipes extends Command
{
    protected $signature   = 'albion:parse-recipes {--fresh : Hapus semua data lama sebelum parse ulang}';
    protected $description = 'Parse items.xml dari storage/app/albion/ dan isi tabel item_recipes';

    /**
     * Elemen XML yang mengandung item craftable.
     * Setiap elemen ini bisa punya <craftingrequirements> di dalamnya.
     *
     * Tambah elemen lain kalau nanti ada kategori baru yang perlu di-parse.
     */
    protected array $itemElements = [
        'weapon',       // Semua senjata (sword, bow, staff, dll)
        'armor',        // Semua armor (chest, head, foot)
        'offhand',      // Off-hand item (shield, tome, quiver)
        'mount',        // Mount
        'bag',          // Bag
        'cape',         // Cape
        'consumable',   // Consumable (food, potion)
        'simpleitem',   // Item sederhana (resource, material)
        'equipmentitem',// Equipment lain
        'journalitem',  // Laborer journal
        'furnitureitem',// Furniture
        'mountskin',    // Mount skin
        'laborer',      // Laborer
        'farmableitem', // Item farming
        'crystalleagueitem', // Crystal league item
    ];

    public function handle(): int
    {
        $this->info('');
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║     ALBION PARSE CRAFTING RECIPES      ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->info('');

        // Cek file items.xml sudah ada
        $xmlPath = Storage::path('albion/items.xml');
        if (!file_exists($xmlPath)) {
            $this->error('File items.xml tidak ditemukan!');
            $this->line('Jalankan dulu: php artisan albion:sync --file=items.xml');
            return self::FAILURE;
        }

        // Hapus data lama jika --fresh
        if ($this->option('fresh')) {
            $this->warn('🗑  Menghapus data lama...');
            DB::table('item_recipes')->truncate();
        }

        $this->line('📖 Membaca items.xml (~9MB, mohon tunggu...)');

        // Load XML — gunakan XMLReader untuk hemat memory karena file besar
        $xml = simplexml_load_file($xmlPath, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$xml) {
            $this->error('Gagal parse XML!');
            return self::FAILURE;
        }

        $this->info('✓ XML berhasil dimuat');
        $this->line('');
        $this->line('🔍 Memproses recipe crafting...');

        $totalInserted = 0;
        $totalSkipped  = 0;
        $batch         = [];
        $batchSize     = 500; // Insert per 500 baris sekaligus agar cepat

        // Loop setiap elemen item yang bisa di-craft
        foreach ($this->itemElements as $elementName) {
            // Ambil semua elemen dengan nama ini dari root XML
            if (!isset($xml->$elementName)) continue;

            foreach ($xml->$elementName as $item) {
                $itemApiId = (string) $item['uniquename'];
                if (!$itemApiId) continue;

                // ── RECIPE NORMAL (enchantment 0) ──────────────────────────
                // <craftingrequirements> langsung di dalam elemen item
                foreach ($item->craftingrequirements as $req) {
                    $silverCost    = (int) ($req['silver'] ?? 0);
                    $craftingFocus = (int) ($req['craftingfocus'] ?? 0);

                    foreach ($req->craftresource as $resource) {
                        $resourceId = (string) $resource['uniquename'];
                        $count      = (int) ($resource['count'] ?? 1);
                        if (!$resourceId || !$count) continue;

                        $batch[] = [
                            'item_api_id'               => $itemApiId,
                            'enchantment_level'         => 0,
                            'resource_api_id'           => $resourceId,
                            'resource_enchantment_level'=> (int) ($resource['enchantmentlevel'] ?? 0),
                            'count'                     => $count,
                            'silver_cost'               => $silverCost,
                            'crafting_focus'            => $craftingFocus,
                            'created_at'                => now(),
                            'updated_at'                => now(),
                        ];
                        $totalInserted++;
                    }
                }

                // ── RECIPE ENCHANTED (enchantment 1-4) ─────────────────────
                // <enchantments> → <enchantment enchantmentlevel="1"> → <craftingrequirements>
                if (isset($item->enchantments->enchantment)) {
                    foreach ($item->enchantments->enchantment as $enchantment) {
                        $encLevel = (int) ($enchantment['enchantmentlevel'] ?? 0);
                        if (!$encLevel) continue;

                        foreach ($enchantment->craftingrequirements as $req) {
                            $silverCost    = (int) ($req['silver'] ?? 0);
                            $craftingFocus = (int) ($req['craftingfocus'] ?? 0);

                            foreach ($req->craftresource as $resource) {
                                $resourceId = (string) $resource['uniquename'];
                                $count      = (int) ($resource['count'] ?? 1);
                                if (!$resourceId || !$count) continue;

                                $batch[] = [
                                    'item_api_id'               => $itemApiId,
                                    'enchantment_level'         => $encLevel,
                                    'resource_api_id'           => $resourceId,
                                    'resource_enchantment_level'=> (int) ($resource['enchantmentlevel'] ?? 0),
                                    'count'                     => $count,
                                    'silver_cost'               => $silverCost,
                                    'crafting_focus'            => $craftingFocus,
                                    'created_at'                => now(),
                                    'updated_at'                => now(),
                                ];
                                $totalInserted++;
                            }
                        }
                    }
                }

                // Insert batch kalau sudah cukup banyak
                if (count($batch) >= $batchSize) {
                    DB::table('item_recipes')->insert($batch);
                    $batch = [];
                    $this->output->write('.');
                }
            }
        }

        // Insert sisa batch yang belum masuk
        if (!empty($batch)) {
            DB::table('item_recipes')->insert($batch);
        }

        $this->line('');
        $this->line('');
        $this->info('════════════════════════════════════════');
        $this->info("✅ Recipe berhasil dimasukkan : {$totalInserted} baris");
        $this->info('Tabel: item_recipes');
        $this->info('');
        $this->line('💡 Tip: Jalankan dengan --fresh untuk parse ulang dari awal');
        $this->line('   php artisan albion:parse-recipes --fresh');
        $this->line('');

        return self::SUCCESS;
    }
}
