<?php

return [
    'title' => 'Kalkulator Flip - Albion Online Tools',
    'panel_title' => 'Kalkulator Flip',
    'subtitle' => 'Hitung batas harga jual/beli, atau cari opportunity flip otomatis',

    'mode' => [
        'simple' => 'Mode Simple',
        'advance' => 'Mode Advance',
    ],

    'simple' => [
        'find_sell_min' => 'Cari Harga Jual Minimal',
        'find_buy_max' => 'Cari Harga Beli Maksimal',
        'add_row' => '+ Tambah Baris',
        'capital' => 'Modal (Harga Beli)',
        'target_sell' => 'Target Harga Jual',
        'premium' => 'Premium',
        'sell_min_result' => 'Jual Minimal Supaya Tidak Rugi',
        'buy_max_result' => 'Beli Maksimal Supaya Tidak Rugi',
        'tax_note' => 'Pajak market: <b>8%</b> tanpa premium, <b>4%</b> dengan premium. Isi kolom Modal, hasil otomatis muncul.',
        'reverse_note' => 'Kebalikan dari tabel di atas — isi target harga jual, dapat batas harga beli supaya tetap untung.',
    ],

    'advance' => [
        'scan_opportunities' => 'Scan Opportunity Flip',
        'filter' => [
            'category' => 'Kategori',
            'cities' => 'Kota',
            'tier' => 'Tier',
            'enchantment' => 'Enchantment',
            'all' => 'Semua',
        ],
        'reset' => 'Reset',
        'scan_btn' => 'Scan',
        'status' => [
            'loading' => 'Memuat...',
            'scanning' => 'Sedang scan...',
            'top_opportunities' => 'Opportunity terbaik',
            'updated' => 'Diupdate',
            'auto_refresh' => 'Auto-refresh setiap 5 menit',
            'cache' => 'Hasil cache',
            'fresh' => 'Baru discan',
            'next_scan' => 'scan berikutnya tersedia',
            'failed' => 'Gagal scan, coba lagi.',
        ],
        'city_min_2' => 'Minimal 2 kota harus dipilih!',
        'city_exclude' => 'Kecuali',
        'empty' => [
            'loading_top' => 'Loading top opportunities... 🔍',
            'no_data' => 'Gak ada opportunity ditemukan 😔',
            'click_scan' => 'Klik Scan buat lihat opportunity 🗡️',
        ],
        'pagination' => [
            'previous' => '‹ Sebelumnya',
            'next' => 'Berikutnya ›',
            'page_of' => 'Halaman :current dari :total',
        ],
    ],
];
