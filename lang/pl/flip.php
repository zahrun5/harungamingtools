<?php

return [
    'title' => 'Kalkulator Flip - Albion Online Tools',
    'panel_title' => 'Kalkulator Flip',
    'subtitle' => 'Oblicz limity kupna/sprzedaży lub znajdź automatyczne okazje do flipu',

    'mode' => [
        'simple' => 'Tryb prosty',
        'advance' => 'Tryb zaawansowany',
    ],

    'simple' => [
        'find_sell_min' => 'Znajdź minimalną cenę sprzedaży',
        'find_buy_max' => 'Znajdź maksymalną cenę kupna',
        'add_row' => '+ Dodaj wiersz',
        'capital' => 'Kapitał (cena kupna)',
        'target_sell' => 'Docelowa cena sprzedaży',
        'premium' => 'Premium',
        'sell_min_result' => 'Minimalna sprzedaż aby nie stracić',
        'buy_max_result' => 'Maksymalny zakup aby nie stracić',
        'tax_note' => 'Podatek rynkowy: <b>8%</b> bez premium, <b>4%</b> z premium. Wypełnij kapitał, wynik pojawi się automatycznie.',
        'reverse_note' => 'Odwrotność powyższej tabeli — wpisz docelową cenę sprzedaży, otrzymaj maksymalny limit kupna dla zysku.',
    ],

    'advance' => [
        'scan_opportunities' => 'Skanuj okazje do flipu',
        'filter' => [
            'category' => 'Kategoria',
            'cities' => 'Miasta',
            'tier' => 'Poziom',
            'enchantment' => 'Zaklęcie',
            'all' => 'Wszystkie',
        ],
        'reset' => 'Resetuj',
        'scan_btn' => 'Skanuj',
        'status' => [
            'loading' => 'Ładowanie...',
            'scanning' => 'Skanowanie...',
            'top_opportunities' => 'Najlepsze okazje',
            'updated' => 'Zaktualizowano',
            'auto_refresh' => 'Auto-odświeżanie co 5 minut',
            'cache' => 'Wynik z cache',
            'fresh' => 'Świeżo zeskanowane',
            'next_scan' => 'następne skanowanie dostępne',
            'failed' => 'Skanowanie nie powiodło się, spróbuj ponownie.',
        ],
        'city_min_2' => 'Należy wybrać co najmniej 2 miasta!',
        'city_exclude' => 'Wyklucz',
        'empty' => [
            'loading_top' => 'Ładowanie najlepszych okazji... 🔍',
            'no_data' => 'Nie znaleziono okazji 😔',
            'click_scan' => 'Kliknij Skanuj aby zobaczyć okazje 🗡️',
        ],
        'pagination' => [
            'previous' => '‹ Poprzedni',
            'next' => 'Następny ›',
            'page_of' => 'Strona :current z :total',
        ],
    ],
];
