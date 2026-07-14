<?php

/**
 * Seed manual: Seeds & Baby Animal
 * -----------------------------------------------------------
 * Item-item ini gak pernah muncul di item_recipes (bukan bahan
 * crafting, tapi ditanam/dikembangbiakin lewat farming/pasture/
 * kennel), jadi tool mapping /dev/test gak bisa nangkep mereka.
 * Makanya di-insert manual langsung ke tabel `items`.
 *
 * Cara pakai:
 *   1. Upload file ini ke server, taruh di mana aja (misal /home/harun/hgt-laravel/)
 *   2. php artisan tinker
 *   3. >>> include 'seed_farming_items.php';
 *      (sesuaikan path kalau taruhnya bukan di root project)
 *
 * Aman dijalanin berkali-kali: pakai updateOrInsert berdasarkan api_id,
 * jadi kalau ternyata sebagian udah ada di DB, gak bakal duplikat.
 */

// category_id: seeds = 514, baby animal Pasture = 520, baby animal Kennel = 525
const CAT_SEEDS = 514;
const CAT_BABY_PASTURE = 520;
const CAT_BABY_KENNEL = 525;

$tierPrefixName = [
    1 => "Novice's", 2 => "Journeyman's", 3 => "Journeyman's",
    4 => "Adept's", 5 => "Expert's", 6 => "Master's",
    7 => "Grandmaster's", 8 => "Elder's",
];

function tierFromApiId(string $apiId): int
{
    preg_match('/^T(\d)/', $apiId, $m);
    return (int) ($m[1] ?? 0);
}

$rows = [];

// ---------------------------------------------------------------
// SEEDS - Crop
// ---------------------------------------------------------------
$cropSeeds = [
    'T1_FARM_CARROT_SEED'  => 'Carrot Seeds',
    'T2_FARM_BEAN_SEED'    => 'Bean Seeds',
    'T3_FARM_WHEAT_SEED'   => 'Wheat Seeds',
    'T4_FARM_TURNIP_SEED'  => 'Turnip Seeds',
    'T5_FARM_CABBAGE_SEED' => 'Cabbage Seeds',
    'T6_FARM_POTATO_SEED'  => 'Potato Seeds',
    'T7_FARM_CORN_SEED'    => 'Corn Seeds',
    'T8_FARM_PUMPKIN_SEED' => 'Pumpkin Seeds',
];

// ---------------------------------------------------------------
// SEEDS - Herb
// ---------------------------------------------------------------
$herbSeeds = [
    'T2_FARM_AGARIC_SEED'   => 'Arcane Agaric Seeds',
    'T3_FARM_COMFREY_SEED'  => 'Brightleaf Comfrey Seeds',
    'T4_FARM_BURDOCK_SEED'  => 'Crenellated Burdock Seeds',
    'T5_FARM_TEASEL_SEED'   => 'Dragon Teasel Seeds',
    'T6_FARM_FOXGLOVE_SEED' => 'Elusive Foxglove Seeds',
    'T7_FARM_MULLEIN_SEED'  => 'Firetouched Mullein Seeds',
    'T8_FARM_YARROW_SEED'   => 'Ghoul Yarrow Seeds',
];

foreach (array_merge($cropSeeds, $herbSeeds) as $apiId => $name) {
    $rows[] = [
        'category_id' => CAT_SEEDS,
        'name'        => $name,
        'api_id'      => $apiId,
        'tier'        => tierFromApiId($apiId),
        'enc'         => 0,
        'quality'     => 1,
        'desc'        => null,
    ];
}

// ---------------------------------------------------------------
// BABY ANIMAL - Pasture (farm basic + transport)
// ---------------------------------------------------------------
$pastureBasic = [
    'T3_FARM_CHICKEN_BABY' => 'Baby Chickens',
    'T4_FARM_GOAT_BABY'    => 'Kid',
    'T5_FARM_GOOSE_BABY'   => 'Gosling',
    'T6_FARM_SHEEP_BABY'   => 'Lamb',
    'T7_FARM_PIG_BABY'     => 'Piglet',
    'T8_FARM_COW_BABY'     => 'Calf',
];
foreach ($pastureBasic as $apiId => $name) {
    $rows[] = [
        'category_id' => CAT_BABY_PASTURE,
        'name'        => $name,
        'api_id'      => $apiId,
        'tier'        => tierFromApiId($apiId),
        'enc'         => 0,
        'quality'     => 1,
        'desc'        => null,
    ];
}

