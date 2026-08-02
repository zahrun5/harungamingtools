<?php

return [
    'title' => 'Strona główna - Albion Online Tools',
    'welcome_title' => 'Witamy w Albion Online Tools👋',
    'welcome_sub' => 'Miejsce spotkań graczy Albion Online — kalkulatory, ranking i społeczność.',

    'sections' => [
        'refine'   => ['title' => '⚒️ Stacje rafinacji', 'sub' => 'Wybierz stację odpowiednią do rodzaju surowca, który chcesz przetworzyć.'],
        'crafting' => ['title' => '🛠️ Stacja rzemieślnicza', 'sub' => 'Sprawdź, ile kosztuje wykonanie twojego ulubionego ekwipunku.'],
        'tools'    => ['title' => '🧰 Inne narzędzia', 'sub' => 'Sprawdź ceny na rynku w czasie rzeczywistym lub zobacz, kto ostatnio zginął w świecie Albion.'],
        'support'  => ['title' => '💛 Wesprzyj HGT', 'sub' => 'Podobają Ci się te narzędzia? Postaw nam kawę albo dołącz do naszej społeczności.'],
    ],

    // ⚠️ 'stonemason' & 'mage-tower', 'hunters-lodge', 'warriors-forge' masih perlu dicek ulang di client game
    'stations' => [
        'smelter'    => ['name' => 'Huta',                   'desc' => 'Przetwarza rudę na sztaby metalu'],
        'lumbermill' => ['name' => 'Tartak',                 'desc' => 'Przetwarza drewno na deski'],
        'stonemason' => ['name' => 'Warsztat kamieniarski',  'desc' => 'Przetwarza kamień na bloki'],
        'tanner'     => ['name' => 'Garbarz',                'desc' => 'Przetwarza skórę surową na wyprawioną'],
        'weaver'     => ['name' => 'Tkacz',                  'desc' => 'Przetwarza włókno na tkaninę'],
    ],

    'crafting_stations' => [
        'mage-tower'     => ['name' => 'Wieża maga',      'desc' => 'Mag i zaklinacz'],
        'hunters-lodge'  => ['name' => 'Chatka łowcy',    'desc' => 'Broń i ubiór łowcy'],
        'warriors-forge' => ['name' => 'Kuźnia wojownika','desc' => 'Miecze i zbroje wojownika'],
    ],

    'tools_list' => [
        'fishing'     => ['name' => 'Kalkulator wędkarski', 'desc' => 'Sprzedać rybę czy ją przetworzyć — co się bardziej opłaca?'],
        'flip'        => ['name' => 'Kalkulator flipowania', 'desc' => 'Oblicz progi kupna/sprzedaży, aby nie stracić na podatku.'],
        'market'      => ['name' => 'Sprawdź ceny rynkowe',  'desc' => 'Śledź aktualne ceny przedmiotów we wszystkich miastach.'],
        'death-recap' => ['name' => 'Podsumowanie śmierci',  'desc' => 'Zobacz szczegóły śmierci i ostatni ekwipunek gracza.'],
    ],

    'support' => [
        'saweria'  => '☕ Saweria',
        'trakteer' => '🎁 Trakteer',
        'channel'  => '📢 Kanał',
        'group'    => '👥 Grupa',
    ],
];