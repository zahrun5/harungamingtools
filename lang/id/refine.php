<?php

return [
    'title' => 'Kalkulator Refine - Albion Online Tools',
    'panel_title' => 'Refine Calculator',
    'search_placeholder' => 'Cari nama item...',

    'filter' => [
        'material' => 'Material',
        'tier' => 'Tier',
        'enchant' => 'Enchant',
        'city' => 'Kota',
        'all' => 'Semua',
        'enchant_option' => 'Enchant .:n',
    ],

    'material_tree' => [
        'logam' => ['label' => 'Logam', 'raw' => 'Bijih Mentah', 'hasil' => 'Balok / Batang'],
        'kayu'  => ['label' => 'Kayu',  'raw' => 'Kayu Mentah',  'hasil' => 'Papan Kayu'],
        'serat' => ['label' => 'Serat', 'raw' => 'Serat Mentah', 'hasil' => 'Kain'],
        'kulit' => ['label' => 'Kulit', 'raw' => 'Kulit Mentah', 'hasil' => 'Kulit Samak'],
        'batu'  => ['label' => 'Batu',  'raw' => 'Batu Mentah',  'hasil' => 'Batu Bata'],
    ],

    'no_items_found' => 'Tidak ada item yang cocok',
    'fetching_prices' => 'Mengambil harga...',
    'prices_fetched' => ':count harga',
    'fallback_suffix' => ':count fallback',
    'fetch_failed' => 'Gagal fetch',

    'inventory' => 'Inventory',
    'reset' => 'Reset',
    'return_label' => 'Return',
    'premium' => 'Premium',
    'sell_order_fee' => 'Pesanan Jual (2.5%)',
    'available_refines' => 'Refine Tersedia',
    'max_label' => 'max',

    'raw_material_cost' => 'Modal Bahan',
    'refine_result_value' => 'Nilai Hasil Refine',
    'tax_label' => 'Pajak (:pct%)',
    'tax_label_result' => 'Pajak :pct% (hasil)',
    'remaining_raw_material' => 'Sisa Bahan Mentah',
    'final_result' => 'Hasil Akhir',
    'total_profit' => 'Total Profit',

    'price_per_unit' => 'Harga per unit (silver)',
    'quantity_max' => 'Jumlah (max 999.999)',
    'return_rate_pct' => 'Return Rate %',
    'quantity_to_refine' => 'Jumlah yang di-refine',
    'refine_all_checkbox' => 'Refine Habis (gunakan semua bahan)',
    'refine_btn' => 'Refine',

    'add_to_inventory' => 'Tambah ke Inventory',
    'save_price' => 'Simpan Harga',
    'price_updated' => 'Harga diperbarui',
    'item_deleted' => ':name dihapus',

    'reset_confirm' => 'Reset semua? Inventory dan data refine akan dihapus.',
    'reset_done' => 'Reset selesai',

    'tutorial' => [
        'help_button' => 'Panduan',
        'next' => 'Lanjut',
        'back' => 'Kembali',
        'start' => 'Mulai Hitung!',
        'step1' => [
            'title' => 'Selamat Datang di Refining Calculator!',
            'text' => 'Gunakan filter untuk memilih jenis material, tier, dan enchantment yang ingin kamu refine.',
        ],
        'step2' => [
            'title' => 'Tambahkan Item ke Inventory',
            'text' => 'Klik item dari daftar untuk menambahkan ke inventory. Masukkan harga dan jumlah bahan mentah yang kamu punya.',
        ],
        'step3' => [
            'title' => 'Atur Return Rate',
            'text' => 'Masukkan return rate dari stasiun refining kamu. Standar: 15.2% (no bonus), Max: 53.9% (max bonus + premium).',
        ],
        'step4' => [
            'title' => 'Klik Tombol Refine',
            'text' => 'Pilih kombinasi refine yang tersedia. Calculator akan menghitung berapa banyak bahan yang dibutuhkan.',
        ],
        'step5' => [
            'title' => 'Lihat Profit Kamu!',
            'text' => 'Calculator akan menunjukkan modal, nilai hasil, pajak, dan total profit. Warna hijau = untung, merah = rugi.',
        ],
    ],
];
