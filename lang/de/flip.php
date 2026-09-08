<?php

return [
    'title' => 'Flip-Rechner - Albion Online Tools',
    'panel_title' => 'Flip-Rechner',
    'subtitle' => 'Kauf-/Verkaufslimits berechnen oder automatische Flip-Möglichkeiten finden',

    'mode' => [
        'simple' => 'Einfacher Modus',
        'advance' => 'Erweiterter Modus',
    ],

    'simple' => [
        'find_sell_min' => 'Mindestverkaufspreis finden',
        'find_buy_max' => 'Höchstkaufpreis finden',
        'add_row' => '+ Zeile hinzufügen',
        'capital' => 'Kapital (Kaufpreis)',
        'target_sell' => 'Zielverkaufspreis',
        'premium' => 'Premium',
        'sell_min_result' => 'Mindestverkauf um nicht zu verlieren',
        'buy_max_result' => 'Höchstkauf um nicht zu verlieren',
        'tax_note' => 'Marktsteuer: <b>8%</b> ohne Premium, <b>4%</b> mit Premium. Kapital eingeben, Ergebnis erscheint automatisch.',
        'reverse_note' => 'Gegenteil der obigen Tabelle — Zielverkaufspreis eingeben, maximales Kauflimit für Gewinn erhalten.',
    ],

    'advance' => [
        'scan_opportunities' => 'Flip-Möglichkeiten scannen',
        'filter' => [
            'category' => 'Kategorie',
            'cities' => 'Städte',
            'tier' => 'Stufe',
            'enchantment' => 'Verzauberung',
            'all' => 'Alle',
        ],
        'reset' => 'Zurücksetzen',
        'scan_btn' => 'Scannen',
        'status' => [
            'loading' => 'Wird geladen...',
            'scanning' => 'Wird gescannt...',
            'top_opportunities' => 'Beste Möglichkeiten',
            'updated' => 'Aktualisiert',
            'auto_refresh' => 'Automatische Aktualisierung alle 5 Minuten',
            'cache' => 'Gecachtes Ergebnis',
            'fresh' => 'Frisch gescannt',
            'next_scan' => 'nächster Scan verfügbar',
            'failed' => 'Scan fehlgeschlagen, erneut versuchen.',
        ],
        'city_min_2' => 'Mindestens 2 Städte müssen ausgewählt sein!',
        'city_exclude' => 'Ausschließen',
        'empty' => [
            'loading_top' => 'Beste Möglichkeiten werden geladen... 🔍',
            'no_data' => 'Keine Möglichkeiten gefunden 😔',
            'click_scan' => 'Scannen klicken um Möglichkeiten zu sehen 🗡️',
        ],
        'pagination' => [
            'previous' => '‹ Zurück',
            'next' => 'Weiter ›',
            'page_of' => 'Seite :current von :total',
        ],
    ],
];
