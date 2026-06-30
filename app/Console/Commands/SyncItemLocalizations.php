<?php

namespace App\Console\Commands;

use App\Models\ItemLocalization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SyncItemLocalizations extends Command
{
    /**
     * php artisan sync:item-localizations
     * php artisan sync:item-localizations --keep-raw
     * php artisan sync:item-localizations --langs=EN-US,ID-ID   (default: semua bahasa yang tersedia)
     */
    protected $signature = 'sync:item-localizations {--langs=} {--keep-raw}';

    protected $description = 'Download localization.json dari ao-bin-dumps, extract semua nama item multi-bahasa, simpan ke tabel item_localizations + backup JSON di database/seeders/data/';

    protected string $sourceUrl = 'https://raw.githubusercontent.com/ao-data/ao-bin-dumps/master/localization.json';

    // Semua locale yang tersedia di file source (per pengecekan manual)
    protected array $allKnownLocales = [
        'EN-US', 'DE-DE', 'FR-FR', 'RU-RU', 'PL-PL', 'ES-ES', 'PT-BR',
        'IT-IT', 'ZH-CN', 'KO-KR', 'JA-JP', 'ZH-TW', 'ID-ID', 'TR-TR', 'AR-SA',
    ];

    public function handle(): int
    {
        $wantedLocales = $this->option('langs')
            ? array_map('trim', explode(',', $this->option('langs')))
            : $this->allKnownLocales;

        $tmpPath = storage_path('app/tmp/localization.json');
        $outputDir = database_path('seeders/data');
        $outputPath = $outputDir . '/item_localizations.json';

        File::ensureDirectoryExists(dirname($tmpPath));
        File::ensureDirectoryExists($outputDir);

        // 1. Download
        $this->info("Downloading localization.json dari ao-data/ao-bin-dumps...");

        $response = Http::timeout(120)->withOptions(['sink' => $tmpPath])->get($this->sourceUrl);

        if (!$response->successful()) {
            $this->error("Gagal download. HTTP status: " . $response->status());
            return self::FAILURE;
        }

        $this->info("Download selesai (" . round(filesize($tmpPath) / 1024 / 1024, 1) . " MB). Parsing...");

        // 2. Parse
        $data = json_decode(file_get_contents($tmpPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("JSON parse error: " . json_last_error_msg());
            return self::FAILURE;
        }

        $entries = $data['tmx']['body']['tu'] ?? null;

        if (!$entries) {
            $this->error("Struktur tidak sesuai ekspektasi (tmx.body.tu tidak ditemukan).");
            return self::FAILURE;
        }

        $this->info("Total entries: " . count($entries) . " | Locales yang diambil: " . implode(', ', $wantedLocales));

        // 3. Filter & extract semua bahasa
        // Struktur akhir: [ api_id => [ locale => name ] ]
        $itemNames = [];
        $rowsForUpsert = [];

        foreach ($entries as $entry) {
            $tuid = $entry['@tuid'] ?? null;

            if (!$tuid || !str_starts_with($tuid, '@ITEMS_') || str_ends_with($tuid, '_DESC')) {
                continue;
            }

            $apiId = substr($tuid, strlen('@ITEMS_'));

            $tuv = $entry['tuv'] ?? [];
            if (isset($tuv['@xml:lang'])) {
                $tuv = [$tuv]; // normalisasi kalau cuma 1 bahasa (bukan array)
            }

            foreach ($tuv as $t) {
                $locale = $t['@xml:lang'] ?? null;
                $name = $t['seg'] ?? null;

                if (!$locale || !$name || !in_array($locale, $wantedLocales, true)) {
                    continue;
                }

                $itemNames[$apiId][$locale] = $name;

                $rowsForUpsert[] = [
                    'api_id' => $apiId,
                    'locale' => $locale,
                    'name' => $name,
                ];
            }
        }

        if (empty($itemNames)) {
            $this->error("Tidak ada item yang berhasil di-extract. Cek struktur source.");
            return self::FAILURE;
        }

        $this->info("Item unik ditemukan: " . count($itemNames));
        $this->info("Total baris (item x locale): " . count($rowsForUpsert));

        // 4. Upsert ke DB, per chunk biar gak overload memory/query
        $this->info("Upserting ke tabel item_localizations...");

        collect($rowsForUpsert)->chunk(500)->each(function ($chunk) {
            ItemLocalization::upsert(
                $chunk->toArray(),
                uniqueBy: ['api_id', 'locale'],
                update: ['name']
            );
        });

        // 5. Backup ke JSON juga, sejajar categories.json, format: { api_id: { locale: name } }
        ksort($itemNames);

        File::put(
            $outputPath,
            json_encode($itemNames, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $this->info("Backup JSON tersimpan di: {$outputPath}");

        // 6. Clear cache lookup lama (karena data berubah)
        // Catatan: Cache::rememberForever pakai key per api_id+locale, jadi flush total
        // lebih simpel daripada nge-track semua key satu-satu. Kalau cache driver
        // production-mu shared (Redis dipakai fitur lain juga), pertimbangkan
        // pakai cache tag/prefix khusus supaya flush ini gak ganggu cache lain.
        Cache::flush();
        $this->info("Cache lookup nama item sudah di-clear.");

        // 7. Cleanup file mentah
        if (!$this->option('keep-raw')) {
            File::delete($tmpPath);
            $this->info("File mentah localization.json (87MB) sudah dihapus.");
        } else {
            $this->info("File mentah disimpan di: {$tmpPath}");
        }

        $this->info("Selesai. " . count($itemNames) . " item x " . count($wantedLocales) . " locale max.");

        return self::SUCCESS;
    }
}
