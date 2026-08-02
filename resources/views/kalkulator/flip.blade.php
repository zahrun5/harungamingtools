@extends('layouts.app')

@section('title', 'Kalkulator Flip - Albion Online Tools')

@section('content')
<style>
  .flip-wrap{
    --card:#221C15; --slot:#1A1510; --border:#332B21;
    --green:#5FB3A8; --red:#C97B5F;
  }
  .flip-header{padding:26px 20px 18px;text-align:center;border-bottom:1px solid var(--border);background:linear-gradient(180deg,#0c0a08,var(--bg-panel,#1C1712));margin:-48px -24px 24px;}
  .flip-header h1{font-family:'Fraunces',serif;font-size:clamp(1.15rem,4vw,1.55rem);color:var(--gold);letter-spacing:.04em;}
  .flip-header p{margin-top:6px;font-size:.82rem;color:var(--text-muted);}

  /* ====== MODE TOGGLE (Simple / Advance) — sama pola dengan refine ====== */
  .mode-toggle{display:flex;gap:8px;padding:0 0 18px;}
  .mode-btn{flex:1;background:var(--card);border:1px solid var(--border);border-radius:8px;color:var(--text-muted);font-family:'Sora',sans-serif;font-size:.85rem;font-weight:600;padding:10px;cursor:pointer;transition:all .15s;}
  .mode-btn.active{background:var(--gold);color:#1a1510;border-color:var(--gold);}

  .panel{background:var(--card);border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:18px;}
  .panel-header{background:#1A1510;border-bottom:1px solid var(--border);padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;}
  .panel-title{font-family:'Fraunces',serif;font-size:.92rem;color:var(--gold);letter-spacing:.03em;display:flex;align-items:center;gap:8px;}
  .add-row-btn{background:var(--card);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:.78rem;font-weight:600;padding:7px 14px;cursor:pointer;white-space:nowrap;transition:border-color .2s,color .2s;}
  .add-row-btn:hover{border-color:var(--gold);color:var(--gold);}

  .table-wrap{overflow-x:auto;}
  table{width:100%;border-collapse:collapse;}
  thead th{font-family:'JetBrains Mono',monospace;font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);text-align:left;padding:10px;border-bottom:1px solid var(--border);background:rgba(0,0,0,0.2);}
  tbody td{padding:8px 10px;border-bottom:1px solid rgba(51,43,33,0.4);}
  tbody tr:hover{background:rgba(34,28,21,0.5);}

  .cell-input{width:100%;background:var(--slot);border:1px solid var(--border);border-radius:6px;color:var(--text);font-family:'Sora',sans-serif;font-size:.85rem;padding:7px 9px;outline:none;}
  .cell-input:focus{border-color:var(--gold);}
  .cell-input::placeholder{color:#5e5142;}

  .cell-result{font-family:'JetBrains Mono',monospace;font-size:.85rem;font-weight:600;color:var(--gold);}
  .cell-result.empty{color:var(--text-muted);font-weight:400;font-style:italic;font-family:'Sora',sans-serif;}

  .premi-check{display:flex;align-items:center;justify-content:center;}
  .premi-check input{width:17px;height:17px;accent-color:var(--gold);cursor:pointer;}

  .del-btn{background:#2a1815;border:1px solid #3e2020;border-radius:6px;color:var(--red);width:28px;height:28px;font-size:.85rem;cursor:pointer;line-height:1;}
  .del-btn:hover{border-color:var(--red);color:#fff;}

  .foot-note{padding:10px 16px;font-size:.78rem;color:var(--text-muted);border-top:1px solid var(--border);background:rgba(0,0,0,0.2);}
  .foot-note b{color:var(--text);}

  /* ====== MODE ADVANCE ====== */
  .sub-toggle{display:flex;gap:6px;padding:12px 16px 0;}
  .sub-btn{flex:1;background:var(--slot);border:1px solid var(--border);border-radius:6px;color:var(--text-muted);font-size:.78rem;font-weight:600;padding:8px;cursor:pointer;transition:all .15s;}
  .sub-btn.active{background:rgba(95,179,168,0.15);border-color:var(--green);color:var(--green);}

  .adv-controls{padding:14px 16px;display:flex;flex-direction:column;gap:12px;border-bottom:1px solid var(--border);}
  .ctrl-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:6px;display:block;}

  .city-grid{display:flex;flex-wrap:wrap;gap:6px;}
  .city-chip{background:var(--slot);border:1px solid var(--border);border-radius:20px;padding:6px 12px;font-size:.78rem;color:var(--text-muted);cursor:pointer;user-select:none;transition:all .15s;}
  .city-chip.sel{background:var(--gold);border-color:var(--gold);color:#1a1510;font-weight:600;}

  .check-row{display:flex;flex-wrap:wrap;gap:14px;}
  .check-item{display:flex;align-items:center;gap:6px;font-size:.8rem;color:var(--text);cursor:pointer;}
  .check-item input{width:16px;height:16px;accent-color:var(--gold);cursor:pointer;}
  .check-item.disabled{opacity:.4;pointer-events:none;}

  .filter-toggle{display:flex;gap:6px;}
  .filter-btn{flex:1;background:var(--slot);border:1px solid var(--border);border-radius:6px;color:var(--text-muted);font-size:.74rem;padding:7px;cursor:pointer;text-align:center;transition:all .15s;}
  .filter-btn.active{background:var(--gold);border-color:var(--gold);color:#1a1510;font-weight:600;}

  .quality-select{background:var(--slot);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:.8rem;padding:7px 10px;}

  .opp-list{max-height:560px;overflow-y:auto;}
  .opp-row{display:flex;align-items:center;gap:10px;padding:10px 14px;border-bottom:1px solid rgba(51,43,33,0.4);cursor:pointer;transition:background .1s;}
  .opp-row:hover{background:rgba(34,28,21,0.5);}
  .opp-icon{width:42px;height:42px;border:1px solid var(--border);border-radius:6px;background:var(--slot);object-fit:contain;flex-shrink:0;}
  .opp-info{flex:1;min-width:0;}
  .opp-name{font-size:.86rem;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
  .opp-tier{display:inline-block;font-size:.68rem;color:var(--gold);border:1px solid var(--border);border-radius:4px;padding:1px 5px;margin-left:6px;}
  .opp-route{font-size:.75rem;color:var(--text-muted);margin-top:3px;display:flex;align-items:center;gap:5px;flex-wrap:wrap;}
  .opp-route b{color:var(--text);font-weight:600;}
  .opp-badge-risk{font-size:.65rem;background:rgba(201,123,95,0.2);color:var(--red);border-radius:4px;padding:1px 5px;}
  .opp-right{text-align:right;flex-shrink:0;}
  .opp-profit{font-family:'JetBrains Mono',monospace;font-size:.9rem;font-weight:700;color:var(--green);}
  .opp-meta{font-size:.68rem;color:var(--text-muted);margin-top:2px;}

  .empty-state{padding:40px 16px;text-align:center;color:var(--text-muted);font-size:.85rem;font-style:italic;}

  .bm-warning{margin:12px 16px 0;padding:10px 12px;background:rgba(201,123,95,0.12);border:1px solid rgba(201,123,95,0.4);border-radius:8px;color:var(--red);font-size:.76rem;line-height:1.4;}

  /* ====== POPUP PAIRING ====== */
  .pair-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.8);z-index:200;display:none;align-items:center;justify-content:center;padding:16px;}
  .pair-overlay.show{display:flex;}
  .pair-box{background:var(--card);border:1px solid var(--border);border-radius:10px;width:100%;max-width:420px;max-height:85vh;overflow-y:auto;}
  .pair-head{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;}
  .pair-head img{width:44px;height:44px;border-radius:6px;background:var(--slot);}
  .pair-head-name{font-size:.95rem;font-weight:700;color:var(--gold);}
  .pair-close{margin-left:auto;background:none;border:none;color:var(--text-muted);font-size:1.1rem;cursor:pointer;}
  .pair-item{padding:10px 16px;border-bottom:1px solid rgba(51,43,33,0.4);display:flex;justify-content:space-between;align-items:center;gap:8px;}
  .pair-route{font-size:.8rem;color:var(--text);}
  .pair-profit{font-family:'JetBrains Mono',monospace;font-size:.85rem;font-weight:700;color:var(--green);}
</style>

<div class="flip-wrap">
  <div class="flip-header">
    <h1>📊 Kalkulator Flip</h1>
    <p>Albion Online &middot; Hitung batas harga jual/beli, atau cari opportunity flip otomatis</p>
  </div>

  <div class="mode-toggle">
    <button id="btnModeSimple" class="mode-btn active" onclick="setFlipMode('simple')">📝 Mode Simple</button>
    <button id="btnModeAdvance" class="mode-btn" onclick="setFlipMode('advance')">⚙️ Mode Advance</button>
  </div>

  {{-- ============================================================ --}}
  {{-- MODE SIMPLE — kalkulator pajak manual (sudah ada sebelumnya)  --}}
  {{-- ============================================================ --}}
  <div id="modeSimpleWrap">
    <div class="panel">
      <div class="panel-header">
        <span class="panel-title">📈 Cari Harga Jual Minimal</span>
        <button class="add-row-btn" onclick="addRow('jual')">+ Tambah Baris</button>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:32px">#</th>
              <th>Modal (Harga Beli)</th>
              <th style="width:80px;text-align:center">Premium</th>
              <th>Jual Minimal Supaya Tidak Rugi</th>
              <th style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="tableJual"></tbody>
        </table>
      </div>
      <div class="foot-note">Pajak market: <b>8%</b> tanpa premium, <b>4%</b> dengan premium. Isi kolom Modal, hasil otomatis muncul.</div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <span class="panel-title">📉 Cari Harga Beli Maksimal</span>
        <button class="add-row-btn" onclick="addRow('beli')">+ Tambah Baris</button>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:32px">#</th>
              <th>Target Harga Jual</th>
              <th style="width:80px;text-align:center">Premium</th>
              <th>Beli Maksimal Supaya Tidak Rugi</th>
              <th style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="tableBeli"></tbody>
        </table>
      </div>
      <div class="foot-note">Kebalikan dari tabel di atas &mdash; isi target harga jual, dapat batas harga beli supaya tetap untung.</div>
    </div>
  </div>

  {{-- ============================================================ --}}
  {{-- MODE ADVANCE — opportunity board otomatis (kota & Black Market) --}}
  {{-- ============================================================ --}}
  <div id="modeAdvanceWrap" style="display:none">
    <div class="panel">
      <div class="sub-toggle">
        <button id="btnSubKota" class="sub-btn active" onclick="setFlipSubMode('kota')">🏙️ Flip Kota</button>
        <button id="btnSubBm" class="sub-btn" onclick="setFlipSubMode('blackmarket')">🏴 Black Market</button>
      </div>

      {{-- ---- KONTROL: FLIP KOTA ---- --}}
      <div class="adv-controls" id="ctrlKota">
        <div>
          <span class="ctrl-label">Pilih Kota (minimal 2)</span>
          <div class="city-grid" id="cityGrid"></div>
        </div>
        <div class="check-row">
          <label class="check-item">
            <input type="checkbox" id="cbPremiumAdv" onchange="saveFlipAdvPrefs();fetchOpportunities()">
            👑 Premium (pajak 8%→4%)
          </label>
          <label class="check-item">
            <input type="checkbox" id="cbSellOrder" onchange="saveFlipAdvPrefs();fetchOpportunities()">
            📋 Sell Order (handling 2.5%)
          </label>
        </div>
        <div>
          <span class="ctrl-label">Quality</span>
          <select class="quality-select" id="qualityKota" onchange="saveFlipAdvPrefs();fetchOpportunities()">
            <option value="1">Normal</option>
            <option value="2">Good</option>
            <option value="3">Outstanding</option>
            <option value="4">Excellent</option>
            <option value="5">Masterpiece</option>
          </select>
        </div>
        <div class="filter-toggle" id="filterKota">
          <div class="filter-btn" data-val="all" onclick="setFilterKota('all')">Semua Item</div>
          <div class="filter-btn" data-val="has_data" onclick="setFilterKota('has_data')">Ada Data</div>
          <div class="filter-btn active" data-val="profitable" onclick="setFilterKota('profitable')">Profit</div>
        </div>
      </div>

      <div class="opp-list" id="oppListKota"></div>
    </div>

      {{-- ---- KONTROL: BLACK MARKET ---- --}}
    <div class="panel" id="panelBm" style="display:none">
      <div class="bm-warning">
        ⚠️ Data harga Black Market sering telat update — selalu cek ulang harga langsung in-game sebelum bawa barang lewat black zone.
      </div>
      <div class="adv-controls">
        <div>
          <span class="ctrl-label">Quality</span>
          <select class="quality-select" id="qualityBm" onchange="saveFlipAdvPrefs();fetchBlackmarket()">
            <option value="1">Normal</option>
            <option value="2">Good</option>
            <option value="3">Outstanding</option>
            <option value="4">Excellent</option>
            <option value="5">Masterpiece</option>
          </select>
        </div>
        <div class="filter-toggle" id="filterBm">
          <div class="filter-btn" data-val="all" onclick="setFilterBm('all')">Semua Item</div>
          <div class="filter-btn" data-val="has_data" onclick="setFilterBm('has_data')">Ada Data</div>
          <div class="filter-btn active" data-val="profitable" onclick="setFilterBm('profitable')">Profit</div>
        </div>
      </div>
      <div class="opp-list" id="oppListBm"></div>
    </div>
  </div>
</div>

{{-- POPUP: rincian semua pasangan kota buat 1 item (khusus Flip Kota) --}}
<div class="pair-overlay" id="pairOverlay" onclick="closePairOnBg(event)">
  <div class="pair-box">
    <div class="pair-head">
      <img id="pairImg" src="" alt="">
      <div class="pair-head-name" id="pairName"></div>
      <button class="pair-close" onclick="closePairPopup()">✕</button>
    </div>
    <div id="pairList"></div>
  </div>
</div>

<script>
const TAX_NORMAL = 0.08;
const TAX_PREMI  = 0.04;

let jualRows = [{}, {}, {}];
let beliRows = [{}, {}, {}];
let sudahDicatat = false;

function fmtSilver(v) {
  if (v == null || isNaN(v)) return '';
  return Math.round(v).toLocaleString('id-ID');
}

function calcResult(type, inputVal, premi) {
  const tax = premi ? TAX_PREMI : TAX_NORMAL;
  if (inputVal === '' || isNaN(inputVal) || inputVal < 0) return null;
  const v = parseFloat(inputVal);
  return type === 'jual' ? v / (1 - tax) : v * (1 - tax);
}

function resultHtmlFor(result) {
  if (result == null) return '<span class="cell-result empty">—</span>';
  return `<span class="cell-result">${fmtSilver(result)} <span style="font-size:.7rem;color:var(--text-muted);font-weight:400">silver</span></span>`;
}

function renderTable(type) {
  const rows = type === 'jual' ? jualRows : beliRows;
  const tbody = document.getElementById(type === 'jual' ? 'tableJual' : 'tableBeli');
  const inputLabel = type === 'jual' ? 'Modal silver' : 'Harga jual silver';

  tbody.innerHTML = rows.map((row, i) => {
    const inputVal = row.input ?? '';
    const premi    = row.premi ?? false;
    const result   = calcResult(type, inputVal, premi);

    return `
    <tr data-idx="${i}">
      <td style="color:var(--text-muted);font-size:.75rem">${i + 1}</td>
      <td>
        <input class="cell-input" type="number" min="0" step="0.01"
          placeholder="${inputLabel}"
          value="${inputVal}"
          oninput="updateCell('${type}', ${i}, 'input', this.value)">
      </td>
      <td>
        <div class="premi-check">
          <input type="checkbox" ${premi ? 'checked' : ''} onchange="updateCell('${type}', ${i}, 'premi', this.checked)">
        </div>
      </td>
      <td class="result-cell">${resultHtmlFor(result)}</td>
      <td><button class="del-btn" onclick="delRow('${type}', ${i})">✕</button></td>
    </tr>`;
  }).join('');
}

function updateCell(type, idx, field, value) {
  const rows = type === 'jual' ? jualRows : beliRows;
  rows[idx][field] = value;

  const row    = rows[idx];
  const result = calcResult(type, row.input ?? '', row.premi ?? false);

  const tbody = document.getElementById(type === 'jual' ? 'tableJual' : 'tableBeli');
  const tr    = tbody.querySelector(`tr[data-idx="${idx}"]`);
  if (tr) {
    tr.querySelector('.result-cell').innerHTML = resultHtmlFor(result);
  }

  if (result != null && !sudahDicatat) {
    sudahDicatat = true;
    catatAktivitas();
  }
}

function addRow(type) {
  (type === 'jual' ? jualRows : beliRows).push({});
  renderTable(type);
}

function delRow(type, idx) {
  const rows = type === 'jual' ? jualRows : beliRows;
  if (rows.length <= 1) { rows[idx] = {}; } else { rows.splice(idx, 1); }
  renderTable(type);
}

async function catatAktivitas(){
  const loggedIn = @json(auth()->check());
  if (!loggedIn) return;
  try{
    await fetch('/api/catat-aktivitas', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ type: 'flip' })
    });
    tampilkanNotifPoin();
  }catch(err){}
}

function tampilkanNotifPoin(){
  let toast = document.getElementById('poinToast');
  if (!toast){
    toast = document.createElement('div');
    toast.id = 'poinToast';
    toast.style.cssText = `position:fixed;bottom:18px;left:18px;z-index:70;background:var(--teal);color:var(--bg);font-size:0.8rem;font-weight:600;padding:10px 16px;border-radius:30px;box-shadow:0 6px 18px rgba(0,0,0,0.35);transition:opacity .3s;`;
    document.body.appendChild(toast);
  }
  toast.textContent = '+1 EXP keaktifan 🎉';
  toast.style.opacity = '1';
  clearTimeout(toast._t);
  toast._t = setTimeout(() => { toast.style.opacity = '0'; }, 2200);
}

// ============================================================
// MODE TOGGLE (Simple / Advance) — persist ke localStorage kayak refine
// ============================================================
const FLIP_MODE_KEY = 'flip_mode';

function setFlipMode(mode) {
  document.getElementById('modeSimpleWrap').style.display   = mode === 'simple'   ? '' : 'none';
  document.getElementById('modeAdvanceWrap').style.display  = mode === 'advance'  ? '' : 'none';
  document.getElementById('btnModeSimple').classList.toggle('active', mode === 'simple');
  document.getElementById('btnModeAdvance').classList.toggle('active', mode === 'advance');
  localStorage.setItem(FLIP_MODE_KEY, mode);

  // Baru fetch data pas pertama kali masuk Advance (hemat request kalau user gak pernah buka)
  if (mode === 'advance' && !advInitialized) {
    advInitialized = true;
    initAdvance();
  }
}

// ============================================================
// SUB-MODE (Flip Kota / Black Market)
// ============================================================
function setFlipSubMode(sub) {
  document.getElementById('ctrlKota').closest('.panel').style.display = sub === 'kota' ? '' : 'none';
  document.getElementById('panelBm').style.display = sub === 'blackmarket' ? '' : 'none';
  document.getElementById('btnSubKota').classList.toggle('active', sub === 'kota');
  document.getElementById('btnSubBm').classList.toggle('active', sub === 'blackmarket');

  if (sub === 'kota' && !kotaFetchedOnce) { kotaFetchedOnce = true; fetchOpportunities(); }
  if (sub === 'blackmarket' && !bmFetchedOnce) { bmFetchedOnce = true; fetchBlackmarket(); }
}

// ============================================================
// STATE — Flip Kota
// ============================================================
const ALL_CITIES = ['Caerleon','Martlock','Bridgewatch','Lymhurst','Fort Sterling','Thetford','Brecilien'];
let selectedCities = ['Caerleon','Martlock']; // default 2 kota biar langsung ada hasil
let filterKota = 'profitable';
let filterBm   = 'profitable';
let advInitialized = false, kotaFetchedOnce = false, bmFetchedOnce = false;

const FLIP_ADV_PREFS_KEY = 'flip_adv_prefs';

function saveFlipAdvPrefs() {
  try {
    localStorage.setItem(FLIP_ADV_PREFS_KEY, JSON.stringify({
      selectedCities,
      premium: document.getElementById('cbPremiumAdv').checked,
      sellOrder: document.getElementById('cbSellOrder').checked,
      qualityKota: document.getElementById('qualityKota').value,
      qualityBm: document.getElementById('qualityBm').value,
      filterKota, filterBm,
    }));
  } catch (e) {}
}

function loadFlipAdvPrefs() {
  try {
    const raw = localStorage.getItem(FLIP_ADV_PREFS_KEY);
    if (!raw) return;
    const p = JSON.parse(raw);
    if (Array.isArray(p.selectedCities) && p.selectedCities.length >= 2) selectedCities = p.selectedCities;
    if (p.premium) document.getElementById('cbPremiumAdv').checked = true;
    if (p.sellOrder) document.getElementById('cbSellOrder').checked = true;
    if (p.qualityKota) document.getElementById('qualityKota').value = p.qualityKota;
    if (p.qualityBm) document.getElementById('qualityBm').value = p.qualityBm;
    if (p.filterKota) filterKota = p.filterKota;
    if (p.filterBm) filterBm = p.filterBm;
  } catch (e) {}
}

function buildCityGrid() {
  const grid = document.getElementById('cityGrid');
  grid.innerHTML = ALL_CITIES.map(c => `
    <div class="city-chip ${selectedCities.includes(c) ? 'sel' : ''}" onclick="toggleCity('${c}')">${c}</div>
  `).join('');
}

function toggleCity(city) {
  if (selectedCities.includes(city)) {
    if (selectedCities.length <= 2) {
      showFlipToast('Minimal pilih 2 kota.');
      return;
    }
    selectedCities = selectedCities.filter(c => c !== city);
  } else {
    selectedCities.push(city);
  }
  buildCityGrid();
  saveFlipAdvPrefs();
  fetchOpportunities();
}

function setFilterKota(val) {
  filterKota = val;
  document.querySelectorAll('#filterKota .filter-btn').forEach(b => b.classList.toggle('active', b.dataset.val === val));
  saveFlipAdvPrefs();
  fetchOpportunities();
}

function setFilterBm(val) {
  filterBm = val;
  document.querySelectorAll('#filterBm .filter-btn').forEach(b => b.classList.toggle('active', b.dataset.val === val));
  saveFlipAdvPrefs();
  fetchBlackmarket();
}

function initAdvance() {
  buildCityGrid();
  loadFlipAdvPrefs();
  buildCityGrid(); // rebuild lagi setelah prefs kepulihkan
  document.querySelectorAll('#filterKota .filter-btn').forEach(b => b.classList.toggle('active', b.dataset.val === filterKota));
  document.querySelectorAll('#filterBm .filter-btn').forEach(b => b.classList.toggle('active', b.dataset.val === filterBm));
  fetchOpportunities();
  kotaFetchedOnce = true;
}

// ============================================================
// FETCH & RENDER — Flip Kota
// ============================================================
function timeAgo(iso) {
  if (!iso) return '-';
  const diffMs = Date.now() - new Date(iso.replace(' ', 'T') + 'Z').getTime();
  const min = Math.floor(diffMs / 60000);
  if (min < 1) return 'baru saja';
  if (min < 60) return `${min}m lalu`;
  const hr = Math.floor(min / 60);
  if (hr < 24) return `${hr}j lalu`;
  return `${Math.floor(hr / 24)}h lalu`;
}

function tierLabel(tier, enc) {
  if (!tier) return '';
  return enc > 0 ? `T${tier}.${enc}` : `T${tier}`;
}

async function fetchOpportunities() {
  const list = document.getElementById('oppListKota');
  list.innerHTML = '<div class="empty-state">Memuat opportunity...</div>';

  const params = new URLSearchParams({
    cities: selectedCities.join(','),
    quality: document.getElementById('qualityKota').value,
    premium: document.getElementById('cbPremiumAdv').checked ? '1' : '0',
    sell_order: document.getElementById('cbSellOrder').checked ? '1' : '0',
    filter: filterKota,
  });

  try {
    const res  = await fetch('/api/flip/opportunities?' + params.toString());
    const data = await res.json();
    if (data.error) { list.innerHTML = `<div class="empty-state">${data.error}</div>`; return; }
    renderOppList(list, data, 'kota');
  } catch (e) {
    list.innerHTML = '<div class="empty-state">Gagal memuat data. Coba lagi.</div>';
  }
}

async function fetchBlackmarket() {
  const list = document.getElementById('oppListBm');
  list.innerHTML = '<div class="empty-state">Memuat opportunity...</div>';

  const params = new URLSearchParams({
    quality: document.getElementById('qualityBm').value,
    filter: filterBm,
  });

  try {
    const res  = await fetch('/api/flip/blackmarket?' + params.toString());
    const data = await res.json();
    renderOppList(list, data, 'bm');
  } catch (e) {
    list.innerHTML = '<div class="empty-state">Gagal memuat data. Coba lagi.</div>';
  }
}

function renderOppList(container, items, kind) {
  if (!items.length) {
    container.innerHTML = '<div class="empty-state">Tidak ada opportunity ditemukan 😔</div>';
    return;
  }

  container.innerHTML = items.map(it => {
    const tier = tierLabel(it.tier, it.enc);
    if (kind === 'kota') {
      return `
      <div class="opp-row" onclick="openPairPopup(${it.item_id})">
        <img class="opp-icon" src="${it.img_url ?? ''}" onerror="this.style.opacity=0.3">
        <div class="opp-info">
          <span class="opp-name">${it.item_name}</span><span class="opp-tier">${tier}</span>
          <div class="opp-route"><b>${it.origin_city}</b> ${fmtSilver(it.origin_price)} → <b>${it.dest_city}</b> ${fmtSilver(it.dest_price_net)}</div>
        </div>
        <div class="opp-right">
          <div class="opp-profit">${it.profit >= 0 ? '+' : ''}${fmtSilver(it.profit)}</div>
          <div class="opp-meta">${it.margin_pct}% · ${timeAgo(it.dest_fetched_at)}</div>
        </div>
      </div>`;
    }
    // Black Market
    const riskBadge = it.origin_is_caerleon ? '' : '<span class="opp-badge-risk">⚠ bukan dari Caerleon</span>';
    return `
      <div class="opp-row">
        <img class="opp-icon" src="${it.img_url ?? ''}" onerror="this.style.opacity=0.3">
        <div class="opp-info">
          <span class="opp-name">${it.item_name}</span><span class="opp-tier">${tier}</span>
          <div class="opp-route"><b>${it.origin_city}</b> ${fmtSilver(it.origin_price)} → <b>Black Market</b> ${fmtSilver(it.bm_price)} ${riskBadge}</div>
        </div>
        <div class="opp-right">
          <div class="opp-profit">${it.profit >= 0 ? '+' : ''}${fmtSilver(it.profit)}</div>
          <div class="opp-meta">${it.margin_pct}% · ${timeAgo(it.bm_fetched_at)}</div>
        </div>
      </div>`;
  }).join('');
}

// ============================================================
// POPUP — semua pasangan kota buat 1 item (Flip Kota aja)
// ============================================================
async function openPairPopup(itemId) {
  document.getElementById('pairOverlay').classList.add('show');
  document.getElementById('pairList').innerHTML = '<div class="empty-state">Memuat...</div>';

  const params = new URLSearchParams({
    cities: selectedCities.join(','),
    quality: document.getElementById('qualityKota').value,
    premium: document.getElementById('cbPremiumAdv').checked ? '1' : '0',
    sell_order: document.getElementById('cbSellOrder').checked ? '1' : '0',
  });

  try {
    const res  = await fetch(`/api/flip/item/${itemId}/pairings?` + params.toString());
    const data = await res.json();
    document.getElementById('pairName').textContent = data.item_name;

    if (!data.pairings.length) {
      document.getElementById('pairList').innerHTML = '<div class="empty-state">Gak ada pasangan kota yang cocok.</div>';
      return;
    }

    document.getElementById('pairList').innerHTML = data.pairings.map(p => `
      <div class="pair-item">
        <span class="pair-route"><b>${p.origin_city}</b> ${fmtSilver(p.origin_price)} → <b>${p.dest_city}</b> ${fmtSilver(p.dest_price_net)}</span>
        <span class="pair-profit">${p.profit >= 0 ? '+' : ''}${fmtSilver(p.profit)}</span>
      </div>
    `).join('');
  } catch (e) {
    document.getElementById('pairList').innerHTML = '<div class="empty-state">Gagal memuat data.</div>';
  }
}

function closePairPopup() {
  document.getElementById('pairOverlay').classList.remove('show');
}

function closePairOnBg(e) {
  if (e.target === document.getElementById('pairOverlay')) closePairPopup();
}

function showFlipToast(msg) {
  let toast = document.getElementById('flipToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'flipToast';
    toast.style.cssText = `position:fixed;bottom:18px;left:18px;z-index:210;background:var(--red);color:#1a1510;font-size:0.8rem;font-weight:600;padding:10px 16px;border-radius:30px;box-shadow:0 6px 18px rgba(0,0,0,0.35);transition:opacity .3s;`;
    document.body.appendChild(toast);
  }
  toast.textContent = msg;
  toast.style.opacity = '1';
  clearTimeout(toast._t);
  toast._t = setTimeout(() => { toast.style.opacity = '0'; }, 2200);
}

renderTable('jual');
renderTable('beli');
setFlipMode(localStorage.getItem(FLIP_MODE_KEY) || 'simple');
</script>

<x-comments page="flip" />
@endsection
