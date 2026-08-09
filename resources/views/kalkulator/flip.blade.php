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

  /* ====== MODE ADVANCE — dibangun ulang, gaya market browser ======
     Semua class di-prefix "fm-" (Flip Market) supaya gak numpuk sama
     class generik punya Mode Simple (.panel, .panel-header, dst) yang
     didefinisikan di atas dengan warna beda. Belum ada logic flip
     kota-ke-kota / Black Market — ini baru tahap browse harga per kota
     pakai endpoint /api/market/* yang sudah ada (gak duplikat backend). */
  .fm-panel{
    background: linear-gradient(180deg, #2e2210 0%, #1e1608 100%);
    border: 2px solid #6b4f1a; border-radius: 4px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.7); overflow: visible;
  }
  .fm-panel-header{
    background: linear-gradient(180deg, #3d2e15 0%, #2a1f0e 100%);
    border-bottom: 1px solid #6b4f1a; padding: 10px 16px;
    display: flex; align-items: center; gap: 10px;
  }
  .fm-panel-title{font-family:'Cinzel',serif;font-size:14px;color:#f0c040;letter-spacing:1px;text-transform:uppercase;}
  .fm-header-search{
    margin-left:auto; background: linear-gradient(180deg, #1a1208 0%, #110e05 100%);
    border:1px solid #4a3510; border-radius:3px; color:#e8d5a3;
    font-family:'Crimson Text',serif; font-size:13px; padding:6px 10px;
    outline:none; width:180px; transition:border-color .15s;
  }
  .fm-header-search:focus{border-color:#b8860b;}
  .fm-header-search::placeholder{color:#a08040;}

  .fm-filter-bar{
    background: linear-gradient(180deg, #251a08 0%, #1a1005 100%);
    border-bottom:1px solid #6b4f1a; padding:10px 12px;
    display:flex; gap:8px; flex-wrap:nowrap; overflow-x:auto; overflow-y:visible; position:relative;
  }
  .fm-flt-wrap{position:relative;}
  .fm-flt-btn{
    display:flex; align-items:center; gap:6px;
    background: linear-gradient(180deg, #c8a84a 0%, #a07828 100%);
    border:1px solid #8b6820; border-radius:3px; color:#2a1800;
    font-family:'Cinzel',serif; font-size:12px; font-weight:600; letter-spacing:.5px;
    padding:7px 12px; cursor:pointer; user-select:none; white-space:nowrap;
    transition:all .1s; justify-content:space-between;
  }
  .fm-flt-btn:hover{background:linear-gradient(180deg,#dabb5a 0%,#b88838 100%);border-color:#f0c040;}
  .fm-flt-btn.open{background:linear-gradient(180deg,#b89030 0%,#907020 100%);border-color:#f0c040;box-shadow:0 0 8px rgba(240,192,64,.3);}
  .fm-flt-btn .fm-flt-label{flex:1;text-align:left;}
  .fm-flt-btn .fm-flt-val{font-size:10px;opacity:.75;max-width:90px;overflow:hidden;text-overflow:ellipsis;}
  .fm-flt-btn .fm-flt-arrow{font-size:8px;opacity:.7;margin-left:2px;transition:transform .15s;}
  .fm-flt-btn.open .fm-flt-arrow{transform:rotate(180deg);}

  .fm-drop-wrap{position:fixed;z-index:9999;display:none;gap:2px;filter:drop-shadow(0 6px 20px rgba(0,0,0,.85));}
  .fm-drop-wrap.show{display:flex;}
  .fm-drop-col{
    min-width:175px; max-height:370px; overflow-y:auto;
    background: linear-gradient(180deg, #e8cf88 0%, #d4b468 100%);
    border:1px solid #8b6820; border-radius:3px; padding:4px;
    display:flex; flex-direction:column; gap:2px;
  }
  .fm-drop-item{
    display:flex; align-items:center; justify-content:space-between;
    padding:8px 10px; border-radius:2px; background:transparent; border:1px solid transparent;
    cursor:pointer; font-family:'Crimson Text',serif; font-size:14px; font-weight:600; color:#2a1800;
    transition:all .08s; white-space:nowrap;
  }
  .fm-drop-item:hover{background:linear-gradient(180deg,#f2dc9a 0%,#e2c878 100%);border-color:#a07828;}
  .fm-drop-item.active{background:linear-gradient(180deg,#b88a28 0%,#906818 100%);border-color:#7a5010;color:#fff8e0;}
  .fm-drop-item .fm-di-arrow{font-size:9px;color:#6b4f1a;margin-left:8px;flex-shrink:0;}
  .fm-drop-item.active .fm-di-arrow{color:#ffe090;}

  .fm-item-list{min-height:160px;padding:0;position:relative;}
  .fm-item-list-empty{
    position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
    color:#a08040; font-style:italic; font-size:14px; text-align:center; font-family:'Crimson Text',serif;
  }
  .fm-item-table-wrap{max-height:480px;overflow-y:auto;}
  .fm-item-row{display:flex;align-items:center;gap:10px;padding:8px 10px;border-bottom:1px solid rgba(107,79,26,.3);cursor:pointer;transition:background .1s;}
  .fm-item-row:hover{background:rgba(61,46,21,.5);}
  .fm-item-icon{width:48px;height:48px;border:1px solid #4a3510;border-radius:3px;background:#1a1208;object-fit:contain;image-rendering:pixelated;display:block;flex-shrink:0;}
  .fm-item-info{flex:1;min-width:0;display:flex;flex-direction:column;gap:6px;}
  .fm-item-name{font-family:'Crimson Text',serif;font-size:15px;color:#dcc08a;font-weight:600;}
  .fm-item-price{font-family:'Cinzel',serif;font-size:11px;color:#f0c040;}

  /* Popup — sama gayanya kayak popup market: harga per kota + tren 30 hari */
  .fm-popup-overlay{position:fixed;inset:0;background:rgba(0,0,0,.82);z-index:10000;display:none;align-items:center;justify-content:center;padding:16px;}
  .fm-popup-overlay.show{display:flex;}
  .fm-popup-box{
    background: linear-gradient(180deg, #2e2210 0%, #1a1208 100%);
    border:2px solid #6b4f1a; border-radius:6px; box-shadow:0 8px 40px rgba(0,0,0,.95);
    width:100%; max-width:400px; max-height:88vh; overflow-y:auto; position:relative;
  }
  .fm-popup-close{
    position:absolute; top:10px; right:10px; width:28px; height:28px; border-radius:50%;
    background: linear-gradient(180deg, #6b1a1a 0%, #4a1010 100%);
    border:1px solid #8b3030; color:#f0c0c0; font-size:14px; cursor:pointer;
    display:flex; align-items:center; justify-content:center; z-index:1;
  }
  .fm-popup-close:hover{background:#8b2020;border-color:#c04040;}
  .fm-popup-head{
    display:flex; gap:14px; padding:16px 44px 16px 16px;
    background: linear-gradient(180deg, #3d2e15 0%, #2a1f0e 100%);
    border-bottom:1px solid #6b4f1a; align-items:center;
  }
  .fm-popup-head img{width:72px;height:72px;border:1px solid #4a3510;border-radius:4px;background:#1a1208;object-fit:contain;flex-shrink:0;}
  .fm-popup-item-name{font-family:'Cinzel',serif;font-size:15px;color:#f0c040;margin-bottom:4px;line-height:1.3;}
  .fm-popup-item-sub{font-family:'Crimson Text',serif;font-size:12px;color:#a08040;}
  .fm-popup-prices{padding:12px 16px;border-bottom:1px solid rgba(107,79,26,.4);}
  .fm-popup-prices-label{font-family:'Cinzel',serif;font-size:10px;color:#a08040;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;}
  .fm-city-prices-grid{display:flex;gap:6px;flex-wrap:wrap;}
  .fm-city-price-box{
    width:44px;height:44px;border-radius:4px;display:flex;flex-direction:column;
    align-items:center;justify-content:center;font-family:'Cinzel',serif;font-size:9px;font-weight:700;
    border:1px solid rgba(0,0,0,.3);gap:2px;cursor:pointer;transition:box-shadow .15s,transform .1s;
  }
  .fm-city-price-box .fm-cpb-val{font-size:11px;font-weight:700;}
  .fm-city-price-box.loading{opacity:.5;}
  .fm-city-price-box.no-data{opacity:.3;cursor:default;}
  .fm-city-price-box.active{box-shadow:0 0 0 2px #f0c060;transform:translateY(-1px);}
  .fm-city-Caerleon{background:#7b1a1a;color:#ffd0d0;border-color:#c0392b;}
  .fm-city-Bridgewatch{background:#7a3a00;color:#ffe0b0;border-color:#e67e22;}
  .fm-city-Fort-Sterling{background:#3a3a3a;color:#f0f0f0;border-color:#bdc3c7;}
  .fm-city-Lymhurst{background:#1a4a1a;color:#c0ffc0;border-color:#27ae60;}
  .fm-city-Martlock{background:#1a2a5a;color:#c0d0ff;border-color:#2980b9;}
  .fm-city-Thetford{background:#3a1a5a;color:#e0c0ff;border-color:#8e44ad;}
  .fm-city-Brecilien{background:#0a3a2a;color:#a0ffe0;border-color:#1abc9c;}
  .fm-popup-history{padding:12px 16px;}
  .fm-popup-history-label{font-size:12px;letter-spacing:.05em;text-transform:uppercase;color:#a89878;margin-bottom:8px;}
  .fm-popup-history-canvas-wrap{position:relative;height:160px;}
  .fm-popup-history-empty,.fm-popup-history-loading{color:#a89878;font-size:13px;display:flex;align-items:center;justify-content:center;height:160px;}
  .fm-popup-loading{padding:40px;text-align:center;color:#a08040;font-style:italic;font-family:'Crimson Text',serif;font-size:14px;}
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


<style>
  .fm-scan-bar{display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #6b4f1a;background:rgba(0,0,0,.15);flex-wrap:wrap;}
  .fm-scan-btn{background:linear-gradient(180deg,#f0c040 0%,#c89020 100%);border:1px solid #8b6820;border-radius:4px;color:#2a1800;font-family:'Cinzel',serif;font-weight:700;font-size:13px;letter-spacing:.5px;padding:10px 22px;cursor:pointer;transition:all .15s;}
  .fm-scan-btn:hover:not(:disabled){background:linear-gradient(180deg,#ffd060 0%,#daa030 100%);}
  .fm-scan-btn:disabled{opacity:.4;cursor:not-allowed;}
  .fm-scan-status{font-family:'Crimson Text',serif;font-size:12px;color:#a08040;}

  .fm-opp-list{min-height:120px;padding:0;position:relative;}
  .fm-opp-row{display:flex;align-items:center;gap:12px;padding:12px 14px;border-bottom:1px solid rgba(107,79,26,.3);}
  .fm-opp-icon{width:44px;height:44px;border:1px solid #4a3510;border-radius:3px;background:#1a1208;object-fit:contain;image-rendering:pixelated;flex-shrink:0;}
  .fm-opp-info{flex:1;min-width:0;}
  .fm-opp-name{font-family:'Crimson Text',serif;font-size:14px;color:#dcc08a;font-weight:600;}
  .fm-opp-tier{font-family:'Cinzel',serif;font-size:10px;color:#f0c040;margin-left:6px;}
  .fm-opp-route{display:flex;align-items:center;gap:6px;margin-top:4px;font-family:'JetBrains Mono',monospace;font-size:11px;color:#a89878;flex-wrap:wrap;}
  .fm-opp-city-badge{padding:2px 6px;border-radius:3px;font-size:10px;font-weight:700;}
  .fm-opp-right{text-align:right;flex-shrink:0;}
  .fm-opp-profit{font-family:'JetBrains Mono',monospace;font-size:15px;font-weight:700;color:#5FB3A8;}
  .fm-opp-meta{font-family:'Crimson Text',serif;font-size:11px;color:#a08040;margin-top:2px;}

  .fm-opp-pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-top:1px solid #6b4f1a;}
  .fm-pg-btn{background:var(--card,#221C15);border:1px solid #6b4f1a;border-radius:4px;color:#dcc08a;font-size:12px;padding:8px 14px;cursor:pointer;}
  .fm-pg-btn:disabled{opacity:.3;cursor:not-allowed;}
</style>

  {{-- ============================================================ --}}
  {{-- MODE ADVANCE — Scan Opportunity Flip.
       Kategori wajib dipilih sampai leaf (gak ada opsi "All") biar
       scope hitung profit kecil. Tier & Enchant boleh "All". Compute
       cuma jalan pas klik tombol Scan (bukan browse otomatis), hasil
       di-cache di server per kombinasi filter (cooldown), pagination
       hasil scan di-slice di sisi client dari data yang sudah didapat
       (gak ada request/hitung ulang per ganti halaman). --}}
  {{-- ============================================================ --}}
  <div id="modeAdvanceWrap" style="display:none">
    <div class="fm-panel">
      <div class="fm-panel-header">
        <span>📊</span>
        <span class="fm-panel-title">Scan Opportunity Flip</span>
      </div>

      <div class="fm-filter-bar" id="fmFilterBar">
        <!-- CATEGORY (wajib sampai leaf, tanpa opsi All) -->
        <div class="fm-flt-wrap">
          <div class="fm-flt-btn" id="fmBtnCategory" onclick="fmToggleDrop('category')">
            <span class="fm-flt-label" id="fmLblCategory">Category</span>
            <span class="fm-flt-val" id="fmValCategory" style="display:none"></span>
            <span class="fm-flt-arrow">▼</span>
          </div>
          <div class="fm-drop-wrap" id="fmDropCategory">
            <div class="fm-drop-col" id="fmColKat1"></div>
            <div class="fm-drop-col" id="fmColKat2" style="display:none"></div>
            <div class="fm-drop-col" id="fmColKat3" style="display:none"></div>
          </div>
        </div>
        <!-- TIER (boleh All) -->
        <div class="fm-flt-wrap">
          <div class="fm-flt-btn" id="fmBtnTier" onclick="fmToggleDrop('tier')">
            <span class="fm-flt-label" id="fmLblTier">Tier</span>
            <span class="fm-flt-val" id="fmValTier" style="display:none"></span>
            <span class="fm-flt-arrow">▼</span>
          </div>
          <div class="fm-drop-wrap" id="fmDropTier">
            <div class="fm-drop-col" id="fmColTier"></div>
          </div>
        </div>
        <!-- ENCHANTMENT (boleh All) -->
        <div class="fm-flt-wrap">
          <div class="fm-flt-btn" id="fmBtnEnc" onclick="fmToggleDrop('enc')">
            <span class="fm-flt-label" id="fmLblEnc">Enchantment</span>
            <span class="fm-flt-val" id="fmValEnc" style="display:none"></span>
            <span class="fm-flt-arrow">▼</span>
          </div>
          <div class="fm-drop-wrap" id="fmDropEnc">
            <div class="fm-drop-col" id="fmColEnc"></div>
          </div>
        </div>
      </div>

      <div class="fm-scan-bar">
        <button class="fm-scan-btn" id="fmScanBtn" onclick="fmRunScan()" disabled>🔍 Scan</button>
        <span class="fm-scan-status" id="fmScanStatus"></span>
      </div>

      <div class="fm-opp-list" id="fmOppList">
        <div class="fm-item-list-empty" id="fmEmptyMsg">Pilih kategori sampai habis (leaf), lalu klik Scan 🗡️</div>
        <div id="fmOppRows" style="display:none"></div>
        <div class="fm-opp-pagination" id="fmPagination" style="display:none">
          <button class="fm-pg-btn" id="fmPgPrev" onclick="fmGoToPage(fmCurrentPage - 1)">‹ Sebelumnya</button>
          <span id="fmPgLabel"></span>
          <button class="fm-pg-btn" id="fmPgNext" onclick="fmGoToPage(fmCurrentPage + 1)">Berikutnya ›</button>
        </div>
      </div>
    </div>
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
    fmInit();
  }
}

// ============================================================
// MODE ADVANCE — Scan Opportunity Flip.
// Kategori wajib dipilih sampai leaf (gak ada opsi "All"). Tier &
// Enchant boleh "All". Compute jalan di server pas tombol Scan
// diklik (endpoint /flip/scan, dengan cooldown per kombinasi
// filter). Hasil scan dikirim penuh sekali, pagination di halaman
// ini di-slice di client — gak ada request/hitung ulang per ganti
// halaman.
// ============================================================
let advInitialized = false;
let FM_CATEGORIES = [];
const FM_TIERS      = [1,2,3,4,5,6,7,8];
const FM_TIER_LABEL = {1:'Tier 1',2:'Tier 2',3:'Tier 3',4:'Tier 4',5:'Tier 5',6:'Tier 6',7:'Tier 7',8:'Tier 8'};
const FM_ENCS       = [0,1,2,3,4];
const FM_CITIES = [
  { id: 'Caerleon',      cls: 'fm-city-Caerleon'      },
  { id: 'Bridgewatch',   cls: 'fm-city-Bridgewatch'   },
  { id: 'Fort Sterling', cls: 'fm-city-Fort-Sterling' },
  { id: 'Lymhurst',      cls: 'fm-city-Lymhurst'      },
  { id: 'Martlock',      cls: 'fm-city-Martlock'      },
  { id: 'Thetford',      cls: 'fm-city-Thetford'      },
  { id: 'Brecilien',     cls: 'fm-city-Brecilien'     },
];

const FM_PER_PAGE = 15;

let fmOpenDrop = null;
let fmSelKat1 = null, fmSelKat2 = null, fmSelKat3 = null, fmSelCatId = null;
let fmSelTier = null, fmSelEnc = null;
let fmAllResults = [];
let fmCurrentPage = 1;

function fmInit() {
  fetch('/api/market/categories')
    .then(r => r.json())
    .then(data => {
      FM_CATEGORIES = data;
      fmBuildTierDrop();
      fmBuildEncDrop();
      fmRefreshCols();
      fmUpdateCatLabel();
      fmUpdateScanBtn();
    });
}

function fmCap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

function fmToggleDrop(name) {
  if (fmOpenDrop === name) { fmCloseDrop(); return; }
  fmCloseDrop();
  fmOpenDrop = name;
  const btn  = document.getElementById('fmBtn'  + fmCap(name));
  const drop = document.getElementById('fmDrop' + fmCap(name));
  const rect = btn.getBoundingClientRect();
  drop.style.top  = (rect.bottom + 3) + 'px';
  drop.style.left = rect.left + 'px';
  drop.classList.add('show');
  btn.classList.add('open');
}

function fmCloseDrop() {
  if (!fmOpenDrop) return;
  document.getElementById('fmDrop' + fmCap(fmOpenDrop)).classList.remove('show');
  document.getElementById('fmBtn'  + fmCap(fmOpenDrop)).classList.remove('open');
  fmOpenDrop = null;
}

document.addEventListener('click', e => {
  if (fmOpenDrop && !e.target.closest('.fm-flt-wrap')) fmCloseDrop();
});

function fmSetFilterVal(lblId, valId, value) {
  const lbl = document.getElementById(lblId);
  const val = document.getElementById(valId);
  if (value) { lbl.style.display = 'none'; val.style.display = ''; val.textContent = value; }
  else       { lbl.style.display = '';     val.style.display = 'none'; }
}

function fmMakeItem(text, hasArrow, isActive, onClick) {
  const el = document.createElement('div');
  el.className = 'fm-drop-item' + (isActive ? ' active' : '');
  el.innerHTML = text + (hasArrow ? '<span class="fm-di-arrow">▶</span>' : '');
  el.addEventListener('click', e => { e.stopPropagation(); onClick(); });
  return el;
}

function fmGetCatName(id, list) {
  for (const c of list) {
    if (c.id === id) return c.name;
    if (c.children) { const found = fmGetCatName(id, c.children); if (found) return found; }
  }
  return null;
}

// --- CATEGORY: wajib dipilih sampai leaf, gak ada opsi "All" di level manapun ---

function fmBuildCol1() {
  const col = document.getElementById('fmColKat1');
  col.innerHTML = '';
  FM_CATEGORIES.forEach(cat => {
    const hasSub = cat.children && cat.children.length > 0;
    col.appendChild(fmMakeItem(cat.name, hasSub, fmSelKat1 === cat.id, () => {
      fmSelKat1 = cat.id; fmSelKat2 = null; fmSelKat3 = null; fmSelCatId = null;
      if (!hasSub) { fmSelCatId = cat.id; fmCloseDrop(); }
      fmRefreshCols(); fmUpdateCatLabel(); fmUpdateScanBtn(); fmResetResults();
    }));
  });
}

function fmBuildCol2() {
  const col2 = document.getElementById('fmColKat2');
  const col3 = document.getElementById('fmColKat3');
  if (!fmSelKat1) { col2.style.display = 'none'; col3.style.display = 'none'; return; }
  const cat1 = FM_CATEGORIES.find(c => c.id === fmSelKat1);
  if (!cat1 || !cat1.children || !cat1.children.length) { col2.style.display = 'none'; col3.style.display = 'none'; return; }
  col2.style.display = ''; col2.innerHTML = '';
  cat1.children.forEach(sub => {
    const hasSub2 = sub.children && sub.children.length > 0;
    col2.appendChild(fmMakeItem(sub.name, hasSub2, fmSelKat2 === sub.id, () => {
      fmSelKat2 = sub.id; fmSelKat3 = null; fmSelCatId = null;
      if (!hasSub2) { fmSelCatId = sub.id; fmCloseDrop(); }
      fmRefreshCols(); fmUpdateCatLabel(); fmUpdateScanBtn(); fmResetResults();
    }));
  });
}

function fmBuildCol3() {
  const col3 = document.getElementById('fmColKat3');
  if (!fmSelKat2) { col3.style.display = 'none'; return; }
  const cat1 = FM_CATEGORIES.find(c => c.id === fmSelKat1);
  const cat2 = cat1?.children?.find(c => c.id === fmSelKat2);
  if (!cat2 || !cat2.children || !cat2.children.length) { col3.style.display = 'none'; return; }
  col3.style.display = ''; col3.innerHTML = '';
  cat2.children.forEach(leaf => {
    col3.appendChild(fmMakeItem(leaf.name, false, fmSelKat3 === leaf.id, () => {
      fmSelKat3 = leaf.id; fmSelCatId = leaf.id;
      fmCloseDrop(); fmUpdateCatLabel(); fmUpdateScanBtn(); fmResetResults();
    }));
  });
}

function fmRefreshCols() { fmBuildCol1(); fmBuildCol2(); fmBuildCol3(); }

function fmUpdateCatLabel() {
  if (fmSelCatId) {
    fmSetFilterVal('fmLblCategory', 'fmValCategory', fmGetCatName(fmSelCatId, FM_CATEGORIES));
  } else if (fmSelKat1) {
    fmSetFilterVal('fmLblCategory', 'fmValCategory', 'Pilih lagi…');
  } else {
    fmSetFilterVal('fmLblCategory', 'fmValCategory', null);
  }
}

// --- TIER & ENCHANT: boleh "All" ---

function fmBuildTierDrop() {
  const col = document.getElementById('fmColTier');
  col.innerHTML = '';
  col.appendChild(fmMakeItem('All', false, fmSelTier === null, () => {
    fmSelTier = null; fmSetFilterVal('fmLblTier', 'fmValTier', null); fmCloseDrop(); fmResetResults();
  }));
  FM_TIERS.forEach(t => col.appendChild(fmMakeItem(FM_TIER_LABEL[t], false, fmSelTier === t, () => {
    fmSelTier = t; fmSetFilterVal('fmLblTier', 'fmValTier', FM_TIER_LABEL[t]); fmCloseDrop(); fmResetResults();
  })));
}

function fmBuildEncDrop() {
  const col = document.getElementById('fmColEnc');
  col.innerHTML = '';
  col.appendChild(fmMakeItem('All', false, fmSelEnc === null, () => {
    fmSelEnc = null; fmSetFilterVal('fmLblEnc', 'fmValEnc', null); fmCloseDrop(); fmResetResults();
  }));
  FM_ENCS.forEach(e => col.appendChild(fmMakeItem('Enchantment ' + e, false, fmSelEnc === e, () => {
    fmSelEnc = e; fmSetFilterVal('fmLblEnc', 'fmValEnc', 'Enc ' + e); fmCloseDrop(); fmResetResults();
  })));
}

// --- SCAN ---

function fmUpdateScanBtn() {
  document.getElementById('fmScanBtn').disabled = !fmSelCatId;
}

function fmResetResults() {
  fmAllResults = [];
  fmCurrentPage = 1;
  document.getElementById('fmScanStatus').textContent = '';
  fmShowOppEmpty('Klik Scan buat lihat opportunity kombinasi ini 🗡️');
}

function fmShowOppEmpty(msg) {
  document.getElementById('fmOppRows').style.display = 'none';
  document.getElementById('fmPagination').style.display = 'none';
  const el = document.getElementById('fmEmptyMsg');
  el.style.display = ''; el.textContent = msg;
}

function fmGetCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function fmFormatTime(iso) {
  try { return new Date(iso).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }); }
  catch (e) { return ''; }
}

function fmRunScan() {
  if (!fmSelCatId) return;
  const btn    = document.getElementById('fmScanBtn');
  const status = document.getElementById('fmScanStatus');
  btn.disabled = true;
  status.textContent = 'Sedang scan…';
  fmShowOppEmpty('Sedang scan harga…');

  const payload = { sub_category_id: fmSelCatId };
  if (fmSelTier !== null) payload.tier = fmSelTier;
  if (fmSelEnc  !== null) payload.enchant = fmSelEnc;

  fetch('/flip/scan', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': fmGetCsrf() },
    body: JSON.stringify(payload),
  })
    .then(r => r.json())
    .then(res => {
      fmAllResults = res.results || [];
      status.textContent = (res.from_cache ? 'Hasil cache — ' : 'Baru discan — ')
        + 'scan berikutnya bisa mulai ' + fmFormatTime(res.next_scan_at);
      fmSetOppPage(1);
      fmUpdateScanBtn();
    })
    .catch(() => {
      status.textContent = 'Gagal scan, coba lagi.';
      fmShowOppEmpty('Gagal scan. Coba klik Scan lagi.');
      fmUpdateScanBtn();
    });
}

function fmCityCls(cityId) {
  const c = FM_CITIES.find(c => c.id === cityId);
  return c ? c.cls : '';
}

function fmRenderOppRows(items) {
  const wrap = document.getElementById('fmOppRows');
  if (!items.length) {
    fmShowOppEmpty('Gak ada opportunity profit buat kombinasi ini 😔');
    return;
  }
  document.getElementById('fmEmptyMsg').style.display = 'none';
  wrap.style.display = '';
  wrap.innerHTML = items.map(it => `
    <div class="fm-opp-row">
      <img class="fm-opp-icon" src="${it.img_url}" alt="${it.name}" loading="lazy" onerror="this.style.opacity=0.3">
      <div class="fm-opp-info">
        <span class="fm-opp-name">${it.name}</span><span class="fm-opp-tier">T${it.tier}${it.enc > 0 ? '.' + it.enc : ''}</span>
        <div class="fm-opp-route">
          <span class="fm-opp-city-badge ${fmCityCls(it.city_from)}">${it.city_from}</span>
          <span>${fmtSilver(it.price_from)}</span>
          <span>→</span>
          <span class="fm-opp-city-badge ${fmCityCls(it.city_to)}">${it.city_to}</span>
          <span>${fmtSilver(it.price_to)}</span>
        </div>
      </div>
      <div class="fm-opp-right">
        <div class="fm-opp-profit">+${fmtSilver(it.profit)}</div>
        <div class="fm-opp-meta">+${it.percent}%</div>
      </div>
    </div>`).join('');
}

function fmUpdatePaginationUI() {
  const pag = document.getElementById('fmPagination');
  const totalPages = Math.max(1, Math.ceil(fmAllResults.length / FM_PER_PAGE));
  if (fmAllResults.length <= FM_PER_PAGE) { pag.style.display = 'none'; return; }
  pag.style.display = 'flex';
  document.getElementById('fmPgLabel').textContent = `Halaman ${fmCurrentPage} dari ${totalPages}`;
  document.getElementById('fmPgPrev').disabled = fmCurrentPage <= 1;
  document.getElementById('fmPgNext').disabled = fmCurrentPage >= totalPages;
}

function fmSetOppPage(page) {
  fmCurrentPage = page;
  const start = (page - 1) * FM_PER_PAGE;
  fmRenderOppRows(fmAllResults.slice(start, start + FM_PER_PAGE));
  fmUpdatePaginationUI();
}

function fmGoToPage(page) {
  const totalPages = Math.max(1, Math.ceil(fmAllResults.length / FM_PER_PAGE));
  if (page < 1 || page > totalPages) return;
  fmSetOppPage(page);
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
