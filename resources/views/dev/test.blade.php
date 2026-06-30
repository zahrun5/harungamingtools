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
.group-items { display:none; }
.group-items.expanded { display:block; }

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

// ============================================================
// TAB SWITCHING
// ============================================================
function switchTab(tab) {
  currentTab = tab;
  currentPage = 1;
  document.getElementById('tab-unmapped').classList.toggle('active', tab === 'unmapped');
  document.getElementById('tab-saved').classList.toggle('active', tab === 'saved');
  document.getElementById('searchInput').value = '';
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
  await loadItems();
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
    <label style="display:flex;align-items:center;gap:6px;font-family:'Crimson Text',serif;font-size:12px;color:var(--text-lt);cursor:pointer;">
      <input type="checkbox" id="genc-${groupId}" data-max-enc="${maxEnc}" style="accent-color:var(--gold-dk);">
      Generate juga varian enchant (sampai .${maxEnc})
    </label>
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
        <span class="group-toggle" onclick="toggleGroup('${groupId}')">Lihat detail ▾</span>
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
      </div>
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

  // simpan referensi item list di elemen untuk dipakai saveGroup nanti
  card.dataset.apiIds = JSON.stringify(groupItems.map(i => i.api_id));

  return card;
}

function toggleGroup(groupId) {
  const el = document.getElementById(groupId + '-items');
  const toggle = document.querySelector(`#${groupId} .group-toggle`);
  el.classList.toggle('expanded');
  toggle.textContent = el.classList.contains('expanded') ? 'Sembunyikan ▴' : 'Lihat detail ▾';
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

  const badge = document.getElementById('gbadge-' + groupId);
  const btn   = document.getElementById('gsavebtn-' + groupId);
  badge.className = 'status-badge saving';
  badge.textContent = '⏳ Menyimpan semua...';
  btn.disabled = true;

  const encCheckbox = document.getElementById('genc-' + groupId);
  const genEnchant  = encCheckbox?.checked || false;
  const maxEnc      = parseInt(encCheckbox?.dataset.maxEnc || '0'); // dinamis dari max_enc, bukan hardcode

  let payload = [];
  apiIds.forEach(apiId => {
    const nameInput = document.getElementById('name-' + apiId);
    const name = nameInput ? nameInput.value.trim() : apiId;
    const tierMatch = apiId.match(/^T(\d)_/);
    const tier = tierMatch ? parseInt(tierMatch[1]) : null;

    // baris dasar (enc 0)
    payload.push({ api_id: apiId, name, tier, enc: 0, category_id: catId });

    // kalau dicentang, generate enc 1..maxEnc (sesuai data asli item ini, bisa 3 atau 4)
    if (genEnchant && maxEnc > 0) {
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
  return row;
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