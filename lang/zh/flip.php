<?php

return [
    'title' => '倒卖计算器 - Albion Online 工具',
    'panel_title' => '倒卖计算器',
    'subtitle' => '计算买卖价格限制或寻找自动倒卖机会',

    'mode' => [
        'simple' => '简单模式',
        'advance' => '高级模式',
    ],

    'simple' => [
        'find_sell_min' => '查找最低售价',
        'find_buy_max' => '查找最高买价',
        'add_row' => '+ 添加行',
        'capital' => '成本（买入价）',
        'target_sell' => '目标售价',
        'premium' => '高级会员',
        'sell_min_result' => '保本最低售价',
        'buy_max_result' => '保本最高买价',
        'tax_note' => '市场税费：无高级会员 <b>8%</b>，有高级会员 <b>4%</b>。填写成本栏，结果自动显示。',
        'reverse_note' => '与上表相反 — 填写目标售价，获得保持盈利的最高买价限制。',
    ],

    'advance' => [
        'scan_opportunities' => '扫描倒卖机会',
        'filter' => [
            'category' => '类别',
            'cities' => '城市',
            'tier' => '等级',
            'enchantment' => '附魔',
            'all' => '全部',
        ],
        'reset' => '重置',
        'scan_btn' => '扫描',
        'status' => [
            'loading' => '加载中...',
            'scanning' => '扫描中...',
            'top_opportunities' => '最佳机会',
            'updated' => '更新时间',
            'auto_refresh' => '每5分钟自动刷新',
            'cache' => '缓存结果',
            'fresh' => '刚扫描',
            'next_scan' => '下次扫描可用时间',
            'failed' => '扫描失败，请重试。',
        ],
        'city_min_2' => '至少需要选择2个城市！',
        'city_exclude' => '排除',
        'empty' => [
            'loading_top' => '加载最佳机会中... 🔍',
            'no_data' => '未找到机会 😔',
            'click_scan' => '点击扫描查看机会 🗡️',
        ],
        'pagination' => [
            'previous' => '‹ 上一页',
            'next' => '下一页 ›',
            'page_of' => '第 :current 页，共 :total 页',
        ],
    ],
];
