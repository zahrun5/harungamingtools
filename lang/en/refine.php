<?php

return [
    'title' => 'Refine Calculator - Albion Online Tools',
    'panel_title' => 'Refine Calculator',
    'search_placeholder' => 'Search item name...',

    'filter' => [
        'material' => 'Material',
        'tier' => 'Tier',
        'enchant' => 'Enchant',
        'city' => 'City',
        'all' => 'All',
        'enchant_option' => 'Enchant .:n',
    ],

    'material_tree' => [
        'logam' => ['label' => 'Metal',  'raw' => 'Raw Ore',   'hasil' => 'Bars / Ingots'],
        'kayu'  => ['label' => 'Wood',   'raw' => 'Raw Wood',  'hasil' => 'Planks'],
        'serat' => ['label' => 'Fiber',  'raw' => 'Raw Fiber', 'hasil' => 'Cloth'],
        'kulit' => ['label' => 'Hide',   'raw' => 'Raw Hide',  'hasil' => 'Leather'],
        'batu'  => ['label' => 'Stone',  'raw' => 'Raw Stone', 'hasil' => 'Stone Blocks'],
    ],

    'no_items_found' => 'No matching items',
    'fetching_prices' => 'Fetching prices...',
    'prices_fetched' => ':count prices',
    'fallback_suffix' => ':count fallback',
    'fetch_failed' => 'Fetch failed',

    'inventory' => 'Inventory',
    'reset' => 'Reset',
    'return_label' => 'Return',
    'premium' => 'Premium',
    'sell_order_fee' => 'Sell Order Fee (2.5%)',
    'available_refines' => 'Available Refines',
    'max_label' => 'max',

    'raw_material_cost' => 'Material Cost',
    'refine_result_value' => 'Refine Result Value',
    'tax_label' => 'Tax (:pct%)',
    'tax_label_result' => 'Tax :pct% (result)',
    'remaining_raw_material' => 'Remaining Raw Material',
    'final_result' => 'Final Result',
    'total_profit' => 'Total Profit',

    'price_per_unit' => 'Price per unit (silver)',
    'quantity_max' => 'Quantity (max 999,999)',
    'return_rate_pct' => 'Return Rate %',
    'quantity_to_refine' => 'Quantity to refine',
    'refine_all_checkbox' => 'Refine All (use all materials)',
    'refine_btn' => 'Refine',

    'add_to_inventory' => 'Add to Inventory',
    'save_price' => 'Save Price',
    'price_updated' => 'Price updated',
    'item_deleted' => ':name deleted',

    'reset_confirm' => 'Reset everything? Inventory and refine data will be deleted.',
    'reset_done' => 'Reset done',

    'tutorial' => [
        'help_button' => 'Help Guide',
        'next' => 'Next',
        'back' => 'Back',
        'start' => 'Start Calculating!',
        'step1' => [
            'title' => 'Welcome to Refining Calculator!',
            'text' => 'Use filters to select material type, tier, and enchantment you want to refine.',
        ],
        'step2' => [
            'title' => 'Add Items to Inventory',
            'text' => 'Click items from the list to add to inventory. Enter the price and quantity of raw materials you have.',
        ],
        'step3' => [
            'title' => 'Set Return Rate',
            'text' => 'Enter the return rate from your refining station. Standard: 15.2% (no bonus), Max: 53.9% (max bonus + premium).',
        ],
        'step4' => [
            'title' => 'Click Refine Button',
            'text' => 'Choose available refine combinations. Calculator will compute how much material is needed.',
        ],
        'step5' => [
            'title' => 'See Your Profit!',
            'text' => 'Calculator will show capital, result value, tax, and total profit. Green = profit, red = loss.',
        ],
    ],
];