// Ox Calf & Foal, tier 3-8, prefix nama tergantung tier
foreach (range(3, 8) as $t) {
    $rows[] = [
        'category_id' => CAT_BABY_PASTURE,
        'name'        => $tierPrefixName[$t] . ' Ox Calf',
        'api_id'      => "T{$t}_FARM_OX_BABY",
        'tier'        => $t,
        'enc'         => 0,
        'quality'     => 1,
        'desc'        => null,
    ];
    $rows[] = [
        'category_id' => CAT_BABY_PASTURE,
        'name'        => $tierPrefixName[$t] . ' Foal',
        'api_id'      => "T{$t}_FARM_HORSE_BABY",
        'tier'        => $t,
        'enc'         => 0,
        'quality'     => 1,
        'desc'        => null,
    ];
}

// ---------------------------------------------------------------
// BABY ANIMAL - Kennel (rare mount)
// ---------------------------------------------------------------
$kennel = [
    'T6_FARM_DIREWOLF_BABY'         => 'Direwolf Pup',
    'T8_FARM_DIREWOLF_BABY'         => 'Ghostwolf Pup',
    'T7_FARM_DIREBOAR_BABY'         => 'Direboar Piglet',
    'T8_FARM_DIREBEAR_BABY'         => 'Direbear Cub',
    'T7_FARM_SWAMPDRAGON_BABY'      => 'Swamp Dragon Pup',
    'T8_FARM_MAMMOTH_BABY'          => 'Mammoth Calf',
    'T5_FARM_COUGAR_BABY'           => 'Swiftclaw Cub',
    'T4_FARM_GIANTSTAG_BABY'        => "Adept's Fawn",
    'T6_FARM_GIANTSTAG_BABY'        => "Master's Fawn",
    'T6_FARM_GIANTSTAG_MOOSE_BABY'  => 'Moose Calf',
    'T8_FARM_RABBIT_EASTER_BABY'      => 'Vibrant Spring Cottontail Egg',
    'T8_FARM_RABBIT_EASTER_BABY_DARK' => 'Eerie Cottontail Egg',

    // Faction mount babies - T5 (normal) & T8 (elite)
    'T5_FARM_MOABIRD_FW_BRIDGEWATCH_BABY'    => 'Baby Moabird',
    'T8_FARM_MOABIRD_FW_BRIDGEWATCH_BABY'    => 'Baby Elite Terrorbird',
    'T5_FARM_DIREBEAR_FW_FORTSTERLING_BABY'  => 'Winter Bear Cub',
    'T8_FARM_DIREBEAR_FW_FORTSTERLING_BABY'  => 'Elite Winter Bear Cub',
    'T5_FARM_DIREBOAR_FW_LYMHURST_BABY'      => 'Wild Boarlet',
    'T8_FARM_DIREBOAR_FW_LYMHURST_BABY'      => 'Elite Wild Boarlet',
    'T5_FARM_RAM_FW_MARTLOCK_BABY'           => 'Bighorn Ram Lamb',
    'T8_FARM_RAM_FW_MARTLOCK_BABY'           => 'Elite Bighorn Ram Lamb',
    'T5_FARM_SWAMPDRAGON_FW_THETFORD_BABY'   => 'Baby Swamp Salamander',
    'T8_FARM_SWAMPDRAGON_FW_THETFORD_BABY'   => 'Baby Elite Swamp Salamander',
    'T5_FARM_GREYWOLF_FW_CAERLEON_BABY'      => 'Caerleon Greywolf Pup',
    'T8_FARM_GREYWOLF_FW_CAERLEON_BABY'      => 'Elite Greywolf Pup',
    'T5_FARM_OWL_FW_BRECILIEN_BABY'          => 'Mystic Owlet',
    'T8_FARM_OWL_FW_BRECILIEN_BABY'          => 'Elite Mystic Owlet',
    'T5_FARM_SPIDER_HELL_BABY'               => 'Hellspinner Baby',
    'T8_FARM_SPIDER_HELL_BABY'               => 'Soulspinner Baby',
];
foreach ($kennel as $apiId => $name) {
    $rows[] = [
        'category_id' => CAT_BABY_KENNEL,
        'name'        => $name,
        'api_id'      => $apiId,
        'tier'        => tierFromApiId($apiId),
        'enc'         => 0,
        'quality'     => 1,
        'desc'        => null,
    ];
}

// ---------------------------------------------------------------
// INSERT (updateOrInsert biar aman dijalanin berkali-kali / gak duplikat)
// ---------------------------------------------------------------
$inserted = 0;
$skipped  = 0;

foreach ($rows as $row) {
    $exists = \DB::table('items')->where('api_id', $row['api_id'])->exists();
    if ($exists) {
        $skipped++;
        continue;
    }
    \DB::table('items')->insert(array_merge($row, [
        'created_at' => now(),
        'updated_at' => now(),
    ]));
    $inserted++;
}

echo "Selesai. Inserted: {$inserted}, Skipped (udah ada): {$skipped}, Total diproses: " . count($rows) . "\n";
