<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AlbionSyncItems extends Command
{
    protected $signature   = 'albion:sync-items';
    protected $description = 'PERBAIKAN DATA item yang SUDAH ADA di tabel items (tier/enc). TIDAK PERNAH insert item baru — item baru wajib lewat mapping tool ("Simpan Semua").';

    // Daftar tag XML consumable, disimpan sebagai referensi kalau nanti perlu
    // dipakai lagi (misal buat fitur lain), TAPI TIDAK dipakai untuk insert baru.
    protected array $itemElements = [
        'weapon', 'armor', 'offhand', 'mount', 'bag', 'cape',
        'consumableitem', 'simpleitem', 'equipmentitem', 'journalitem',
        'furnitureitem', 'mountskin', 'farmableitem', 'crystalleagueitem',
    ];

    public function handle(): int
    {
        $this->info('');
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║   ALBION FIX ITEM (existing rows only) ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->warn('⚠️  Command ini TIDAK insert item baru ke tabel items.');
        $this->warn('⚠️  Item baru wajib di-mapping manual lewat halaman mapping tool.');
        $this->line('');

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

        // Ambil semua api_id yang SUDAH ADA di tabel items — hanya baris inilah
        // yang boleh disentuh/diupdate oleh command ini.
        $existingItems = DB::table('items')->pluck('id', 'api_id');

        $totalChecked = 0;
        $totalFixed   = 0;

        foreach ($this->itemElements as $elementName) {
            if (!isset($xml->$elementName)) continue;

            foreach ($xml->$elementName as $item) {
                $apiId = (string) $item['uniquename'];
                if (!$apiId || !isset($existingItems[$apiId])) continue; // skip item yang belum ada di DB

                $totalChecked++;

                $enc = (int) ($item['enchantmentlevel'] ?? 0);
                if ($enc === 0 && preg_match('/_LEVEL(\d+)$/', $apiId, $m)) {
                    $enc = (int) $m[1];
                }

                $currentEnc = DB::table('items')->where('id', $existingItems[$apiId])->value('enc');
                if ((int) $currentEnc !== $enc) {
                    DB::table('items')->where('id', $existingItems[$apiId])->update([
                        'enc'        => $enc,
                        'updated_at' => now(),
                    ]);
                    $totalFixed++;
                }
            }
        }

        $this->line('');
        $this->info('════════════════════════════════════════');
        $this->info("🔍 Item dicek     : {$totalChecked} (sudah ada di DB)");
        $this->info("🔧 Enc diperbaiki : {$totalFixed}");
        $this->line('');
        $this->line('💡 Item BARU yang belum ada di DB tidak disentuh sama sekali.');
        $this->line('   Cek halaman mapping tool tab "Belum di-DB" untuk mapping manual.');

        return self::SUCCESS;
    }
}
