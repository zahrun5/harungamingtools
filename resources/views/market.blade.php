@extends('layouts.app')

@section('title', __('market.title'))

@section('meta_description', 'Browse Albion Online market prices across all cities. Real-time price data, price history charts, crafting recipes, item comparison. Updated via Albion Data API.')

@section('meta_keywords', 'albion market prices, price history, item prices albion, market data, albion online data, price tracker, market browser')

@section('og_description', 'Albion Online Market Browser: Real-time prices from 7 cities, price history charts (1D/7D/30D), crafting recipes, quality comparison. Free market data tool.')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
<style>
:root {
  --parch-lt:  #dcc08a;
  --panel-bd:  #6b4f1a;
  --gold:      #f0c040;
  --gold-dk:   #b8860b;
  --text-lt:   #e8d5a3;
  --text-dim:  #a08040;
  --slot-bg:   #1a1208;
  --slot-bd:   #4a3510;
}
.app { padding: 0 0 40px; }
.panel {
  background: linear-gradient(180deg, #2e2210 0%, #1e1608 100%);
  border: 2px solid var(--panel-bd);
  border-radius: 4px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.7);
  overflow: visible;
}
.panel-header {
  background: linear-gradient(180deg, #3d2e15 0%, #2a1f0e 100%);
  border-bottom: 1px solid var(--panel-bd);
  padding: 10px 16px;
  display: flex; align-items: center; gap: 10px;
}
.panel-title { font-family: 'Cinzel', serif; font-size: 14px; color: var(--gold); letter-spacing: 1px; text-transform: uppercase; }
.header-search {
  margin-left: auto;
  background: linear-gradient(180deg, #1a1208 0%, #110e05 100%);
  border: 1px solid var(--slot-bd); border-radius: 3px;
  color: var(--text-lt); font-family: 'Crimson Text', serif;
  font-size: 13px; padding: 6px 10px; outline: none;
  transition: border-color 0.15s; width: 180px;
}
.header-search:focus { border-color: var(--gold-dk); }
.header-search::placeholder { color: var(--text-dim); }

/* ====== FILTER BAR ====== */
.filter-bar {
  background: linear-gradient(180deg, #251a08 0%, #1a1005 100%);
  border-bottom: 1px solid var(--panel-bd);
  padding: 10px 12px;
  display: flex; gap: 8px; flex-wrap: nowrap;
  overflow-x: auto; overflow-y: visible; position: relative;
}
.flt-wrap { position: relative; }
.flt-btn {
  display: flex; align-items: center; gap: 6px;
  background: linear-gradient(180deg, #c8a84a 0%, #a07828 100%);
  border: 1px solid #8b6820; border-radius: 3px;
  color: #2a1800; font-family: 'Cinzel', serif; font-size: 12px;
  font-weight: 600; letter-spacing: 0.5px; padding: 7px 12px;
  cursor: pointer; user-select: none; white-space: nowrap;
  transition: all 0.1s; justify-content: space-between;
}
.flt-btn:hover { background: linear-gradient(180deg, #dabb5a 0%, #b88838 100%); border-color: var(--gold); }
.flt-btn.open  { background: linear-gradient(180deg, #b89030 0%, #907020 100%); border-color: var(--gold); box-shadow: 0 0 8px rgba(240,192,64,0.3); }
.flt-btn .flt-label { flex: 1; text-align: left; }
.flt-btn .flt-val   { font-size: 10px; opacity: 0.75; max-width: 90px; overflow: hidden; text-overflow: ellipsis; }
.flt-btn .flt-arrow { font-size: 8px; opacity: 0.7; margin-left: 2px; transition: transform 0.15s; }
.flt-btn.open .flt-arrow { transform: rotate(180deg); }

/* ====== DROPDOWN ====== */
.drop-wrap {
  position: fixed; z-index: 9999;
  display: none; gap: 2px;
  filter: drop-shadow(0 6px 20px rgba(0,0,0,0.85));
}
.drop-wrap.show { display: flex; }
.drop-col {
  min-width: 175px; max-height: 370px; overflow-y: auto;
  background: linear-gradient(180deg, #e8cf88 0%, #d4b468 100%);
  border: 1px solid #8b6820; border-radius: 3px;
  padding: 4px; display: flex; flex-direction: column; gap: 2px;
}
.drop-item {
  display: flex; align-items: center; justify-content: space-between;
  padding: 8px 10px; border-radius: 2px;
  background: transparent; border: 1px solid transparent;
  cursor: pointer; font-family: 'Crimson Text', serif;
  font-size: 14px; font-weight: 600; color: #2a1800;
  transition: all 0.08s; white-space: nowrap;
}
.drop-item:hover  { background: linear-gradient(180deg, #f2dc9a 0%, #e2c878 100%); border-color: #a07828; }
.drop-item.active { background: linear-gradient(180deg, #b88a28 0%, #906818 100%); border-color: #7a5010; color: #fff8e0; }
.drop-item .di-arrow { font-size: 9px; color: #6b4f1a; margin-left: 8px; flex-shrink: 0; }
.drop-item.active .di-arrow { color: #ffe090; }

/* ====== ITEM LIST ====== */
.item-list { min-height: 160px; padding: 0; position: relative; }
.item-list-empty {
  position: absolute; inset: 0;
  display: flex; align-items: center; justify-content: center;
  color: var(--text-dim);
  font-style: italic; font-size: 14px; text-align: center;
  font-family: 'Crimson Text', serif;
}

/* Scroll wrapper */
.item-table-wrap { max-height: 480px; overflow-y: auto; }

/* Tiap baris — gaya refine */
.item-row {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 10px;
  border-bottom: 1px solid rgba(107,79,26,0.3);
  cursor: pointer; transition: background 0.1s;
}
.item-row:hover { background: rgba(61,46,21,0.5); }

/* Icon + badge */
.item-icon-wrap { position: relative; flex-shrink: 0; }
.item-icon {
  width: 48px; height: 48px;
  border: 1px solid var(--slot-bd); border-radius: 3px;
  background: var(--slot-bg); object-fit: contain;
  image-rendering: pixelated; display: block;
}
.tier-badge {
  position: absolute; top: 1px; left: 1px;
  width: 16px; height: 16px; border-radius: 2px;
  font-family: 'Cinzel', serif; font-size: 9px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  background: rgba(0,0,0,0.78); border: 1px solid currentColor;
  color: var(--gold);
}
.enc-badge {
  position: absolute; bottom: 1px; right: 1px;
  width: 14px; height: 14px; border-radius: 2px;
  font-size: 8px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  background: rgba(140,80,0,0.85); color: #ffe090;
}

/* Info kanan: nama + badge harga */
.item-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
.item-name { font-family: 'Crimson Text', serif; font-size: 15px; color: var(--parch-lt); font-weight: 600; }
.item-price { font-family: 'Cinzel', serif; font-size: 11px; color: var(--gold); }
.item-price.loading { color: var(--text-dim); font-style: italic; }

/* ====== SEARCH ====== */
.search-bar { padding: 10px 12px 0; }
.search-input {
  width: 100%;
  background: linear-gradient(180deg, #1a1208 0%, #110e05 100%);
  border: 1px solid var(--slot-bd); border-radius: 3px;
  color: var(--text-lt); font-family: 'Crimson Text', serif;
  font-size: 14px; padding: 8px 12px; outline: none;
  transition: border-color 0.15s;
}
.search-input:focus { border-color: var(--gold-dk); }
.search-input::placeholder { color: var(--text-dim); }

/* ====== POPUP ====== */
.popup-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.45);
  z-index: 10000;
  display: none; align-items: center; justify-content: center;
  padding: 16px;
}
.popup-overlay.show { display: flex; }
.popup-box {
  background: linear-gradient(180deg, #2e2210 0%, #1a1208 100%);
  border: 2px solid var(--panel-bd); border-radius: 6px;
  box-shadow: 0 8px 40px rgba(0,0,0,0.95);
  width: 100%; max-width: 400px;
  max-height: 88vh; overflow-y: auto;
  position: relative;
}
.popup-close {
  position: absolute; top: 10px; right: 10px;
  width: 28px; height: 28px; border-radius: 50%;
  background: linear-gradient(180deg, #6b1a1a 0%, #4a1010 100%);
  border: 1px solid #8b3030; color: #f0c0c0;
  font-size: 14px; cursor: pointer;
  display: flex; align-items: center; justify-content: center; z-index: 1;
}
.popup-close:hover { background: #8b2020; border-color: #c04040; }

/* Popup head: gambar + nama */
.popup-head {
  display: flex; gap: 14px; padding: 16px 44px 16px 16px;
  background: linear-gradient(180deg, #3d2e15 0%, #2a1f0e 100%);
  border-bottom: 1px solid var(--panel-bd); align-items: center;
}
.popup-head img {
  width: 72px; height: 72px;
  border: 1px solid var(--slot-bd); border-radius: 4px;
  background: var(--slot-bg); object-fit: contain; flex-shrink: 0;
}
.popup-item-name {
  font-family: 'Cinzel', serif; font-size: 15px;
  color: var(--gold); margin-bottom: 4px; line-height: 1.3;
}
.popup-item-sub {
  font-family: 'Crimson Text', serif; font-size: 12px;
  color: var(--text-dim);
}

/* Harga per kota */
.popup-prices {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(107,79,26,0.4);
}
.popup-prices-label {
  font-family: 'Cinzel', serif; font-size: 10px;
  color: var(--text-dim); text-transform: uppercase;
  letter-spacing: 1px; margin-bottom: 8px;
}
.city-prices-grid {
  display: flex; gap: 6px; flex-wrap: wrap;
}
.city-price-box {
  width: 44px; height: 44px; border-radius: 4px;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  font-family: 'Cinzel', serif; font-size: 9px;
  font-weight: 700; cursor: default;
  border: 1px solid rgba(0,0,0,0.3);
  gap: 2px;
}
.city-price-box .cpb-val { font-size: 11px; font-weight: 700; }
.city-price-box.loading  { opacity: 0.5; }
.city-price-box.no-data  { opacity: 0.3; }
.city-price-box { cursor: pointer; transition: box-shadow 0.15s, transform 0.1s; }
.city-price-box.active   { box-shadow: 0 0 0 2px #f0c060; transform: translateY(-1px); }
.city-price-box.no-data  { cursor: default; }

.popup-history { margin-top: 14px; }
.popup-history-label {
  font-size: 12px; letter-spacing: 0.05em; text-transform: uppercase;
  color: var(--text-dim, #a89878); margin-bottom: 8px;
  display: flex; align-items: center; justify-content: space-between;
}
.history-period-buttons {
  display: flex; gap: 4px;
}
.history-period-btn {
  padding: 3px 8px; font-size: 10px; font-family: 'Cinzel', serif;
  background: linear-gradient(180deg, #3a3a3a 0%, #2a2a2a 100%);
  border: 1px solid #4a4a4a; border-radius: 3px;
  color: var(--text-dim); cursor: pointer;
  transition: all 0.15s;
}
.history-period-btn:hover {
  background: linear-gradient(180deg, #4a4a4a 0%, #3a3a3a 100%);
  border-color: #6a6a6a;
}
.history-period-btn.active {
  background: linear-gradient(180deg, #c8a84a 0%, #a07828 100%);
  border-color: #8b6820;
  color: #2a1800;
}
.popup-history-canvas-wrap { position: relative; height: 160px; }
.popup-history-empty {
  color: var(--text-dim, #a89878); font-style: italic; font-size: 13px;
  display: flex; align-items: center; justify-content: center; height: 160px;
}
.popup-history-loading {
  color: var(--text-dim, #a89878); font-size: 13px;
  display: flex; align-items: center; justify-content: center; height: 160px;
}

/* Warna kota Albion */
.city-Caerleon      { background: #7b1a1a; color: #ffd0d0; border-color: #c0392b; }
.city-Bridgewatch   { background: #7a3a00; color: #ffe0b0; border-color: #e67e22; }
.city-Fort-Sterling { background: #3a3a3a; color: #f0f0f0; border-color: #bdc3c7; }
.city-Lymhurst      { background: #1a4a1a; color: #c0ffc0; border-color: #27ae60; }
.city-Martlock      { background: #1a2a5a; color: #c0d0ff; border-color: #2980b9; }
.city-Thetford      { background: #3a1a5a; color: #e0c0ff; border-color: #8e44ad; }
.city-Brecilien     { background: #0a3a2a; color: #a0ffe0; border-color: #1abc9c; }

/* Resources / bahan */
.popup-resources {
  padding: 12px 16px 16px;
}
.popup-resources-label {
  font-family: 'Cinzel', serif; font-size: 10px;
  color: var(--text-dim); text-transform: uppercase;
  letter-spacing: 1px; margin-bottom: 8px;
}
.resource-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(50px, 1fr)); gap: 8px; }
.resource-item {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  position: relative; cursor: pointer; transition: opacity 0.12s;
}
.resource-item:hover { opacity: 0.8; }
.resource-item img {
  width: 48px; height: 48px; object-fit: contain;
  border: 1px solid var(--slot-bd); border-radius: 3px;
  background: var(--slot-bg);
}
.resource-count {
  position: absolute; bottom: -5px; right: -5px;
  font-family: 'Cinzel', serif; font-size: 11px;
  color: var(--gold); font-weight: 700;
  background: var(--dark-bg); border: 1px solid var(--gold);
  border-radius: 3px; padding: 1px 3px;
}
.popup-loading {
  padding: 40px; text-align: center;
  color: var(--text-dim); font-style: italic;
  font-family: 'Crimson Text', serif; font-size: 14px;
}

::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-thumb { background: #8b6820; border-radius: 3px; }
</style>

<div>
  <div class="panel">
    <div class="panel-header">
      <span>💰</span>
      <span class="panel-title">{{ __('market.panel_title') }}</span>
      <input type="text" class="header-search" id="searchInput" placeholder="{{ __('market.search_placeholder') }}" oninput="onSearch()">
    </div>

    <div class="filter-bar" id="filterBar">
      <!-- CATEGORY -->
      <div class="flt-wrap">
        <div class="flt-btn" id="btnCategory" onclick="toggleDrop('category')">
          <span class="flt-label" id="lblCategory">{{ __('market.filter.category') }}</span>
          <span class="flt-val"   id="valCategory" style="display:none"></span>
          <span class="flt-arrow">▼</span>
        </div>
        <div class="drop-wrap" id="dropCategory">
          <div class="drop-col" id="colKat1"></div>
          <div class="drop-col" id="colKat2" style="display:none"></div>
          <div class="drop-col" id="colKat3" style="display:none"></div>
        </div>
      </div>
      <!-- TIER -->
      <div class="flt-wrap">
        <div class="flt-btn" id="btnTier" onclick="toggleDrop('tier')">
          <span class="flt-label" id="lblTier">{{ __('market.filter.tier') }}</span>
          <span class="flt-val"   id="valTier" style="display:none"></span>
          <span class="flt-arrow">▼</span>
        </div>
        <div class="drop-wrap" id="dropTier">
          <div class="drop-col" id="colTier"></div>
        </div>
      </div>
      <!-- ENCHANTMENT -->
      <div class="flt-wrap">
        <div class="flt-btn" id="btnEnc" onclick="toggleDrop('enc')">
          <span class="flt-label" id="lblEnc">{{ __('market.filter.enchantment') }}</span>
          <span class="flt-val"   id="valEnc" style="display:none"></span>
          <span class="flt-arrow">▼</span>
        </div>
        <div class="drop-wrap" id="dropEnc">
          <div class="drop-col" id="colEnc"></div>
        </div>
      </div>
      <!-- QUALITY -->
      <div class="flt-wrap">
        <div class="flt-btn" id="btnQuality" onclick="toggleDrop('quality')">
          <span class="flt-label" id="lblQuality">{{ __('market.filter.quality') }}</span>
          <span class="flt-val"   id="valQuality" style="display:none"></span>
          <span class="flt-arrow">▼</span>
        </div>
        <div class="drop-wrap" id="dropQuality">
          <div class="drop-col" id="colQuality"></div>
        </div>
      </div>
    </div>


    <!-- Item List -->
    <div class="item-list" id="itemList">
      <div class="item-list-empty" id="emptyMsg">{{ __('market.empty_select_category') }}</div>
      <div class="item-table-wrap" id="itemTableWrap" style="display:none">
        <div id="itemGrid"></div>
      </div>
    </div>
  </div>
</div>

<!-- ====== POPUP OVERLAY ====== -->
<div class="popup-overlay" id="popupOverlay" onclick="closePopupOnBg(event)">
  <div class="popup-box" id="popupBox">
    <button class="popup-close" onclick="closePopup()">✕</button>
    <div id="popupContent">
      <div class="popup-loading">{{ __('market.popup.loading') }}</div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.0/chart.umd.min.js"></script>
<script>
// ============================================================
// TRANSLATIONS
// ============================================================
const TRANS = {
  filter: {
    category: @json(__('market.filter.category')),
    tier: @json(__('market.filter.tier')),
    enchantment: @json(__('market.filter.enchantment')),
    quality: @json(__('market.filter.quality')),
    all: @json(__('market.filter.all')),
  },
  tierLabel: @json(__('market.tier_label')),
  qualityLabel: @json(__('market.quality_label')),
  enchantmentLabel: @json(__('market.enchantment_label')),
  enchantmentShort: @json(__('market.enchantment_short')),
  emptySelectCategory: @json(__('market.empty_select_category')),
  loadingItems: @json(__('market.loading_items')),
  noItemsFound: @json(__('market.no_items_found')),
  failedLoadItems: @json(__('market.failed_load_items')),
  popup: {
    loading: @json(__('market.popup.loading')),
    failedLoad: @json(__('market.popup.failed_load')),
    pricePerCity: @json(__('market.popup.price_per_city')),
    priceTrend30d: @json(__('market.popup.price_trend_30d')),
    selectCityForTrend: @json(__('market.popup.select_city_for_trend')),
    loadingTrend: @json(__('market.popup.loading_trend')),
    notEnoughData: @json(__('market.popup.not_enough_data')),
    failedLoadHistory: @json(__('market.popup.failed_load_history')),
    craftingMaterials: @json(__('market.popup.crafting_materials')),
    noRecipe: @json(__('market.popup.no_recipe')),
  }
};

// ============================================================
// KOTA & WARNA
// ============================================================
const CITIES = [
  { id: 'Caerleon',      label: 'CAE', cls: 'city-Caerleon'      },
  { id: 'Bridgewatch',   label: 'BRI', cls: 'city-Bridgewatch'   },
  { id: 'Fort Sterling', label: 'FOR', cls: 'city-Fort-Sterling'  },
  { id: 'Lymhurst',      label: 'LYM', cls: 'city-Lymhurst'      },
  { id: 'Martlock',      label: 'MAR', cls: 'city-Martlock'       },
  { id: 'Thetford',      label: 'THR', cls: 'city-Thetford'       },
  { id: 'Brecilien',     label: 'BRE', cls: 'city-Brecilien'      },
];

// ============================================================
// SERVER / REGION AODP — samain dengan region yang dipakai backend
// (session('server')) biar harga di list & harga di popup konsisten,
// bukan selalu Americas kayak refine.
// ============================================================
const CURRENT_SERVER = @json(session('server', 'americas'));
const AODP_BASE_URL = {
  americas: 'https://west.albion-online-data.com',
  europe:   'https://europe.albion-online-data.com',
  asia:     'https://east.albion-online-data.com',
};
const AODP_BASE = AODP_BASE_URL[CURRENT_SERVER] || AODP_BASE_URL.americas;

// ============================================================
// HISTORI HARGA — state + fungsi switcher kota
// ============================================================
let currentPopupItem  = null; // { api_id, enc } item yang lagi dibuka popup-nya
let currentHistoryCity = null;
let currentHistoryDays = 7; // default 7 days
let historyChart = null; // instance Chart.js aktif, biar bisa di-destroy pas ganti kota/item

function destroyHistoryChart() {
  if (historyChart) {
    historyChart.destroy();
    historyChart = null;
  }
}

function selectHistoryPeriod(days) {
  currentHistoryDays = days;
  // Update button states
  document.querySelectorAll('.history-period-btn').forEach(btn => btn.classList.remove('active'));
  document.getElementById('period-' + days).classList.add('active');
  // Reload chart dengan periode baru
  if (currentHistoryCity) {
    loadPriceHistory(currentHistoryCity);
  }
}

function selectHistoryCity(city, el) {
  if (el.classList.contains('no-data')) return; // kota tanpa harga, gak ada histori juga
  currentHistoryCity = city;

  // Highlight box yang lagi aktif
  document.querySelectorAll('.city-price-box.active').forEach(b => b.classList.remove('active'));
  el.classList.add('active');

  document.getElementById('historyCityLabel').textContent = city;
  loadPriceHistory(city);
}

function loadPriceHistory(city) {
  if (!currentPopupItem?.api_id) return;

  const wrap = document.getElementById('historyCanvasWrap');
  wrap.innerHTML = '<div class="popup-history-loading">' + TRANS.popup.loadingTrend + '</div>';
  destroyHistoryChart();

  const params = new URLSearchParams({
    city,
    enc: currentPopupItem.enc || 0,
    days: currentHistoryDays,
    quality: selQuality,
    server: CURRENT_SERVER,
  });

  fetch(`/api/market/item/${currentPopupItem.api_id}/price-history?${params}`)
    .then(r => r.json())
    .then(res => {
      // Kalau popup udah ditutup / ganti item / ganti kota lagi sebelum ini selesai
      if (currentHistoryCity !== city) return;

      const data = res.data ?? [];
      if (!data.length) {
        wrap.innerHTML = '<div class="popup-history-empty">' + TRANS.popup.notEnoughData + '</div>';
        return;
      }

      wrap.innerHTML = '<canvas id="historyCanvas"></canvas>';
      const ctx = document.getElementById('historyCanvas').getContext('2d');

      historyChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.map(d => d.date),
          datasets: [{
            data: data.map(d => d.price),
            borderColor: '#f0c060',
            backgroundColor: 'rgba(240, 192, 96, 0.15)',
            fill: true,
            tension: 0.25,
            pointRadius: 2,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { 
            legend: { display: false },
            tooltip: {
              callbacks: {
                title: function(context) {
                  const dateStr = context[0].label;
                  if (!dateStr) return '';
                  const date = new Date(dateStr);
                  if (isNaN(date.getTime())) return dateStr;
                  
                  const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
                  
                  // Format sesuai periode: 1D -> jam:menit lengkap, 7D/30D -> tanggal lengkap
                  if (currentHistoryDays === 1) {
                    const h = date.getHours().toString().padStart(2, '0');
                    const m = date.getMinutes().toString().padStart(2, '0');
                    return h + ':' + m;
                  } else {
                    return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
                  }
                },
                label: function(context) {
                  return 'Harga: ' + formatSilver(context.parsed.y);
                }
              }
            }
          },
          scales: {
            x: { 
              ticks: { 
                maxTicksLimit: 6, 
                color: '#a89878',
                callback: function(value, index) {
                  const dateStr = this.getLabelForValue(value);
                  if (!dateStr) return '';
                  const date = new Date(dateStr);
                  if (isNaN(date.getTime())) return dateStr;
                  
                  const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
                  
                  // Format sesuai periode: 1D -> jam:menit, 7D/30D -> tanggal bulan
                  if (currentHistoryDays === 1) {
                    const h = date.getHours().toString().padStart(2, '0');
                    const m = date.getMinutes().toString().padStart(2, '0');
                    return h + ':' + m;
                  } else {
                    return date.getDate() + ' ' + months[date.getMonth()];
                  }
                }
              }, 
              grid: { display: false } 
            },
            y: { ticks: { color: '#a89878', callback: v => formatSilver(v) }, grid: { color: 'rgba(168,152,120,0.1)' } },
          },
        },
      });
    })
    .catch(() => {
      if (currentHistoryCity !== city) return;
      wrap.innerHTML = '<div class="popup-history-empty">' + TRANS.popup.failedLoadHistory + '</div>';
    });
}

// ============================================================
// STATE
// ============================================================
let CATEGORIES = [];
let priceCache = {}; // item.id -> harga terendah antar kota (client-side fetch ke AODP)
// PENTING: value-nya angka (1-8) biar nyambung sama kolom 'tier' di DB yang isinya
// angka juga, bukan string "T1".."T8". TIER_LABEL cuma buat tampilan aja.
const TIERS      = [1,2,3,4,5,6,7,8];
const TIER_LABEL = TRANS.tierLabel;
const ENCS       = [0,1,2,3,4];
// Sinkron sama Item::QUALITY_MAP di backend (Normal=1 s.d. Masterpiece=5).
// Ini murni buat pilih VARIAN gambar & harga yang di-cek (qualities= param ke AODP),
// BUKAN filter tabel items() di backend (soalnya kolom quality di DB item selalu 'Normal').
const QUALITIES       = [1,2,3,4,5];
const QUALITY_LABEL   = TRANS.qualityLabel;

// Kategori ROOT yang support quality system (ID dari tabel categories)
// - Weapons, Armor (chest/head/foot), Off-hands, Capes, Bags, Mounts
// - Gathering equipment HANYA fishing rod yang punya quality, tapi kita include
//   parent category-nya dan nanti filter di level item (api_id contains 'FISHINGROD')
const QUALITY_SUPPORTED_ROOT_CATEGORIES = [1, 154, 186, 217, 248, 270, 286, 289, 371];

let openDrop = null;
let selCatId = null;
let selTier  = null;
let selEnc   = null;
let selQuality = 1; // default Normal
let searchQ  = '';
let lastRenderedItems = []; // simpen list terakhir biar quality bisa ganti gambar tanpa refetch backend

// ============================================================
// HELPER: Cek apakah kategori yang dipilih support quality
// ============================================================
function getRootCategoryId(id, tree) {
  for (const root of tree) {
    if (root.id === id) return root.id;
    if (root.children) {
      for (const sub of root.children) {
        if (sub.id === id) return root.id;
        if (sub.children) {
          for (const leaf of sub.children) {
            if (leaf.id === id) return root.id;
          }
        }
      }
    }
  }
  return null;
}

function isCategorySupportsQuality(catId) {
  if (!catId) return false;
  const rootId = getRootCategoryId(catId, CATEGORIES);
  return QUALITY_SUPPORTED_ROOT_CATEGORIES.includes(rootId);
}

// ============================================================
// PERSISTENCE — simpan filter kategori/tier/enchant ke localStorage
// biar gak reset pas reload atau pindah server (Asia <-> Eropa),
// yang keduanya sama-sama full page reload.
// ============================================================
const MARKET_FILTER_KEY = 'ct_market_filters';

function saveMarketFilters() {
  try {
    localStorage.setItem(MARKET_FILTER_KEY, JSON.stringify({
      selKat1, selKat2, selKat3, selCatId, selTier, selEnc, selQuality,
    }));
  } catch (e) {}
}

// Dipanggil sekali begitu CATEGORIES udah kefetch. Validasi tiap id
// kategori masih ada di tree sekarang sebelum dipulihkan — kalau
// kategori itu udah gak ada/berubah, biarkan null daripada state rusak.
function loadMarketFilters() {
  let saved;
  try {
    const raw = localStorage.getItem(MARKET_FILTER_KEY);
    if (!raw) return;
    saved = JSON.parse(raw);
  } catch (e) { return; }
  if (!saved) return;

  if (saved.selKat1 !== null && getCatName(saved.selKat1, CATEGORIES)) selKat1 = saved.selKat1;
  if (selKat1 !== null && saved.selKat2 !== null && getCatName(saved.selKat2, CATEGORIES)) selKat2 = saved.selKat2;
  if (selKat2 !== null && saved.selKat3 !== null && getCatName(saved.selKat3, CATEGORIES)) selKat3 = saved.selKat3;
  selCatId = selKat3 || selKat2 || selKat1 || null;

  if (saved.selTier !== null && TIERS.includes(saved.selTier)) selTier = saved.selTier;
  if (saved.selEnc  !== null && ENCS.includes(saved.selEnc))   selEnc  = saved.selEnc;
  if (saved.selQuality !== undefined && QUALITIES.includes(saved.selQuality)) selQuality = saved.selQuality;
}

// ============================================================
// DROPDOWN
// ============================================================
function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

function toggleDrop(name) {
  if (openDrop === name) { closeDrop(); return; }
  closeDrop();
  openDrop = name;
  const btn  = document.getElementById('btn'  + cap(name));
  const drop = document.getElementById('drop' + cap(name));
  const rect = btn.getBoundingClientRect();
  drop.style.top  = (rect.bottom + 3) + 'px';
  drop.style.left = rect.left + 'px';
  drop.classList.add('show');
  btn.classList.add('open');
}

function closeDrop() {
  if (!openDrop) return;
  document.getElementById('drop' + cap(openDrop)).classList.remove('show');
  document.getElementById('btn'  + cap(openDrop)).classList.remove('open');
  openDrop = null;
}

document.addEventListener('click', e => {
  if (openDrop && !e.target.closest('.flt-wrap')) closeDrop();
});

function setFilterVal(lblId, valId, value) {
  const lbl = document.getElementById(lblId);
  const val = document.getElementById(valId);
  if (value) {
    lbl.style.display = 'none'; val.style.display = ''; val.textContent = value;
  } else {
    lbl.style.display = ''; val.style.display = 'none';
  }
}

function makeItem(text, hasArrow, isActive, onClick) {
  const el = document.createElement('div');
  el.className = 'drop-item' + (isActive ? ' active' : '');
  el.innerHTML = text + (hasArrow ? '<span class="di-arrow">▶</span>' : '');
  el.addEventListener('click', e => { e.stopPropagation(); onClick(); });
  return el;
}

// ============================================================
// CATEGORY DROPDOWN
// ============================================================
let selKat1 = null, selKat2 = null, selKat3 = null;

function buildCol1() {
  const col = document.getElementById('colKat1');
  col.innerHTML = '';
  col.appendChild(makeItem(TRANS.filter.all, false, !selKat1, () => {
    selKat1 = null; selKat2 = null; selKat3 = null; selCatId = null;
    refreshCols(); updateCatLabel(); saveMarketFilters(); fetchItems();
  }));
  CATEGORIES.forEach(cat => {
    const hasSub = cat.children && cat.children.length > 0;
    col.appendChild(makeItem(cat.name, hasSub, selKat1 === cat.id, () => {
      selKat1 = cat.id; selKat2 = null; selKat3 = null; selCatId = cat.id;
      refreshCols(); updateCatLabel(); saveMarketFilters();
      if (!hasSub) closeDrop();
      fetchItems(); // langsung tampilin item kategori ini (+descendant-nya), gak perlu drill sampai leaf
      // Pre-fetch harga kategori ini di background (non-blocking)
      preloadCategoryPrices(cat.id);
    }));
  });
}

function buildCol2() {
  const col2 = document.getElementById('colKat2');
  const col3 = document.getElementById('colKat3');
  if (!selKat1) { col2.style.display = 'none'; col3.style.display = 'none'; return; }
  const cat1 = CATEGORIES.find(c => c.id === selKat1);
  if (!cat1 || !cat1.children || !cat1.children.length) { col2.style.display = 'none'; col3.style.display = 'none'; return; }
  col2.style.display = ''; col2.innerHTML = '';
  col2.appendChild(makeItem(TRANS.filter.all, false, !selKat2, () => {
    selKat2 = null; selKat3 = null; selCatId = selKat1;
    refreshCols(); updateCatLabel(); saveMarketFilters(); fetchItems();
  }));
  cat1.children.forEach(sub => {
    const hasSub2 = sub.children && sub.children.length > 0;
    col2.appendChild(makeItem(sub.name, hasSub2, selKat2 === sub.id, () => {
      selKat2 = sub.id; selKat3 = null; selCatId = sub.id;
      refreshCols(); updateCatLabel(); saveMarketFilters();
      if (!hasSub2) closeDrop();
      fetchItems(); // langsung tampilin item kategori ini (+descendant-nya)
      // Pre-fetch harga kategori ini di background (non-blocking)
      preloadCategoryPrices(sub.id);
    }));
  });
  buildCol3();
}

function buildCol3() {
  const col3 = document.getElementById('colKat3');
  if (!selKat1 || !selKat2) { col3.style.display = 'none'; return; }
  const cat1 = CATEGORIES.find(c => c.id === selKat1);
  const cat2 = cat1 && cat1.children.find(c => c.id === selKat2);
  if (!cat2 || !cat2.children || !cat2.children.length) { col3.style.display = 'none'; return; }
  col3.style.display = ''; col3.innerHTML = '';
  col3.appendChild(makeItem(TRANS.filter.all, false, !selKat3, () => {
    selKat3 = null; selCatId = selKat2;
    buildCol3(); updateCatLabel(); saveMarketFilters(); fetchItems();
  }));
  cat2.children.forEach(item => {
    col3.appendChild(makeItem(item.name, false, selKat3 === item.id, () => {
      selKat3 = item.id; selCatId = item.id;
      buildCol3(); updateCatLabel(); closeDrop(); saveMarketFilters(); fetchItems();
      // Pre-fetch harga kategori ini di background (non-blocking)
      preloadCategoryPrices(item.id);
    }));
  });
}

function refreshCols() { buildCol1(); buildCol2(); }

function getCatName(id, list) {
  for (const c of list) {
    if (c.id === id) return c.name;
    if (c.children) {
      for (const s of c.children) {
        if (s.id === id) return s.name;
        if (s.children) for (const l of s.children) { if (l.id === id) return l.name; }
      }
    }
  }
  return null;
}

function updateCatLabel() {
  const chosen = selKat3 || selKat2 || selKat1;
  setFilterVal('lblCategory', 'valCategory', chosen ? getCatName(chosen, CATEGORIES) : null);
}

// ============================================================
// TIER / ENC / QUALITY DROPDOWN
// ============================================================
function buildTierDrop() {
  const col = document.getElementById('colTier');
  col.innerHTML = '';
  col.appendChild(makeItem(TRANS.filter.all, false, !selTier, () => {
    selTier = null; setFilterVal('lblTier','valTier',null); closeDrop(); saveMarketFilters(); fetchItems();
  }));
  TIERS.forEach(t => col.appendChild(makeItem(TIER_LABEL[t], false, selTier === t, () => {
    selTier = t; setFilterVal('lblTier','valTier',TIER_LABEL[t]); closeDrop(); saveMarketFilters(); fetchItems();
  })));
}

function buildEncDrop() {
  const col = document.getElementById('colEnc');
  col.innerHTML = '';
  col.appendChild(makeItem(TRANS.filter.all, false, selEnc === null, () => {
    selEnc = null; setFilterVal('lblEnc','valEnc',null); closeDrop(); saveMarketFilters(); fetchItems();
  }));
  ENCS.forEach(e => col.appendChild(makeItem(TRANS.enchantmentLabel.replace(':n', e), false, selEnc === e, () => {
    selEnc = e; setFilterVal('lblEnc','valEnc', TRANS.enchantmentShort.replace(':n', e)); closeDrop(); saveMarketFilters(); fetchItems();
  })));
}

// Quality BEDA dari Tier/Enc: dia BUKAN filter ke backend (kolom quality di
// tabel items selalu 'Normal'), jadi milih quality gak perlu fetchItems() ulang.
// Yang berubah cuma: (1) suffix ?quality= di gambar list, (2) qualities= yang
// dikirim ke AODP buat harga list & popup. Makanya di sini panggil renderItems()
// + fetchMarketPrices() pakai data yang udah ada (lastRenderedItems), bukan refetch.
function buildQualityDrop() {
  const col = document.getElementById('colQuality');
  col.innerHTML = '';
  QUALITIES.forEach(q => col.appendChild(makeItem(QUALITY_LABEL[q], false, selQuality === q, () => {
    selQuality = q;
    setFilterVal('lblQuality', 'valQuality', QUALITY_LABEL[q]);
    closeDrop();
    saveMarketFilters();
    priceCache = {}; // harga lama gak valid lagi buat quality baru
    if (lastRenderedItems.length) {
      renderItems(lastRenderedItems);
      fetchMarketPrices(lastRenderedItems);
    }
  })));
}

// ============================================================
// SEARCH
// ============================================================
let searchTimer = null;
function onSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    searchQ = document.getElementById('searchInput').value.trim();
    fetchItems();
  }, 400);
}

// ============================================================
// FETCH ITEMS
// ============================================================
function showEmpty(msg) {
  document.getElementById('itemTableWrap').style.display = 'none';
  const el = document.getElementById('emptyMsg');
  el.style.display = ''; el.textContent = msg;
}

function fetchItems() {
  // Cek apakah quality selain Normal dipilih tapi kategori tidak support quality
  if (selQuality > 1 && selCatId && !isCategorySupportsQuality(selCatId)) {
    showEmpty('Quality hanya berlaku untuk Weapons, Armor, Bags, Capes, Mounts, dan Fishing Rod');
    return;
  }
  
  showEmpty(TRANS.loadingItems);
  const params = new URLSearchParams();
  if (selCatId) params.set('category_id', selCatId);
  if (selTier)  params.set('tier', selTier);
  if (selEnc !== null) params.set('enc', selEnc);
  fetch('/api/market/items?' + params.toString())
    .then(r => r.json())
    .then(items => {
      let filtered = items;
      if (searchQ) {
        const q = searchQ.toLowerCase();
        filtered = items.filter(i => i.name.toLowerCase().includes(q));
      }
      
      // Filter khusus untuk gathering tools: hanya fishing rod dan gathering armor yang punya quality
      if (selQuality > 1 && selCatId) {
        const rootId = getRootCategoryId(selCatId, CATEGORIES);
        if (rootId === 371) { // Gathering equipment
          filtered = filtered.filter(i => {
            if (!i.api_id) return false;
            // Hanya fishing rod dan gathering armor/gear yang punya quality
            return i.api_id.includes('FISHINGROD') || i.api_id.includes('GATHERER');
          });
        }
      }
      
      if (!filtered.length) { showEmpty(TRANS.noItemsFound); return; }
      lastRenderedItems = filtered;
      renderItems(filtered);
      fetchMarketPrices(filtered); // ambil harga client-side (ala refine), lalu render ulang row begitu selesai
    })
    .catch(() => showEmpty(TRANS.failedLoadItems));
}

// ============================================================
// RENDER ITEM ROWS (flex, gaya refine)
// ============================================================
function renderItems(items) {
  const grid = document.getElementById('itemGrid');
  grid.innerHTML = '';
  document.getElementById('itemTableWrap').style.display = '';
  document.getElementById('emptyMsg').style.display = 'none';

  items.forEach(item => {
    const row = document.createElement('div');
    row.className = 'item-row';
    // Override suffix ?quality= dari backend (selalu 'Normal' karena kolom quality
    // di DB item gak pernah diisi selain itu) dengan quality yang lagi dipilih user.
    const qualityImgUrl = item.img_url ? item.img_url.split('?')[0] + '?quality=' + selQuality : null;
    row.innerHTML = `
      <div class="item-icon-wrap">
            ${qualityImgUrl
      ? `<img class="item-icon" src="${qualityImgUrl}" alt="${item.name}" loading="lazy" onerror="this.style.display='none'">`
      : `<div class="item-icon" style="display:flex;align-items:center;justify-content:center;font-size:18px;">?</div>`
    }
      </div>
      <div class="item-info">
        <span class="item-name">${item.name}</span>
        ${priceCache[item.id] ? `<span class="item-price">${formatSilver(priceCache[item.id])}</span>` : ''}
      </div>`;

    row.addEventListener('click', () => openPopup(item.id));
    grid.appendChild(row);
  });
}

// ============================================================
// BANGUN ID BUAT QUERY AODP DARI api_id + enc.
// PENTING: equipment pakai suffix "@enc" (mis. T4_BAG@1), tapi RAW/REFINED
// RESOURCE (WOOD/ORE/HIDE/FIBER/ROCK & turunannya) sudah punya "_LEVELx"
// baked-in di api_id itu sendiri — kalau ditambah "@enc" lagi jadi salah
// format dan AODP gak bakal balikin harga. Makanya dicek dulu di sini.
// ============================================================
// Format AODP: equipment → "api_id@enc" (mis. T4_BAG@1)
// Resource/consumable → "api_id@enc" JUGA, api_id-nya udah termasuk "_LEVELx"
// jadi hasilnya "T5_FIBER_LEVEL2@2" (samain kayak refine_blade.php: api()).
function buildAodpId(item) {
  const enc = item.enc || 0;
  return enc > 0 ? `${item.api_id}@${enc}` : item.api_id;
}
// ============================================================
// AMBIL HARGA CLIENT-SIDE (ala refine) — fetch langsung dari BROWSER
// ke AODP, per batch max 200 id. Harga per item diambil yang TERENDAH
// antar 7 kota (bukan cache dari backend, biar list gak berat di server).
// Begitu selesai, render ulang list biar harga muncul.
// ============================================================
async function fetchMarketPrices(items) {
  const idMap = items.filter(it => it.api_id).map(it => ({ id: it.id, aodpId: buildAodpId(it) }));
  if (!idMap.length) return;

  const uniqueAodpIds = [...new Set(idMap.map(m => m.aodpId))];
  const kotaParam = CITIES.map(c => c.id).join(',');
  const lowestByAodpId = {};

  try {
    for (let i = 0; i < uniqueAodpIds.length; i += 200) {
      const batch = uniqueAodpIds.slice(i, i + 200);
      const url = `${AODP_BASE}/api/v2/stats/prices/${batch.join(',')}?locations=${encodeURIComponent(kotaParam)}&qualities=${selQuality}`;
      const res  = await fetch(url);
      const data = await res.json();
      for (const e of data) {
        if (e.sell_price_min > 0) {
          if (!lowestByAodpId[e.item_id] || e.sell_price_min < lowestByAodpId[e.item_id]) {
            lowestByAodpId[e.item_id] = e.sell_price_min;
          }
        }
      }
    }

    for (const m of idMap) {
      const price = lowestByAodpId[m.aodpId];
      if (price) priceCache[m.id] = price;
    }

    renderItems(items); // render ulang biar harga yang barusan didapat muncul
  } catch (e) {
    // gagal fetch harga -> list tetap tampil tanpa harga, gak ganggu fungsi utama
  }
}

// ============================================================
// FORMAT ANGKA HARGA — cuma angka + suffix M/K, tanpa label mata uang.
// ============================================================
function formatSilver(n) {
  if (n >= 1_000_000) return (n/1_000_000).toFixed(1) + 'M';
  if (n >= 1_000)     return (n/1_000).toFixed(1) + 'K';
  return String(n);
}

// ============================================================
// POPUP — render INSTAN pakai harga cache DB dulu (biar gak nunggu
// AODP yang bisa 1-8 detik), lalu di background kita fetch harga
// LANGSUNG DARI BROWSER KE AODP (persis kayak list) dan TIMPA box
// yang sudah tampil. UI popup ini SUDAH GAK BERGANTUNG ke endpoint
// refresh backend sama sekali — itu cuma dipanggil diam-diam di
// belakang layar buat jaga tabel item_prices tetap fresh (kebutuhan
// lain), hasilnya gak dipakai buat nampilin apa-apa di popup.
// ============================================================
function openPopup(itemId) {
  document.getElementById('popupContent').innerHTML = '<div class="popup-loading">' + TRANS.popup.loading + '</div>';
  document.getElementById('popupOverlay').classList.add('show');

  fetch('/api/market/item/' + itemId)
    .then(r => r.json())
    .then(item => {
      console.log('Item data received:', item);
      console.log('Resources:', item.resources);
      renderPopup(item);                       // instan, pakai harga cache DB yang sudah ada (kalau ada)
      fetchPopupPricesFromAodp(item); // lalu ambil harga REAL dari browser -> AODP (quality dari dropdown, via getAodpQualitiesParam()), timpa box
      silentlyRefreshBackendCache(itemId);  // diam-diam suruh backend refresh tabel item_prices, TIDAK dipakai buat UI
    })
    .catch((err) => {
      console.error('Failed to fetch item:', err);
      document.getElementById('popupContent').innerHTML = '<div class="popup-loading">' + TRANS.popup.failedLoad + '</div>';
    });
}

// ============================================================
// AMBIL HARGA POPUP LANGSUNG DARI BROWSER KE AODP — sama persis
// gayanya kayak fetchMarketPrices() punya list, bedanya di sini kita
// butuh breakdown PER KOTA (bukan cuma yang terendah), soalnya popup
// nampilin 7 kotak harga. Begitu respons AODP datang, tiap kotak
// ditimpa dengan harganya masing-masing. Kalau AODP gagal/timeout,
// box dibiarkan seperti render instan sebelumnya (cache DB / '—').
// Ini SATU-SATUNYA sumber harga popup sekarang; gak ada ketergantungan
// ke endpoint refresh-prices backend.
// ============================================================
function fetchPopupPricesFromAodp(item) {
  if (!item.api_id) return;

  CITIES.forEach(c => {
    document.getElementById('cpb-' + c.id.replace(' ', '-'))?.classList.add('loading');
  });

  const aodpId    = buildAodpId(item);
  const kotaParam = CITIES.map(c => c.id).join(',');
  const url = `${AODP_BASE}/api/v2/stats/prices/${aodpId}?locations=${encodeURIComponent(kotaParam)}&qualities=${selQuality}`;

  fetch(url)
    .then(r => r.json())
    .then(data => {
      // Ambil harga TERENDAH per kota, bukan overwrite langsung — soalnya kalau
      // quality "All" dipilih, AODP balikin beberapa entry per kota (satu per
      // quality), jadi entry terakhir yang datang gak boleh sembarang nimpa.
      const priceByCity = {};
      for (const e of data) {
        if (e.sell_price_min > 0 && (!priceByCity[e.city] || e.sell_price_min < priceByCity[e.city])) {
          priceByCity[e.city] = e.sell_price_min;
        }
      }
      CITIES.forEach(c => {
        const boxId = 'cpb-' + c.id.replace(' ', '-');
        const box = document.getElementById(boxId);
        if (!box) return; // popup udah ditutup / item lain dibuka
        box.classList.remove('loading');
        const price = priceByCity[c.id];
        if (!price) {
          box.classList.add('no-data');
          box.querySelector('.cpb-val').textContent = '—';
          return;
        }
        box.classList.remove('no-data');
        box.querySelector('.cpb-val').textContent = formatSilver(price);
      });
    })
    .catch(() => {
      CITIES.forEach(c => document.getElementById('cpb-' + c.id.replace(' ', '-'))?.classList.remove('loading'));
      // gagal fetch AODP -> box dibiarkan sesuai render instan (cache DB kalau ada, atau '—')
    });
}

// ============================================================
// DIAM-DIAM minta backend refresh harga ke tabel item_prices.
// Fire-and-forget: hasilnya (berhasil/gagal) SAMA SEKALI gak dipakai
// buat update UI popup — itu udah jadi tugas fetchPopupPricesFromAodp
// di atas. Ini cuma biar item_prices tetap ke-update buat kebutuhan
// lain (list, export, dsb), jadi popup gak nunggu ini sama sekali.
// ============================================================
function silentlyRefreshBackendCache(itemId) {
  fetch(`/api/market/item/${itemId}/refresh-prices`, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'X-CSRF-TOKEN': getCsrf() },
  }).catch(() => {}); // gagal gak masalah, popup gak bergantung sama ini
}

function renderPopup(item) {
  const enc      = item.enc ?? 0;
  const tierText = (item.tier ?? '') + (enc > 0 ? '.' + enc : '');
  const apiIdEnc = enc > 0 ? `${item.api_id}@${enc}` : item.api_id;
  // Sama kayak di list: override suffix ?quality= dari backend (selalu 'Normal')
  // dengan quality yang lagi dipilih user di dropdown (getImageQuality() handle
  // kasus "All" -> fallback ke Normal karena "All" gak punya varian gambar).
  const popupImgUrl = item.img_url ? item.img_url.split('?')[0] + '?quality=' + selQuality : item.img_url;

  // Kotak harga kota — langsung pakai harga cache DB dulu (instan), gak nunggu
  // AODP. fetchPopupPricesFromAodp() bakal nimpa box ini di background begitu
  // hasil fetch browser -> AODP datang (itu sumber utamanya sekarang).
  const cityBoxes = CITIES.map(c => {
    const price = item.prices?.[c.id] ?? 0;
    const boxId = 'cpb-' + c.id.replace(' ', '-');
    const clickAttr = `onclick="selectHistoryCity('${c.id}', this)"`;
    return `
    <div class="city-price-box ${price ? '' : 'no-data'} ${c.cls}" id="${boxId}" title="${c.id}" ${clickAttr}>
      <span class="cpb-val">${price ? formatSilver(price) : '—'}</span>
    </div>
  `;
  }).join('');

  // Simpan konteks item aktif buat dipakai loadPriceHistory() pas user pencet kota
  currentPopupItem = { api_id: item.api_id, enc: enc };
  currentHistoryCity = null;
  currentHistoryDays = 7; // reset ke default 7 days
  destroyHistoryChart();

  // Group resources per recipe variant (seperti di halaman crafting)
  const groupedResources = groupRecipeResources(item.resources || []);
  
  console.log('Building resourcesHtml, item.resources:', item.resources);
  console.log('Grouped resources:', groupedResources);
  
  const resourcesHtml = groupedResources.length
    ? groupedResources.map((group, groupIdx) => {
        const groupHtml = group.map(r => {
          console.log('Resource:', r);
          return `
          <div class="resource-item" onclick="openPopup(${r.item_id ?? 'null'})" ${!r.item_id ? 'style="cursor:default;opacity:0.7"' : ''} title="${r.name}">
            <img src="${r.img_url}" alt="${r.name}" onerror="this.style.opacity=0.3">
            <div class="resource-count">×${r.count}</div>
          </div>
        `;
        }).join('');
        
        // Separator antar recipe variant
        const separator = groupIdx < groupedResources.length - 1 
          ? '<div style="grid-column: 1/-1; height: 1px; background: rgba(107,79,26,0.3); margin: 4px 0;"></div>' 
          : '';
        
        return groupHtml + separator;
      }).join('')
    : '<div style="color:var(--text-dim);font-style:italic;font-size:13px">' + TRANS.popup.noRecipe + '</div>';
  
  console.log('resourcesHtml:', resourcesHtml);

  document.getElementById('popupContent').innerHTML = `
    <div class="popup-head">
      <img src="${popupImgUrl}" alt="${item.name}" onerror="this.style.opacity=0.3">
      <div>
        <div class="popup-item-name">${item.name}</div>
        <div class="popup-item-sub">${tierText}</div>
      </div>
    </div>
    <div class="popup-prices">
      <div class="popup-prices-label">${TRANS.popup.pricePerCity}</div>
      <div class="city-prices-grid">${cityBoxes}</div>
    </div>
    <div class="popup-history">
      <div class="popup-history-label">
        <span>${TRANS.popup.priceTrend30d} — <span id="historyCityLabel">-</span></span>
        <div class="history-period-buttons">
          <button class="history-period-btn" id="period-1" onclick="selectHistoryPeriod(1)">1D</button>
          <button class="history-period-btn active" id="period-7" onclick="selectHistoryPeriod(7)">7D</button>
          <button class="history-period-btn" id="period-30" onclick="selectHistoryPeriod(30)">30D</button>
        </div>
      </div>
      <div class="popup-history-canvas-wrap" id="historyCanvasWrap">
        <div class="popup-history-loading">${TRANS.popup.loadingTrend}</div>
      </div>
    </div>
    <div class="popup-resources">
      <div class="popup-resources-label">${TRANS.popup.craftingMaterials}</div>
      <div class="resource-list">${resourcesHtml}</div>
    </div>
  `;
  
  // Auto-select kota dengan harga terendah untuk chart
  setTimeout(() => {
    const cityPrices = CITIES.map(c => ({
      city: c.id,
      price: item.prices?.[c.id] ?? 0,
      box: document.getElementById('cpb-' + c.id.replace(' ', '-'))
    })).filter(cp => cp.price > 0);
    
    if (cityPrices.length > 0) {
      // Pilih kota dengan harga terendah
      cityPrices.sort((a, b) => a.price - b.price);
      const cheapest = cityPrices[0];
      if (cheapest.box) {
        selectHistoryCity(cheapest.city, cheapest.box);
      }
    }
  }, 100);
}

// Helper: Group resources per recipe variant (copy dari crafting.blade.php)
function groupRecipeResources(resources) {
  if (!resources || !resources.length) return [];
  const groups = [];
  let current = [];
  let seen = new Set();
  resources.forEach(r => {
    const key = r.item_id ?? r.name;
    if (seen.has(key)) { 
      groups.push(current); 
      current = []; 
      seen = new Set(); 
    }
    seen.add(key);
    current.push(r);
  });
  if (current.length) groups.push(current);
  return groups;
}

// ============================================================
// PRE-LOAD CATEGORY PRICES — dijalankan pas user klik kategori
// (non-blocking, jalan di background). Kalau berhasil, nanti
// pas user buka item di kategori itu, harga udah di-cache.
// ============================================================
function preloadCategoryPrices(categoryId) {
  fetch('/api/market/category/' + categoryId + '/refresh-prices', { method: 'POST' })
    .catch(() => {}); // kalau gagal, biarkan aja, fallback ke per-item nanti
}

function getCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function closePopup() {
  document.getElementById('popupOverlay').classList.remove('show');
}

function closePopupOnBg(e) {
  if (e.target === document.getElementById('popupOverlay')) closePopup();
}

// ============================================================
// INIT
// ============================================================
fetch('/api/market/categories')
  .then(r => r.json())
  .then(data => {
    CATEGORIES = data;
    loadMarketFilters(); // pulihkan kategori/tier/enchant dari sesi sebelumnya (kalau ada)
    refreshCols();
    updateCatLabel();
    buildTierDrop();
    setFilterVal('lblTier', 'valTier', selTier !== null ? TIER_LABEL[selTier] : null);
    buildEncDrop();
    setFilterVal('lblEnc', 'valEnc', selEnc !== null ? ('Enc ' + selEnc) : null);
    buildQualityDrop();
    setFilterVal('lblQuality', 'valQuality', QUALITY_LABEL[selQuality]);
    fetchItems(); // load item sesuai filter yang udah dipulihkan (atau semua item kalau belum pernah difilter)
  });
</script>
<x-comments page="market" />
@endsection