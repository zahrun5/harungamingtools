@extends('layouts.app')
@section('title', 'Cek Harga Item — HGT')
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
  background: rgba(0,0,0,0.82);
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
      <span class="panel-title">Cek Harga Item</span>
      <input type="text" class="header-search" id="searchInput" placeholder="Cari nama item..." oninput="onSearch()">
    </div>

    <div class="filter-bar" id="filterBar">
      <!-- CATEGORY -->
      <div class="flt-wrap">
        <div class="flt-btn" id="btnCategory" onclick="toggleDrop('category')">
          <span class="flt-label" id="lblCategory">Category</span>
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
          <span class="flt-label" id="lblTier">Tier</span>
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
          <span class="flt-label" id="lblEnc">Enchantment</span>
          <span class="flt-val"   id="valEnc" style="display:none"></span>
          <span class="flt-arrow">▼</span>
        </div>
        <div class="drop-wrap" id="dropEnc">
          <div class="drop-col" id="colEnc"></div>
        </div>
      </div>
    </div>


    <!-- Item List -->
    <div class="item-list" id="itemList">
      <div class="item-list-empty" id="emptyMsg">Pilih kategori untuk melihat item 🗡️</div>
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
      <div class="popup-loading">Memuat...</div>
    </div>
  </div>
</div>

<script>
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
// STATE
// ============================================================
let CATEGORIES = [];
// PENTING: value-nya angka (1-8) biar nyambung sama kolom 'tier' di DB yang isinya
// angka juga, bukan string "T1".."T8". TIER_LABEL cuma buat tampilan aja.
const TIERS      = [1,2,3,4,5,6,7,8];
const TIER_LABEL = {1:'Tier 1',2:'Tier 2',3:'Tier 3',4:'Tier 4',5:'Tier 5',6:'Tier 6',7:'Tier 7',8:'Tier 8'};
const ENCS       = [0,1,2,3,4];

