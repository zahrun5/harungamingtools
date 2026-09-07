<?php

return [
    'title' => 'Калькулятор переработки - Albion Online Tools',
    'panel_title' => 'Калькулятор переработки',
    'search_placeholder' => 'Поиск по названию предмета...',

    'filter' => [
        'material' => 'Материал',
        'tier' => 'Уровень',
        'enchant' => 'Зачарование',
        'city' => 'Город',
        'all' => 'Все',
        'enchant_option' => 'Зачарование .:n',
    ],

    'material_tree' => [
        'logam' => ['label' => 'Металл',  'raw' => 'Рудá',          'hasil' => 'Слитки'],
        'kayu'  => ['label' => 'Дерево',  'raw' => 'Сырая древесина', 'hasil' => 'Доски'],
        'serat' => ['label' => 'Волокно', 'raw' => 'Сырое волокно', 'hasil' => 'Ткань'],
        'kulit' => ['label' => 'Шкура',   'raw' => 'Сырая шкура',   'hasil' => 'Кожа'],
        'batu'  => ['label' => 'Камень',  'raw' => 'Сырой камень',  'hasil' => 'Каменные блоки'],
    ],

    'no_items_found' => 'Нет подходящих предметов',
    'fetching_prices' => 'Загрузка цен...',
    'prices_fetched' => ':count цен',
    'fallback_suffix' => ':count запасных',
    'fetch_failed' => 'Не удалось загрузить',

    'inventory' => 'Инвентарь',
    'reset' => 'Сброс',
    'return_label' => 'Возврат',
    'premium' => 'Премиум',
    'sell_order_fee' => 'Ордер на продажу (2,5%)',
    'available_refines' => 'Доступная переработка',
    'max_label' => 'макс',

    'raw_material_cost' => 'Стоимость материалов',
    'refine_result_value' => 'Стоимость результата переработки',
    'tax_label' => 'Налог (:pct%)',
    'tax_label_result' => 'Налог :pct% (с результата)',
    'remaining_raw_material' => 'Оставшееся сырьё',
    'final_result' => 'Итоговый результат',
    'total_profit' => 'Итоговая прибыль',

    'price_per_unit' => 'Цена за единицу (серебро)',
    'quantity_max' => 'Количество (макс. 999 999)',
    'return_rate_pct' => 'Процент возврата %',
    'quantity_to_refine' => 'Количество для переработки',
    'refine_all_checkbox' => 'Переработать всё (использовать все материалы)',
    'refine_btn' => 'Переработать',

    'add_to_inventory' => 'Добавить в инвентарь',
    'save_price' => 'Сохранить цену',
    'price_updated' => 'Цена обновлена',
    'item_deleted' => ':name удалён',

    'reset_confirm' => 'Сбросить всё? Инвентарь и данные переработки будут удалены.',
    'reset_done' => 'Сброс завершён',
];
