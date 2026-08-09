<?php

namespace App\Console\Commands;

use App\Models\JournalRequirement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

class SyncJournalRequirements extends Command
{
    protected $signature = 'albion:sync-journal-requirements';

    protected $description = 'Sync max fame & base loot amount tiap journal (per tipe & tier) dari items.xml';

    /**
     * Mapping suffix uniquename (T{tier}_JOURNAL_{SUFFIX}) ke journal_name
     * yang dipakai di crafting_stations.journal_name.
     *
     * Kalau ada suffix baru yang belum ke-mapping di sini, journal_name-nya
     * bakal di-generate otomatis dari suffix (lihat resolveJournalName())
     * biar sync tidak gagal, tapi sebaiknya ditambahkan manual supaya
     * konsisten dengan penamaan resmi di game.
     */
    private const JOURNAL_NAME_MAP = [
        // Crafting journals
        'HUNTER' => "Fletcher's Journal",
        'MAGE' => "Imbuer's Journal",
        'WARRIOR' => "Blacksmith's Journal",
        'TOOLMAKER' => "Tinker's Journal",

        // Gathering journals
        'WOOD' => "Lumberjack's Journal",
        'ORE' => "Miner's Journal",
        'HIDE' => "Skinner's Journal",
        'FIBER' => "Farmer's Journal",
        'STONE' => "Quarrier's Journal",
        'FISHING' => "Fisherman's Journal",

        // Lainnya
        'MERCENARY' => "Mercenary's Journal",

        // Journal trofi — TROPHY_GENERAL adalah "Generalist's Journal" yang
        // dipakai di dropdown crafting station (bukan entry terpisah "JOURNAL_GENERAL",
        // itu tidak ada di items.xml — sudah dikonfirmasi lewat grep).
        'TROPHY_GENERAL' => "Generalist's Journal",
        'TROPHY_MERCENARY' => "Mercenary's Trophy Journal",
        'TROPHY_HIDE' => "Skinner's Trophy Journal",
        'TROPHY_WOOD' => "Lumberjack's Trophy Journal",
        'TROPHY_STONE' => "Quarrier's Trophy Journal",
        'TROPHY_ORE' => "Miner's Trophy Journal",
        'TROPHY_FIBER' => "Farmer's Trophy Journal",
        'TROPHY_FISHING' => "Fisherman's Trophy Journal",
    ];

    public function handle(): int
    {
        $path = 'albion/items.xml';

        if (! Storage::disk('local')->exists($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $xmlContent = Storage::disk('local')->get($path);
        $xml = new SimpleXMLElement($xmlContent);

        // Item journal biasanya berupa <simpleitem> di items.xml
        $journalNodes = $xml->xpath('//*[starts-with(@uniquename, "T") and contains(@uniquename, "_JOURNAL_")]');

        if (empty($journalNodes)) {
            $this->warn('Tidak ada node journal ditemukan di items.xml.');

            return self::SUCCESS;
        }

        $synced = 0;

        foreach ($journalNodes as $node) {
            $uniqueName = (string) $node['uniquename'];
            $tier = (int) $node['tier'];
            $maxFame = (int) $node['maxfame'];
            $baseLootAmount = isset($node['baselootamount'])
                ? (float) $node['baselootamount']
                : null;

            if ($tier === 0 || $maxFame === 0) {
                continue;
            }

            $suffix = $this->extractSuffix($uniqueName);
            $journalName = $this->resolveJournalName($suffix);

            JournalRequirement::updateOrCreate(
                [
                    'journal_name' => $journalName,
                    'tier' => $tier,
                ],
                [
                    'unique_name' => $uniqueName,
                    'max_fame' => $maxFame,
                    'base_loot_amount' => $baseLootAmount,
                ]
            );

            $synced++;
        }

        $this->info("Selesai. {$synced} journal requirement disinkronkan.");

        return self::SUCCESS;
    }

    private function extractSuffix(string $uniqueName): string
    {
        // T4_JOURNAL_HUNTER -> HUNTER
        return preg_replace('/^T\d+_JOURNAL_/', '', $uniqueName);
    }

    private function resolveJournalName(string $suffix): string
    {
        if (isset(self::JOURNAL_NAME_MAP[$suffix])) {
            return self::JOURNAL_NAME_MAP[$suffix];
        }

        // Fallback biar sync tidak gagal untuk suffix yang belum dipetakan.
        // Sebaiknya ditambahkan ke JOURNAL_NAME_MAP begitu ketahuan.
        return ucfirst(strtolower($suffix))." Journal";
    }
}
