<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class AlbionBuildCatalog extends Command
{
    protected $signature   = 'albion:build-catalog';
    protected $description = 'Bikin cache JSON semua uniquename dari items.xml (dipakai buat deteksi item yang belum di-mapping, termasuk food/potion yang gak muncul di item_recipes)';

    protected array $itemElements = [
        'weapon', 'armor', 'offhand', 'mount', 'bag', 'cape',
        'consumableitem', 'simpleitem', 'equipmentitem', 'journalitem',
        'furnitureitem', 'mountskin', 'farmableitem', 'crystalleagueitem',
    ];

    public function handle(): int
    {
        $xmlPath = storage_path('app/private/albion/items.xml');
        if (!file_exists($xmlPath)) {
            $this->error('File items.xml tidak ditemukan!');
            return self::FAILURE;
        }

        $this->line('📖 Membaca items.xml...');
        $xml = simplexml_load_file($xmlPath, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$xml) {
            $this->error('Gagal parse XML!');
            return self::FAILURE;
        }

        $apiIds = [];
        foreach ($this->itemElements as $elementName) {
            if (!isset($xml->$elementName)) continue;
            foreach ($xml->$elementName as $item) {
                $apiId = (string) $item['uniquename'];
                if ($apiId) $apiIds[] = $apiId;
            }
        }

        $apiIds = array_values(array_unique($apiIds));
        sort($apiIds);

        $outPath = storage_path('app/private/albion/catalog.json');
        file_put_contents($outPath, json_encode($apiIds));

        $this->info("✅ Katalog dibuat: " . count($apiIds) . " uniquename disimpan ke catalog.json");
        $this->line('💡 Jalankan lagi command ini tiap kali items.xml di-update dari game.');

        return self::SUCCESS;
    }
}
