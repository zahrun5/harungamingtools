<?php

/**
 * Seed manual: Crop & Herb Plants (hasil panen)
 * -----------------------------------------------------------
 * Sama kayak seeds & baby animal - item hasil panen ini juga
 * gak pernah muncul di item_recipes sebagai resource_api_id
 * (kalaupun dipakai di recipe Cooking/Alchemist, kemungkinan
 * recipe itu belum ke-parse), jadi gak kejaring tool mapping.
 * Insert manual langsung ke tabel `items`.
 *
 * Cara pakai:
 *   1. Upload ke root project (~/hgt-laravel/)
 *   2. php artisan tinker
 *   3. >>> include 'seed_plants.php';
 *
 * Aman dijalanin berkali-kali (skip kalau api_id udah ada).
 */

$catPlants = 515; // Farming > Farm > plants

$tierFromApiId = function (string $apiId): int {
    preg_match('/^T(\d)/', $apiId, $m);
    return (int) ($m[1] ?? 0);
};

$rows = [];

// Crop plants - cuma 4 yang masih bolong (T3/T6/T7/T8 udah ada di DB)
$cropPlants = [
    'T1_CARROT' => 'Carrots',
    'T2_BEAN'   => 'Beans',
    'T4_TURNIP' => 'Turnips',
    'T5_CABBAGE'=> 'Cabbage',
];

// Herb plants - semua 7 masih kosong
$herbPlants = [
    'T2_AGARIC'   => 'Arcane Agaric',
    'T3_COMFREY'  => 'Brightleaf Comfrey',
    'T4_BURDOCK'  => 'Crenellated Burdock',
    'T5_TEASEL'   => 'Dragon Teasel',
    'T6_FOXGLOVE' => 'Elusive Foxglove',
    'T7_MULLEIN'  => 'Firetouched Mullein',
    'T8_YARROW'   => 'Ghoul Yarrow',
];

foreach (array_merge($cropPlants, $herbPlants) as $apiId => $name) {
    $rows[] = [
        'category_id' => $catPlants,
        'name'        => $name,
        'api_id'      => $apiId,
        'tier'        => $tierFromApiId($apiId),
        'enc'         => 0,
        'quality'     => 1,
        'desc'        => null,
    ];
}

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