let openDrop = null;
let selCatId = null;
let selTier  = null;
let selEnc   = null;
let searchQ  = '';

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
  col.appendChild(makeItem('All', false, !selKat1, () => {
    selKat1 = null; selKat2 = null; selKat3 = null; selCatId = null;
    refreshCols(); updateCatLabel(); fetchItems();
  }));
  CATEGORIES.forEach(cat => {
    const hasSub = cat.children && cat.children.length > 0;
    col.appendChild(makeItem(cat.name, hasSub, selKat1 === cat.id, () => {
      selKat1 = cat.id; selKat2 = null; selKat3 = null; selCatId = cat.id;
      refreshCols(); updateCatLabel();
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
  col2.appendChild(makeItem('All', false, !selKat2, () => {
    selKat2 = null; selKat3 = null; selCatId = selKat1;
    refreshCols(); updateCatLabel(); fetchItems();
  }));
  cat1.children.forEach(sub => {
    const hasSub2 = sub.children && sub.children.length > 0;
    col2.appendChild(makeItem(sub.name, hasSub2, selKat2 === sub.id, () => {
      selKat2 = sub.id; selKat3 = null; selCatId = sub.id;
      refreshCols(); updateCatLabel();
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
  col3.appendChild(makeItem('All', false, !selKat3, () => {
    selKat3 = null; selCatId = selKat2;
    buildCol3(); updateCatLabel(); fetchItems();
  }));
  cat2.children.forEach(item => {
    col3.appendChild(makeItem(item.name, false, selKat3 === item.id, () => {
      selKat3 = item.id; selCatId = item.id;
      buildCol3(); updateCatLabel(); closeDrop(); fetchItems();
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
  col.appendChild(makeItem('All', false, !selTier, () => {
    selTier = null; setFilterVal('lblTier','valTier',null); closeDrop(); fetchItems();
  }));
  TIERS.forEach(t => col.appendChild(makeItem(TIER_LABEL[t], false, selTier === t, () => {
    selTier = t; setFilterVal('lblTier','valTier',TIER_LABEL[t]); closeDrop(); fetchItems();
  })));
}

function buildEncDrop() {
  const col = document.getElementById('colEnc');
  col.innerHTML = '';
  col.appendChild(makeItem('All', false, selEnc === null, () => {
    selEnc = null; setFilterVal('lblEnc','valEnc',null); closeDrop(); fetchItems();
  }));
  ENCS.forEach(e => col.appendChild(makeItem('Enchantment ' + e, false, selEnc === e, () => {
    selEnc = e; setFilterVal('lblEnc','valEnc','Enc '+e); closeDrop(); fetchItems();
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
  showEmpty('Memuat item...');
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
      if (!filtered.length) { showEmpty('Tidak ada item ditemukan 😔'); return; }
      renderItems(filtered);
    })
    .catch(() => showEmpty('Gagal memuat item. Coba lagi.'));
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
    row.innerHTML = `
      <div class="item-icon-wrap">
        ${item.img_url
          ? `<img class="item-icon" src="${item.img_url}" alt="${item.name}" onerror="this.style.display='none'">`
          : `<div class="item-icon" style="display:flex;align-items:center;justify-content:center;font-size:18px;">?</div>`
        }
      </div>
      <div class="item-info">
        <span class="item-name">${item.name}</span>
      </div>`;

    row.addEventListener('click', () => openPopup(item.id));
    grid.appendChild(row);
  });
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
// POPUP — render INSTAN pakai harga cache dulu (biar gak nunggu
// API luar yang bisa 1-8 detik), lalu di background kita coba
// ambil harga real-time dan TIMPA box yang sudah tampil. Kalau
// real-time gagal, cache yang sudah tampil dibiarkan (itu fallback-nya).
// ============================================================
function openPopup(itemId) {
  document.getElementById('popupContent').innerHTML = '<div class="popup-loading">Memuat...</div>';
  document.getElementById('popupOverlay').classList.add('show');

  fetch('/api/market/item/' + itemId)
    .then(r => r.json())
    .then(item => {
      renderPopup(item);          // instan, pakai harga cache yang sudah ada
      refreshPopupPrices(itemId); // lalu coba real-time di background, timpa kalau berhasil
    })
    .catch(() => {
      document.getElementById('popupContent').innerHTML = '<div class="popup-loading">Gagal memuat data.</div>';
    });
}

// ============================================================
// REFRESH HARGA (background) — minta backend fetch real-time ke
// Albion Online Data Project. Kalau berhasil & ada harganya, box
// ditimpa dengan nilai real-time itu. Kalau gagal/kosong, box
// dibiarkan seperti apa adanya (masih nunjukin harga cache).
// ============================================================
function refreshPopupPrices(itemId) {
  CITIES.forEach(c => {
    const boxId = 'cpb-' + c.id.replace(' ', '-');
    document.getElementById(boxId)?.classList.add('loading');
  });

  fetch(`/api/market/item/${itemId}/refresh-prices`, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'X-CSRF-TOKEN': getCsrf() },
  })
    .then(r => r.json())
    .then(data => {
      CITIES.forEach(c => {
        const boxId = 'cpb-' + c.id.replace(' ', '-');
        const box = document.getElementById(boxId);
        if (!box) return; // popup udah ditutup / item lain dibuka
        box.classList.remove('loading');
        const price = data.prices?.[c.id] ?? 0;
        if (!price) return; // gak ada harga sama sekali (real-time maupun cache) → biarkan tampilan sebelumnya
        box.classList.remove('no-data');
        box.querySelector('.cpb-val').textContent = formatSilver(price);
      });
    })
    .catch(() => {
      CITIES.forEach(c => document.getElementById('cpb-' + c.id.replace(' ', '-'))?.classList.remove('loading'));
      // gagal total → biarkan harga cache yang sudah tampil dari renderPopup
    });
}

function renderPopup(item) {
  const enc      = item.enc ?? 0;
  const tierText = (item.tier ?? '') + (enc > 0 ? '.' + enc : '');
  const apiIdEnc = enc > 0 ? `${item.api_id}@${enc}` : item.api_id;

  // Kotak harga kota — langsung pakai harga cache dulu (instan), gak nunggu
  // fetch real-time. refreshPopupPrices() bakal nimpa box ini di background
  // begitu hasil real-time datang.
  const cityBoxes = CITIES.map(c => {
    const price = item.prices?.[c.id] ?? 0;
    const boxId = 'cpb-' + c.id.replace(' ', '-');
    return `
    <div class="city-price-box ${price ? '' : 'no-data'} ${c.cls}" id="${boxId}" title="${c.id}">
      <span class="cpb-val">${price ? formatSilver(price) : '—'}</span>
    </div>
  `;
  }).join('');

  // Buat list resource — minimal design: cuma gambar + jumlah aja
  // Kalau ada 2 recipe berbeda bahan, dipisahin dengan jarak/line
  const resourcesHtml = item.resources && item.resources.length
    ? item.resources.map((r, idx) => `
      <div class="resource-item" onclick="openPopup(${r.item_id ?? 'null'})" ${!r.item_id ? 'style="cursor:default;opacity:0.7"' : ''} title="${r.name}">
        <img src="${r.img_url}" alt="${r.name}" onerror="this.style.opacity=0.3">
        <div class="resource-count">×${r.count}</div>
      </div>
    `).join('')
    : '<div style="color:var(--text-dim);font-style:italic;font-size:13px">Tidak ada recipe</div>';

  document.getElementById('popupContent').innerHTML = `
    <div class="popup-head">
      <img src="${item.img_url}" alt="${item.name}" onerror="this.style.opacity=0.3">
      <div>
        <div class="popup-item-name">${item.name}</div>
        <div class="popup-item-sub">${tierText}</div>
      </div>
    </div>
    <div class="popup-prices">
      <div class="popup-prices-label">Harga per Kota</div>
      <div class="city-prices-grid">${cityBoxes}</div>
    </div>
    <div class="popup-resources">
      <div class="popup-resources-label">Bahan Crafting</div>
      <div class="resource-list">${resourcesHtml}</div>
    </div>
  `;
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
    buildCol1();
    buildTierDrop();
    buildEncDrop();
    fetchItems(); // load semua item dari awal, gak perlu pilih kategori dulu
  });
</script>
@endsection