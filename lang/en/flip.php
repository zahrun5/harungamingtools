<?php

return [
    'title' => 'Flip Calculator - Albion Online Tools',
    'panel_title' => 'Flip Calculator',
    'subtitle' => 'Calculate buy/sell limits or find automatic flip opportunities',

    'mode' => [
        'simple' => 'Simple Mode',
        'advance' => 'Advance Mode',
    ],

    'simple' => [
        'find_sell_min' => 'Find Minimum Sell Price',
        'find_buy_max' => 'Find Maximum Buy Price',
        'add_row' => '+ Add Row',
        'capital' => 'Capital (Buy Price)',
        'target_sell' => 'Target Sell Price',
        'premium' => 'Premium',
        'sell_min_result' => 'Minimum Sell to Break Even',
        'buy_max_result' => 'Maximum Buy to Break Even',
        'tax_note' => 'Market tax: <b>8%</b> without premium, <b>4%</b> with premium. Fill the Capital column, results appear automatically.',
        'reverse_note' => 'Opposite of the table above — fill target sell price, get maximum buy limit to stay profitable.',
    ],

    'advance' => [
        'scan_opportunities' => 'Scan Flip Opportunities',
        'filter' => [
            'category' => 'Category',
            'cities' => 'Cities',
            'tier' => 'Tier',
            'enchantment' => 'Enchantment',
            'all' => 'All',
        ],
        'reset' => 'Reset',
        'scan_btn' => 'Scan',
        'status' => [
            'loading' => 'Loading...',
            'scanning' => 'Scanning...',
            'top_opportunities' => 'Top opportunities',
            'updated' => 'Updated',
            'auto_refresh' => 'Auto-refresh every 5 minutes',
            'cache' => 'Cached results',
            'fresh' => 'Freshly scanned',
            'next_scan' => 'next scan available',
            'failed' => 'Scan failed, try again.',
        ],
        'city_min_2' => 'At least 2 cities must be selected!',
        'city_exclude' => 'Exclude',
        'empty' => [
            'loading_top' => 'Loading top opportunities... 🔍',
            'no_data' => 'No opportunities found 😔',
            'click_scan' => 'Click Scan to see opportunities 🗡️',
        ],
        'pagination' => [
            'previous' => '‹ Previous',
            'next' => 'Next ›',
            'page_of' => 'Page :current of :total',
        ],
    ],
];
