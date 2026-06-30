<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Item;

class AlbionSyncItems extends Command
{
    protected $signature   = 'albion:sync-items {--fresh : Hapus semua item lama sebelum sync}';
    protected $description = 'Parse items.xml dan isi tabel items + categories otomatis';

    protected array $itemElements = [
        'weapon', 'armor', 'offhand', 'mount', 'bag', 'cape',
        'consumable', 'simpleitem', 'equipmentitem', 'journalitem',
        'furnitureitem', 'mountskin', 'farmableitem', 'crystalleagueitem',
    ];

    public function handle(): int
    {
        $this->info('');
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║        ALBION SYNC ITEMS & CATEGORIES  ║');
        $this->info('╚════════════════════════════════════════╝');

        $xmlPath = storage_path('app/private/albion/items.xml');
        if (!file_exists($xmlPath)) {
            $this->error('File items.xml tidak ditemukan!');
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('🗑  Menghapus data lama...');
            DB::table('items')->truncate();
            DB::table('categories')->truncate();
        }

        $this->line('📖 Membaca items.xml...');
        $xml = simplexml_load_file($xmlPath, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$xml) {
            $this->error('Gagal parse XML!');
            return self::FAILURE;
        }

        // Cache category agar tidak query DB berkali-kali
        // Format: 'shopcategory/shopsubcategory1' => category_id
        $catCache = [];

        $getOrCreateCat = function (string $shopCat, string $shopSub) use (&$catCache) {
            // Level 1: shopcategory
            $key1 = $shopCat;
            if (!isset($catCache[$key1])) {
                $cat = Category::firstOrCreate(
                    ['name' => $shopCat, 'parent_id' => null]
                );
                $catCache[$key1] = $cat->id;
            }
            $parentId = $catCache[$key1];

            if (!$shopSub) return $parentId;

            // Level 2: shopsubcategory1
            $key2 = "$shopCat/$shopSub";
            if (!isset($catCache[$key2])) {
                $cat = Category::firstOrCreate(
                    ['name' => $shopSub, 'parent_id' => $parentId]
                );
                $catCache[$key2] = $cat->id;
            }
            return $catCache[$key2];
        };

        $totalInserted = 0;
        $totalSkipped  = 0;
        $batch         = [];
        $batchSize     = 500;

        // Set existing api_ids agar tidak duplikat
        $existing = DB::table('items')->pluck('api_id')->flip()->toArray();

        foreach ($this->itemElements as $elementName) {
            if (!isset($xml->$elementName)) continue;

            foreach ($xml->$elementName as $item) {
                $apiId = (string) $item['uniquename'];
                if (!$apiId) continue;

                if (isset($existing[$apiId])) {
                    $totalSkipped++;
                    continue;
                }

                $shopCat = (string) ($item['shopcategory'] ?? '');
                $shopSub = (string) ($item['shopsubcategory1'] ?? '');
                $tier    = (string) ($item['tier'] ?? '');
                $enc     = (int)    ($item['enchantmentlevel'] ?? 0);

                if (!$shopCat) continue; // skip item tanpa kategori

                $categoryId = $getOrCreateCat($shopCat, $shopSub);

                // Prettify nama dari api_id
                // T4_2H_BROADSWORD → Broadsword
                // T4_METALBAR → Metalbar
                $nameParts = explode('_', $apiId);
                // Buang prefix tier (T1-T8) dan enc (@1 dst)
                array_shift($nameParts); // buang T4
                if (count($nameParts) > 1 && $nameParts[0] === '2H') {
                    array_shift($nameParts); // buang 2H
                }
                $name = ucwords(strtolower(implode(' ', $nameParts)));

                $tierLabel = $tier ? 'T' . $tier : null;

                $batch[] = [
                    'category_id' => $categoryId,
                    'name'        => $name,
                    'api_id'      => $apiId,
                    'tier'        => $tierLabel,
                    'enc'         => $enc,
                    'quality'     => null,
                    'desc'        => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
                $existing[$apiId] = true;
                $totalInserted++;

                if (count($batch) >= $batchSize) {
                    DB::table('items')->insert($batch);
                    $batch = [];
                    $this->output->write('.');
                }
            }
        }

        if (!empty($batch)) {
            DB::table('items')->insert($batch);
        }

        $this->line('');
        $this->line('');
        $this->info('════════════════════════════════════════');
        $this->info("✅ Item dimasukkan  : {$totalInserted}");
        $this->info("⏭  Item dilewati   : {$totalSkipped} (sudah ada)");
        $this->info('');
        $this->line('💡 Gunakan --fresh untuk sync ulang dari awal');
        $this->line('   php artisan albion:sync-items --fresh');

        return self::SUCCESS;
    }
}
