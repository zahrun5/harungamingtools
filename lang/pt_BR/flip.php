<?php

return [
    'title' => 'Calculadora de Flip - Albion Online Tools',
    'panel_title' => 'Calculadora de Flip',
    'subtitle' => 'Calcule limites de compra/venda ou encontre oportunidades de flip automáticas',

    'mode' => [
        'simple' => 'Modo Simples',
        'advance' => 'Modo Avançado',
    ],

    'simple' => [
        'find_sell_min' => 'Encontrar Preço Mínimo de Venda',
        'find_buy_max' => 'Encontrar Preço Máximo de Compra',
        'add_row' => '+ Adicionar Linha',
        'capital' => 'Capital (Preço de Compra)',
        'target_sell' => 'Preço de Venda Alvo',
        'premium' => 'Premium',
        'sell_min_result' => 'Venda Mínima para Não Perder',
        'buy_max_result' => 'Compra Máxima para Não Perder',
        'tax_note' => 'Taxa de mercado: <b>8%</b> sem premium, <b>4%</b> com premium. Preencha o Capital, o resultado aparece automaticamente.',
        'reverse_note' => 'Oposto da tabela acima — preencha o preço de venda alvo, obtenha o limite máximo de compra para permanecer lucrativo.',
    ],

    'advance' => [
        'scan_opportunities' => 'Escanear Oportunidades de Flip',
        'filter' => [
            'category' => 'Categoria',
            'cities' => 'Cidades',
            'tier' => 'Tier',
            'enchantment' => 'Encantamento',
            'all' => 'Todos',
        ],
        'reset' => 'Resetar',
        'scan_btn' => 'Escanear',
        'status' => [
            'loading' => 'Carregando...',
            'scanning' => 'Escaneando...',
            'top_opportunities' => 'Melhores oportunidades',
            'updated' => 'Atualizado',
            'auto_refresh' => 'Auto-atualização a cada 5 minutos',
            'cache' => 'Resultado em cache',
            'fresh' => 'Recém escaneado',
            'next_scan' => 'próximo scan disponível',
            'failed' => 'Scan falhou, tente novamente.',
        ],
        'city_min_2' => 'Pelo menos 2 cidades devem ser selecionadas!',
        'city_exclude' => 'Excluir',
        'empty' => [
            'loading_top' => 'Carregando melhores oportunidades... 🔍',
            'no_data' => 'Nenhuma oportunidade encontrada 😔',
            'click_scan' => 'Clique em Escanear para ver oportunidades 🗡️',
        ],
        'pagination' => [
            'previous' => '‹ Anterior',
            'next' => 'Próximo ›',
            'page_of' => 'Página :current de :total',
        ],
    ],
];
