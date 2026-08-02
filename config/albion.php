<?php

return [
    'fame' => [
        // Pengali F_B, cuma valid T4+ (T1-3 belum terkonfirmasi resmi
        // di wiki.albiononline.com/wiki/Fame)
        'tier_multiplier' => [
            4 => 22.5,
            5 => 90,
            6 => 270,
            7 => 645,
            8 => 1395,
        ],
        // Fame yang dibutuhkan buat mengisi 1 crafting journal per tier
        'journal_requirement' => [
            2 => 300,
            3 => 600,
            4 => 1200,
            5 => 2400,
            6 => 4800,
            7 => 9600,
            8 => 19200,
        ],
    ],

    'journal' => [
        // Harga beli journal kosong dari laborer (statis, NPC price,
        // bukan harga market) — sumber: wiki.albiononline.com/wiki/Journal
        'price' => [
            2 => 500,
            3 => 1000,
            4 => 2000,
            5 => 4000,
            6 => 8000,
            7 => 16000,
            8 => 32000,
        ],

        // Jumlah item resource dari 1 journal penuh, di 100% yield
        'base_amount' => [
            2 => 38,
            3 => 24,
            4 => 16,
            5 => 8,
            6 => 5.3333,
            7 => 4.4651,
            8 => 4.129,
        ],

        // Proporsi tipe resource yang dihasilkan per jenis laborer
        'laborer_ratio' => [
            'Blacksmith' => ['cloth' => 0.1111, 'leather' => 0,      'metalbar' => 0.6869, 'planks' => 0.20],
            'Fletcher'   => ['cloth' => 0,      'leather' => 0.4666, 'metalbar' => 0.2667, 'planks' => 0.2667],
            'Imbuer'     => ['cloth' => 0.4889, 'leather' => 0,      'metalbar' => 0.1111, 'planks' => 0.40],
            'Tinker'     => ['cloth' => 0.2222, 'leather' => 0.1111, 'metalbar' => 0.2222, 'planks' => 0.4444],
        ],
    ],
];
