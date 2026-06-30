@extends('layouts.app')

@section('title', 'Kalkulator Mancing - HarunGamingTools')

@section('content')
<style>
  .fish-wrap{
    --panel:#1C1712; --card:#221C15; --slot:#1A1510;
    --border:#332B21; --border-soft:#2A2318;
    --red:#C97B5F;
  }
  .fish-header{padding:26px 20px 18px;text-align:center;border-bottom:1px solid var(--border);background:linear-gradient(180deg,#0c0a08,var(--panel));margin:-48px -24px 24px;}
  .fish-header h1{font-family:'Fraunces',serif;font-size:clamp(1.15rem,4vw,1.55rem);color:var(--gold);letter-spacing:.04em;}
  .fish-header p{margin-top:6px;font-size:.82rem;color:var(--text-muted);}

  .price-bar{display:flex;align-items:center;gap:10px;background:var(--card);border:1px solid var(--border);border-radius:10px;padding:13px 16px;margin-bottom:18px;flex-wrap:wrap;}
  .price-bar label{font-size:.82rem;color:var(--text-muted);}
  .price-bar input{width:130px;background:var(--slot);border:1px solid var(--border);border-radius:6px;color:var(--gold);font-family:'JetBrains Mono',monospace;font-weight:600;font-size:.92rem;padding:7px 10px;outline:none;}
  .price-bar input:focus{border-color:var(--gold);}
  .price-bar span{font-size:.75rem;color:var(--text-muted);}

  .btn-add-fish{width:100%;display:flex;align-items:center;justify-content:center;gap:8px;background:var(--card);border:1px dashed var(--border-soft);color:var(--gold);font-weight:600;font-size:.92rem;padding:14px;border-radius:10px;cursor:pointer;transition:border-color .2s, background .2s;margin-bottom:20px;}
  .btn-add-fish:hover{border-color:var(--gold);background:#241d12;}

  .inv-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
  .inv-title{font-family:'Fraunces',serif;font-size:.92rem;color:var(--gold);letter-spacing:.05em;}
  .inv-count{font-family:'JetBrains Mono',monospace;font-size:.75rem;color:var(--text-muted);margin-left:8px;}
  .btn-toggle{display:flex;align-items:center;gap:6px;background:var(--card);border:1px solid var(--border);color:var(--text-muted);font-size:.78rem;font-weight:600;padding:6px 12px;border-radius:7px;cursor:pointer;transition:color .2s, border-color .2s;}
  .btn-toggle:hover{color:var(--gold);border-color:var(--gold-dim);}

  .inv-empty{text-align:center;color:var(--text-muted);font-size:.85rem;padding:30px 10px;border:1px dashed var(--border-soft);border-radius:10px;background:var(--card);}

  .inv-list{list-style:none;display:flex;flex-direction:column;gap:8px;}
  .inv-row{display:flex;align-items:center;gap:12px;background:var(--card);border:1px solid var(--border);border-radius:10px;padding:10px 12px;}
  .inv-icon{width:44px;height:44px;border-radius:8px;background:var(--slot);border:1px solid var(--border-soft);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;position:relative;}
  .inv-icon img{width:100%;height:100%;object-fit:contain;}
  .inv-icon .fallback{font-family:'Fraunces',serif;font-weight:700;color:var(--gold);font-size:1rem;}
  .inv-tier{position:absolute;bottom:-1px;right:-1px;background:var(--gold);color:#1a1208;font-size:.6rem;font-weight:700;padding:1px 4px;border-radius:4px 0 0 0;font-family:'JetBrains Mono',monospace;}
  .inv-info{flex:1;min-width:0;}
  .inv-name{font-size:.88rem;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
  .inv-sub{font-size:.72rem;color:var(--text-muted);margin-top:2px;}

  .qty-stepper{display:flex;align-items:center;gap:0;border:1px solid var(--border);border-radius:7px;overflow:hidden;flex-shrink:0;}
  .qty-stepper button{width:26px;height:30px;background:var(--slot);border:none;color:var(--text-muted);font-size:.95rem;cursor:pointer;}
  .qty-stepper button:hover{color:var(--gold);}
  .qty-stepper input{width:38px;height:30px;text-align:center;background:var(--slot);border:none;border-left:1px solid var(--border);border-right:1px solid var(--border);color:var(--text);font-family:'JetBrains Mono',monospace;font-size:.82rem;outline:none;}

  .inv-price{width:78px;flex-shrink:0;}
  .inv-price input{width:100%;background:var(--slot);border:1px solid var(--border);border-radius:6px;color:var(--text);font-family:'JetBrains Mono',monospace;font-size:.78rem;padding:6px 6px;outline:none;text-align:right;}
  .inv-price input:focus{border-color:var(--teal);}

  .inv-total{width:70px;flex-shrink:0;text-align:right;font-family:'JetBrains Mono',monospace;font-size:.8rem;color:var(--gold);font-weight:600;}

  .btn-remove{width:26px;height:26px;flex-shrink:0;background:none;border:none;color:var(--text-muted);font-size:1rem;cursor:pointer;border-radius:6px;transition:color .15s, background .15s;}
  .btn-remove:hover{color:var(--red);background:#2a1815;}

  .actions{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap;}
  .btn{display:inline-flex;align-items:center;gap:7px;padding:11px 22px;border-radius:8px;border:none;font-weight:600;font-size:.85rem;cursor:pointer;transition:opacity .15s, transform .1s;}
  .btn:active{transform:scale(.97);}
  .btn-reset{background:#2a1e1e;color:var(--red);border:1px solid #3e2020;}
  .btn-calc{background:var(--gold);color:#1a1208;margin-left:auto;}
  .btn-calc:hover{background:#e8b863;}

  #hasil{margin-top:30px;display:none;}
  .hasil-header{font-family:'Fraunces',serif;font-size:.92rem;color:var(--gold);letter-spacing:.05em;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border);}
  .summary-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:18px;}
  .summary-card{background:var(--card);border:1px solid var(--border);border-radius:9px;padding:14px 10px;text-align:center;}
  .s-label{font-size:.65rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;}
  .s-val{font-family:'Fraunces',serif;font-size:.98rem;font-weight:700;}
  .s-val.profit{color:var(--teal);}
  .s-val.neutral{color:var(--text);}
  .s-val.accent{color:var(--gold);}

  .res-list{list-style:none;display:flex;flex-direction:column;gap:8px;}
  .res-row{display:flex;align-items:center;gap:12px;background:var(--card);border:1px solid var(--border);border-radius:10px;padding:10px 12px;}
  .res-info{flex:1;min-width:0;}
  .res-name{font-size:.86rem;font-weight:600;}
  .res-detail{font-size:.72rem;color:var(--text-muted);margin-top:2px;}
  .res-diff{font-family:'JetBrains Mono',monospace;font-size:.82rem;font-weight:600;text-align:right;width:90px;flex-shrink:0;}
  .diff-pos{color:var(--teal);}
  .diff-neg{color:var(--red);}
  .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.68rem;font-weight:700;flex-shrink:0;letter-spacing:.03em;}
  .badge-sell{background:#0e2a1a;color:var(--teal);border:1px solid #1a5030;}
  .badge-cut{background:#2e1010;color:var(--red);border:1px solid #5e2020;}

  .modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.6);display:none;align-items:flex-end;justify-content:center;z-index:100;backdrop-filter:blur(2px);}
  .modal-backdrop.open{display:flex;}
  .picker{background:var(--bg-panel,var(--panel));border:1px solid var(--border);border-radius:16px 16px 0 0;width:100%;max-width:720px;height:82vh;display:flex;flex-direction:column;animation:slideUp .2s ease-out;}
  @media (min-width:640px){.modal-backdrop{align-items:center;}.picker{border-radius:14px;height:78vh;}}
  @keyframes slideUp{from{transform:translateY(20px);opacity:0;}to{transform:translateY(0);opacity:1;}}
  .picker-head{display:flex;align-items:center;gap:10px;padding:16px 16px 12px;border-bottom:1px solid var(--border);}
  .picker-head h2{font-family:'Fraunces',serif;font-size:.92rem;color:var(--gold);flex:1;}
  .picker-close{background:var(--card);border:1px solid var(--border);color:var(--text-muted);width:30px;height:30px;border-radius:7px;cursor:pointer;font-size:1rem;}
  .picker-close:hover{color:var(--red);}
  .picker-search{padding:12px 16px;border-bottom:1px solid var(--border);}
  .picker-search input{width:100%;background:var(--slot);border:1px solid var(--border);border-radius:8px;color:var(--text);padding:10px 12px;font-size:.85rem;outline:none;}
  .picker-search input:focus{border-color:var(--gold);}

  /* === PERBAIKAN TIER PILL === */
  .picker-tiers{
    display:flex;align-items:center;gap:6px;padding:10px 16px 12px;
    overflow-x:auto;overflow-y:hidden;border-bottom:1px solid var(--border);
    scrollbar-width:none;-ms-overflow-style:none;flex-shrink:0;min-height:48px;
  }
  .picker-tiers::-webkit-scrollbar{display:none;height:0;}
  .tier-pill{
    flex-shrink:0;font-family:'JetBrains Mono',monospace;font-size:.72rem;font-weight:600;
    color:var(--text-muted);background:var(--card);border:1px solid var(--border);
    padding:8px 14px;border-radius:999px;cursor:pointer;white-space:nowrap;line-height:1;
    transition:background .15s, color .15s, border-color .15s;
  }
  .tier-pill.active{background:var(--gold);color:#1a1208;border-color:var(--gold);box-shadow:0 2px 6px rgba(217,166,83,0.35);}
  /* === END PERBAIKAN === */

  .picker-list{list-style:none;overflow-y:auto;flex:1;padding:8px 10px 16px;}
  .picker-item{display:flex;align-items:center;gap:12px;padding:10px;border-radius:10px;cursor:pointer;transition:background .15s;}
  .picker-item:hover{background:var(--card);}
  .picker-icon{width:42px;height:42px;border-radius:8px;background:var(--slot);border:1px solid var(--border-soft);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;}
  .picker-icon img{width:100%;height:100%;object-fit:contain;}
  .picker-icon .fallback{font-family:'Fraunces',serif;font-weight:700;color:var(--gold);font-size:.95rem;}
  .picker-info{flex:1;min-width:0;}
  .picker-name{font-size:.86rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
  .picker-sub{font-size:.72rem;color:var(--text-muted);margin-top:2px;}
  .picker-price{font-family:'JetBrains Mono',monospace;font-size:.78rem;color:var(--gold);flex-shrink:0;}
  .picker-empty{text-align:center;color:var(--text-muted);font-size:.85rem;padding:30px 10px;}

  @media (max-width:480px){.summary-grid{grid-template-columns:1fr 1fr;}.inv-sub{display:none;}}
</style>

<div class="fish-wrap">
  <div class="fish-header">
    <h1>🎣 Kalkulator Mancing</h1>
    <p>Albion Online &middot; Bandingkan untung jual ikan langsung vs dicincang</p>
  </div>

  <div class="price-bar">
    <label>🥩 Harga Daging Cincang (T1) per unit</label>
    <input type="number" id="hargaCincang" value="336" min="1">
    <span>silver</span>
  </div>

  <button class="btn-add-fish" id="btnOpenPicker">＋ Tambah Ikan</button>

  <div class="inv-header">
    <div><span class="inv-title">INVENTORY</span><span class="inv-count" id="invCount">(0)</span></div>
    <button class="btn-toggle" id="btnToggleInv"><span id="toggleIcon">👁</span> <span id="toggleLabel">Sembunyikan</span></button>
  </div>

  <div id="invContainer">
    <div class="inv-empty" id="invEmpty">Belum ada ikan. Klik "Tambah Ikan" buat mulai.</div>
    <ul class="inv-list" id="invList"></ul>
  </div>

  <div class="actions">
    <button class="btn btn-reset" onclick="resetAll()">↺ Reset</button>
    <button class="btn btn-calc" onclick="hitung()">⚖ Hitung Sekarang</button>
  </div>

  <div id="hasil">
    <div class="hasil-header">📊 Hasil Analisis</div>
    <div class="summary-grid" id="summaryGrid"></div>
    <ul class="res-list" id="resList"></ul>
  </div>
</div>

<div class="modal-backdrop" id="modalBackdrop">
  <div class="picker">
    <div class="picker-head">
      <h2>Pilih Ikan</h2>
      <button class="picker-close" id="btnClosePicker">✕</button>
    </div>
    <div class="picker-search">
      <input type="text" id="searchInput" placeholder="Cari nama ikan...">
    </div>
    <div class="picker-tiers" id="tierPills"></div>
    <ul class="picker-list" id="pickerList"></ul>
  </div>
</div>

<script>
const IKAN_DB = [
  {uid:"T1_FISH_FRESHWATER_ALL_COMMON", id:"Rud Biasa", tier:"I", potong:1, def:273},
  {uid:"T1_FISH_SALTWATER_ALL_COMMON", id:"Haring Biasa", tier:"I", potong:1, def:456},
  {uid:"T2_FISH_FRESHWATER_ALL_COMMON", id:"Ikan Mas Bergaris", tier:"II", potong:2, def:404},
  {uid:"T2_FISH_SALTWATER_ALL_COMMON", id:"Ikan Makerel Bergaris", tier:"II", potong:2, def:564},
  {uid:"T3_FISH_FRESHWATER_AVALON_RARE", id:"Kakap Putih Albion", tier:"III", potong:3, def:932},
  {uid:"T3_FISH_SALTWATER_ALL_COMMON", id:"Plaice Tepi Laut Datar", tier:"III", potong:3, def:786},
  {uid:"T3_FISH_FRESHWATER_FOREST_RARE", id:"Belut Sungai Hijau", tier:"III", potong:10, def:3081},
  {uid:"T3_FISH_FRESHWATER_STEPPE_RARE", id:"Kepiting Hilir", tier:"III", potong:10, def:3069},
  {uid:"T3_FISH_FRESHWATER_HIGHLANDS_RARE", id:"Pengintai Aliran Berbatu", tier:"III", potong:10, def:3067},
  {uid:"T3_FISH_FRESHWATER_MOUNTAIN_RARE", id:"Mata Dingin Dataran Tinggi", tier:"III", potong:10, def:3095},
  {uid:"T3_FISH_FRESHWATER_SWAMP_RARE", id:"Kerang Hijau", tier:"III", potong:10, def:3060},
  {uid:"T3_FISH_SALTWATER_ALL_RARE", id:"Cumi-Cumi Laut Dangkal", tier:"III", potong:10, def:3088},
  {uid:"T4_FISH_FRESHWATER_ALL_COMMON", id:"Cucut Sisik Biru", tier:"IV", potong:4, def:1297},
  {uid:"T4_FISH_SALTWATER_ALL_COMMON", id:"Kod Sisik Biru", tier:"IV", potong:4, def:1205},
  {uid:"T5_FISH_FRESHWATER_ALL_COMMON", id:"Ikan Trout Berbintik", tier:"V", potong:6, def:1930},
  {uid:"T5_FISH_SALTWATER_ALL_COMMON", id:"Ikan Serigala Berbintik", tier:"V", potong:6, def:1802},
  {uid:"T5_FISH_FRESHWATER_FOREST_RARE", id:"Belut Mata Air Merah", tier:"V", potong:20, def:6149},
  {uid:"T5_FISH_FRESHWATER_STEPPE_RARE", id:"Kepiting Sungai Kering", tier:"V", potong:20, def:6117},
  {uid:"T5_FISH_FRESHWATER_AVALON_RARE", id:"Kakap Kabut Bening", tier:"V", potong:20, def:6214},
  {uid:"T5_FISH_FRESHWATER_HIGHLANDS_RARE", id:"Pengintai Aliran Deras", tier:"V", potong:20, def:6137},
  {uid:"T5_FISH_FRESHWATER_MOUNTAIN_RARE", id:"Ikan Buta Pegunungan", tier:"V", potong:20, def:6154},
  {uid:"T5_FISH_FRESHWATER_SWAMP_RARE", id:"Kerang Air Keruh", tier:"V", potong:20, def:6083},
  {uid:"T5_FISH_SALTWATER_ALL_RARE", id:"Gurita Tengah Laut", tier:"V", potong:20, def:6235},
  {uid:"T6_FISH_FRESHWATER_ALL_COMMON", id:"Zander Sisik Cerah", tier:"VI", potong:8, def:2596},
  {uid:"T6_FISH_SALTWATER_ALL_COMMON", id:"Salmon Sirip Kuat", tier:"VI", potong:8, def:2624},
  {uid:"T7_FISH_FRESHWATER_ALL_COMMON", id:"Lele Mulut Menjuntai", tier:"VII", potong:10, def:3441},
  {uid:"T7_FISH_SALTWATER_ALL_COMMON", id:"Tuna Sirip Biru", tier:"VII", potong:10, def:3332},
  {uid:"T7_FISH_FRESHWATER_FOREST_RARE", id:"Belut Sungai Mati", tier:"VII", potong:30, def:60654},
  {uid:"T7_FISH_FRESHWATER_STEPPE_RARE", id:"Kepiting Lubang Pasir", tier:"VII", potong:30, def:22203},
  {uid:"T7_FISH_FRESHWATER_HIGHLANDS_RARE", id:"Pengintai Jeram Guntur", tier:"VII", potong:30, def:11553},
  {uid:"T7_FISH_FRESHWATER_MOUNTAIN_RARE", id:"Ikan Buta Puncak Es", tier:"VII", potong:30, def:9594},
  {uid:"T7_FISH_FRESHWATER_SWAMP_RARE", id:"Kerang Rawa Hitam", tier:"VII", potong:30, def:9196},
  {uid:"T7_FISH_SALTWATER_ALL_RARE", id:"Kraken Laut Dalam", tier:"VII", potong:30, def:28119},
  {uid:"T7_FISH_FRESHWATER_AVALON_RARE", id:"Kakap Kabut Murni", tier:"VII", potong:30, def:80600},
  {uid:"T8_FISH_FRESHWATER_ALL_COMMON", id:"Sturgeon Sungai", tier:"VIII", potong:14, def:7212},
  {uid:"T8_FISH_SALTWATER_ALL_COMMON", id:"Todak Sisik Besi", tier:"VIII", potong:14, def:8022},
  {uid:"T8_FISH_SALTWATER_ALL_BOSS_SHARK", id:"Hiu", tier:"VIII", potong:200, def:99000},
];
const TIERS = ["I","II","III","IV","V","VI","VII","VIII"];

function renderUrl(uid, size=84){ return `https://render.albiononline.com/v1/item/${uid}.png?size=${size}`; }
function iconHtml(uid, name){
  return `<img src="${renderUrl(uid)}" alt="${name}" loading="lazy"
    onerror="this.style.display='none';this.parentElement.querySelector('.fallback')?.classList.remove('hidden')||this.parentElement.insertAdjacentHTML('beforeend','<span class=\\'fallback\\'>${name.charAt(0)}</span>')">`;
}

let inventory = [];
let rowSeq = 0;
let invVisible = true;
let activeTier = 'ALL';

const modalBackdrop = document.getElementById('modalBackdrop');
document.getElementById('btnOpenPicker').onclick = () => { modalBackdrop.classList.add('open'); renderPickerList(); document.getElementById('searchInput').focus(); };
document.getElementById('btnClosePicker').onclick = () => modalBackdrop.classList.remove('open');
modalBackdrop.addEventListener('click', e => { if (e.target === modalBackdrop) modalBackdrop.classList.remove('open'); });

function buildTierPills(){
  const wrap = document.getElementById('tierPills');
  wrap.innerHTML = `<button class="tier-pill active" data-tier="ALL">Semua</button>` +
    TIERS.map(tr => `<button class="tier-pill" data-tier="${tr}">T${TIERS.indexOf(tr)+1}</button>`).join('');
  wrap.querySelectorAll('.tier-pill').forEach(btn => {
    btn.onclick = () => {
      activeTier = btn.dataset.tier;
      wrap.querySelectorAll('.tier-pill').forEach(b => b.classList.toggle('active', b === btn));
      renderPickerList();
    };
  });
}

function renderPickerList(){
  const q = document.getElementById('searchInput').value.trim().toLowerCase();
  const list = document.getElementById('pickerList');
  const filtered = IKAN_DB.filter(i => (activeTier === 'ALL' || i.tier === activeTier) && i.id.toLowerCase().includes(q));
  if (!filtered.length){ list.innerHTML = `<li class="picker-empty">Ikan tidak ditemukan.</li>`; return; }
  list.innerHTML = filtered.map(i => `
    <li class="picker-item" data-uid="${i.uid}">
      <div class="picker-icon">${iconHtml(i.uid, i.id)}</div>
      <div class="picker-info">
        <div class="picker-name">${i.id}</div>
        <div class="picker-sub">Tier ${i.tier} &middot; ${i.potong} ptg cincang</div>
      </div>
      <div class="picker-price">${i.def.toLocaleString('id-ID')}</div>
    </li>`).join('');
  list.querySelectorAll('.picker-item').forEach(li => { li.onclick = () => addToInventory(li.dataset.uid); });
}
document.getElementById('searchInput').addEventListener('input', renderPickerList);

function addToInventory(uid){
  const existing = inventory.find(it => it.uid === uid);
  if (existing){ existing.qty += 1; }
  else { const data = IKAN_DB.find(i => i.uid === uid); inventory.push({ id: ++rowSeq, uid, qty: 1, price: data.def }); }
  renderInventory();
}

function renderInventory(){
  document.getElementById('invCount').textContent = `(${inventory.length})`;
  document.getElementById('invEmpty').style.display = inventory.length ? 'none' : 'block';
  const list = document.getElementById('invList');
  list.innerHTML = inventory.map(it => {
    const d = IKAN_DB.find(i => i.uid === it.uid);
    const total = it.qty * it.price;
    return `
    <li class="inv-row" data-id="${it.id}">
      <div class="inv-icon">${iconHtml(d.uid, d.id)}<span class="inv-tier">T${TIERS.indexOf(d.tier)+1}</span></div>
      <div class="inv-info"><div class="inv-name">${d.id}</div><div class="inv-sub">${d.potong} ptg cincang/ekor</div></div>
      <div class="qty-stepper">
        <button onclick="stepQty(${it.id},-1)">−</button>
        <input type="number" value="${it.qty}" min="1" oninput="setQty(${it.id}, this.value)">
        <button onclick="stepQty(${it.id},1)">+</button>
      </div>
      <div class="inv-price"><input type="number" value="${it.price}" min="0" oninput="setPrice(${it.id}, this.value)"></div>
      <div class="inv-total">${total.toLocaleString('id-ID')}</div>
      <button class="btn-remove" onclick="removeItem(${it.id})">✕</button>
    </li>`;
  }).join('');
}

function stepQty(id, delta){ const it = inventory.find(i => i.id === id); if (it){ it.qty = Math.max(1, it.qty + delta); const row = document.querySelector(`.inv-row[data-id="${id}"]`); if (row) row.querySelector('.qty-stepper input').value = it.qty; updateRowTotal(id); } }
function setQty(id, val){ const it = inventory.find(i => i.id === id); if (it){ it.qty = Math.max(1, parseInt(val) || 1); updateRowTotal(id); } }
function setPrice(id, val){ const it = inventory.find(i => i.id === id); if (it){ it.price = Math.max(0, parseFloat(val) || 0); updateRowTotal(id); } }
function updateRowTotal(id){ const it = inventory.find(i => i.id === id); const row = document.querySelector(`.inv-row[data-id="${id}"]`); if (it && row){ row.querySelector('.inv-total').textContent = (it.qty * it.price).toLocaleString('id-ID'); } }
function removeItem(id){ inventory = inventory.filter(i => i.id !== id); renderInventory(); }

document.getElementById('btnToggleInv').onclick = () => {
  invVisible = !invVisible;
  document.getElementById('invContainer').style.display = invVisible ? 'block' : 'none';
  document.getElementById('toggleLabel').textContent = invVisible ? 'Sembunyikan' : 'Tampilkan';
  document.getElementById('toggleIcon').textContent = invVisible ? '👁' : '🙈';
};

function resetAll(){ inventory = []; renderInventory(); document.getElementById('hasil').style.display = 'none'; }
function fmt(n){ return Math.round(n).toLocaleString('id-ID'); }

function hitung(){
  if (!inventory.length){ alert('Tambahkan minimal satu ikan dulu.'); return; }
  const hargaCincang = parseFloat(document.getElementById('hargaCincang').value) || 336;
  let totalJualAll = 0, totalCincangAll = 0;
  const items = inventory.map(it => {
    const d = IKAN_DB.find(i => i.uid === it.uid);
    const totalJual = it.qty * it.price;
    const totalPotongan = d.potong * it.qty;
    const totalCincang = totalPotongan * hargaCincang;
    const selisih = totalJual - totalCincang;
    totalJualAll += totalJual; totalCincangAll += totalCincang;
    return { d, qty: it.qty, totalJual, totalPotongan, totalCincang, selisih };
  });
  const optimalTotal = items.reduce((s, it) => s + Math.max(it.totalJual, it.totalCincang), 0);

  document.getElementById('summaryGrid').innerHTML = `
    <div class="summary-card"><div class="s-label">Semua Dijual</div><div class="s-val neutral">${fmt(totalJualAll)}</div></div>
    <div class="summary-card"><div class="s-label">Semua Dicincang</div><div class="s-val accent">${fmt(totalCincangAll)}</div></div>
    <div class="summary-card"><div class="s-label">Optimal</div><div class="s-val profit">${fmt(optimalTotal)}</div></div>`;

  document.getElementById('resList').innerHTML = items.map(it => {
    const rec = it.selisih >= 0 ? `<span class="badge badge-sell">✔ Jual</span>` : `<span class="badge badge-cut">✂ Cincang</span>`;
    return `
    <li class="res-row">
      <div class="inv-icon">${iconHtml(it.d.uid, it.d.id)}</div>
      <div class="res-info"><div class="res-name">${it.d.id} <span style="color:var(--text-muted);font-weight:400;">×${it.qty}</span></div>
      <div class="res-detail">Jual ${fmt(it.totalJual)} &middot; Cincang ${fmt(it.totalCincang)} (${fmt(it.totalPotongan)} ptg)</div></div>
      ${rec}
      <div class="res-diff ${it.selisih >= 0 ? 'diff-pos' : 'diff-neg'}">${it.selisih >= 0 ? '+' : ''}${fmt(it.selisih)}</div>
    </li>`;
  }).join('');

  const hasil = document.getElementById('hasil');
  hasil.style.display = 'block';
  hasil.scrollIntoView({ behavior:'smooth', block:'start' });
  catatAktivitas();
}

async function catatAktivitas(){
  const loggedIn = @json(auth()->check());
  if (!loggedIn) return;
  try{
    await fetch('/api/catat-aktivitas', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
      },
      body: JSON.stringify({ type: 'fishing' })
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

buildTierPills();
renderInventory();
</script>

<x-comments page="fishing" />
@endsection