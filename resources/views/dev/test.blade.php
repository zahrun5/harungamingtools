@extends('layouts.app')
@section('title', 'Dev Testing')
@section('content')
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
<style>
:root {
  --parch-lt: #dcc08a;
  --panel-bd: #6b4f1a;
  --gold:     #f0c040;
  --gold-dk:  #b8860b;
  --text-lt:  #e8d5a3;
  --text-dim: #a08040;
  --slot-bg:  #1a1208;
  --slot-bd:  #4a3510;
}
.panel {
  background: linear-gradient(180deg,#2e2210 0%,#1e1608 100%);
  border: 2px solid var(--panel-bd); border-radius:6px;
  box-shadow: 0 4px 24px rgba(0,0,0,.7);
  margin-bottom: 24px; overflow: hidden;
}
.panel-header {
  background: linear-gradient(180deg,#3d2e15 0%,#2a1f0e 100%);
  border-bottom: 1px solid var(--panel-bd);
  padding: 10px 16px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;
}
.panel-title { font-family:'Cinzel',serif; font-size:14px; color:var(--gold); letter-spacing:1px; text-transform:uppercase; }

/* TOOLBAR */
.toolbar { padding:12px 14px; display:flex; gap:8px; flex-wrap:wrap; align-items:center; border-bottom:1px solid var(--panel-bd); }
.tb-input {
  background: linear-gradient(180deg,#1a1208 0%,#110e05 100%);
  border:1px solid var(--slot-bd); border-radius:3px;
  color:var(--text-lt); font-family:'Crimson Text',serif; font-size:14px;
  padding:7px 12px; outline:none; transition:border-color .15s;
}
.tb-input:focus { border-color:var(--gold-dk); }
.tb-input::placeholder { color:var(--text-dim); }
.tb-input.search { flex:1; min-width:160px; }
.tb-btn {
  font-family:'Cinzel',serif; font-size:11px; font-weight:700;
  padding:8px 14px; border-radius:3px; cursor:pointer; border:1px solid;
  transition:all .1s; white-space:nowrap;
}
.tb-btn.gold { background:linear-gradient(180deg,#c8a84a,#a07828); border-color:#8b6820; color:#2a1800; }
.tb-btn.gold:hover { background:linear-gradient(180deg,#dabb5a,#b88838); }
.tb-btn.red  { background:linear-gradient(180deg,#7b1a1a,#4a1010); border-color:#8b3030; color:#ffd0d0; }
.tb-btn.red:hover  { background:#8b2020; }
.tb-stat { font-family:'Cinzel',serif; font-size:11px; color:var(--text-dim); flex:1 1 100%; }
@media (min-width: 860px) {
  .tb-stat { flex:0 0 auto; margin-left:auto; }
}

/* GROUP CARD (kelompok base name, isinya beberapa tier/enc) */
.group-card {
  border-bottom: 2px solid var(--panel-bd);
  background: linear-gradient(180deg,#241b0c 0%,#1a1308 100%);
}
.group-header {
  display:flex; flex-wrap:wrap; align-items:center; gap:10px;
  padding:12px 14px; border-bottom:1px solid rgba(107,79,26,.4);
  background: rgba(61,46,21,.5);
}
.group-title-row { display:flex; align-items:center; gap:8px; flex:1 1 100%; }
.group-title {
  font-family:'Cinzel',serif; font-size:13px; color:var(--gold);
  letter-spacing:.5px;
}
.group-count { font-family:'Cinzel',serif; font-size:10px; color:var(--text-dim); }
.group-cat-selects { display:flex; gap:6px; flex:1 1 100%; flex-wrap:wrap; }
.group-actions { display:flex; align-items:center; gap:10px; flex:1 1 100%; justify-content:space-between; }
.group-save-btn {
  font-family:'Cinzel',serif; font-size:11px; font-weight:700;
  padding:8px 18px; border-radius:3px; cursor:pointer;
  background:linear-gradient(180deg,#c8a84a,#a07828);
  border:1px solid #8b6820; color:#2a1800;
  transition:all .1s;
}
.group-save-btn:hover { background:linear-gradient(180deg,#dabb5a,#b88838); }
.group-save-btn:disabled { opacity:.4; cursor:default; }
.group-toggle {
  font-family:'Cinzel',serif; font-size:10px; color:var(--text-dim);
  cursor:pointer; padding:4px 10px; border:1px solid var(--slot-bd); border-radius:3px;
  background:var(--slot-bg); margin-left:auto;
}
.group-items { display:block; }
.group-items.collapsed { display:none; }

/* ITEM ROW */
.item-row {
  display:flex; flex-wrap:wrap; align-items:center; gap:10px;
  padding:10px 14px 10px 36px; border-bottom:1px solid rgba(107,79,26,.25);
  transition:background .1s;
}
.item-row:hover { background:rgba(61,46,21,.4); }
.item-row.saved { background:rgba(0,80,0,.15); }
.item-img {
  width:48px; height:48px; flex-shrink:0;
  border:1px solid var(--slot-bd); border-radius:3px;
  background:var(--slot-bg); object-fit:contain; image-rendering:pixelated;
}
.item-img.broken { opacity:.25; }
.item-head { display:flex; align-items:center; gap:10px; flex:1 1 100%; min-width:0; }
.item-api { font-family:'Cinzel',serif; font-size:11px; color:var(--text-dim); flex:1; min-width:0; word-break:break-all; }
.item-name-input {
  flex:1 1 100%; min-width:140px;
  background:linear-gradient(180deg,#1a1208,#110e05);
  border:1px solid var(--slot-bd); border-radius:3px;
  color:var(--text-lt); font-family:'Crimson Text',serif; font-size:13px;
  padding:7px 10px; outline:none;
}
.item-name-input:focus { border-color:var(--gold-dk); }

/* CATEGORY SELECT */
.cat-selects { display:flex; gap:6px; flex:1 1 100%; flex-wrap:wrap; }
.cat-sel {
  flex:1 1 30%; min-width:90px;
  background:linear-gradient(180deg,#1a1208,#110e05);
  border:1px solid var(--slot-bd); border-radius:3px;
  color:var(--text-lt); font-family:'Crimson Text',serif; font-size:12px;
  padding:7px 6px; outline:none; cursor:pointer;
}
.cat-sel:focus { border-color:var(--gold-dk); }
.cat-sel option { background:#1a1208; color:var(--text-lt); }

/* ROW FOOTER: badge + tombol */
.row-footer { display:flex; align-items:center; justify-content:space-between; gap:10px; flex:1 1 100%; }

/* STATUS BADGE */
.status-badge {
  font-family:'Cinzel',serif; font-size:10px; font-weight:700;
  padding:4px 9px; border-radius:3px; flex-shrink:0;
}
.status-badge.saved   { background:#1a4a1a; color:#c0ffc0; border:1px solid #27ae60; }
.status-badge.unsaved { background:#3a2a00; color:#ffe090; border:1px solid #b8860b; }
.status-badge.saving  { background:#1a2a5a; color:#c0d0ff; border:1px solid #2980b9; }

/* SAVE BTN per row */
.row-save-btn {
  font-family:'Cinzel',serif; font-size:11px; font-weight:700;
  padding:7px 16px; border-radius:3px; cursor:pointer;
  background:linear-gradient(180deg,#c8a84a,#a07828);
  border:1px solid #8b6820; color:#2a1800;
  transition:all .1s; flex-shrink:0;
}
.row-save-btn:hover { background:linear-gradient(180deg,#dabb5a,#b88838); }
.row-save-btn:disabled { opacity:.4; cursor:default; }

/* Desktop: kembali ke layout sejajar horizontal */
@media (min-width: 860px) {
  .item-row { flex-wrap:nowrap; }
  .item-head { flex:0 0 auto; width:280px; }
  .item-name-input { flex:0 0 160px; }
  .cat-selects { flex:1 1 auto; }
  .row-footer { flex:0 0 auto; width:auto; gap:10px; }
}

/* PAGINATION */
.pagination { padding:12px 14px; display:flex; gap:6px; align-items:center; }
.page-btn {
  font-family:'Cinzel',serif; font-size:11px; font-weight:700;
  padding:6px 12px; border-radius:3px; cursor:pointer;
  background:linear-gradient(180deg,#3d2e15,#2a1f0e);
  border:1px solid var(--panel-bd); color:var(--gold);
  transition:all .1s;
}
.page-btn:hover { border-color:var(--gold); }
.page-btn:disabled { opacity:.3; cursor:default; }
.page-info { font-family:'Cinzel',serif; font-size:11px; color:var(--text-dim); }

/* CATEGORY SEARCH (pengganti 3 dropdown cascading) */
.cat-suggest {
  position:absolute; top:100%; left:0; right:0; z-index:50;
  background:#1a1208; border:1px solid var(--gold-dk); border-radius:3px;
  max-height:220px; overflow-y:auto; display:none;
  box-shadow:0 6px 20px rgba(0,0,0,.8);
}
.cat-suggest.open { display:block; }
.cat-suggest-item {
  padding:8px 12px; font-family:'Crimson Text',serif; font-size:13px;
  color:var(--text-lt); cursor:pointer; border-bottom:1px solid rgba(107,79,26,.3);
}
.cat-suggest-item:hover { background:rgba(184,134,11,.25); }
.cat-suggest-item .path-dim { color:var(--text-dim); font-size:11px; }
.cat-selected {
  font-family:'Crimson Text',serif; font-size:12px; color:var(--gold);
  margin-top:4px; min-height:16px;
}

/* EMPTY */
.empty-msg { padding:32px; text-align:center; font-family:'Crimson Text',serif; font-size:14px; color:var(--text-dim); font-style:italic; }

/* TABS */
.tabs { display:flex; gap:6px; padding:10px 14px 0; }
.tab-btn {
  font-family:'Cinzel',serif; font-size:11px; font-weight:700;
  padding:8px 16px; border-radius:4px 4px 0 0; cursor:pointer;
  background:var(--slot-bg); border:1px solid var(--panel-bd); border-bottom:none;
  color:var(--text-dim);
}
.tab-btn.active { background:linear-gradient(180deg,#3d2e15,#2a1f0e); color:var(--gold); }

/* SAVED ITEM ROW */
.saved-row {
  display:flex; flex-wrap:wrap; align-items:center; gap:10px;
  padding:10px 14px; border-bottom:1px solid rgba(107,79,26,.25);
}
.saved-row:hover { background:rgba(61,46,21,.4); }
.saved-cat-current {
  font-family:'Crimson Text',serif; font-size:12px; color:var(--text-dim);
  flex:0 0 auto;
}
.saved-update-btn {
  font-family:'Cinzel',serif; font-size:10px; font-weight:700;
  padding:6px 12px; border-radius:3px; cursor:pointer;
  background:linear-gradient(180deg,#c8a84a,#a07828);
  border:1px solid #8b6820; color:#2a1800;
}
.saved-update-btn:disabled { opacity:.4; cursor:default; }

/* TOAST */
#toast {
  position:fixed; bottom:24px; right:24px; z-index:9999;
  font-family:'Cinzel',serif; font-size:12px; font-weight:700;
  padding:10px 18px; border-radius:4px;
  background:linear-gradient(180deg,#1a4a1a,#0e2e0e);
  border:1px solid #27ae60; color:#c0ffc0;
  box-shadow:0 4px 16px rgba(0,0,0,.8);
  display:none; animation:fadeIn .2s;
}
@keyframes fadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
</style>

<div>
  <!-- PANEL TOOL MAPPING -->
  <div class="panel">
    <div class="panel-header">
      <span>🗺️</span>
      <span class="panel-title">Item Mapping Tool</span>
      <span class="tb-stat" id="statText">Memuat...</span>
    </div>

    <div class="tabs">
      <div class="tab-btn active" id="tab-unmapped" onclick="switchTab('unmapped')">⏳ Belum di-DB</div>
      <div class="tab-btn" id="tab-saved" onclick="switchTab('saved')">✅ Sudah Tersimpan (koreksi)</div>
    </div>

    <div class="toolbar">
      <input type="text" class="tb-input search" id="searchInput" placeholder="Cari api_id... (misal: SWORD, METALBAR)" oninput="onSearch()">
      <button class="tb-btn red" type="button" onclick="resetSearch()" style="flex-shrink:0;">
        ↺ Reset
      </button>
    </div>
    <div class="toolbar" style="border-top:1px solid rgba(107,79,26,.3); padding-top:8px; flex-wrap:wrap; gap:8px;">
      <select class="cat-sel" id="catShortcut1" onchange="onCatShortcut1Change()">
        <option value="">— Level 1 —</option>
      </select>
      <select class="cat-sel" id="catShortcut2" onchange="onCatShortcut2Change()" disabled>
        <option value="">— Level 2 —</option>
      </select>
      <select class="cat-sel" id="catShortcut3" disabled>
        <option value="">— Level 3 —</option>
      </select>
      <button class="group-save-btn" onclick="searchByCatShortcut()" style="flex-shrink:0;">
        🔍 Cari Item yang Relevan
      </button>
    </div>

    <div id="itemList"><div class="empty-msg">Memuat data...</div></div>

    <div class="pagination" id="pagination" style="display:none">
      <button class="page-btn" id="btnPrev" onclick="prevPage()">◀ Prev</button>
      <span class="page-info" id="pageInfo"></span>
      <button class="page-btn" id="btnNext" onclick="nextPage()">Next ▶</button>
    </div>
  </div>
</div>

<div id="toast"></div>

<script>
// ============================================================
// STATE
// ============================================================
let CATEGORIES = [];
let currentPage = 1;
let totalPages  = 1;
let searchTimer = null;
let currentTab  = 'unmapped'; // 'unmapped' | 'saved'

// Kategori terakhir yang dipakai lewat shortcut "Cari Item yang Relevan".
// Dipakai buat auto-fill dropdown kategori di tiap group card hasil pencarian.
let lastSearchCat1 = null;
let lastSearchCat2 = null;
let lastSearchCat3 = null;

// ============================================================
// TAB SWITCHING
// ============================================================
function switchTab(tab) {
  currentTab = tab;
  currentPage = 1;
  document.getElementById('tab-unmapped').classList.toggle('active', tab === 'unmapped');
  document.getElementById('tab-saved').classList.toggle('active', tab === 'saved');
  document.getElementById('searchInput').value = '';
  lastSearchCat1 = lastSearchCat2 = lastSearchCat3 = null;
  if (tab === 'unmapped') {
    loadItems();
  } else {
    loadSavedItems();
  }
}

// ============================================================
// INIT
// ============================================================
async function init() {
  // Load categories
  const res  = await fetch('/api/market/categories', { credentials: 'same-origin' });
  CATEGORIES = await res.json();
  fillCatShortcutLevel1();
  await loadItems();
}

// ============================================================
// SHORTCUT: cascading dropdown kategori (Level 1 -> 2 -> 3) di bawah
// search box utama. Setelah pilih sampai level 3, klik tombol
// "Cari Item yang Relevan" buat ngisi kotak pencarian otomatis pakai
// nama kategori level 3 itu (karena kebanyakan namanya sama dengan nama item).
// ============================================================
function fillCatShortcutLevel1() {
  const sel1 = document.getElementById('catShortcut1');
  CATEGORIES.forEach(c => {
    sel1.innerHTML += `<option value="${c.id}">${c.name}</option>`;
  });
}

function onCatShortcut1Change() {
  const sel1 = document.getElementById('catShortcut1');
  const sel2 = document.getElementById('catShortcut2');
  const sel3 = document.getElementById('catShortcut3');
  const catId = parseInt(sel1.value);

  sel2.innerHTML = '<option value="">— Level 2 —</option>';
  sel3.innerHTML = '<option value="">— Level 3 —</option>';
  sel2.disabled = true; sel3.disabled = true;

  if (!catId) return;
  const cat = CATEGORIES.find(c => c.id === catId);
  if (!cat || !cat.children.length) return;

  cat.children.forEach(c => {
    sel2.innerHTML += `<option value="${c.id}">${c.name}</option>`;
  });
  sel2.disabled = false;
}

function onCatShortcut2Change() {
  const sel1 = document.getElementById('catShortcut1');
  const sel2 = document.getElementById('catShortcut2');
  const sel3 = document.getElementById('catShortcut3');
  const cat1Id = parseInt(sel1.value);
  const cat2Id = parseInt(sel2.value);

  sel3.innerHTML = '<option value="">— Level 3 —</option>';
  sel3.disabled = true;

  if (!cat2Id) return;
  const cat1 = CATEGORIES.find(c => c.id === cat1Id);
  if (!cat1) return;
  const cat2 = cat1.children.find(c => c.id === cat2Id);
  if (!cat2 || !cat2.children.length) return;

  cat2.children.forEach(c => {
    sel3.innerHTML += `<option value="${c.id}">${c.name}</option>`;
  });
  sel3.disabled = false;
}

// ============================================================
// RESET — bersihin search box + dropdown kategori shortcut
// sekaligus, biar gak perlu hapus manual satu-satu pas mau
// mulai pencarian baru dari nol.
// ============================================================
function resetSearch() {
  document.getElementById('searchInput').value = '';

  const sel1 = document.getElementById('catShortcut1');
  const sel2 = document.getElementById('catShortcut2');
  const sel3 = document.getElementById('catShortcut3');
  sel1.value = '';
  sel2.innerHTML = '<option value="">— Level 2 —</option>';
  sel3.innerHTML = '<option value="">— Level 3 —</option>';
  sel2.disabled = true; sel3.disabled = true;

  lastSearchCat1 = lastSearchCat2 = lastSearchCat3 = null;
  currentPage = 1;
  currentTab === 'unmapped' ? loadItems() : loadSavedItems();
  showToast('↺ Pencarian direset');
}

function searchByCatShortcut() {
  const sel1 = document.getElementById('catShortcut1');
  const sel2 = document.getElementById('catShortcut2');
  const sel3 = document.getElementById('catShortcut3');
  const selectedOption = sel3.value
    ? sel3.options[sel3.selectedIndex]
    : (sel2.value ? sel2.options[sel2.selectedIndex] : null);

  if (!selectedOption || !selectedOption.value) {
    showToast('⚠️ Pilih kategori dulu (minimal sampai Level 2 atau 3)', 'warn');
    return;
  }

  // Simpan kategori yang dipilih biar dipakai buat auto-fill kategori
  // di tiap group card hasil pencarian (lihat prefillGroupCategories()).
  lastSearchCat1 = parseInt(sel1.value) || null;
  lastSearchCat2 = parseInt(sel2.value) || null;
  lastSearchCat3 = parseInt(sel3.value) || null;

  document.getElementById('searchInput').value = selectedOption.textContent;
  currentPage = 1;
  currentTab === 'unmapped' ? loadItems() : loadSavedItems();
  showToast('🔍 Mencari item: "' + selectedOption.textContent + '"');
}

// ============================================================
// LOAD ITEMS dari item_recipes
// ============================================================
async function loadItems() {
  document.getElementById('itemList').innerHTML = '<div class="empty-msg">Memuat...</div>';
  document.getElementById('pagination').style.display = 'none';

  const search    = document.getElementById('searchInput').value.trim();
  const params    = new URLSearchParams({ page: currentPage });
  if (search) params.set('search', search);

  const res  = await fetch('/dev/recipe-items?' + params.toString(), { credentials: 'same-origin' });
  if (!res.ok) {
    document.getElementById('itemList').innerHTML =
      '<div class="empty-msg">⚠️ Gagal load data (status ' + res.status + '). Pastikan kamu sudah login.</div>';
    return;
  }
  const data = await res.json();

  totalPages = data.last_page;

  // Endpoint ini hanya mengembalikan item yang BELUM ada di tabel items,
  // jadi semua hasilnya pasti in_db = false
  let items = data.data.map(i => ({ ...i, in_db: false, pretty: i.name }));

  document.getElementById('statText').textContent =
    `Total belum di-DB: ${data.total} item | Halaman ${data.page}/${data.last_page}`;

  if (!items.length) {
    document.getElementById('itemList').innerHTML = '<div class="empty-msg">Tidak ada item 😔</div>';
    return;
  }

  // ── GROUPING: kelompokkan item berdasarkan base name ──
  // Buang prefix tier (T1_...T8_) dan suffix enchant (@1, @2, @3, @4)
  const groups = {};
  items.forEach(item => {
    const baseKey = getBaseKey(item.api_id);
    if (!groups[baseKey]) groups[baseKey] = [];
    groups[baseKey].push(item);
  });

  const list = document.createElement('div');
  Object.keys(groups).forEach(baseKey => {
    list.appendChild(makeGroupCard(baseKey, groups[baseKey]));
  });
  document.getElementById('itemList').replaceWith(list);
  list.id = 'itemList';

  // Pagination
  document.getElementById('pagination').style.display = 'flex';
  document.getElementById('pageInfo').textContent = `Hal ${currentPage} / ${totalPages}`;
  document.getElementById('btnPrev').disabled = currentPage <= 1;
  document.getElementById('btnNext').disabled = currentPage >= totalPages;
}

// ============================================================
// GROUPING HELPER: ambil base key dari api_id
// Contoh: T1_2H_TOOL_AXE -> TOOL_AXE, T4_METALBAR@2 -> METALBAR
// ============================================================
function getBaseKey(apiId) {
  let key = apiId.replace(/@\d$/, '');           // buang suffix enchant equipment @1..@4
  key = key.replace(/_LEVEL[1-4]$/, '');         // buang suffix enchant resource _LEVEL1..4
  key = key.replace(/^T[1-8]_/, '');             // buang prefix tier T1_...T8_
  key = key.replace(/^2H_/, '');                 // buang prefix 2H_ (kalau tier sudah dibuang duluan, ini jaga-jaga urutan kebalik)
  return key;
}

// ============================================================
// BUAT GROUP CARD (1 base name, berisi beberapa tier/enc)
// ============================================================
function makeGroupCard(baseKey, groupItems) {
  const groupId = 'group-' + baseKey.replace(/[^A-Za-z0-9]/g, '');
  const card = document.createElement('div');
  card.className = 'group-card';
  card.id = groupId;

  const prettyBase = groupItems[0].pretty; // sudah final dari backend (prettifyApiId), tidak perlu di-strip lagi

  const maxEnc = groupItems[0].max_enc || 0;

  const encActionHtml = maxEnc > 0 ? `
    <span style="font-family:'Crimson Text',serif;font-size:11px;color:var(--text-lt);font-style:italic;">
      ✨ Varian enchant (.1–.${maxEnc}) otomatis ikut digenerate
    </span>
  ` : `
    <span style="font-family:'Crimson Text',serif;font-size:11px;color:var(--text-dim);font-style:italic;">
      Item ini tidak punya varian enchant
    </span>
  `;

  card.innerHTML = `
    <div class="group-header">
      <div class="group-title-row">
        <span class="group-title">📦 ${prettyBase}</span>
        <span class="group-count">(${groupItems.length} varian)</span>
        <span class="group-toggle" onclick="toggleGroup('${groupId}')">Sembunyikan ▴</span>
        <button class="tb-btn gold" type="button" onclick="searchGroupVariants('${groupId}')" title="Cari semua tier/enchant item ini di seluruh database, bukan cuma di halaman ini">
          🔄 Cari Variasi
        </button>
      </div>
      <div class="group-cat-selects">
        <select class="cat-sel" id="gcat1-${groupId}" onchange="onGroupCat1Change('${groupId}')">
          <option value="">— Level 1 —</option>
          ${CATEGORIES.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
        </select>
        <select class="cat-sel" id="gcat2-${groupId}" onchange="onGroupCat2Change('${groupId}')" disabled>
          <option value="">— Level 2 —</option>
        </select>
        <select class="cat-sel" id="gcat3-${groupId}" disabled>
          <option value="">— Level 3 —</option>
        </select>
        <div class="cat-search-wrap" style="position:relative;">
          <button class="tb-btn gold" type="button" onclick="searchGroupSubcat('${groupId}')">🔍 Cari sub kategori</button>
          <div class="cat-suggest" id="gsuggest-${groupId}"></div>
        </div>
      </div>
      <div class="cat-selected" id="gselected-${groupId}"></div>
      <div class="group-actions">
        ${encActionHtml}
        <span class="status-badge unsaved" id="gbadge-${groupId}">⏳ Belum di-mapping</span>
        <button class="group-save-btn" id="gsavebtn-${groupId}" onclick="saveGroup('${groupId}')">
          💾 Simpan Semua (${groupItems.length})
        </button>
      </div>
    </div>
    <div class="group-items" id="${groupId}-items"></div>
  `;

  const itemsContainer = card.querySelector('.group-items');
  groupItems.forEach(item => {
    itemsContainer.appendChild(makeRow(item, groupId));
  });

  // Auto-fill kategori kalau hasil ini datang dari pencarian via shortcut kategori
  prefillGroupCategories(card, groupId);

  // simpan referensi item list + max_enc di elemen untuk dipakai saveGroup nanti
  card.dataset.apiIds = JSON.stringify(groupItems.map(i => i.api_id));
  card.dataset.maxEnc = maxEnc;
  card.dataset.prettyBase = prettyBase; // dipakai fitur "Cari sub kategori"
  card.dataset.baseKey = baseKey;       // dipakai fitur "Cari variasi"

  return card;
}

// ============================================================
// AUTO-FILL kategori group card pakai kategori terakhir yang
// dipilih lewat shortcut "Cari Item yang Relevan". Pakai
// card.querySelector (bukan document.getElementById) karena saat
// fungsi ini dipanggil, card belum tentu sudah nempel ke document.
// ============================================================
function prefillGroupCategories(card, groupId) {
  if (!lastSearchCat1) return;

  const sel1 = card.querySelector('#gcat1-' + groupId);
  const sel2 = card.querySelector('#gcat2-' + groupId);
  const sel3 = card.querySelector('#gcat3-' + groupId);
  if (!sel1) return;

  const cat1 = CATEGORIES.find(c => c.id === lastSearchCat1);
  if (!cat1) return;
  sel1.value = lastSearchCat1;

  if (cat1.children.length) {
    cat1.children.forEach(c => {
      sel2.innerHTML += `<option value="${c.id}">${c.name}</option>`;
    });
    sel2.disabled = false;
  }

  if (lastSearchCat2) {
    const cat2 = cat1.children.find(c => c.id === lastSearchCat2);
    if (cat2) {
      sel2.value = lastSearchCat2;
      if (cat2.children.length) {
        cat2.children.forEach(c => {
          sel3.innerHTML += `<option value="${c.id}">${c.name}</option>`;
        });
        sel3.disabled = false;
      }
      if (lastSearchCat3) {
        sel3.value = lastSearchCat3;
      }
    }
  }
}

// ============================================================
// CARI SUB KATEGORI — cocokkan nama item dengan pohon kategori,
// tampilkan hasil sebagai chip yang bisa diklik buat auto-isi
// dropdown Level 1/2/3 grup ini. Pengganti klak-klik manual satu-satu.
// ============================================================
let CATEGORY_PATH_INDEX = null;

function buildCategoryPathIndex() {
  const paths = [];
  CATEGORIES.forEach(l1 => {
    if (l1.children && l1.children.length) {
      l1.children.forEach(l2 => {
        if (l2.children && l2.children.length) {
          l2.children.forEach(l3 => {
            paths.push({ id1: l1.id, id2: l2.id, id3: l3.id, matchName: l3.name, text: `${l1.name} > ${l2.name} > ${l3.name}` });
          });
        } else {
          paths.push({ id1: l1.id, id2: l2.id, id3: null, matchName: l2.name, text: `${l1.name} > ${l2.name}` });
        }
      });
    } else {
      paths.push({ id1: l1.id, id2: null, id3: null, matchName: l1.name, text: l1.name });
    }
  });
  CATEGORY_PATH_INDEX = paths;
}

function normCatText(s) {
  return (s || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
}

function scoreCategoryMatch(itemNorm, catNorm) {
  if (!catNorm) return 0;
  const catWords = catNorm.split(' ').filter(Boolean);
  if (!catWords.length) return 0;
  const itemWords = itemNorm.split(' ').filter(Boolean);
  let score = 0;
  catWords.forEach(cw => {
    if (itemWords.includes(cw)) score += 3;
    else if (itemNorm.includes(cw)) score += 1;
  });
  if (itemNorm.includes(catNorm)) score += 5;
  return score / catWords.length;
}

function findCategoryMatches(itemName, limit = 6) {
  if (!CATEGORY_PATH_INDEX) buildCategoryPathIndex();
  const itemNorm = normCatText(itemName);
  if (!itemNorm) return [];
  return CATEGORY_PATH_INDEX
    .map(p => ({ ...p, score: scoreCategoryMatch(itemNorm, normCatText(p.matchName)) }))
    .filter(p => p.score > 0)
    .sort((a, b) => b.score - a.score)
    .slice(0, limit);
}

function searchGroupSubcat(groupId) {
  const card = document.getElementById(groupId);
  const name = (card.dataset.prettyBase || '').trim();
  const box  = document.getElementById('gsuggest-' + groupId);

  if (!name) {
    showToast('⚠️ Nama item kosong, gak bisa dicari kategorinya!', 'warn');
    box.classList.remove('open');
    return;
  }

  const matches = findCategoryMatches(name);
  if (!matches.length) {
    box.innerHTML = `<div class="cat-suggest-item">Gak ada saran kategori yang cocok 😕</div>`;
    box.dataset.matches = '[]';
    box.classList.add('open');
    return;
  }

  box.innerHTML = matches.map((m, idx) => `
    <div class="cat-suggest-item" onclick="selectGroupCategory('${groupId}', ${idx})">
      ${m.text}
    </div>
  `).join('');
  box.dataset.matches = JSON.stringify(matches);
  box.classList.add('open');
}

function selectGroupCategory(groupId, idx) {
  const box = document.getElementById('gsuggest-' + groupId);
  const matches = JSON.parse(box.dataset.matches || '[]');
  const m = matches[idx];
  if (!m) return;

  const sel1 = document.getElementById('gcat1-' + groupId);
  const sel2 = document.getElementById('gcat2-' + groupId);
  const sel3 = document.getElementById('gcat3-' + groupId);

  sel1.value = m.id1;
  onGroupCat1Change(groupId); // isi ulang opsi level 2 sesuai level 1 terpilih

  if (m.id2) {
    sel2.value = m.id2;
    onGroupCat2Change(groupId); // isi ulang opsi level 3 sesuai level 2 terpilih
  }
  if (m.id3) {
    sel3.value = m.id3;
  }

  document.getElementById('gselected-' + groupId).textContent = '✓ ' + m.text;
  box.classList.remove('open');
}

// Tutup dropdown saran kalau klik di luar area tombol pencarian
document.addEventListener('click', (e) => {
  if (!e.target.closest('.cat-search-wrap')) {
    document.querySelectorAll('.cat-suggest.open').forEach(el => el.classList.remove('open'));
  }
});

function toggleGroup(groupId) {
  const el = document.getElementById(groupId + '-items');
  const toggle = document.querySelector(`#${groupId} .group-toggle`);
  el.classList.toggle('collapsed');
  toggle.textContent = el.classList.contains('collapsed') ? 'Lihat detail ▾' : 'Sembunyikan ▴';
}

// ============================================================
// CARI VARIASI — sekali klik, search ulang pakai base key item
// ini (tanpa prefix tier T1-T8 & suffix enchant) ke SELURUH
// database via endpoint yang sama. Berguna karena tier/enchant
// dari item yang sama biasanya kesebar di banyak halaman kalau
// browsing tanpa search, jadi grouping per-halaman jadi gak akurat
// (cuma nemu varian yang kebetulan satu halaman).
// ============================================================
function searchGroupVariants(groupId) {
  const card = document.getElementById(groupId);
  const baseKey = card.dataset.baseKey;
  if (!baseKey) {
    showToast('⚠️ Gak ketemu base key item ini!', 'warn');
    return;
  }

  document.getElementById('searchInput').value = baseKey;
  currentPage = 1;
  currentTab === 'unmapped' ? loadItems() : loadSavedItems();
  showToast('🔄 Mencari semua varian: "' + baseKey + '"');
}

// ============================================================
// CASCADING CATEGORY untuk GROUP (level header, berlaku ke semua item dalam grup)
// ============================================================
function onGroupCat1Change(groupId) {
  const sel1 = document.getElementById('gcat1-' + groupId);
  const sel2 = document.getElementById('gcat2-' + groupId);
  const sel3 = document.getElementById('gcat3-' + groupId);
  const catId = parseInt(sel1.value);

  sel2.innerHTML = '<option value="">— Level 2 —</option>';
  sel3.innerHTML = '<option value="">— Level 3 —</option>';
  sel2.disabled = true; sel3.disabled = true;

  if (!catId) return;
  const cat = CATEGORIES.find(c => c.id === catId);
  if (!cat || !cat.children.length) return;

  cat.children.forEach(c => {
    sel2.innerHTML += `<option value="${c.id}">${c.name}</option>`;
  });
  sel2.disabled = false;
}

function onGroupCat2Change(groupId) {
  const sel1 = document.getElementById('gcat1-' + groupId);
  const sel2 = document.getElementById('gcat2-' + groupId);
  const sel3 = document.getElementById('gcat3-' + groupId);
  const cat1Id = parseInt(sel1.value);
  const cat2Id = parseInt(sel2.value);

  sel3.innerHTML = '<option value="">— Level 3 —</option>';
  sel3.disabled = true;

  if (!cat2Id) return;
  const cat1 = CATEGORIES.find(c => c.id === cat1Id);
  if (!cat1) return;
  const cat2 = cat1.children.find(c => c.id === cat2Id);
  if (!cat2 || !cat2.children.length) return;

  cat2.children.forEach(c => {
    sel3.innerHTML += `<option value="${c.id}">${c.name}</option>`;
  });
  sel3.disabled = false;
}

// ============================================================
// SAVE SELURUH ITEM DALAM SATU GROUP (sekali klik)
// ============================================================
async function saveGroup(groupId) {
  const sel1 = document.getElementById('gcat1-' + groupId);
  const sel2 = document.getElementById('gcat2-' + groupId);
  const sel3 = document.getElementById('gcat3-' + groupId);
  const catId = parseInt(sel3.value) || parseInt(sel2.value) || parseInt(sel1.value);

  if (!catId) { showToast('⚠️ Pilih kategori dulu untuk grup ini!', 'warn'); return; }

  const card = document.getElementById(groupId);
  const apiIds = JSON.parse(card.dataset.apiIds);
  const maxEnc = parseInt(card.dataset.maxEnc || '0'); // disimpan langsung dari group data, bukan dari checkbox lagi

  const badge = document.getElementById('gbadge-' + groupId);
  const btn   = document.getElementById('gsavebtn-' + groupId);
  badge.className = 'status-badge saving';
  badge.textContent = '⏳ Menyimpan semua...';
  btn.disabled = true;

  let payload = [];
  apiIds.forEach(apiId => {
    const nameInput = document.getElementById('name-' + apiId);
    const name = nameInput ? nameInput.value.trim() : apiId;
    const tierMatch = apiId.match(/^T(\d)_/);
    const tier = tierMatch ? parseInt(tierMatch[1]) : null;

    // baris dasar (enc 0)
    payload.push({ api_id: apiId, name, tier, enc: 0, category_id: catId });

    // otomatis generate enc 1..maxEnc kalau item ini memang punya varian enchant
    if (maxEnc > 0) {
      for (let e = 1; e <= maxEnc; e++) {
        payload.push({ api_id: apiId, name, tier, enc: e, category_id: catId });
      }
    }
  });

  const res = await fetch('/dev/save-items', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
    body: JSON.stringify({ items: payload }),
  });
  const data = await res.json();

  if (res.ok) {
    badge.className = 'status-badge saved';
    badge.textContent = `✅ ${data.saved} item disimpan`;
    card.classList.add('saved');
    showToast(`✅ Grup "${groupId}" — ${data.saved} item berhasil disimpan!`);
    setTimeout(() => loadItems(), 800);
  } else {
    badge.className = 'status-badge unsaved';
    badge.textContent = '❌ Gagal simpan';
    btn.disabled = false;
    showToast('❌ Gagal simpan grup', 'warn');
  }
}

// ============================================================
// BUAT ROW per item (di dalam group card)
// ============================================================
function makeRow(item, groupId) {
  const row = document.createElement('div');
  row.className = 'item-row' + (item.in_db ? ' saved' : '');
  row.id = 'row-' + item.api_id;

  row.innerHTML = `
    <div class="item-head">
      <img class="item-img" src="${item.img_url}"
           onerror="this.classList.add('broken')"
           title="${item.api_id}">
      <div class="item-api">${item.api_id}</div>
    </div>
    <input class="item-name-input" id="name-${item.api_id}"
           value="${item.pretty}" placeholder="Nama item">
  `;

  return row;
}

// ============================================================
// SEARCH & PAGINATION (tab-aware)
// ============================================================
function onSearch() {
  // Pencarian manual (ketik teks) gak ada kategori spesifik yang relevan,
  // jadi reset auto-fill kategori biar gak ke-bawa dari pencarian shortcut sebelumnya.
  lastSearchCat1 = lastSearchCat2 = lastSearchCat3 = null;
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    currentPage = 1;
    currentTab === 'unmapped' ? loadItems() : loadSavedItems();
  }, 400);
}
function prevPage() {
  if (currentPage > 1) {
    currentPage--;
    currentTab === 'unmapped' ? loadItems() : loadSavedItems();
  }
}
function nextPage() {
  if (currentPage < totalPages) {
    currentPage++;
    currentTab === 'unmapped' ? loadItems() : loadSavedItems();
  }
}

// ============================================================
// SAVED ITEMS TAB — koreksi kategori item yang sudah tersimpan
// ============================================================
async function loadSavedItems() {
  document.getElementById('itemList').innerHTML = '<div class="empty-msg">Memuat...</div>';
  document.getElementById('pagination').style.display = 'none';

  const search = document.getElementById('searchInput').value.trim();
  const params = new URLSearchParams({ page: currentPage });
  if (search) params.set('search', search);

  const res = await fetch('/dev/saved-items?' + params.toString(), { credentials: 'same-origin' });
  if (!res.ok) {
    document.getElementById('itemList').innerHTML =
      '<div class="empty-msg">⚠️ Gagal load data (status ' + res.status + ').</div>';
    return;
  }
  const data = await res.json();
  totalPages = data.last_page;

  document.getElementById('statText').textContent =
    `Total tersimpan: ${data.total} item | Halaman ${data.page}/${data.last_page}`;

  if (!data.data.length) {
    document.getElementById('itemList').innerHTML = '<div class="empty-msg">Belum ada item tersimpan 😔</div>';
    return;
  }

  const list = document.createElement('div');
  data.data.forEach(item => list.appendChild(makeSavedRow(item)));
  document.getElementById('itemList').replaceWith(list);
  list.id = 'itemList';

  document.getElementById('pagination').style.display = 'flex';
  document.getElementById('pageInfo').textContent = `Hal ${currentPage} / ${totalPages}`;
  document.getElementById('btnPrev').disabled = currentPage <= 1;
  document.getElementById('btnNext').disabled = currentPage >= totalPages;
}

function makeSavedRow(item) {
  const row = document.createElement('div');
  row.className = 'saved-row';
  row.id = 'saved-' + item.id;

  const encLabel = item.enc > 0 ? ` (Enchant ${item.enc})` : '';

  row.innerHTML = `
    <img class="item-img" src="${item.img_url}" onerror="this.classList.add('broken')" title="${item.api_id}">
    <div class="item-api" style="flex:1 1 200px;">
      ${item.api_id}${encLabel}<br>
      <span style="color:var(--text-lt);">${item.name}</span>
    </div>
    <div class="saved-cat-current">Sekarang: <strong style="color:var(--gold);">${item.category}</strong></div>
    <select class="cat-sel" id="scat1-${item.id}" onchange="onSavedCat1Change(${item.id})" style="flex:0 0 120px;">
      <option value="">— Level 1 —</option>
      ${CATEGORIES.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
    </select>
    <select class="cat-sel" id="scat2-${item.id}" onchange="onSavedCat2Change(${item.id})" disabled style="flex:0 0 120px;">
      <option value="">— Level 2 —</option>
    </select>
    <select class="cat-sel" id="scat3-${item.id}" disabled style="flex:0 0 120px;">
      <option value="">— Level 3 —</option>
    </select>
    <button class="saved-update-btn" onclick="updateSavedCategory(${item.id})">Update</button>
  `;

  // Auto-fill kategori kalau hasil ini datang dari pencarian via shortcut kategori
  prefillSavedRowCategory(row, item.id);

  return row;
}

// ============================================================
// AUTO-FILL kategori saved-row, sama logikanya kayak
// prefillGroupCategories() tapi buat pola id scat1/scat2/scat3.
// ============================================================
function prefillSavedRowCategory(row, itemId) {
  if (!lastSearchCat1) return;

  const sel1 = row.querySelector('#scat1-' + itemId);
  const sel2 = row.querySelector('#scat2-' + itemId);
  const sel3 = row.querySelector('#scat3-' + itemId);
  if (!sel1) return;

  const cat1 = CATEGORIES.find(c => c.id === lastSearchCat1);
  if (!cat1) return;
  sel1.value = lastSearchCat1;

  if (cat1.children.length) {
    cat1.children.forEach(c => {
      sel2.innerHTML += `<option value="${c.id}">${c.name}</option>`;
    });
    sel2.disabled = false;
  }

  if (lastSearchCat2) {
    const cat2 = cat1.children.find(c => c.id === lastSearchCat2);
    if (cat2) {
      sel2.value = lastSearchCat2;
      if (cat2.children.length) {
        cat2.children.forEach(c => {
          sel3.innerHTML += `<option value="${c.id}">${c.name}</option>`;
        });
        sel3.disabled = false;
      }
      if (lastSearchCat3) {
        sel3.value = lastSearchCat3;
      }
    }
  }
}

function onSavedCat1Change(id) {
  const sel1 = document.getElementById('scat1-' + id);
  const sel2 = document.getElementById('scat2-' + id);
  const sel3 = document.getElementById('scat3-' + id);
  const catId = parseInt(sel1.value);

  sel2.innerHTML = '<option value="">— Level 2 —</option>';
  sel3.innerHTML = '<option value="">— Level 3 —</option>';
  sel2.disabled = true; sel3.disabled = true;

  if (!catId) return;
  const cat = CATEGORIES.find(c => c.id === catId);
  if (!cat || !cat.children.length) return;
  cat.children.forEach(c => sel2.innerHTML += `<option value="${c.id}">${c.name}</option>`);
  sel2.disabled = false;
}

function onSavedCat2Change(id) {
  const sel1 = document.getElementById('scat1-' + id);
  const sel2 = document.getElementById('scat2-' + id);
  const sel3 = document.getElementById('scat3-' + id);
  const cat1Id = parseInt(sel1.value);
  const cat2Id = parseInt(sel2.value);

  sel3.innerHTML = '<option value="">— Level 3 —</option>';
  sel3.disabled = true;

  if (!cat2Id) return;
  const cat1 = CATEGORIES.find(c => c.id === cat1Id);
  if (!cat1) return;
  const cat2 = cat1.children.find(c => c.id === cat2Id);
  if (!cat2 || !cat2.children.length) return;
  cat2.children.forEach(c => sel3.innerHTML += `<option value="${c.id}">${c.name}</option>`);
  sel3.disabled = false;
}

async function updateSavedCategory(id) {
  const sel1 = document.getElementById('scat1-' + id);
  const sel2 = document.getElementById('scat2-' + id);
  const sel3 = document.getElementById('scat3-' + id);
  const catId = parseInt(sel3.value) || parseInt(sel2.value) || parseInt(sel1.value);

  if (!catId) { showToast('⚠️ Pilih kategori baru dulu!', 'warn'); return; }

  const res = await fetch(`/dev/items/${id}/category`, {
    method: 'PATCH',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
    body: JSON.stringify({ category_id: catId }),
  });

  if (res.ok) {
    showToast('✅ Kategori berhasil diupdate!');
    setTimeout(() => loadSavedItems(), 500);
  } else {
    showToast('❌ Gagal update kategori', 'warn');
  }
}

// ============================================================
// HELPERS
// ============================================================
function getCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function showToast(msg, type) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.display = 'block';
  if (type === 'warn') {
    t.style.background = 'linear-gradient(180deg,#4a3a00,#2a2000)';
    t.style.borderColor = '#b8860b';
    t.style.color = '#ffe090';
  } else {
    t.style.background = 'linear-gradient(180deg,#1a4a1a,#0e2e0e)';
    t.style.borderColor = '#27ae60';
    t.style.color = '#c0ffc0';
  }
  clearTimeout(window._toastTimer);
  window._toastTimer = setTimeout(() => { t.style.display = 'none'; }, 2500);
}

init();
</script>
@endsection