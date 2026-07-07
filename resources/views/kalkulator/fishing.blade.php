@extends('layouts.app')

@section('title', 'Kalkulator Mancing - HarunGamingTools')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
<style>
.fish-wrap{
  --bd:      #6b4f1a;
  --gold:    #f0c040;
  --gold-dk: #b8860b;
  --lt:      #dcc08a;
  --dim:     #a08040;
  --sbg:     #1a1208;
  --sbd:     #4a3510;
  --teal:    #7fd8a8;
  --red:     #f0806a;
  background: #0d0a05;
  color: var(--lt);
  font-family: 'Crimson Text', Georgia, serif;
  margin: -48px -24px;
  padding: 20px 0 60px;
}
.fish-wrap *{ box-sizing:border-box; }
.fish-wrap .app{ max-width:900px; margin:0 auto; padding:0 12px; display:flex; flex-direction:column; gap:10px; }

/* PANEL */
.fish-wrap .panel{ background:linear-gradient(180deg,#2e2210,#1e1608); border:2px solid var(--bd); border-radius:4px; box-shadow:0 4px 24px rgba(0,0,0,.7); overflow:hidden; }
.fish-wrap .ph{ background:linear-gradient(180deg,#3d2e15,#2a1f0e); border-bottom:1px solid var(--bd); padding:9px 14px; display:flex; align-items:center; gap:8px; }
.fish-wrap .ph-title{ font-family:'Cinzel',serif; font-size:13px; color:var(--gold); letter-spacing:1px; text-transform:uppercase; flex:1; }
.fish-wrap .api-status{ font-size:10px; font-family:'Cinzel',serif; color:var(--dim); }
.fish-wrap .api-status.loading{ color:var(--gold); }
.fish-wrap .api-status.ok{ color:#6b8; }
.fish-wrap .api-status.err{ color:#f86; }

/* HEADER SEARCH (ala Refine) */
.fish-wrap .header-search{ margin-left:auto; background:linear-gradient(180deg,#1a1208,#110e05); border:1px solid var(--sbd); border-radius:3px; color:var(--lt); font-family:'Crimson Text',serif; font-size:13px; padding:6px 10px; outline:none; transition:border-color .15s; width:170px; }
.fish-wrap .header-search:focus{ border-color:var(--gold-dk); }
.fish-wrap .header-search::placeholder{ color:var(--dim); }

/* FILTER BAR (ala Refine — tombol + dropdown popover) */
.fish-wrap .flt-bar{ background:linear-gradient(180deg,#251a08,#1a1005); border-bottom:1px solid var(--bd); padding:10px 12px; display:flex; gap:8px; flex-wrap:nowrap; overflow-x:auto; overflow-y:visible; position:relative; }
.fish-wrap .flt-wrap{ position:relative; }
.fish-wrap .flt-btn{ display:flex; align-items:center; gap:6px; background:linear-gradient(180deg,#c8a84a,#a07828); border:1px solid #8b6820; border-radius:3px; color:#2a1800; font-family:'Cinzel',serif; font-size:12px; font-weight:700; letter-spacing:.5px; padding:7px 12px; cursor:pointer; user-select:none; white-space:nowrap; transition:all .1s; justify-content:space-between; }
.fish-wrap .flt-btn:hover{ background:linear-gradient(180deg,#dabb5a,#b88838); border-color:var(--gold); }
.fish-wrap .flt-btn.open{ background:linear-gradient(180deg,#b89030,#907020); border-color:var(--gold); box-shadow:0 0 8px rgba(240,192,64,.3); }
.fish-wrap .flt-btn .flt-label{ flex:1; text-align:left; }
.fish-wrap .flt-btn .flt-val{ font-size:10px; opacity:.75; max-width:90px; overflow:hidden; text-overflow:ellipsis; }
.fish-wrap .flt-btn .flt-arrow{ font-size:8px; opacity:.7; margin-left:2px; transition:transform .15s; }
.fish-wrap .flt-btn.open .flt-arrow{ transform:rotate(180deg); }

.fish-wrap .drop-wrap{ position:fixed; z-index:9999; display:none; gap:2px; filter:drop-shadow(0 6px 20px rgba(0,0,0,.85)); }
.fish-wrap .drop-wrap.show{ display:flex; }
.fish-wrap .drop-col{ min-width:175px; max-height:370px; overflow-y:auto; background:linear-gradient(180deg,#e8cf88,#d4b468); border:1px solid #8b6820; border-radius:3px; padding:4px; display:flex; flex-direction:column; gap:2px; }
.fish-wrap .drop-item{ display:flex; align-items:center; justify-content:space-between; padding:8px 10px; border-radius:2px; background:transparent; border:1px solid transparent; cursor:pointer; font-family:'Crimson Text',serif; font-size:14px; font-weight:600; color:#2a1800; transition:all .08s; white-space:nowrap; }
.fish-wrap .drop-item:hover{ background:linear-gradient(180deg,#f2dc9a,#e2c878); border-color:#a07828; }
.fish-wrap .drop-item.active{ background:linear-gradient(180deg,#b88a28,#906818); border-color:#7a5010; color:#fff8e0; }
.fish-wrap .drop-item .di-arrow{ font-size:9px; color:#6b4f1a; margin-left:8px; flex-shrink:0; }
.fish-wrap .drop-item.active .di-arrow{ color:#ffe090; }

/* ITEM LIST (polos — icon 48px, nama, harga di kanan) */
.fish-wrap .item-list{ max-height:340px; overflow-y:auto; }
.fish-wrap .item-row{ display:flex; align-items:center; gap:10px; padding:8px 10px; border-bottom:1px solid rgba(107,79,26,.3); cursor:pointer; transition:background .1s; }
.fish-wrap .item-row:hover{ background:rgba(61,46,21,.5); }
.fish-wrap .item-icon{ width:48px; height:48px; border:1px solid var(--sbd); border-radius:3px; background:var(--sbg); display:block; object-fit:contain; flex-shrink:0; }
.fish-wrap .item-name{ font-family:'Crimson Text',serif; font-size:15px; color:var(--lt); font-weight:600; flex:1; }
.fish-wrap .item-price{ font-size:12px; color:var(--gold); font-family:'Cinzel',serif; min-width:55px; text-align:right; }
.fish-wrap .empty-inv{ text-align:center; padding:18px; color:var(--dim); font-style:italic; font-size:13px; }

/* BOTTOM BAR */
.fish-wrap .bot-bar{ display:flex; gap:6px; align-items:center; padding:8px 12px; border-top:1px solid var(--bd); background:linear-gradient(180deg,#251a08,#1a1005); flex-wrap:wrap; }
.fish-wrap .ret-wrap{ display:flex; align-items:center; gap:5px; background:var(--sbg); border:1px solid var(--sbd); border-radius:3px; padding:5px 9px; }
.fish-wrap .ret-wrap label{ font-size:11px; color:var(--dim); white-space:nowrap; }
.fish-wrap .ret-inp{ width:60px; background:transparent; border:none; color:var(--gold); font-size:14px; font-weight:600; text-align:right; outline:none; }
.fish-wrap .inv-btn{ background:linear-gradient(180deg,#4a3818,#2e2210); border:1px solid var(--bd); border-radius:3px; color:var(--lt); font-size:12px; padding:7px 12px; cursor:pointer; white-space:nowrap; transition:all .15s; flex:1; }
.fish-wrap .inv-btn:hover, .fish-wrap .inv-btn.active{ border-color:var(--gold); color:var(--gold); }
.fish-wrap .reset-btn{ background:linear-gradient(180deg,#6b1a1a,#4a1010); border:1px solid #8b3030; border-radius:3px; color:#f0c0c0; font-size:12px; padding:7px 12px; cursor:pointer; white-space:nowrap; }
.fish-wrap .reset-btn:hover{ border-color:#c04040; }

/* INVENTORY */
.fish-wrap .inv-section{ padding:10px 12px; display:none; border-top:1px solid var(--bd); }
.fish-wrap .inv-section.show{ display:block; }
.fish-wrap .inv-lbl{ font-family:'Cinzel',serif; font-size:10px; color:var(--dim); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px; }
.fish-wrap .inv-grid{ display:grid; grid-template-columns:repeat(5,1fr); gap:4px; }
.fish-wrap .slot{ aspect-ratio:1; background:var(--sbg); border:1px solid var(--sbd); border-radius:3px; position:relative; cursor:pointer; overflow:hidden; }
.fish-wrap .slot.filled:hover{ border-color:var(--gold); }
.fish-wrap .slot img{ width:100%; height:100%; object-fit:contain; display:block; }
.fish-wrap .slot .st{ position:absolute; top:1px; left:1px; font-family:'Cinzel',serif; font-size:7px; font-weight:700; color:#fff; background:rgba(0,0,0,.7); padding:0 2px; border-radius:1px; }
.fish-wrap .slot .sq{ position:absolute; bottom:1px; right:2px; font-size:9px; font-weight:700; color:#fff; text-shadow:0 1px 2px #000; }
.fish-wrap .slot .sh{ position:absolute; top:1px; right:2px; font-size:7px; color:var(--gold); font-family:'Cinzel',serif; text-shadow:0 1px 2px #000; }

/* PERBANDINGAN (preview jual vs cincang vs optimal) */
.fish-wrap .preview-section{ padding:10px 12px; display:none; border-top:1px solid var(--bd); }
.fish-wrap .preview-section.show{ display:block; }
.fish-wrap .summary-grid{ display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; }
.fish-wrap .summary-card{ background:var(--sbg); border:1px solid var(--sbd); border-radius:4px; padding:12px 6px; text-align:center; }
.fish-wrap .s-label{ font-family:'Cinzel',serif; font-size:.6rem; color:var(--dim); text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px; }
.fish-wrap .s-val{ font-family:'Cinzel',serif; font-size:.9rem; font-weight:700; }
.fish-wrap .s-val.neutral{ color:var(--lt); }
.fish-wrap .s-val.accent{ color:var(--gold); }
.fish-wrap .s-val.profit{ color:var(--teal); }

/* COIN FOOTER (modal vs nilai sekarang vs profit) */
.fish-wrap .coin-footer{ display:none; flex-direction:column; padding:10px 12px; background:rgba(0,0,0,.3); border-top:1px solid var(--bd); gap:8px; }
.fish-wrap .coin-footer.show{ display:flex; }
.fish-wrap .coin-row{ display:flex; justify-content:space-between; align-items:center; }
.fish-wrap .coin-side{ display:flex; align-items:center; gap:8px; }
.fish-wrap .coin-icon{ width:26px; height:26px; border-radius:50%; background:radial-gradient(circle at 35% 35%,#888,#333); display:flex; align-items:center; justify-content:center; font-size:13px; border:2px solid #555; flex-shrink:0; }
.fish-wrap .coin-lbl{ font-size:10px; color:var(--dim); display:block; }
.fish-wrap .coin-val{ font-family:'Cinzel',serif; font-size:15px; font-weight:700; color:var(--gold); }
.fish-wrap .profit-row{ display:flex; justify-content:flex-end; padding-top:6px; border-top:1px solid rgba(107,79,26,.4); }
.fish-wrap .profit-item{ display:flex; flex-direction:column; align-items:flex-end; }
.fish-wrap .profit-lbl{ font-size:10px; color:var(--dim); }
.fish-wrap .profit-val{ font-family:'Cinzel',serif; font-size:15px; font-weight:700; }
.fish-wrap .profit-val.pos{ color:#6f8; }
.fish-wrap .profit-val.neg{ color:#f86; }

/* PROSES BUTTON */
.fish-wrap .hitung-wrap{ padding:12px; border-top:1px solid var(--bd); background:rgba(0,0,0,.2); }
.fish-wrap .btn-hitung{ width:100%; background:linear-gradient(180deg,#8b4a00,#5a2e00); border:1px solid #c06010; border-radius:3px; color:var(--gold); font-family:'Cinzel',serif; font-size:13px; font-weight:700; letter-spacing:1px; padding:12px; cursor:pointer; text-transform:uppercase; }
.fish-wrap .btn-hitung:hover{ background:linear-gradient(180deg,#a05800,#703800); }

/* POPUP */
.fish-wrap .overlay{ position:fixed; inset:0; background:rgba(0,0,0,.78); z-index:100; display:none; align-items:center; justify-content:center; }
.fish-wrap .overlay.show{ display:flex; }
.fish-wrap .popup{ background:linear-gradient(180deg,#3d2e15,#2a1f0e); border:2px solid var(--bd); border-radius:4px; box-shadow:0 8px 40px rgba(0,0,0,.9); width:310px; max-width:95vw; overflow:hidden; }
.fish-wrap .pop-head{ display:flex; gap:10px; padding:12px; background:linear-gradient(180deg,#4a3818,#2e2210); border-bottom:1px solid var(--bd); align-items:flex-start; }
.fish-wrap .pop-icon{ width:52px; height:52px; border:1px solid var(--sbd); border-radius:3px; background:var(--sbg); flex-shrink:0; object-fit:contain; }
.fish-wrap .pop-name{ font-family:'Cinzel',serif; font-size:14px; color:var(--gold); margin-bottom:3px; }
.fish-wrap .pop-desc{ font-size:11px; color:var(--dim); font-style:italic; }
.fish-wrap .pop-close{ margin-left:auto; background:linear-gradient(180deg,#6b1a1a,#4a1010); border:1px solid #8b3030; border-radius:50%; color:#f0c0c0; width:24px; height:24px; font-size:13px; cursor:pointer; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.fish-wrap .pop-body{ padding:12px; display:flex; flex-direction:column; gap:10px; }
.fish-wrap .pop-field label{ display:block; font-family:'Cinzel',serif; font-size:10px; color:var(--dim); text-transform:uppercase; letter-spacing:.5px; margin-bottom:5px; }
.fish-wrap .pop-field input[type=number]{ width:100%; background:var(--sbg); border:1px solid var(--sbd); border-radius:3px; color:var(--lt); font-size:14px; padding:6px 9px; outline:none; }
.fish-wrap .pop-field input[type=number]:focus{ border-color:var(--gold); }
.fish-wrap .slider-wrap{ display:flex; align-items:center; gap:7px; }
.fish-wrap .slider-wrap input[type=range]{ flex:1; accent-color:var(--gold); }
.fish-wrap .slider-val{ background:var(--sbg); border:1px solid var(--sbd); border-radius:3px; color:var(--gold); font-family:'Cinzel',serif; font-size:13px; font-weight:700; width:60px; text-align:center; padding:4px 5px; outline:none; }
.fish-wrap .pop-btn-row{ display:flex; gap:7px; }
.fish-wrap .btn-add{ flex:1; background:linear-gradient(180deg,#4a6b1a,#2e4210); border:1px solid #6b8b30; border-radius:3px; color:#d0f0a0; font-family:'Cinzel',serif; font-size:12px; font-weight:700; letter-spacing:1px; padding:10px; cursor:pointer; text-transform:uppercase; }
.fish-wrap .btn-add:hover{ border-color:#8bc040; color:#fff; }
.fish-wrap .btn-del{ background:linear-gradient(180deg,#6b1a1a,#4a1010); border:1px solid #8b3030; border-radius:3px; color:#f0c0c0; font-family:'Cinzel',serif; font-size:12px; font-weight:700; padding:10px 13px; cursor:pointer; }
.fish-wrap .btn-del:hover{ border-color:#c04040; }

.fish-wrap .toast{ position:fixed; bottom:20px; left:50%; transform:translateX(-50%) translateY(60px); background:#3d2e15; border:1px solid var(--gold-dk); border-radius:3px; color:var(--gold); font-family:'Cinzel',serif; font-size:11px; padding:7px 14px; transition:transform .25s; z-index:200; white-space:nowrap; }
.fish-wrap .toast.show{ transform:translateX(-50%) translateY(0); }
</style>

<div class="fish-wrap">
  <div class="app">

    <div class="panel">
      <div class="ph">
        <span>🎣</span>
        <span class="ph-title">Kalkulator Mancing</span>
        <input type="text" class="header-search" id="searchInputFish" placeholder="Cari nama ikan..." oninput="onSearchFish()">
        <span class="api-status" id="apiStatus"></span>
      </div>

      <div class="flt-bar" id="fltBarFish">
        <!-- TIER -->
        <div class="flt-wrap">
          <div class="flt-btn" id="btnTier" onclick="toggleDrop('tier')">
            <span class="flt-label" id="lblTier">Tier</span>
            <span class="flt-val" id="valTier" style="display:none"></span>
            <span class="flt-arrow">▼</span>
          </div>
          <div class="drop-wrap" id="dropTier">
            <div class="drop-col" id="colTier"></div>
          </div>
        </div>
        <!-- KOTA -->
        <div class="flt-wrap">
          <div class="flt-btn" id="btnKota" onclick="toggleDrop('kota')">
            <span class="flt-label" id="lblKota" style="display:none">Kota</span>
            <span class="flt-val" id="valKota">Thetford</span>
            <span class="flt-arrow">▼</span>
          </div>
          <div class="drop-wrap" id="dropKota">
            <div class="drop-col" id="colKota"></div>
          </div>
        </div>
      </div>

      <div class="item-list" id="itemList"></div>

      <div class="bot-bar">
        <div class="ret-wrap">
          <label>🥩 Cincang (T1)</label>
          <input class="ret-inp" type="number" id="hargaCincang" value="336" min="1" onchange="renderInventory()">
        </div>
        <button class="inv-btn" id="invBtn" onclick="toggleInv()">📦 Inventory (<span id="invCount">0</span>)</button>
        <button class="reset-btn" onclick="doReset()">🗑 Reset</button>
      </div>

      <div class="inv-section" id="invSection">
        <div class="inv-lbl">📦 Inventory</div>
        <div class="inv-grid" id="invGrid"></div>
      </div>

      <div class="preview-section" id="previewSection">
        <div class="inv-lbl">📊 Perbandingan (kalau diproses sekarang)</div>
        <div class="summary-grid" id="summaryGrid"></div>
      </div>

      <div class="hitung-wrap">
        <button class="btn-hitung" onclick="prosesCincang()">🔪 Proses Cincang</button>
      </div>

      <div class="coin-footer" id="coinFooter">
        <div class="coin-row">
          <div class="coin-side">
            <div class="coin-icon">🪙</div>
            <div>
              <span class="coin-lbl">Modal (Ikan Sebelum Diproses)</span>
              <span class="coin-val" id="coinModal">0</span>
            </div>
          </div>
          <div class="coin-side">
            <div>
              <span class="coin-lbl" style="text-align:right;display:block">Nilai Sekarang</span>
              <span class="coin-val" id="coinNilai">0</span>
            </div>
            <div class="coin-icon">🪙</div>
          </div>
        </div>
        <div class="profit-row">
          <div class="profit-item">
            <span class="profit-lbl">Profit</span>
            <span class="profit-val" id="profitVal">0</span>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<div class="fish-wrap">
  <div class="overlay" id="overlayItem" onclick="closeOverlayOutside(event,'overlayItem')">
    <div class="popup">
      <div class="pop-head">
        <img class="pop-icon" id="popIcon" src="" alt="">
        <div>
          <div class="pop-name" id="popName">—</div>
          <div class="pop-desc" id="popDesc">—</div>
        </div>
        <button class="pop-close" onclick="closeOverlay('overlayItem')">✕</button>
      </div>
      <div class="pop-body">
        <div class="pop-field">
          <label>Harga per ekor (silver)</label>
          <input type="number" id="popHarga" placeholder="0" min="0">
        </div>
        <div class="pop-field">
          <label>Jumlah ekor</label>
          <div class="slider-wrap">
            <input type="range" id="popSlider" min="1" max="999" value="10" oninput="syncQty('s')">
            <input class="slider-val" type="number" id="popQty" value="10" min="1" max="999999" oninput="syncQty('v')">
          </div>
        </div>
        <div class="pop-btn-row" id="popBtnRow"></div>
      </div>
    </div>
  </div>
  <div class="toast" id="toast"></div>
</div>

<script>
// ===================== DATA =====================
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

// Item hasil cincang — pseudo-item, gak muncul di item-list, cuma nongol di inventory
const DAGING_UID = "T1_FISHCHOPS";
IKAN_DB.push({uid:DAGING_UID, id:"Daging Cincang", tier:"I", potong:0, def:0});

const TIER_COL   = {I:'#ccc', II:'#aaa', III:'#6b8', IV:'#68f', V:'#c8f', VI:'#fa8', VII:'#f64', VIII:'#ff0'};
const FISH_TIERS = ['I','II','III','IV','V','VI','VII','VIII'];
const KOTA_LIST  = ['Caerleon','Bridgewatch','Fort Sterling','Lymhurst','Martlock','Thetford','Brecilien'];

function iconUrl(uid){ return `https://render.albiononline.com/v1/item/${uid}.png?size=64&quality=1`; }
function fmt(n){ return Math.round(n||0).toLocaleString('id-ID'); }
function tierLabel(tier){ return 'T' + (FISH_TIERS.indexOf(tier)+1); }

let inventory = [];      // {uid, qty, harga, manual}
let invShown  = false;
let popupUid  = null;
let popupInvIdx = null;
let priceCache = {};     // uid -> harga
let priceCityCache = {}; // uid -> {harga, kota}
let modalLock = null;    // null = belum pernah proses, number = nilai ikan sebelum proses pertama

// ===================== FILTER STATE =====================
let fTier   = null;      // 'I'..'VIII'
let fKota   = 'Thetford';
let fSearch = '';

// ===================== DROPDOWN HELPERS (ala Refine) =====================
let openDropR = null;
function cap(s){ return s.charAt(0).toUpperCase() + s.slice(1); }

function toggleDrop(name){
  if (openDropR === name) { closeDrop(); return; }
  closeDrop();
  openDropR = name;
  const btn  = document.getElementById('btn'  + cap(name));
  const drop = document.getElementById('drop' + cap(name));
  const rect = btn.getBoundingClientRect();
  drop.style.top  = (rect.bottom + 3) + 'px';
  drop.style.left = rect.left + 'px';
  drop.classList.add('show');
  btn.classList.add('open');
}
function closeDrop(){
  if (!openDropR) return;
  document.getElementById('drop' + cap(openDropR)).classList.remove('show');
  document.getElementById('btn'  + cap(openDropR)).classList.remove('open');
  openDropR = null;
}
document.addEventListener('click', e => {
  if (openDropR && !e.target.closest('.flt-wrap')) closeDrop();
});
function setFilterVal(lblId, valId, value){
  const lbl = document.getElementById(lblId);
  const val = document.getElementById(valId);
  if (value) { lbl.style.display='none'; val.style.display=''; val.textContent=value; }
  else       { lbl.style.display='';     val.style.display='none'; }
}
function makeItem(text, hasArrow, isActive, onClick){
  const el = document.createElement('div');
  el.className = 'drop-item' + (isActive ? ' active' : '');
  el.innerHTML = text + (hasArrow ? '<span class="di-arrow">▶</span>' : '');
  el.addEventListener('click', e => { e.stopPropagation(); onClick(); });
  return el;
}

// --- TIER ---
function buildTierDropFish(){
  const col = document.getElementById('colTier');
  col.innerHTML = '';
  col.appendChild(makeItem('Semua', false, !fTier, () => {
    fTier = null; setFilterVal('lblTier','valTier',null); closeDrop(); filterItems();
  }));
  FISH_TIERS.forEach(t => col.appendChild(makeItem(tierLabel(t), false, fTier === t, () => {
    fTier = t; setFilterVal('lblTier','valTier', tierLabel(t)); closeDrop(); filterItems();
  })));
}

// --- KOTA ---
function buildKotaDropFish(){
  const col = document.getElementById('colKota');
  col.innerHTML = '';
  KOTA_LIST.forEach(k => col.appendChild(makeItem(k, false, fKota === k, () => {
    fKota = k; setFilterVal('lblKota','valKota', k); closeDrop(); onKotaChange();
  })));
}

// --- SEARCH (debounce) ---
let searchTimerFish = null;
function onSearchFish(){
  clearTimeout(searchTimerFish);
  searchTimerFish = setTimeout(() => {
    fSearch = document.getElementById('searchInputFish').value.trim();
    filterItems();
  }, 300);
}

// ===================== FILTER & RENDER LIST =====================
function filterItems(){
  const tier = fTier || '';
  const q    = fSearch.toLowerCase();
  const filtered = IKAN_DB.filter(i => {
    if (i.uid === DAGING_UID) return false; // pseudo-item, gak ditambah manual
    if (tier && i.tier !== tier) return false;
    if (q && !i.id.toLowerCase().includes(q)) return false;
    return true;
  });
  window._fl = filtered;
  const el = document.getElementById('itemList');
  if (!filtered.length){ el.innerHTML = '<div class="empty-inv">Ikan tidak ditemukan.</div>'; return; }
  el.innerHTML = filtered.map((i, idx) => {
    const h = priceCache[i.uid];
    return `<div class="item-row" onclick="openAdd(${idx})">
      <img class="item-icon" src="${iconUrl(i.uid)}" alt="" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2248%22 height=%2248%22><rect width=%2248%22 height=%2248%22 fill=%22%231a1208%22/></svg>'">
      <div class="item-name">${i.id}</div>
      ${h ? `<span class="item-price">${fmt(h)}</span>` : ''}
    </div>`;
  }).join('');
}

// ===================== FETCH HARGA PER KOTA =====================
const FALLBACK_ORDER = ['Bridgewatch','Fort Sterling','Lymhurst','Martlock','Thetford','Caerleon','Brecilien'];

async function fetchBatch(ids, kota){
  const url = `https://west.albion-online-data.com/api/v2/stats/prices/${ids.join(',')}?locations=${encodeURIComponent(kota)}&qualities=1`;
  const res = await fetch(url);
  const data = await res.json();
  const result = {};
  for (const e of data) if (e.sell_price_min > 0) result[e.item_id] = e.sell_price_min;
  return result;
}

async function onKotaChange(){
  const kota = fKota;
  if (!kota) return;
  const st = document.getElementById('apiStatus');
  st.className = 'api-status loading'; st.textContent = '⏳ Mengambil harga...';

  const allKeys = IKAN_DB.filter(i => i.uid !== DAGING_UID).map(i => i.uid);
  priceCache = {}; priceCityCache = {};
  const fallbacks = FALLBACK_ORDER.filter(k => k !== kota);

  try {
    for (let i = 0; i < allKeys.length; i += 200) {
      const batch = allKeys.slice(i, i + 200);
      const result = await fetchBatch(batch, kota);
      for (const [id, harga] of Object.entries(result)) { priceCache[id] = harga; priceCityCache[id] = {harga, kota}; }
    }
    let missing = allKeys.filter(k => !priceCache[k]);
    for (const fbKota of fallbacks) {
      if (!missing.length) break;
      for (let i = 0; i < missing.length; i += 200) {
        const batch = missing.slice(i, i + 200);
        const result = await fetchBatch(batch, fbKota);
        for (const [id, harga] of Object.entries(result)) { priceCache[id] = harga; priceCityCache[id] = {harga, kota: fbKota}; }
      }
      missing = allKeys.filter(k => !priceCache[k]);
    }
    const fetched  = Object.keys(priceCache).length;
    const fallback = Object.values(priceCityCache).filter(v => v.kota !== kota).length;
    st.className = 'api-status ok';
    st.textContent = `✅ ${fetched} harga${fallback > 0 ? ` (${fallback} fallback)` : ''}`;

    for (const inv of inventory) {
      if (inv.uid === DAGING_UID) continue;
      const c = priceCache[inv.uid];
      if (c && c > 0 && !inv.manual) inv.harga = c;
    }
    filterItems(); renderInventory();
  } catch (e) { st.className = 'api-status err'; st.textContent = '⚠️ Gagal fetch'; }
}

// ===================== POPUP TAMBAH / EDIT =====================
function openAdd(idx){
  const i = window._fl[idx];
  popupUid = i.uid; popupInvIdx = null;
  document.getElementById('popIcon').src = iconUrl(i.uid);
  document.getElementById('popName').textContent = i.id;
  document.getElementById('popDesc').textContent = `Tier ${tierLabel(i.tier)} · ${i.potong} ptg cincang/ekor`;
  const cached = priceCache[i.uid] || i.def || 0;
  document.getElementById('popHarga').value = cached;
  document.getElementById('popSlider').value = 10;
  document.getElementById('popQty').value = 10;
  document.getElementById('popSlider').closest('.pop-field').style.display = '';
  document.getElementById('popBtnRow').innerHTML = `<button class="btn-add" onclick="doAdd()">➕ Tambah ke Inventory</button>`;
  openOverlay('overlayItem');
}

function openEdit(i){
  const inv = inventory[i];
  if (!inv) return;
  const d = IKAN_DB.find(x => x.uid === inv.uid);
  popupUid = inv.uid; popupInvIdx = i;
  document.getElementById('popIcon').src = iconUrl(inv.uid);
  document.getElementById('popName').textContent = d.id;
  document.getElementById('popDesc').textContent = inv.uid === DAGING_UID
    ? 'Daging hasil cincang ikan'
    : `Tier ${tierLabel(d.tier)} · ${d.potong} ptg cincang/ekor`;
  document.getElementById('popHarga').value = inv.harga || 0;
  document.getElementById('popSlider').value = Math.min(inv.qty, 999);
  document.getElementById('popQty').value = inv.qty;
  document.getElementById('popSlider').closest('.pop-field').style.display = '';
  document.getElementById('popBtnRow').innerHTML = `
    <button class="btn-add" onclick="doEdit()">💾 Simpan</button>
    <button class="btn-del" onclick="doHapus()">🗑</button>`;
  openOverlay('overlayItem');
}

function syncQty(src){
  const s = document.getElementById('popSlider'), v = document.getElementById('popQty');
  if (src === 's') v.value = s.value; else s.value = Math.min(parseInt(v.value) || 1, 999);
}

function doAdd(){
  if (!popupUid) return;
  const qty = Math.min(parseInt(document.getElementById('popQty').value) || 1, 999999);
  const harga = parseFloat(document.getElementById('popHarga').value) || 0;
  const d = IKAN_DB.find(i => i.uid === popupUid);
  const ex = inventory.findIndex(inv => inv.uid === popupUid);
  if (ex >= 0) {
    inventory[ex].qty = Math.min(inventory[ex].qty + qty, 999999);
    inventory[ex].harga = harga || inventory[ex].harga;
    inventory[ex].manual = true;
    showToast(`📦 ${d.id} → ${inventory[ex].qty}`);
  } else {
    inventory.push({ uid: popupUid, qty, harga, manual: true });
    showToast(`✅ ${d.id} × ${qty}`);
  }
  if (!invShown) toggleInv();
  closeOverlay('overlayItem');
  renderInventory();
}

function doEdit(){
  if (popupInvIdx === null) return;
  inventory[popupInvIdx].qty = Math.min(parseInt(document.getElementById('popQty').value) || 1, 999999);
  inventory[popupInvIdx].harga = parseFloat(document.getElementById('popHarga').value) || 0;
  inventory[popupInvIdx].manual = true;
  closeOverlay('overlayItem');
  renderInventory();
  showToast('✏️ Diperbarui');
}

function doHapus(){
  if (popupInvIdx === null) return;
  const d = IKAN_DB.find(i => i.uid === inventory[popupInvIdx].uid);
  inventory.splice(popupInvIdx, 1);
  closeOverlay('overlayItem');
  renderInventory();
  showToast(`🗑 ${d.id} dihapus`);
}

function doReset(){
  inventory = [];
  modalLock = null;
  renderInventory();
  document.getElementById('coinFooter').classList.remove('show');
  showToast('↺ Direset');
}

// ===================== INVENTORY GRID (stack max 999/slot) =====================
function toggleInv(){
  invShown = !invShown;
  document.getElementById('invSection').classList.toggle('show', invShown);
  document.getElementById('invBtn').classList.toggle('active', invShown);
}

function renderInventory(){
  // Sinkronkan harga stack Daging Cincang ke input "Harga Cincang" biar nilainya live
  const hargaCincang = parseFloat(document.getElementById('hargaCincang').value) || 336;
  const dagingInv = inventory.find(inv => inv.uid === DAGING_UID);
  if (dagingInv) dagingInv.harga = hargaCincang;

  // Pecah tiap stack jadi slot visual, max 999/slot (kalau lebih, lanjut ke slot baru)
  let visualSlots = [];
  inventory.forEach((inv, i) => {
    let sisa = inv.qty;
    while (sisa > 0) {
      visualSlots.push({ inv, i, qty: Math.min(sisa, 999) });
      sisa -= 999;
    }
  });

  document.getElementById('invCount').textContent = inventory.length;
  const grid = document.getElementById('invGrid');
  const slots = Math.max(visualSlots.length, 10);
  let html = '';
  for (let s = 0; s < slots; s++) {
    const vs = visualSlots[s];
    if (vs) {
      const inv = vs.inv;
      const d = IKAN_DB.find(i => i.uid === inv.uid);
      const isDaging = inv.uid === DAGING_UID;
      const tc = TIER_COL[d.tier] || '#fff';
      html += `<div class="slot filled" title="${d.id} × ${inv.qty}" onclick="openEdit(${vs.i})">
        <img src="${iconUrl(inv.uid)}" alt="" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2248%22 height=%2248%22><rect width=%2248%22 height=%2248%22 fill=%22%231a1208%22/></svg>'">
        <span class="st" style="color:${isDaging ? 'var(--gold)' : tc}">${isDaging ? '🥩' : tierLabel(d.tier)}</span>
        <span class="sq">${vs.qty}</span>
        ${inv.harga > 0 ? `<span class="sh">${fmt(inv.harga)}</span>` : ''}
      </div>`;
    } else html += `<div class="slot"></div>`;
  }
  grid.innerHTML = html;

  renderPreview();
  updateFooter();
}

// ===================== PERBANDINGAN & MODAL/PROFIT =====================
function calcNilaiInventory(){
  return inventory.reduce((s, inv) => s + inv.qty * (inv.harga || 0), 0);
}

// Kalau diproses sekarang: berapa kalau semua ikan dijual, semua dicincang, atau pilihan terbaik per ikan
// (item Daging Cincang yang udah ada gak dihitung ulang, nilainya udah final)
function calcPreview(){
  const hargaCincang = parseFloat(document.getElementById('hargaCincang').value) || 336;
  let totalJual = 0, totalCincang = 0, totalOptimal = 0;
  inventory.forEach(inv => {
    if (inv.uid === DAGING_UID) return;
    const d = IKAN_DB.find(i => i.uid === inv.uid);
    const jual    = inv.qty * (inv.harga || 0);
    const cincang = inv.qty * d.potong * hargaCincang;
    totalJual    += jual;
    totalCincang += cincang;
    totalOptimal += Math.max(jual, cincang);
  });
  return { totalJual, totalCincang, totalOptimal };
}

function renderPreview(){
  const wrap = document.getElementById('previewSection');
  const adaIkan = inventory.some(inv => inv.uid !== DAGING_UID);
  if (!adaIkan) { wrap.classList.remove('show'); return; }
  wrap.classList.add('show');
  const { totalJual, totalCincang, totalOptimal } = calcPreview();
  document.getElementById('summaryGrid').innerHTML = `
    <div class="summary-card"><div class="s-label">Semua Dijual</div><div class="s-val neutral">${fmt(totalJual)}</div></div>
    <div class="summary-card"><div class="s-label">Semua Dicincang</div><div class="s-val accent">${fmt(totalCincang)}</div></div>
    <div class="summary-card"><div class="s-label">Pilihan Terbaik</div><div class="s-val profit">${fmt(totalOptimal)}</div></div>`;
}

function updateFooter(){
  if (modalLock === null) return; // belum pernah proses
  const nilaiSekarang = calcNilaiInventory();
  const profit = nilaiSekarang - modalLock;
  document.getElementById('coinModal').textContent = fmt(modalLock);
  document.getElementById('coinNilai').textContent = fmt(nilaiSekarang);
  const pEl = document.getElementById('profitVal');
  pEl.textContent = (profit >= 0 ? '+' : '') + fmt(profit);
  pEl.className   = 'profit-val ' + (profit >= 0 ? 'pos' : 'neg');
}

// ===================== PROSES CINCANG (otomatis, hasil masuk inventory) =====================
function prosesCincang(){
  if (!inventory.length){ alert('Tambahkan minimal satu ikan dulu.'); return; }
  const hargaCincang = parseFloat(document.getElementById('hargaCincang').value) || 336;

  // Snapshot modal SEBELUM ikan dikonversi jadi daging (cuma sekali, pas proses pertama)
  if (modalLock === null) {
    modalLock = calcNilaiInventory();
    document.getElementById('coinFooter').classList.add('show');
  }

  let dagingTambahan = 0, jumlahDicincang = 0, jumlahDisimpan = 0;

  inventory = inventory.filter(inv => {
    if (inv.uid === DAGING_UID) return true; // stack daging yang udah ada, jangan diproses ulang
    const d = IKAN_DB.find(i => i.uid === inv.uid);
    const nilaiCincang = d.potong * hargaCincang;
    const nilaiJual     = inv.harga || 0;
    if (nilaiCincang > nilaiJual) {
      dagingTambahan  += d.potong * inv.qty;
      jumlahDicincang += inv.qty;
      return false; // ikan ini dikonsumsi, jadi daging
    }
    jumlahDisimpan += inv.qty;
    return true; // lebih untung dijual apa adanya, tetap disimpan
  });

  if (dagingTambahan > 0) {
    const ex = inventory.find(inv => inv.uid === DAGING_UID);
    if (ex) ex.qty = Math.min(ex.qty + dagingTambahan, 999999999);
    else inventory.push({ uid: DAGING_UID, qty: dagingTambahan, harga: hargaCincang, manual: true });
  }

  renderInventory();
  if (!invShown) toggleInv();

  if (jumlahDicincang === 0) {
    showToast('👍 Semua ikan lebih untung dijual, gak ada yang dicincang');
  } else {
    showToast(`🔪 ${jumlahDicincang} ekor dicincang → ${dagingTambahan} daging · ${jumlahDisimpan} ekor tetap disimpan`);
  }
  catatAktivitas();
}

// ===================== HELPER UMUM =====================
function openOverlay(id){ document.getElementById(id).classList.add('show'); }
function closeOverlay(id){ document.getElementById(id).classList.remove('show'); }
function closeOverlayOutside(e, id){ if (e.target === document.getElementById(id)) closeOverlay(id); }

function showToast(msg){
  const t = document.getElementById('toast');
  t.textContent = msg; t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2200);
}

async function catatAktivitas(){
  const loggedIn = @json(auth()->check());
  if (!loggedIn) return;
  try {
    await fetch('/api/catat-aktivitas', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
      },
      body: JSON.stringify({ type: 'fishing' })
    });
  } catch (err) {}
}

// ===================== INIT =====================
buildTierDropFish();
buildKotaDropFish();
filterItems();
renderInventory();
onKotaChange();
</script>

<x-comments page="fishing" />
@endsection