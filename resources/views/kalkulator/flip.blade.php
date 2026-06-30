@extends('layouts.app')

@section('title', 'Kalkulator Flip - HarunGamingTools')

@section('content')
<style>
  .flip-wrap{
    --card:#221C15; --slot:#1A1510; --border:#332B21;
    --green:#5FB3A8; --red:#C97B5F;
  }
  .flip-header{padding:26px 20px 18px;text-align:center;border-bottom:1px solid var(--border);background:linear-gradient(180deg,#0c0a08,var(--bg-panel,#1C1712));margin:-48px -24px 24px;}
  .flip-header h1{font-family:'Fraunces',serif;font-size:clamp(1.15rem,4vw,1.55rem);color:var(--gold);letter-spacing:.04em;}
  .flip-header p{margin-top:6px;font-size:.82rem;color:var(--text-muted);}

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
</style>

<div class="flip-wrap">
  <div class="flip-header">
    <h1>📊 Kalkulator Flip</h1>
    <p>Albion Online &middot; Hitung batas harga jual/beli supaya tidak rugi pajak market</p>
  </div>

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

renderTable('jual');
renderTable('beli');
</script>

<x-comments page="flip" />
@endsection