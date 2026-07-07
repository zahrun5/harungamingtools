<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AlbionSync extends Command
{
    protected $signature   = 'albion:sync {--file= : Download file tertentu saja, misal --file=items.xml}';
    protected $description = 'Download data game Albion Online dari repo ao-bin-dumps ke storage/app/albion/';

    /**
     * Daftar file yang akan di-download dari repo ao-bin-dumps.
     *
     * Tambah atau hapus entry sesuai kebutuhan.
     * Format: 'nama_file' => 'keterangan isi file'
     *
     * FILE YANG TERSEDIA DI REPO (referensi lengkap):
     * - items.xml/json         → Data semua item: weapon, armor, resource, consumable, dll
     *                            Termasuk craftingrequirements (recipe + bahan) dan enchantment
     * - resources.xml/json     → Data resource alam: ore, wood, fiber, hide, stone, dll
     * - buildings.xml/json     → Data gedung: crafting station, refining station, dll
     * - spells.xml/json        → Data semua spell/ability item
     * - mobs.xml/json          → Data mob/monster dan drop-nya
     * - harvestables.xml/json  → Data node resource di dunia (ore vein, tree, dll)
     * - loot.xml/json          → Data loot table dari chest, mob, dungeon
     * - world.json             → Data peta dunia: cluster, biome, koneksi antar zone
     * - progressiontables.xml  → Data fame/progression untuk crafting & gathering
     * - craftingmodifiers.xml  → Modifier crafting: bonus focus, return rate, dll
     * - buffshrines.xml        → Data shrine buff di dunia
     * - achievements.xml       → Data achievement/combat spec
     */
    protected array $files = [
        // ── WAJIB ──────────────────────────────────────────────────────────
        // Berisi semua item game beserta recipe crafting lengkap per tier & enchantment
        'items.xml'            => 'Semua item + recipe crafting (wajib untuk crafting calculator)',

        // ── RESOURCE & DUNIA ───────────────────────────────────────────────
        // Data node resource yang bisa di-gather di dunia
        'resources.xml'        => 'Data resource alam (ore, wood, fiber, hide, stone)',

        // Data peta dunia: nama zone, biome, koneksi portal
        'world.json'           => 'Peta dunia Albion (zone, biome, koneksi)',

        // ── GEDUNG & CRAFTING ──────────────────────────────────────────────
        // Data semua gedung: crafting station, refining station, storage, dll
        'buildings.xml'        => 'Data gedung (crafting/refining station, storage)',

        // Modifier crafting: return rate bahan, bonus focus per gedung, dll
        'craftingmodifiers.xml'=> 'Modifier crafting (return rate, focus bonus)',

        // Tabel fame/XP untuk crafting & gathering per tier
        'progressiontables.xml'=> 'Tabel fame crafting & gathering per tier',

        // ── COMBAT & GAMEPLAY ──────────────────────────────────────────────
        // Data semua spell/ability: nama, deskripsi, cooldown, efek
        'spells.xml'           => 'Data semua spell/ability item',

        // Data mob/monster: stats, drop, lokasi spawn
        'mobs.xml'             => 'Data mob/monster dan drop-nya',

        // Loot table: isi chest, drop mob, reward dungeon
        'loot.xml'             => 'Loot table (chest, mob drop, dungeon reward)',
    ];

    // Base URL raw file dari GitHub
    const BASE_URL = 'https://raw.githubusercontent.com/ao-data/ao-bin-dumps/master/';

    // Folder penyimpanan di dalam storage/app/
    const STORAGE_DIR = 'albion';

    public function handle(): int
    {
        $this->info('');
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║       ALBION ONLINE DATA SYNC          ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->info('');

        // Pastikan folder albion/ sudah ada di storage/app/
        if (!Storage::exists(self::STORAGE_DIR)) {
            Storage::makeDirectory(self::STORAGE_DIR);
            $this->line('📁 Folder storage/app/albion/ dibuat.');
        }

        // Jika opsi --file diisi, hanya download file itu saja
        $targetFile = $this->option('file');
        if ($targetFile) {
            if (!isset($this->files[$targetFile])) {
                $this->error("File '{$targetFile}' tidak ada dalam daftar.");
                $this->line('File yang tersedia: ' . implode(', ', array_keys($this->files)));
                return self::FAILURE;
            }
            $filesToDownload = [$targetFile => $this->files[$targetFile]];
        } else {
            $filesToDownload = $this->files;
        }

        $success = 0;
        $failed  = 0;

        foreach ($filesToDownload as $filename => $description) {
            $this->line("⬇  Mengunduh {$filename}...");
            $this->line("   ({$description})");

            $url      = self::BASE_URL . $filename;
            $savePath = self::STORAGE_DIR . '/' . $filename;

            try {
                // Download file — timeout 120 detik karena beberapa file besar (items.xml ~9MB)
                $response = Http::timeout(120)->get($url);

                if ($response->failed()) {
                    $this->error("   ✗ Gagal download {$filename} (HTTP {$response->status()})");
                    $failed++;
                    continue;
                }

                // Simpan ke storage/app/albion/
                Storage::put($savePath, $response->body());

                $size = round(strlen($response->body()) / 1024, 1);
                $this->info("   ✓ {$filename} tersimpan ({$size} KB)");
                $success++;

            } catch (\Exception $e) {
                $this->error("   ✗ Error: " . $e->getMessage());
                $failed++;
            }

            $this->line('');
        }

        // ── RINGKASAN ──────────────────────────────────────────────────────
        $this->info('════════════════════════════════════════');
        $this->info("✅ Berhasil : {$success} file");
        if ($failed > 0) {
            $this->warn("❌ Gagal    : {$failed} file");
        }
        $this->info('File tersimpan di: storage/app/albion/');
        $this->info('');

        // Tampilkan catatan kapan terakhir sync
        $logPath = self::STORAGE_DIR . '/last_sync.txt';
        Storage::put($logPath, 'Last sync: ' . now()->toDateTimeString() . PHP_EOL .
            'Files: ' . implode(', ', array_keys($filesToDownload)));
        $this->line('📝 Log tersimpan di storage/app/albion/last_sync.txt');
        $this->line('');

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
