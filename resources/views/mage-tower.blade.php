@extends('layouts.app')
@section('title', $stationName . ' — Albion Online Tools')
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

/* ====== MODE TOGGLE (Simple / Advance) ====== */
.mode-toggle { display:flex; gap:0; background:var(--slot-bg); border:1px solid var(--slot-bd); border-radius:4px; padding:3px; margin-bottom:10px; }
.mode-btn { flex:1; background:transparent; border:none; color:var(--text-dim); font-family:'Cinzel',serif; font-size:12px; font-weight:700; letter-spacing:1px; padding:10px; cursor:pointer; border-radius:3px; text-transform:uppercase; transition:all .15s; }
.mode-btn.active { background:linear-gradient(180deg,#4a3818,#2e2210); color:var(--gold); border:1px solid var(--panel-bd); }

/* ====== WIZARD (MODE SIMPLE) ====== */
.wiz-step { padding:14px 0; border-bottom:1px solid rgba(107,79,26,.3); }
.wiz-step:last-child { border-bottom:none; }
.wiz-label { font-family:'Cinzel',serif; font-size:11px; color:var(--text-dim); text-transform:uppercase; letter-spacing:1px; margin-bottom:10px; }
.wiz-options { display:flex; flex-wrap:wrap; gap:7px; }
.wiz-opt { background:linear-gradient(180deg,#3d2e15,#2a1f0e); border:1px solid var(--panel-bd); border-radius:4px; color:var(--text-lt); font-family:'Crimson Text',serif; font-size:14px; padding:9px 15px; cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:7px; }
.wiz-opt:hover { border-color:var(--gold); }
.wiz-opt.sel { border-color:var(--gold); background:linear-gradient(180deg,#5a4520,#3a2c10); color:var(--gold); }
.wiz-opt .wo-arrow { font-size:9px; opacity:.7; }
.wiz-cat-crumb { font-family:'Crimson Text',serif; font-style:italic; font-size:12px; color:var(--text-dim); margin:12px 0 8px 16px; }
.wiz-cat-crumb-3 { margin-left:32px; }
.wiz-cat-lvl2 { margin-left:16px; padding-left:10px; border-left:2px solid rgba(240,192,64,.35); }
.wiz-cat-lvl3 { margin-left:32px; padding-left:10px; border-left:2px solid rgba(240,192,64,.6); }
.wiz-next-row { margin-top:10px; }
.wiz-btn-lanjut { background:linear-gradient(180deg,#4a6b1a,#2e4210); border:1px solid #6b8b30; border-radius:3px; color:#d0f0a0; font-family:'Cinzel',serif; font-size:12px; font-weight:700; letter-spacing:1px; padding:9px 16px; cursor:pointer; text-transform:uppercase; }
.wiz-btn-lanjut:hover { border-color:#8bc040; color:#fff; }
.wiz-btn-lanjut:disabled { opacity:.4; cursor:not-allowed; }
.wiz-item-list { max-height:360px; overflow-y:auto; border:1px solid var(--slot-bd); border-radius:3px; }
.wiz-input-row { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.wiz-input-row label { font-size:12px; color:var(--text-dim); min-width:150px; }
.wiz-input-row input[type=number] { background:var(--slot-bg); border:1px solid var(--slot-bd); border-radius:3px; color:var(--text-lt); font-size:14px; padding:8px 10px; outline:none; width:120px; }
.wiz-input-row input:focus { border-color:var(--gold); }
.wiz-btn-hitung { width:100%; background:linear-gradient(180deg,#8b4a00,#5a2e00); border:1px solid #c06010; border-radius:3px; color:var(--gold); font-family:'Cinzel',serif; font-size:13px; font-weight:700; letter-spacing:1px; padding:12px; cursor:pointer; text-transform:uppercase; margin-top:4px; }
.wiz-btn-hitung:hover { background:linear-gradient(180deg,#a05800,#703800); }
.wiz-selected-item { display:flex; align-items:center; gap:10px; background:rgba(0,0,0,.25); border:1px solid var(--slot-bd); border-radius:4px; padding:8px 10px; margin-bottom:12px; }
.wiz-selected-item img { width:40px; height:40px; object-fit:contain; border:1px solid var(--slot-bd); border-radius:3px; background:var(--slot-bg); }
.wiz-selected-item .wsi-name { flex:1; font-family:'Crimson Text',serif; font-size:14px; color:var(--gold); }
.wiz-selected-item .wsi-change { background:none; border:1px solid var(--slot-bd); border-radius:3px; color:var(--text-dim); font-size:11px; padding:5px 9px; cursor:pointer; }
.wiz-selected-item .wsi-change:hover { border-color:var(--gold); color:var(--gold); }
.wiz-result { margin:14px 0; background:linear-gradient(180deg,#2e2210,#1e1608); border:2px solid var(--panel-bd); border-radius:4px; padding:16px; }
.wiz-result-text { font-size:15px; line-height:1.7; color:var(--text-lt); }
.wiz-result-text b { color:var(--gold); }
.bahan-row { display:flex; gap:8px; align-items:center; margin-bottom:8px; flex-wrap:wrap; }
.bahan-slot { display:flex; flex-direction:column; align-items:center; gap:3px; }
.bahan-slot img { width:44px; height:44px; border:1px solid var(--slot-bd); border-radius:3px; background:var(--slot-bg); object-fit:contain; }
.bahan-qty { font-family:'Cinzel',serif; font-size:11px; font-weight:700; text-align:center; }
.bahan-qty .butuh { color:var(--gold); }
.bahan-qty .punya { color:#6f8; }
.bahan-name { font-size:9px; color:var(--text-dim); text-align:center; max-width:60px; line-height:1.2; }
.bahan-arrow { font-size:16px; color:var(--text-dim); align-self:center; padding-bottom:18px; }
.reset-btn { background:linear-gradient(180deg,#6b1a1a,#4a1010); border:1px solid #8b3030; border-radius:3px; color:#f0c0c0; font-size:12px; padding:9px 12px; cursor:pointer; }
.reset-btn:hover { border-color:#c04040; }

/* ====== CRAFT SLOTS ROW (Mode Advance) ====== */
.craft-slots-row { padding:14px 16px; border-top:1px solid var(--panel-bd); display:flex; flex-direction:column; gap:10px; }
.craft-slots-empty { color:var(--text-dim); font-style:italic; font-size:13px; font-family:'Crimson Text',serif; }
.craft-recipe-tabs { display:flex; gap:6px; flex-wrap:wrap; }
.craft-tab { background:linear-gradient(180deg,#3d2e15,#2a1f0e); border:1px solid var(--panel-bd); border-radius:4px; color:var(--text-dim); font-family:'Cinzel',serif; font-size:11px; font-weight:700; letter-spacing:.5px; padding:7px 14px; cursor:pointer; text-transform:uppercase; transition:all .12s; }
.craft-tab:hover { border-color:var(--gold-dk); color:var(--text-lt); }
.craft-tab.active { border-color:var(--gold); background:linear-gradient(180deg,#5a4520,#3a2c10); color:var(--gold); }
.craft-slots-grid { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.craft-slot { width:54px; height:54px; background:var(--slot-bg); border:1px solid var(--slot-bd); border-radius:4px; position:relative; flex-shrink:0; cursor:pointer; }
.craft-slot img { width:100%; height:100%; object-fit:contain; display:block; }
.craft-slot .cs-need { position:absolute; bottom:1px; right:2px; font-size:9px; font-weight:700; font-family:'Cinzel',serif; color:#f0c0c0; text-shadow:0 1px 2px #000; padding:0 1px; background:rgba(0,0,0,.55); border-radius:2px; }
.craft-slot.ok { border-color:#6f8; }
.craft-slot.ok .cs-need { color:#8fe0a0; }
.craft-arrow { font-size:18px; color:var(--text-dim); }
.craft-reset-row { padding:0 16px 14px; }

/* ====== TABEL DIKUNCI SAAT ITEM SEDANG DIPILIH ====== */
.item-row.ct-dim { opacity:.28; filter:grayscale(65%); pointer-events:none; }
.item-row.ct-selected { border:1px solid var(--gold); background:rgba(240,192,64,.1); border-radius:3px; }
#itemTableWrap.ct-locked { overflow:hidden; }

/* ====== BOTTOM BAR: Return % + Tombol Craft ====== */
.craft-bottom-bar { display:flex; gap:8px; align-items:center; padding:10px 16px; border-top:1px solid var(--panel-bd); background:rgba(0,0,0,.15); flex-wrap:wrap; }
.craft-ret-wrap { display:flex; align-items:center; gap:5px; background:var(--slot-bg); border:1px solid var(--slot-bd); border-radius:3px; padding:5px 9px; }
.craft-ret-wrap label { font-size:11px; color:var(--text-dim); white-space:nowrap; }
.craft-ret-inp { width:52px; background:transparent; border:none; color:var(--gold); font-size:14px; font-weight:600; text-align:right; outline:none; }
.craft-modal-wrap { display:flex; align-items:center; gap:5px; background:var(--slot-bg); border:1px solid var(--slot-bd); border-radius:3px; padding:5px 9px; }
.craft-modal-wrap label { font-size:11px; color:var(--text-dim); white-space:nowrap; }
.craft-modal-wrap span { font-family:'Cinzel',serif; font-size:14px; font-weight:700; color:var(--gold); }
.craft-sell-wrap { display:flex; align-items:center; gap:5px; background:var(--slot-bg); border:1px solid var(--slot-bd); border-radius:3px; padding:5px 9px; }
.craft-sell-wrap label { font-size:11px; color:var(--text-dim); white-space:nowrap; }
.craft-sell-inp { width:74px; background:transparent; border:none; color:var(--gold); font-size:14px; font-weight:600; text-align:right; outline:none; }
.craft-qty-wrap { display:flex; align-items:center; gap:5px; background:var(--slot-bg); border:1px solid var(--slot-bd); border-radius:3px; padding:5px 9px; }
.craft-qty-wrap label { font-size:11px; color:var(--text-dim); white-space:nowrap; }
.craft-qty-inp { width:44px; background:transparent; border:none; color:var(--gold); font-size:14px; font-weight:600; text-align:right; outline:none; }
.craft-qty-inp:disabled { opacity:.45; }
.craft-max-chk { display:flex; align-items:center; gap:4px; cursor:pointer; font-size:11px; color:var(--text-dim); white-space:nowrap; }
.craft-max-chk input { cursor:pointer; }

/* ====== JOURNAL PICKER ====== */
.craft-journal-section { padding: 0 16px 14px; }
.craft-journal-toggle { display:flex; align-items:center; gap:7px; cursor:pointer; font-family:'Crimson Text',serif; font-size:14px; color:var(--text-lt); user-select:none; }
.craft-journal-toggle input { cursor:pointer; }
.craft-journal-picker { display:flex; align-items:center; gap:10px; margin-top:10px; background:rgba(0,0,0,.25); border:1px solid var(--slot-bd); border-radius:4px; padding:8px 10px; }
.craft-journal-picker img { width:40px; height:40px; object-fit:contain; border:1px solid var(--slot-bd); border-radius:3px; background:var(--slot-bg); flex-shrink:0; }
.craft-journal-picker select { background:var(--slot-bg); border:1px solid var(--slot-bd); border-radius:3px; color:var(--text-lt); font-family:'Crimson Text',serif; font-size:13px; padding:6px 8px; outline:none; cursor:pointer; }
.craft-journal-picker select:focus { border-color:var(--gold-dk); }
.craft-result-panel { margin:0 16px 14px; background:linear-gradient(180deg,#2e2210 0%,#1e1608 100%); border:2px solid var(--panel-bd); border-radius:4px; padding:12px 14px; display:flex; flex-direction:column; gap:7px; }
.crp-row { display:flex; justify-content:space-between; align-items:center; }
.crp-label { font-family:'Cinzel',serif; font-size:10px; color:var(--text-dim); text-transform:uppercase; letter-spacing:.5px; }
.crp-val { font-family:'Cinzel',serif; font-size:15px; font-weight:700; color:var(--gold); }
.crp-val.positive { color:#8fe0a0; }
.crp-val.negative { color:#f08080; }
.crp-group-lbl { font-family:'Cinzel',serif; font-size:11px; color:var(--gold-dk); text-transform:uppercase; letter-spacing:1px; margin-top:4px; padding-bottom:3px; border-bottom:1px solid rgba(107,79,26,.4); }
.crp-group-lbl:first-child { margin-top:0; }
.crp-subtotal { margin-top:2px; padding-top:5px; border-top:1px dashed rgba(107,79,26,.5); }
.crp-subtotal .crp-label { color:var(--text-lt); }
.crp-total { margin-top:4px; padding-top:7px; border-top:1px solid var(--panel-bd); }
.crp-total .crp-label { color:var(--text-lt); font-size:11px; }
.crp-total .crp-val { font-size:17px; }

/* ====== STATUS JURNAL DI INVENTORY (full + partial jadi slot ikon, bukan teks) ====== */
.cinv-slot.journal-full,
.cinv-slot.journal-partial { border-color:var(--gold-dk); background:rgba(240,192,64,.08); }
.cinv-slot.journal-partial { opacity:.85; }
.cinv-slot .cinv-qty-frac { position:absolute; bottom:1px; left:2px; right:2px; font-size:8px; font-weight:700; color:#fff; text-shadow:0 1px 2px #000; text-align:center; line-height:1.1; }
.craft-btn { flex:1; background:linear-gradient(180deg,#8b4a00,#5a2e00); border:1px solid #c06010; border-radius:3px; color:var(--gold); font-family:'Cinzel',serif; font-size:13px; font-weight:700; letter-spacing:1px; padding:10px; cursor:pointer; text-transform:uppercase; }
.craft-btn:hover:not(:disabled) { background:linear-gradient(180deg,#a05800,#703800); }
.craft-btn:disabled { opacity:.35; cursor:not-allowed; }

/* ====== INVENTORY (Mode Advance Craft) ====== */
.craft-inv-section { padding:12px 16px 16px; border-top:1px solid var(--panel-bd); }
.craft-inv-lbl { font-family:'Cinzel',serif; font-size:10px; color:var(--text-dim); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; }
.cinv-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:5px; }
.cinv-slot { aspect-ratio:1; background:var(--slot-bg); border:1px solid var(--slot-bd); border-radius:3px; position:relative; cursor:pointer; overflow:hidden; }
.cinv-slot.filled:hover { border-color:var(--gold); }
.cinv-slot img { width:100%; height:100%; object-fit:contain; display:block; }
.cinv-slot .cinv-qty { position:absolute; bottom:1px; right:2px; font-size:10px; font-weight:700; color:#fff; text-shadow:0 1px 2px #000; }

/* ====== RECIPE POPUP (grouping Resep 1 / Resep 2) ====== */
.craft-recipe-group { margin-bottom:16px; }
.craft-recipe-group:last-child { margin-bottom:0; }
.craft-recipe-title { font-family:'Cinzel',serif; font-size:11px; color:var(--gold); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; }
.craft-recipe-items { display:flex; gap:10px; flex-wrap:wrap; }
.craft-res-item { display:flex; flex-direction:column; align-items:center; gap:4px; width:64px; cursor:pointer; padding:6px; border-radius:4px; border:1px solid transparent; transition:all .12s; }
.craft-res-item:hover { border-color:var(--gold-dk); background:rgba(240,192,64,.06); }
.craft-res-item img { width:44px; height:44px; object-fit:contain; border:1px solid var(--slot-bd); border-radius:3px; background:var(--slot-bg); }
.craft-res-item .cri-count { font-family:'Cinzel',serif; font-size:10px; font-weight:700; color:var(--text-dim); }
.craft-res-item.ok .cri-count { color:#8fe0a0; }
.craft-res-item .cri-name { font-size:9px; color:var(--text-dim); text-align:center; line-height:1.2; }

/* ====== TOAST ====== */
.craft-toast { position:fixed; bottom:20px; left:50%; transform:translateX(-50%) translateY(60px); background:#3d2e15; border:1px solid var(--gold-dk); border-radius:3px; color:var(--gold); font-family:'Cinzel',serif; font-size:11px; padding:7px 14px; transition:transform .25s; z-index:10001; white-space:nowrap; }
.craft-toast.show { transform:translateX(-50%) translateY(0); }

/* DESKTOP TWO-COLUMN LAYOUT — Mode Advance saja (item list kiri, slot resep+inventory+craft kanan) */
#mtAdvanceWrap .rw-main { display:flex; flex-direction:column; }
#mtAdvanceWrap .rw-col-left, #mtAdvanceWrap .rw-col-right { display:flex; flex-direction:column; min-width:0; }

@media (min-width:960px) {
  #mtAdvanceWrap { max-width:1180px; margin:0 auto; }
  #mtAdvanceWrap .rw-main { flex-direction:row; align-items:flex-start; }
  #mtAdvanceWrap .rw-col-left { flex:1 1 auto; border-right:1px solid var(--panel-bd); }
  #mtAdvanceWrap .rw-col-right { flex:0 0 360px; }
  #mtAdvanceWrap .item-list { max-height:600px; overflow-y:auto; }
}
</style>

<div class="mode-toggle">
  <button id="btnModeSimple" class="mode-btn active" onclick="setMTMode('simple')">🧙 Mode Simple</button>
  <button id="btnModeAdvance" class="mode-btn" onclick="setMTMode('advance')">⚙️ Mode Advance</button>
</div>

<!-- ====== MODE SIMPLE (WIZARD) ====== -->
<div id="mtSimpleWrap">
  <div class="panel">
    <div class="panel-header">
      <span>🧙</span>
      <span class="panel-title">{{ $stationName }} — Mode Simple</span>
    </div>
    <div style="padding:16px;">

      <div class="wiz-step" id="wizMtCatStep">
        <div class="wiz-label">1. Pilih Kategori</div>
        <div class="wiz-options" id="wizMtCat1Opts"></div>

        <div id="wizMtCat2Block" style="display:none;">
          <div class="wiz-cat-crumb" id="wizMtCat2Label"></div>
          <div class="wiz-options wiz-cat-lvl2" id="wizMtCat2Opts"></div>
        </div>

        <div id="wizMtCat3Block" style="display:none;">
          <div class="wiz-cat-crumb wiz-cat-crumb-3" id="wizMtCat3Label"></div>
          <div class="wiz-options wiz-cat-lvl3" id="wizMtCat3Opts"></div>
        </div>

        <div class="wiz-next-row">
          <button class="wiz-btn-lanjut" id="wizMtCatLanjut" disabled onclick="wizMtGoToItemStep()">Lanjut ke Pilih Item →</button>
        </div>
      </div>

      <div class="wiz-selected-item" id="wizMtCatSummary" style="display:none;"></div>

      <div class="wiz-step" id="wizMtItemStep" style="display:none;">
        <div class="wiz-label">2. Pilih Item</div>
        <div class="wiz-options" id="wizMtTierOpts" style="margin-bottom:8px;"></div>
        <div class="wiz-options" id="wizMtEncOpts" style="margin-bottom:8px;"></div>
        <input type="text" class="header-search" id="wizMtSearch" placeholder="Cari nama item..." style="width:100%;margin-bottom:8px;" oninput="wizMtOnSearch()">
        <div class="wiz-item-list" id="wizMtItemList">
          <div style="padding:16px;text-align:center;color:var(--text-dim);font-style:italic;" id="wizMtItemListEmpty">Memuat item...</div>
          <div id="wizMtItemGrid"></div>
        </div>
      </div>

      <div class="wiz-step" id="wizMtQtyStep" style="display:none;">
        <div class="wiz-label">3. Jumlah &amp; Return Bonus</div>
        <div class="wiz-selected-item" id="wizMtSelectedItem"></div>
        <div class="wiz-input-row">
          <label>Mau buat berapa?</label>
          <input type="number" id="wizMtQty" value="1" min="1">
        </div>
        <div class="wiz-input-row">
          <label>Return bonus (%)</label>
          <input type="number" id="wizMtReturn" value="15.2" min="0" max="100" step="0.1">
        </div>
        <button class="wiz-btn-hitung" onclick="wizMtCompute()">🪄 Hitung Bahan</button>
      </div>

      <div class="wiz-result" id="wizMtResult" style="display:none;">
        <div class="craft-recipe-tabs" id="wizMtRecipeTabs" style="margin-bottom:10px;"></div>
        <div class="wiz-result-text" id="wizMtResultText"></div>
        <div id="wizMtResultVisual" style="margin-top:12px;"></div>
        <button class="reset-btn" style="margin-top:14px;width:100%;" onclick="wizMtReset()">🔄 Hitung Ulang</button>
      </div>

    </div>
  </div>
</div>

<!-- ====== MODE ADVANCE (existing, tidak diubah) ====== -->
<div id="mtAdvanceWrap" style="display:none">
<div>
  <div class="panel">
    <div class="panel-header">
      <span>🪄</span>
      <span class="panel-title">{{ $stationName }}</span>
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


    <div class="rw-main">
      <div class="rw-col-left">
        <!-- Item List -->
        <div class="item-list" id="itemList">
          <div class="item-list-empty" id="emptyMsg">Pilih kategori untuk melihat senjata & armor 🪄</div>
          <div class="item-table-wrap" id="itemTableWrap" style="display:none">
            <div id="itemGrid"></div>
          </div>
        </div>
      </div>

      <div class="rw-col-right">
        <!-- SLOT RESEP — tab Resep 1/Resep 2 + slot bahan dari resep aktif -->
        <div class="craft-slots-row" id="craftSlotsRow">
          <div class="craft-recipe-tabs" id="craftRecipeTabs"></div>
          <div class="craft-slots-grid" id="craftSlotsGrid">
            <div class="craft-slots-empty">Pilih item dari daftar di atas untuk mulai crafting 🪄</div>
          </div>
        </div>
        <div class="craft-reset-row" id="craftResetRow" style="display:none">
          <button class="reset-btn" onclick="resetCraftTarget()">🔄 Ganti Item</button>
        </div>

        <!-- JURNAL -->
        <div class="craft-journal-section" id="craftJournalSection" style="display:none">
          <label class="craft-journal-toggle">
            <input type="checkbox" id="useJournalChk" onchange="onUseJournalChange()"> 📔 Gunakan Jurnal
          </label>
          <div class="craft-journal-picker" id="journalPickerRow" style="display:none">
            <img id="journalIcon" src="" alt="Jurnal" onerror="this.style.opacity=.3">
            <select id="journalTypeSelect" onchange="onJournalOptionChange()"></select>
            <select id="journalTierSelect" onchange="onJournalOptionChange()"></select>
          </div>
          <!-- Status jurnal (penuh + progress terisi sebagian) sekarang tampil
               sebagai ikon langsung di grid Inventory di bawah, bukan teks lagi. -->
        </div>

        <!-- INVENTORY -->
        <div class="craft-inv-section">
          <div class="craft-inv-lbl">📦 Inventory (<span id="craftInvCount">0</span>)</div>
          <div class="cinv-grid" id="craftInvGrid"></div>
        </div>

        <!-- BOTTOM BAR: Return % + Harga Jual + Modal + Tombol Craft -->
        <div class="craft-bottom-bar">
          <div class="craft-ret-wrap">
            <label>♻️ Return</label>
            <input class="craft-ret-inp" type="number" id="craftReturn" value="21.5" min="0" max="100" step="0.1">
            <span style="color:var(--text-dim);font-size:12px">%</span>
          </div>
          <div class="craft-qty-wrap">
            <label>🔢 Jumlah</label>
            <input class="craft-qty-inp" type="number" id="craftQty" value="1" min="1" disabled oninput="onCraftQtyInput()">
            <label class="craft-max-chk"><input type="checkbox" id="craftHabis" checked onchange="onCraftHabisChange()"> Max</label>
            <label class="craft-max-chk"><input type="checkbox" id="craftHabiskanBahan" onchange="onCraftHabiskanBahanChange()"> Habiskan Bahan</label>
          </div>
          <div class="craft-sell-wrap">
            <label>💵 Harga Jual</label>
            <input class="craft-sell-inp" type="number" id="craftSellPrice" placeholder="0" min="0" oninput="craftSellPriceIsDefault=false">
          </div>
          <div class="craft-modal-wrap">
            <label>💰 Modal</label>
            <span id="craftModalVal">0</span>
          </div>
          <button class="craft-btn" id="craftBtn" disabled onclick="doCraft()">⚒️ Craft</button>
        </div>

        <!-- HASIL CRAFT — muncul begitu tombol Craft berhasil ditekan.
             MODAL: bahan crafting (SNAPSHOT sekali di craft pertama sesi ini)
                    + jurnal dibutuhkan (DINAMIS, dari totalFameAccumulated).
             PROFIT: item hasil craft (real-time) + sisa bahan (real-time)
                    + jurnal penuh (dinamis) + total profit. -->
        <div class="craft-result-panel" id="craftResultPanel" style="display:none">
          <div class="crp-group-lbl">💰 Modal</div>
          <div class="crp-row"><span class="crp-label">Bahan Crafting</span><span class="crp-val" id="crpModalBahan">0</span></div>
          <div class="crp-row" id="crpModalJurnalRow" style="display:none"><span class="crp-label">Jurnal Dibutuhkan</span><span class="crp-val" id="crpModalJurnal">0</span></div>
          <div class="crp-row crp-subtotal"><span class="crp-label">Total Modal</span><span class="crp-val" id="crpTotalModal">0</span></div>

          <div class="crp-group-lbl">📈 Profit</div>
          <div class="crp-row"><span class="crp-label">Item Hasil Craft</span><span class="crp-val" id="crpProfitItem">0</span></div>
          <div class="crp-row"><span class="crp-label">Sisa Bahan</span><span class="crp-val" id="crpProfitSisa">0</span></div>
          <div class="crp-row" id="crpProfitJurnalRow" style="display:none"><span class="crp-label">Jurnal Penuh</span><span class="crp-val" id="crpProfitJurnal">0</span></div>
          <div class="crp-row crp-subtotal"><span class="crp-label">Hasil Akhir</span><span class="crp-val" id="crpHasilAkhir">0</span></div>

          <div class="crp-row crp-total"><span class="crp-label">Total Profit</span><span class="crp-val" id="crpTotalProfit">0</span></div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>

<!-- ====== POPUP TAMBAH BAHAN KE INVENTORY ====== -->
<div class="popup-overlay" id="craftAddOverlay" onclick="closeCraftAddOnBg(event)">
  <div class="popup-box" id="craftAddBox" style="max-width:340px;">
    <button class="popup-close" onclick="closeCraftAddOverlay()">✕</button>
    <div class="popup-head">
      <img id="caIcon" src="" alt="" onerror="this.style.opacity=.3">
      <div>
        <div class="popup-item-name" id="caName">—</div>
        <div class="popup-item-sub" id="caNeed">—</div>
      </div>
    </div>
    <div style="padding:14px 16px;display:flex;flex-direction:column;gap:10px;">
      <div>
        <label style="display:block;font-family:'Cinzel',serif;font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Harga per unit (opsional)</label>
        <input type="number" id="caHarga" placeholder="0" min="0" style="width:100%;background:var(--slot-bg);border:1px solid var(--slot-bd);border-radius:3px;color:var(--text-lt);font-size:14px;padding:8px 10px;outline:none;">
      </div>
      <div>
        <label style="display:block;font-family:'Cinzel',serif;font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Jumlah</label>
        <input type="number" id="caQty" value="1" min="1" style="width:100%;background:var(--slot-bg);border:1px solid var(--slot-bd);border-radius:3px;color:var(--text-lt);font-size:14px;padding:8px 10px;outline:none;">
      </div>
      <div class="pop-btn-row" id="caBtnRow" style="display:flex;gap:7px;">
        <button class="wiz-btn-hitung" style="flex:1" onclick="doCraftAddResource()">➕ Tambah ke Inventory</button>
      </div>
    </div>
  </div>
</div>

<!-- ====== POPUP JURNAL (klik slot Jurnal Penuh / Jurnal Terisi Sebagian di Inventory) ====== -->
<div class="popup-overlay" id="journalPopupOverlay" onclick="closeJournalPopupOnBg(event)">
  <div class="popup-box" id="journalPopupBox" style="max-width:340px;">
    <button class="popup-close" onclick="closeJournalPopup()">✕</button>
    <div class="popup-head">
      <img id="jpIcon" src="" alt="" onerror="this.style.opacity=.3">
      <div>
        <div class="popup-item-name" id="jpName">—</div>
        <div class="popup-item-sub" id="jpSub">—</div>
      </div>
    </div>
    <div style="padding:14px 16px;display:flex;flex-direction:column;gap:10px;">
      <!-- Jurnal PENUH — harga otomatis terisi (dari resource_value_by_tier), tetap bisa diedit manual -->
      <div id="jpFullFields">
        <label style="display:block;font-family:'Cinzel',serif;font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Harga per Jurnal Penuh</label>
        <input type="number" id="jpHarga" placeholder="0" min="0" style="width:100%;background:var(--slot-bg);border:1px solid var(--slot-bd);border-radius:3px;color:var(--text-lt);font-size:14px;padding:8px 10px;outline:none;" oninput="onJournalPriceOverrideInput()">
      </div>
      <!-- Jurnal TERISI SEBAGIAN — cuma info progress fame, read-only, gak ikut Profit -->
      <div id="jpPartialFields" style="display:none;">
        <div style="font-family:'Crimson Text',serif;font-size:14px;color:var(--text-lt);text-align:center;padding:6px 0;">
          Progress: <span id="jpFameProgress" style="color:var(--gold);font-weight:700;">0 / 0</span> fame
        </div>
      </div>
    </div>
  </div>
</div>

<div class="craft-toast" id="craftToast"></div>

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
// FILTER STATE PERSISTENCE (Bug D) — Category/Tier/Enc ke-reset tiap
// kali halaman di-refresh. Fix: persist ke localStorage, pola sama
// kayak craftInv (saveCraftState). saveFilterState() dipanggil tiap
// fetchItems() (satu titik, dipanggil abis semua handler filter ganti
// value), restoreFilterState() dipanggil sekali di init SEBELUM
// buildCol1/buildTierDrop/buildEncDrop biar dropdown-nya kebangun
// dengan state yg benar dari awal.
// ============================================================
function filterStorageKey() { return 'ct_craft_filter_' + STATION; }

function saveFilterState() {
  try {
    localStorage.setItem(filterStorageKey(), JSON.stringify({
      selKat1, selKat2, selKat3, selCatId, selTier, selEnc, searchQ,
    }));
  } catch (e) {}
}

function restoreFilterState() {
  try {
    const raw = localStorage.getItem(filterStorageKey());
    if (!raw) return;
    const s = JSON.parse(raw);
    selKat1  = s.selKat1  ?? null;
    selKat2  = s.selKat2  ?? null;
    selKat3  = s.selKat3  ?? null;
    selCatId = s.selCatId ?? null;
    selTier  = s.selTier  ?? null;
    selEnc   = (s.selEnc === undefined) ? null : s.selEnc;
    searchQ  = s.searchQ  ?? '';
  } catch (e) {}
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
  saveFilterState();
  showEmpty('Memuat item...');
  const params = new URLSearchParams();
  if (selCatId) params.set('category_id', selCatId);
  if (selTier)  params.set('tier', selTier);
  if (selEnc !== null) params.set('enc', selEnc);
  fetch(`${CRAFT_API_BASE}/items?` + params.toString())
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
      ? `<img class="item-icon" src="${item.img_url}" alt="${item.name}" loading="lazy" onerror="this.style.display='none'">`
      : `<div class="item-icon" style="display:flex;align-items:center;justify-content:center;font-size:18px;">?</div>`
    }
      </div>
      <div class="item-info">
        <span class="item-name">${item.name}</span>
      </div>`;

    row.dataset.itemId = item.id;
    row.addEventListener('click', () => selectCraftTarget(item.id));
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

  fetch('/api/crafting/item/' + itemId)
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

  fetch(`/api/crafting/item/${itemId}/refresh-prices`, {
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
  fetch('/api/crafting/category/' + categoryId + '/refresh-prices', { method: 'POST' })
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
// MODE TOGGLE (Simple / Advance)
// ============================================================
function setMTMode(mode) {
  const isSimple = mode === 'simple';
  document.getElementById('mtSimpleWrap').style.display = isSimple ? '' : 'none';
  document.getElementById('mtAdvanceWrap').style.display = isSimple ? 'none' : '';
  document.getElementById('btnModeSimple').classList.toggle('active', isSimple);
  document.getElementById('btnModeAdvance').classList.toggle('active', !isSimple);
  localStorage.setItem('mt_mode', mode);
}

// ============================================================
// WIZARD (MODE SIMPLE) — pilih kategori (drill-down) → pilih item
// → jumlah & return% → hitung bahan (1 level resep, dari 'resources')
// ============================================================
let wizMt = {
  cat1: null, cat2: null, cat3: null, // objek kategori terpilih tiap level
  catId: null,                        // id kategori terdalam yang aktif (dipakai utk fetch item)
  tier: null, enc: null,
  itemId: null, item: null,
  recipes: [], activeRecipe: 0,       // grup resep (heuristik sama kayak Mode Advance) + resep yg lagi aktif
  qty: 1, retPct: 15.2,
  searchTimer: null,
};

function wizMtOptButton(label, isActive, hasArrow, onClick) {
  const el = document.createElement('div');
  el.className = 'wiz-opt' + (isActive ? ' sel' : '');
  el.innerHTML = label + (hasArrow ? '<span class="wo-arrow">▶</span>' : '');
  el.addEventListener('click', onClick);
  return el;
}

function wizMtBuildCat1() {
  const el = document.getElementById('wizMtCat1Opts');
  el.innerHTML = '';
  CATEGORIES.forEach(cat => {
    const hasSub = cat.children && cat.children.length > 0;
    el.appendChild(wizMtOptButton(cat.name, wizMt.cat1 && wizMt.cat1.id === cat.id, hasSub, () => wizMtSelectCat(1, cat)));
  });
}

function wizMtBuildCat2() {
  const block = document.getElementById('wizMtCat2Block');
  const el    = document.getElementById('wizMtCat2Opts');
  if (!wizMt.cat1 || !wizMt.cat1.children || !wizMt.cat1.children.length) {
    block.style.display = 'none'; el.innerHTML = ''; return;
  }
  block.style.display = '';
  document.getElementById('wizMtCat2Label').textContent = '↳ Sub-kategori dari "' + wizMt.cat1.name + '"';
  el.innerHTML = '';
  wizMt.cat1.children.forEach(sub => {
    const hasSub2 = sub.children && sub.children.length > 0;
    el.appendChild(wizMtOptButton(sub.name, wizMt.cat2 && wizMt.cat2.id === sub.id, hasSub2, () => wizMtSelectCat(2, sub)));
  });
}

function wizMtBuildCat3() {
  const block = document.getElementById('wizMtCat3Block');
  const el    = document.getElementById('wizMtCat3Opts');
  if (!wizMt.cat2 || !wizMt.cat2.children || !wizMt.cat2.children.length) {
    block.style.display = 'none'; el.innerHTML = ''; return;
  }
  block.style.display = '';
  document.getElementById('wizMtCat3Label').textContent = '↳ Sub-kategori dari "' + wizMt.cat2.name + '"';
  el.innerHTML = '';
  wizMt.cat2.children.forEach(leaf => {
    el.appendChild(wizMtOptButton(leaf.name, wizMt.cat3 && wizMt.cat3.id === leaf.id, false, () => wizMtSelectCat(3, leaf)));
  });
}

function wizMtCategoryBreadcrumb() {
  return [wizMt.cat1, wizMt.cat2, wizMt.cat3].filter(Boolean).map(c => c.name).join(' → ');
}

// Klik kategori level manapun langsung jadi kandidat "catId" aktif (sama kayak
// Mode Advance: kategori tengah pun bisa langsung dipakai buat lihat item-nya
// beserta descendant-nya). Kalau kategori yang diklik gak punya sub (leaf),
// langsung auto-lanjut ke Step 2 tanpa perlu klik tombol.
function wizMtSelectCat(level, cat) {
  if (level === 1) { wizMt.cat1 = cat; wizMt.cat2 = null; wizMt.cat3 = null; }
  if (level === 2) { wizMt.cat2 = cat; wizMt.cat3 = null; }
  if (level === 3) { wizMt.cat3 = cat; }
  wizMt.catId = cat.id;
  wizMtBuildCat1(); wizMtBuildCat2(); wizMtBuildCat3();
  document.getElementById('wizMtCatLanjut').disabled = false;

  const isLeaf = !(cat.children && cat.children.length);
  if (isLeaf) {
    wizMtGoToItemStep();
  } else {
    document.getElementById('wizMtCatStep').scrollIntoView({behavior:'smooth', block:'nearest'});
  }
}

function wizMtGoToItemStep() {
  // Sembunyikan Step 1, ganti jadi ringkasan breadcrumb + tombol "Ganti Kategori"
  document.getElementById('wizMtCatStep').style.display = 'none';
  const summary = document.getElementById('wizMtCatSummary');
  summary.style.display = '';
  summary.innerHTML = `
    <span class="wsi-name">📁 ${wizMtCategoryBreadcrumb()}</span>
    <button class="wsi-change" onclick="wizMtBackToCatStep()">Ganti Kategori</button>`;

  document.getElementById('wizMtItemStep').style.display = '';
  document.getElementById('wizMtQtyStep').style.display  = 'none';
  document.getElementById('wizMtResult').style.display   = 'none';
  wizMt.tier = null; wizMt.enc = null;
  wizMtBuildTierOpts();
  wizMtBuildEncOpts();
  document.getElementById('wizMtSearch').value = '';
  wizMtFetchItems();
  document.getElementById('wizMtItemStep').scrollIntoView({behavior:'smooth', block:'nearest'});
}

function wizMtBackToCatStep() {
  document.getElementById('wizMtCatSummary').style.display = 'none';
  document.getElementById('wizMtItemStep').style.display   = 'none';
  document.getElementById('wizMtQtyStep').style.display    = 'none';
  document.getElementById('wizMtResult').style.display     = 'none';
  document.getElementById('wizMtCatStep').style.display    = '';
  document.getElementById('wizMtCatStep').scrollIntoView({behavior:'smooth', block:'nearest'});
}

function wizMtBuildTierOpts() {
  const el = document.getElementById('wizMtTierOpts');
  el.innerHTML = '';
  el.appendChild(wizMtOptButton('Semua Tier', wizMt.tier === null, false, () => { wizMt.tier = null; wizMtBuildTierOpts(); wizMtFetchItems(); }));
  TIERS.forEach(t => el.appendChild(wizMtOptButton(TIER_LABEL[t], wizMt.tier === t, false, () => { wizMt.tier = t; wizMtBuildTierOpts(); wizMtFetchItems(); })));
}

function wizMtBuildEncOpts() {
  const el = document.getElementById('wizMtEncOpts');
  el.innerHTML = '';
  el.appendChild(wizMtOptButton('Semua Enchant', wizMt.enc === null, false, () => { wizMt.enc = null; wizMtBuildEncOpts(); wizMtFetchItems(); }));
  ENCS.forEach(e => el.appendChild(wizMtOptButton('Enc ' + e, wizMt.enc === e, false, () => { wizMt.enc = e; wizMtBuildEncOpts(); wizMtFetchItems(); })));
}

function wizMtOnSearch() {
  clearTimeout(wizMt.searchTimer);
  wizMt.searchTimer = setTimeout(wizMtFetchItems, 400);
}

function wizMtFetchItems() {
  const empty = document.getElementById('wizMtItemListEmpty');
  const grid  = document.getElementById('wizMtItemGrid');
  empty.style.display = ''; empty.textContent = 'Memuat item...'; grid.innerHTML = '';

  const params = new URLSearchParams();
  if (wizMt.catId) params.set('category_id', wizMt.catId);
  if (wizMt.tier)  params.set('tier', wizMt.tier);
  if (wizMt.enc !== null) params.set('enc', wizMt.enc);

  fetch(`${CRAFT_API_BASE}/items?` + params.toString())
    .then(r => r.json())
    .then(items => {
      const q = document.getElementById('wizMtSearch').value.trim().toLowerCase();
      const filtered = q ? items.filter(i => i.name.toLowerCase().includes(q)) : items;
      if (!filtered.length) { empty.style.display = ''; empty.textContent = 'Tidak ada item ditemukan 😔'; return; }
      empty.style.display = 'none';
      grid.innerHTML = filtered.map(item => `
        <div class="item-row" onclick="wizMtSelectItem(${item.id})">
          <div class="item-icon-wrap">
            ${item.img_url
              ? `<img class="item-icon" src="${item.img_url}" alt="${item.name}" loading="lazy" onerror="this.style.display='none'">`
              : `<div class="item-icon" style="display:flex;align-items:center;justify-content:center;font-size:18px;">?</div>`}
          </div>
          <div class="item-info"><span class="item-name">${item.name}</span></div>
        </div>`).join('');
    })
    .catch(() => { empty.style.display = ''; empty.textContent = 'Gagal memuat item. Coba lagi.'; });
}

function wizMtSelectItem(itemId) {
  fetch('/api/crafting/item/' + itemId)
    .then(r => r.json())
    .then(item => {
      wizMt.itemId = itemId;
      wizMt.item = item;
      // Backend sekarang kirim recipe_groups yang udah dipisah rapi per alternatif
      // resep. Fallback ke heuristik JS lama kalau API belum update / field kosong.
      wizMt.recipes = (item.recipe_groups && item.recipe_groups.length)
        ? item.recipe_groups
        : groupRecipeResources(item.resources);
      wizMt.activeRecipe = 0;
      document.getElementById('wizMtItemStep').style.display = 'none';
      document.getElementById('wizMtQtyStep').style.display  = '';
      document.getElementById('wizMtResult').style.display   = 'none';
      document.getElementById('wizMtSelectedItem').innerHTML = `
        <img src="${item.img_url}" alt="${item.name}">
        <span class="wsi-name">${item.name}</span>
        <button class="wsi-change" onclick="wizMtBackToItemStep()">Ganti Item</button>`;
      document.getElementById('wizMtQtyStep').scrollIntoView({behavior:'smooth', block:'nearest'});
    })
    .catch(() => {
      document.getElementById('wizMtSelectedItem').innerHTML = '<span style="color:#f86">Gagal memuat detail item.</span>';
    });
}

function wizMtBackToItemStep() {
  document.getElementById('wizMtQtyStep').style.display  = 'none';
  document.getElementById('wizMtItemStep').style.display = '';
}

// Hitung bahan — cuma 1 level resep (sesuai data 'resources' dari API),
// dikali jumlah target, dikurangi return%. Gak breakdown rekursif sampai bahan mentah.
// Kalau item punya >1 resep alternatif (misal Adept's Cultist Robe), resource-nya
// dipisah pakai groupRecipeResources() (heuristik sama kayak Mode Advance) dan
// ditampilkan lewat tab Resep 1/Resep 2, bukan digabung jadi satu kalimat/visual.
function wizMtCompute() {
  const item = wizMt.item;
  if (!item) return;
  wizMt.qty    = Math.max(1, parseInt(document.getElementById('wizMtQty').value) || 1);
  wizMt.retPct = Math.min(100, Math.max(0, parseFloat(document.getElementById('wizMtReturn').value) || 0));

  if (!wizMt.recipes.length) {
    document.getElementById('wizMtRecipeTabs').innerHTML   = '';
    document.getElementById('wizMtResultText').innerHTML   = `<b>${item.name}</b> tidak punya data resep bahan.`;
    document.getElementById('wizMtResultVisual').innerHTML = '';
    document.getElementById('wizMtResult').style.display   = '';
    document.getElementById('wizMtResult').scrollIntoView({behavior:'smooth', block:'nearest'});
    return;
  }

  wizMtRenderResult();
  document.getElementById('wizMtResult').style.display = '';
  document.getElementById('wizMtResult').scrollIntoView({behavior:'smooth', block:'nearest'});
}

function wizMtSwitchRecipe(gi) {
  if (gi === wizMt.activeRecipe) return;
  wizMt.activeRecipe = gi;
  wizMtRenderResult();
}

function wizMtRenderResult() {
  const item   = wizMt.item;
  const qty    = wizMt.qty;
  const retPct = wizMt.retPct;

  // Tab Resep 1 / Resep 2 — cuma ditampilin kalau emang ada >1 alternatif resep
  document.getElementById('wizMtRecipeTabs').innerHTML = wizMt.recipes.length > 1
    ? wizMt.recipes.map((_, gi) => `
        <button class="craft-tab ${gi === wizMt.activeRecipe ? 'active' : ''}" onclick="wizMtSwitchRecipe(${gi})">Resep ${gi + 1}</button>
      `).join('')
    : '';

  const activeGroup = wizMt.recipes[wizMt.activeRecipe] || [];
  const rows = activeGroup.map(r => {
    const gross = qty * r.count;
    const ret   = Math.round(gross * retPct / 100);
    return { r, needed: gross - ret };
  });

  let kalimat = `Untuk membuat <b>${item.name}</b> sejumlah <b>${qty}</b>, dengan return <b>${retPct}%</b>, dibutuhkan `;
  kalimat += rows.map(row => `<b>${row.r.name}</b> sebanyak <b>${row.needed}</b>`).join(', ') + '.';

  let visual = rows.map(row => `
    <div class="bahan-slot">
      <img src="${row.r.img_url}" alt="${row.r.name}">
      <div class="bahan-qty"><span class="butuh">${row.needed}</span></div>
      <div class="bahan-name">${row.r.name}</div>
    </div>`).join('<span class="bahan-arrow">+</span>');
  visual += `<span class="bahan-arrow">→</span>
    <div class="bahan-slot">
      <img src="${item.img_url}" alt="${item.name}">
      <div class="bahan-qty"><span class="punya">${qty}</span></div>
      <div class="bahan-name">${item.name}</div>
    </div>`;

  document.getElementById('wizMtResultText').innerHTML   = kalimat;
  document.getElementById('wizMtResultVisual').innerHTML = `<div class="bahan-row">${visual}</div>`;
}

function wizMtReset() {
  wizMt = { cat1:null, cat2:null, cat3:null, catId:null, tier:null, enc:null, itemId:null, item:null, recipes:[], activeRecipe:0, qty:1, retPct:15.2, searchTimer:null };
  document.getElementById('wizMtCatLanjut').disabled = true;
  document.getElementById('wizMtRecipeTabs').innerHTML = '';
  wizMtBuildCat1(); wizMtBuildCat2(); wizMtBuildCat3();
  document.getElementById('wizMtCatSummary').style.display = 'none';
  document.getElementById('wizMtCatStep').style.display   = '';
  document.getElementById('wizMtItemStep').style.display  = 'none';
  document.getElementById('wizMtQtyStep').style.display   = 'none';
  document.getElementById('wizMtResult').style.display    = 'none';
}

// ============================================================
// CRAFTING (Mode Advance) — pilih item target dari tabel,
// pilih bahan dari resep (Resep 1 / Resep 2), kumpulin di
// Inventory, lalu Craft kalau salah satu resep udah lengkap.
// ============================================================
let craftTarget       = null; // item yg lagi mau dibuat
let craftRecipes      = [];   // array grup resep, tiap grup = array resource
let craftActiveRecipe = 0;    // index resep yg lagi aktif/dipilih (tab)
let craftInv          = [];   // {itemId, name, imgUrl, qty, harga}
let craftPending       = null; // resource yg lagi diproses di popup tambah
let craftSellPriceIsDefault = false; // true kalau nilai di input Harga Jual masih hasil auto-fill (belum diedit manual)
let bahanPriceCache = {}; // cache {item_id: harga_termurah_dari_api}, buat pre-fill popup "Tambah ke Inventory"
let craftEditIdx       = null; // index craftInv yg lagi diedit (null = mode tambah baru)

// ------------------------------------------------------------
// FAME / JOURNAL PROFIT — akumulasi per sesi crafting (target sama).
// totalFameAccumulated numpuk terus SELAMA "Gunakan Jurnal" aktif pas
// craft itu terjadi (pakai fame.F_B, BUKAN F_C — journal gak kena bonus
// item_type). craftModalLock = nilai bahan crafting DIKUNCI sekali di
// craft pertama sesi ini, gak berubah lagi meski craft berkali-kali.
// Dua-duanya di-reset tiap kali ganti item target.
// ------------------------------------------------------------
let totalFameAccumulated = 0;
let craftModalLock       = null;

// Konstanta dari config/albion.php, dipakai buat hitung kebutuhan &
// nilai jurnal (frontend, biar gak bolak-balik ke server tiap render).
const ALBION_JOURNAL_REQUIREMENT = @json(config('albion.fame.journal_requirement'));
const ALBION_JOURNAL_PRICE       = @json(config('albion.journal.price'));
const ALBION_JOURNAL_BASE_AMOUNT = @json(config('albion.journal.base_amount'));
const ALBION_LABORER_RATIO       = @json(config('albion.journal.laborer_ratio'));

// Mapping nama journal -> tipe laborer, buat ambil proporsi resource dari
// ALBION_LABORER_RATIO. Generalist's Journal SENGAJA tidak ada di sini —
// proporsi resource-nya belum diketahui (lihat FITUR-FAME-JOURNAL-CRAFTING.md).
const JOURNAL_LABORER_TYPE = {
  "Imbuer's Journal":     'Imbuer',
  "Fletcher's Journal":   'Fletcher',
  "Blacksmith's Journal": 'Blacksmith',
  "Tinker's Journal":     'Tinker',
};

// ------------------------------------------------------------
// JOURNAL PICKER — checkbox "Gunakan Jurnal" + pilihan jenis & tier.
// Data journal_options/default_journal/default_journal_tier datang
// dari response itemDetail (perlu query ?station= biar backend tau
// journal apa yg relevan buat station ini).
// ------------------------------------------------------------
let craftJournalOptions = []; // [{name, tiers:[...]}]
let craftUseJournal     = false;
let craftJournalType    = null;
let craftJournalTier    = null;

// Mapping nama journal -> uniquename buat generate URL render icon.
// CATATAN: JOURNAL_MAGE & JOURNAL_TOOLMAKER sudah dikonfirmasi valid.
// JOURNAL_WARRIOR / JOURNAL_HUNTER / JOURNAL_GENERAL masih dugaan pola
// penamaan — kalau ternyata gambar 404 di salah satu, ganti value di sini.
const JOURNAL_UNIQUE_NAME = {
  "Imbuer's Journal":     'JOURNAL_MAGE',
  "Fletcher's Journal":   'JOURNAL_HUNTER',
  "Blacksmith's Journal": 'JOURNAL_WARRIOR',
  "Tinker's Journal":     'JOURNAL_TOOLMAKER',
  "Generalist's Journal": 'JOURNAL_GENERAL',
};

function journalIconUrl(name, tier) {
  const code = JOURNAL_UNIQUE_NAME[name];
  if (!code) return '';
  // Suffix _EMPTY = state journal kosong (belum mulai keisi) — ini yang
  // relevan buat picker, soalnya user baru MILIH mau bawa jurnal apa,
  // belum craft apa-apa. State lain (tanpa suffix = Partially Full,
  // _FULL = penuh) dipakai laborer/inventory in-game, gak relevan di sini.
  return `https://render.albiononline.com/v1/item/T${tier}_${code}_EMPTY.png`;
}

// Dipakai buat slot Inventory: jurnal PENUH (_FULL) dan jurnal yang lagi
// terisi sebagian (tanpa suffix = state "Partially Full" bawaan game).
function journalFullIconUrl(name, tier) {
  const code = JOURNAL_UNIQUE_NAME[name];
  if (!code) return '';
  return `https://render.albiononline.com/v1/item/T${tier}_${code}_FULL.png`;
}
function journalPartialIconUrl(name, tier) {
  const code = JOURNAL_UNIQUE_NAME[name];
  if (!code) return '';
  return `https://render.albiononline.com/v1/item/T${tier}_${code}.png`;
}

// Harga manual per jurnal PENUH — kalau user edit lewat popup slot
// Inventory, nilai ini dipakai ganti hitungan otomatis di panel Profit.
// null = belum diedit, masih pakai getJournalResourceValue() otomatis.
let journalPriceOverride = null;

// CATATAN: belum ada penanda resep di data (lihat diskusi soal Adept's Cultist
// Robe yg py 2 resep tercampur 1 array). Sementara dipisah pakai heuristik:
// begitu ketemu item yg NAMANYA udah muncul di grup aktif, mulai grup baru.
// Ini stop-gap doang — kalau nanti backend nambahin kolom pembeda resep,
// ganti fungsi ini biar baca kolom itu langsung.
function groupRecipeResources(resources) {
  if (!resources || !resources.length) return [];
  const groups = [];
  let current = [];
  let seen = new Set();
  resources.forEach(r => {
    const key = r.item_id ?? r.name;
    if (seen.has(key)) { groups.push(current); current = []; seen = new Set(); }
    seen.add(key);
    current.push(r);
  });
  if (current.length) groups.push(current);
  return groups;
}

// Nilai silver dari resource balik SATU journal PENUH, di yield% yang
// lagi dipilih user. Datanya dibaca dari field 'resource_value_by_tier'
// yang HARUSNYA dikirim backend di tiap journal_options entry (lihat
// FITUR-FAME-JOURNAL-CRAFTING.md — journalResourceValue() di PHP).
// PENTING: kalau backend BELUM ngirim field ini (mis. endpoint itemDetail
// belum diupdate, atau Generalist's Journal yang memang sengaja gak ada
// datanya), fungsi ini return null dan UI nampilin '—' alih-alih dihitung
// ke Profit — BUKAN dianggap 0.
function getJournalResourceValue() {
  if (!craftTarget || !craftJournalType || !craftJournalTier) return null;
  const opt = craftJournalOptions.find(o => o.name === craftJournalType);
  const valByTier = opt && opt.resource_value_by_tier;
  const val = valByTier ? valByTier[craftJournalTier] : null;
  if (val === undefined || val === null) return null;
  return val; // asumsi yield 100% — field Yield% sudah dihapus dari UI
}

// Nilai silver "sisa bahan" resep aktif, real-time dari craftInv sekarang
// (bukan snapshot) — dipakai buat panel Profit.
function calcGroupValue(group) {
  let total = 0;
  (group || []).forEach(r => {
    const inv = craftInv.find(i => (r.item_id ? i.itemId === r.item_id : i.name === r.name));
    if (inv) total += inv.qty * (inv.harga || 0);
  });
  return total;
}

// ------------------------------------------------------------
// PERSISTENCE — simpan target + inventory bahan yg lagi dikumpulin
// ke localStorage, biar gak hilang kalau halaman di-reload.
// ------------------------------------------------------------
function craftStorageKey() {
  return 'ct_craft_state_' + STATION;
}

function saveCraftState() {
  try {
    localStorage.setItem(craftStorageKey(), JSON.stringify({
      targetId: craftTarget ? craftTarget.id : null,
      activeRecipe: craftActiveRecipe,
      inv: craftInv,
      journal: {
        use: craftUseJournal, type: craftJournalType, tier: craftJournalTier,
      },
      journalPriceOverride,
      totalFameAccumulated,
      craftModalLock,
    }));
  } catch (e) {}
}

function clearCraftState() {
  try { localStorage.removeItem(craftStorageKey()); } catch (e) {}
}

function loadCraftState() {
  let state;
  try {
    const raw = localStorage.getItem(craftStorageKey());
    if (!raw) return;
    state = JSON.parse(raw);
  } catch (e) { return; }
  if (!state || !state.targetId) return;

  fetch(`/api/crafting/item/${state.targetId}?station=${STATION}`)
    .then(r => r.json())
    .then(item => {
      craftTarget       = item;
      craftRecipes      = (item.recipe_groups && item.recipe_groups.length)
        ? item.recipe_groups
        : groupRecipeResources(item.resources);
      craftActiveRecipe = state.activeRecipe || 0;
      craftInv          = state.inv || [];
      totalFameAccumulated = state.totalFameAccumulated || 0;
      craftModalLock        = (state.craftModalLock != null) ? state.craftModalLock : null;
      journalPriceOverride  = (state.journalPriceOverride != null) ? state.journalPriceOverride : null;
      lockItemTable(item.id);
      renderCraftInventory();
      renderCraftSlots();
      renderJournalPicker(item, state.journal); // pulihkan pilihan journal sebelumnya kalau ada
      updateCraftQtyField();
      updateCraftModal();
      updateCraftButtonState();
      renderCraftResultPanel();
      applyCraftSellPriceDefault(item.prices);
      refreshCraftSellPrice(item.id);
      fetchBahanDefaultPrices(craftRecipes);
    })
    .catch(() => clearCraftState()); // item udah gak valid -> buang state lama
}

function getInvQty(itemId, name) {
  const found = craftInv.find(i => (itemId ? i.itemId === itemId : i.name === name));
  return found ? found.qty : 0;
}

function isGroupSatisfied(group, jumlah = 1) {
  return group.every(r => getInvQty(r.item_id, r.name) >= r.count * jumlah);
}

// Berapa banyak item hasil yang bisa dibuat sekaligus dari bahan yang ada
// di inventory saat ini — sama konsepnya kayak maxOutput di refine.
function getMaxCraftable(group) {
  if (!group || !group.length) return 0;
  return Math.min(...group.map(r => Math.floor(getInvQty(r.item_id, r.name) / r.count)));
}

// Sinkronkan field Jumlah dengan bahan yang tersedia. Kalau checkbox
// "Max" dicentang, field dikunci dan otomatis ikut max craftable.
function updateCraftQtyField() {
  const group = craftRecipes[craftActiveRecipe] || [];
  const max   = getMaxCraftable(group);
  const habis = document.getElementById('craftHabis').checked;
  const qtyInp = document.getElementById('craftQty');
  qtyInp.max = Math.max(max, 1);
  if (habis) {
    qtyInp.value = max > 0 ? max : 1;
    qtyInp.disabled = true;
  } else {
    qtyInp.disabled = false;
    if ((parseInt(qtyInp.value) || 1) > max) qtyInp.value = Math.max(max, 1);
  }
}

function onCraftHabisChange() {
  const maxChecked   = document.getElementById('craftHabis').checked;
  const habisBahanEl = document.getElementById('craftHabiskanBahan');
  habisBahanEl.disabled = maxChecked; // saling eksklusif dgn Habiskan Bahan
  if (maxChecked) habisBahanEl.checked = false;
  updateCraftQtyField();
  updateCraftModal();
  updateCraftButtonState();
}

// Checkbox "Habiskan Bahan" — mode loop simulasi (simulateLoopN), saling
// eksklusif sama checkbox "Max". Field Jumlah gak relevan di mode ini
// (jumlah hasil ditentukan otomatis dari simulasi), jadi dikunci disabled.
function onCraftHabiskanBahanChange() {
  const habisBahan = document.getElementById('craftHabiskanBahan').checked;
  const maxEl      = document.getElementById('craftHabis');
  const qtyInp     = document.getElementById('craftQty');
  maxEl.disabled = habisBahan;
  if (habisBahan) {
    maxEl.checked = false;
    qtyInp.disabled = true;
  } else {
    updateCraftQtyField(); // balikin ke behaviour normal (ikut checkbox Max)
  }
  updateCraftModal();
  updateCraftButtonState();
}

function onCraftQtyInput() {
  updateCraftModal();
  updateCraftButtonState();
}

function isHabiskanBahanMode() {
  const el = document.getElementById('craftHabiskanBahan');
  return el ? el.checked : false;
}

// Isi ulang dropdown tier sesuai jenis journal yg lagi dipilih
// (tiap jenis journal punya range tier yang sama, tapi disusun generik
// biar tetap benar kalau nanti journal_options per jenis punya range beda).
function buildJournalTierOptions() {
  const opt = craftJournalOptions.find(o => o.name === craftJournalType);
  const tiers = opt ? opt.tiers : [];
  const tierSelect = document.getElementById('journalTierSelect');
  tierSelect.innerHTML = tiers.map(t => `<option value="${t}">Tier ${t}</option>`).join('');
}

function updateJournalIcon() {
  document.getElementById('journalIcon').src = journalIconUrl(craftJournalType, craftJournalTier);
}

function onJournalOptionChange() {
  craftJournalType = document.getElementById('journalTypeSelect').value;
  buildJournalTierOptions();
  const tierSelect = document.getElementById('journalTierSelect');
  const stillValid = [...tierSelect.options].some(o => o.value == craftJournalTier);
  if (!stillValid) craftJournalTier = parseInt(tierSelect.options[tierSelect.options.length - 1]?.value) || craftJournalTier;
  tierSelect.value = craftJournalTier;
  updateJournalIcon();
  renderCraftInventory();
  renderCraftResultPanel();
  saveCraftState();
}

function onUseJournalChange() {
  craftUseJournal = document.getElementById('useJournalChk').checked;
  document.getElementById('journalPickerRow').style.display = craftUseJournal ? '' : 'none';
  renderCraftInventory();
  renderCraftResultPanel();
  saveCraftState();
}

// Dipanggil tiap kali item target berhasil dimuat (dari selectCraftTarget
// maupun loadCraftState). savedJournal (opsional) = state journal yg
// dipulihkan dari localStorage, biar pilihan gak reset pas reload halaman.
function renderJournalPicker(item, savedJournal) {
  craftJournalOptions = item.journal_options || [];
  const section = document.getElementById('craftJournalSection');

  if (!craftJournalOptions.length) {
    section.style.display = 'none';
    craftUseJournal = false;
    return;
  }
  section.style.display = '';

  const typeSelect = document.getElementById('journalTypeSelect');
  typeSelect.innerHTML = craftJournalOptions.map(o => `<option value="${o.name}">${o.name}</option>`).join('');

  craftJournalType = (savedJournal && savedJournal.type) || item.default_journal || craftJournalOptions[0].name;
  typeSelect.value = craftJournalType;
  buildJournalTierOptions();

  craftJournalTier = (savedJournal && savedJournal.tier) || item.default_journal_tier;
  document.getElementById('journalTierSelect').value = craftJournalTier;

  craftUseJournal = savedJournal ? !!savedJournal.use : false;
  document.getElementById('useJournalChk').checked = craftUseJournal;
  document.getElementById('journalPickerRow').style.display = craftUseJournal ? '' : 'none';

  updateJournalIcon();
  renderCraftInventory();
}

// ------------------------------------------------------------
// POPUP SLOT JURNAL — dibuka dari klik slot "Jurnal Penuh" / "Jurnal
// Terisi Sebagian" di grid Inventory (lihat renderCraftInventory).
// FULL  : harga auto-terisi dari getJournalResourceValue(), tetap bisa
//         diedit manual (journalPriceOverride, ikut ke panel Profit).
// PARTIAL: cuma info progress fame, read-only, TIDAK ikut Profit.
// ------------------------------------------------------------
let journalPopupMode = null; // 'full' | 'partial'

function openJournalFullPopup() {
  if (!craftJournalTier || !ALBION_JOURNAL_REQUIREMENT[craftJournalTier]) return;
  journalPopupMode = 'full';
  const req  = ALBION_JOURNAL_REQUIREMENT[craftJournalTier];
  const full = Math.floor(totalFameAccumulated / req);
  const autoVal = getJournalResourceValue();

  document.getElementById('jpIcon').src = journalFullIconUrl(craftJournalType, craftJournalTier);
  document.getElementById('jpName').textContent = 'Jurnal Penuh';
  document.getElementById('jpSub').textContent = `× ${full}`;
  document.getElementById('jpFullFields').style.display = '';
  document.getElementById('jpPartialFields').style.display = 'none';
  document.getElementById('jpHarga').value = journalPriceOverride !== null
    ? journalPriceOverride
    : (autoVal !== null ? Math.round(autoVal) : '');
  document.getElementById('journalPopupOverlay').classList.add('show');
}

function openJournalPartialPopup() {
  if (!craftJournalTier || !ALBION_JOURNAL_REQUIREMENT[craftJournalTier]) return;
  journalPopupMode = 'partial';
  const req      = ALBION_JOURNAL_REQUIREMENT[craftJournalTier];
  const full     = Math.floor(totalFameAccumulated / req);
  const sisaFame = totalFameAccumulated - (full * req);

  document.getElementById('jpIcon').src = journalPartialIconUrl(craftJournalType, craftJournalTier);
  document.getElementById('jpName').textContent = 'Jurnal Terisi Sebagian';
  document.getElementById('jpSub').textContent = 'Progress ke jurnal berikutnya';
  document.getElementById('jpFullFields').style.display = 'none';
  document.getElementById('jpPartialFields').style.display = '';
  document.getElementById('jpFameProgress').textContent = `${Math.round(sisaFame)} / ${req}`;
  document.getElementById('journalPopupOverlay').classList.add('show');
}

function closeJournalPopup() {
  document.getElementById('journalPopupOverlay').classList.remove('show');
  journalPopupMode = null;
}
function closeJournalPopupOnBg(e) { if (e.target === document.getElementById('journalPopupOverlay')) closeJournalPopup(); }

function onJournalPriceOverrideInput() {
  const val = parseFloat(document.getElementById('jpHarga').value);
  journalPriceOverride = isNaN(val) ? null : val;
  renderCraftResultPanel();
  saveCraftState();
}

// Status jurnal (penuh + progress terisi sebagian) sekarang dirender
// langsung sebagai slot ikon di grid Inventory — lihat renderCraftInventory()
// dan popup openJournalFullPopup()/openJournalPartialPopup() di atas.

function selectCraftTarget(itemId) {
  fetch(`/api/crafting/item/${itemId}?station=${STATION}`)
    .then(r => r.json())
    .then(item => {
      craftTarget       = item;
      craftRecipes      = (item.recipe_groups && item.recipe_groups.length)
        ? item.recipe_groups
        : groupRecipeResources(item.resources);
      craftActiveRecipe = 0;
      craftInv          = [];
      totalFameAccumulated = 0; // sesi baru = akumulasi fame & modal-lock direset
      craftModalLock        = null;
      journalPriceOverride  = null;
      lockItemTable(itemId);
      renderCraftInventory();
      renderCraftSlots();
      renderJournalPicker(item); // reset ke default journal station ini (item baru = gak ada saved state)
      updateCraftQtyField();
      updateCraftModal();
      updateCraftButtonState();
      saveCraftState();
      document.getElementById('craftResultPanel').style.display = 'none';

      // Harga jual default = harga item hasil craft (weapon/equipment-nya sendiri),
      // dari cache dulu (instan) — kalau cache masih kosong, refreshCraftSellPrice()
      // di bawah bakal coba ambil real-time dan ngisi begitu datang.
      applyCraftSellPriceDefault(item.prices);
      refreshCraftSellPrice(itemId);
      fetchBahanDefaultPrices(craftRecipes);
    })
    .catch(() => showCraftToast('❌ Gagal memuat resep.'));
}

// ============================================================
// HARGA DEFAULT BAHAN — begitu target dipilih, fetch harga cache
// tiap bahan unik (item_id) dari semua resep sekaligus (Fine Cloth,
// Runewood Plank, dll), biar popup "Tambah ke Inventory" gak kosong
// kalau kamu klik Tambah tanpa ngetik harga dulu.
// ============================================================
function fetchBahanDefaultPrices(recipeGroups) {
  const ids = new Set();
  recipeGroups.forEach(group => group.forEach(r => { if (r.item_id) ids.add(r.item_id); }));
  const toFetch = [...ids].filter(id => !(id in bahanPriceCache));
  if (!toFetch.length) return;

  Promise.all(toFetch.map(id =>
    fetch('/api/crafting/item/' + id)
      .then(r => r.json())
      .then(item => {
        const prices = Object.values(item.prices || {}).filter(p => p > 0);
        bahanPriceCache[id] = prices.length ? Math.min(...prices) : 0; // harga termurah = biaya beli bahan
      })
      .catch(() => { bahanPriceCache[id] = 0; })
  ));
}

function applyCraftSellPriceDefault(prices) {
  const vals = Object.values(prices || {}).filter(p => p > 0);
  document.getElementById('craftSellPrice').value = vals.length ? Math.max(...vals) : '';
  craftSellPriceIsDefault = true; // tandai ini hasil auto-fill, boleh ditimpa refresh
}

// ============================================================
// REFRESH HARGA JUAL (background) — sama kayak refreshPopupPrices,
// coba ambil harga real-time ke Albion Online Data Project. Kalau
// user udah edit manual (craftSellPriceIsDefault=false) atau udah
// ganti ke item lain, jangan ditimpa.
// ============================================================
function refreshCraftSellPrice(itemId) {
  fetch(`/api/crafting/item/${itemId}/refresh-prices`, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'X-CSRF-TOKEN': getCsrf() },
  })
    .then(r => r.json())
    .then(data => {
      if (!craftTarget || craftTarget.id !== itemId) return; // udah pindah item
      if (!craftSellPriceIsDefault) return;                  // udah diedit manual, biarkan
      const vals = Object.values(data.prices || {}).filter(p => p > 0);
      if (vals.length) document.getElementById('craftSellPrice').value = Math.max(...vals);
    })
    .catch(() => {}); // gagal → biarkan nilai cache yang udah tampil
}

function switchCraftRecipe(gi) {
  if (gi === craftActiveRecipe) return;
  craftActiveRecipe = gi;
  craftInv = []; // ganti resep -> bahan yg udah dikumpulin buat resep lama gak relevan lagi
  renderCraftInventory();
  renderCraftSlots();
  updateCraftQtyField();
  updateCraftModal();
  updateCraftButtonState();
  saveCraftState();
  showCraftToast('🔄 Pindah ke Resep ' + (gi + 1));
}

function resetCraftTarget() {
  craftTarget       = null;
  craftRecipes      = [];
  craftActiveRecipe = 0;
  craftInv          = [];
  craftUseJournal   = false;
  craftJournalOptions = [];
  totalFameAccumulated = 0;
  craftModalLock        = null;
  journalPriceOverride  = null;
  document.getElementById('craftJournalSection').style.display = 'none';
  document.getElementById('journalPickerRow').style.display = 'none';
  unlockItemTable();
  renderCraftInventory();
  renderCraftSlots();
  updateCraftQtyField();
  updateCraftModal();
  updateCraftButtonState();
  clearCraftState();
  document.getElementById('craftSellPrice').value = '';
  craftSellPriceIsDefault = false;
  document.getElementById('craftResultPanel').style.display = 'none';
}

// ------------------------------------------------------------
// KUNCI TABEL — item lain digelapin & gak bisa diklik/discroll,
// cuma item yg dipilih yg tetap normal.
// ------------------------------------------------------------
function lockItemTable(selectedId) {
  document.getElementById('itemTableWrap').classList.add('ct-locked');
  document.querySelectorAll('#itemGrid .item-row').forEach(row => {
    if (parseInt(row.dataset.itemId) === selectedId) {
      row.classList.add('ct-selected');
      row.classList.remove('ct-dim');
    } else {
      row.classList.add('ct-dim');
      row.classList.remove('ct-selected');
    }
  });
}

function unlockItemTable() {
  document.getElementById('itemTableWrap').classList.remove('ct-locked');
  document.querySelectorAll('#itemGrid .item-row').forEach(row => {
    row.classList.remove('ct-dim', 'ct-selected');
  });
}

// ------------------------------------------------------------
// SLOT RESEP — preview bahan (dari resep paling terpenuhi) + target
// ------------------------------------------------------------
function renderCraftSlots() {
  const tabsWrap = document.getElementById('craftRecipeTabs');
  const gridWrap = document.getElementById('craftSlotsGrid');
  document.getElementById('craftResetRow').style.display = craftTarget ? '' : 'none';

  if (!craftTarget) {
    tabsWrap.innerHTML = '';
    gridWrap.innerHTML = '<div class="craft-slots-empty">Pilih item dari daftar di atas untuk mulai crafting 🪄</div>';
    return;
  }

  // Tab Resep 1 / Resep 2 — cuma ditampilin kalau emang ada >1 alternatif resep
  tabsWrap.innerHTML = craftRecipes.length > 1
    ? craftRecipes.map((_, gi) => `
        <button class="craft-tab ${gi === craftActiveRecipe ? 'active' : ''}" onclick="switchCraftRecipe(${gi})">Resep ${gi + 1}</button>
      `).join('')
    : '';

  const activeGroup = craftRecipes[craftActiveRecipe] || [];
  const matSlots = activeGroup.slice(0, 4).map((r, ri) => {
    const have = getInvQty(r.item_id, r.name);
    const ok = have >= r.count;
    return `<div class="craft-slot ${ok ? 'ok' : ''}" title="${r.name}" onclick="openResourceAdd(${craftActiveRecipe}, ${ri})">
      <img src="${r.img_url || ''}" alt="${r.name}" onerror="this.style.opacity=.3">
      <span class="cs-need">${have}/${r.count}</span>
    </div>`;
  });
  while (matSlots.length < 4) matSlots.push('<div class="craft-slot"></div>');

  const maxCraftable = getMaxCraftable(activeGroup);
  gridWrap.innerHTML = `
    ${matSlots.join('')}
    <span class="craft-arrow">→</span>
    <div class="craft-slot ${maxCraftable > 0 ? 'ok' : ''}" title="${craftTarget.name}">
      <img src="${craftTarget.img_url || ''}" alt="${craftTarget.name}" onerror="this.style.opacity=.3">
      <span class="cs-need">max ${maxCraftable}</span>
    </div>`;
}

// ------------------------------------------------------------
// POPUP TAMBAH / EDIT BAHAN
// ------------------------------------------------------------
function openResourceAdd(gi, ri) {
  const r = craftRecipes[gi][ri];
  craftPending = r;
  craftEditIdx = null;
  const existing = craftInv.find(i => (r.item_id ? i.itemId === r.item_id : i.name === r.name));

  document.getElementById('caIcon').src = r.img_url || '';
  document.getElementById('caName').textContent = r.name;
  document.getElementById('caNeed').textContent = 'Dibutuhkan ' + r.count + ' / craft';
  document.getElementById('caHarga').value = existing ? (existing.harga || '') : (bahanPriceCache[r.item_id] || '');
  document.getElementById('caQty').value = r.count;
  document.getElementById('caBtnRow').innerHTML = `<button class="wiz-btn-hitung" style="flex:1" onclick="doCraftAddResource()">➕ Tambah ke Inventory</button>`;
  document.getElementById('craftAddOverlay').classList.add('show');
}

function openCraftInvEdit(idx) {
  const inv = craftInv[idx];
  if (!inv) return;
  craftPending = null;
  craftEditIdx = idx;

  document.getElementById('caIcon').src = inv.imgUrl || '';
  document.getElementById('caName').textContent = inv.name;
  document.getElementById('caNeed').textContent = 'Ubah jumlah / harga, atau hapus';
  document.getElementById('caHarga').value = inv.harga || bahanPriceCache[inv.itemId] || '';
  document.getElementById('caQty').value = inv.qty;
  document.getElementById('caBtnRow').innerHTML = `
    <button class="wiz-btn-hitung" style="flex:1" onclick="doCraftEditResource()">💾 Simpan</button>
    <button class="reset-btn" onclick="doCraftDeleteResource()">🗑</button>`;
  document.getElementById('craftAddOverlay').classList.add('show');
}

function closeCraftAddOverlay() {
  document.getElementById('craftAddOverlay').classList.remove('show');
  craftPending = null;
  craftEditIdx = null;
}
function closeCraftAddOnBg(e) { if (e.target === document.getElementById('craftAddOverlay')) closeCraftAddOverlay(); }

function doCraftAddResource() {
  if (!craftPending) return;
  const r = craftPending;
  const qty   = Math.max(1, parseInt(document.getElementById('caQty').value) || 1);
  const harga = parseFloat(document.getElementById('caHarga').value) || 0;
  const existing = craftInv.find(i => (r.item_id ? i.itemId === r.item_id : i.name === r.name));

  if (existing) {
    existing.qty += qty;
    if (harga) existing.harga = harga;
  } else {
    craftInv.push({ itemId: r.item_id ?? null, name: r.name, imgUrl: r.img_url, qty, harga });
  }

  closeCraftAddOverlay();
  renderCraftInventory();
  renderCraftSlots();
  updateCraftQtyField();
  updateCraftModal();
  updateCraftButtonState();
  saveCraftState();
  showCraftToast(`📦 ${r.name} → ${qty}`);
}

function doCraftEditResource() {
  if (craftEditIdx === null) return;
  craftInv[craftEditIdx].qty   = Math.max(1, parseInt(document.getElementById('caQty').value) || 1);
  craftInv[craftEditIdx].harga = parseFloat(document.getElementById('caHarga').value) || 0;
  closeCraftAddOverlay();
  renderCraftInventory(); renderCraftSlots(); updateCraftQtyField(); updateCraftModal(); updateCraftButtonState();
  saveCraftState();
  showCraftToast('✏️ Diperbarui');
}

function doCraftDeleteResource() {
  if (craftEditIdx === null) return;
  craftInv.splice(craftEditIdx, 1);
  closeCraftAddOverlay();
  renderCraftInventory(); renderCraftSlots(); updateCraftQtyField(); updateCraftModal(); updateCraftButtonState();
  saveCraftState();
  showCraftToast('🗑 Dihapus');
}

// ------------------------------------------------------------
// INVENTORY GRID
// ------------------------------------------------------------
function renderCraftInventory() {
  const grid = document.getElementById('craftInvGrid');
  let html = '';

  // Slot jurnal (PENUH + TERISI SEBAGIAN) — cuma muncul kalau "Gunakan
  // Jurnal" aktif dan tier-nya sudah dipilih. Dihitung dari
  // totalFameAccumulated, real-time tiap kali grid ini di-render ulang.
  if (craftUseJournal && craftJournalTier && ALBION_JOURNAL_REQUIREMENT[craftJournalTier]) {
    const req      = ALBION_JOURNAL_REQUIREMENT[craftJournalTier];
    const full     = Math.floor(totalFameAccumulated / req);
    const sisaFame = totalFameAccumulated - (full * req);

    html += `<div class="cinv-slot journal-full" title="Jurnal Penuh × ${full} — klik buat atur harga" onclick="openJournalFullPopup()">
      <img src="${journalFullIconUrl(craftJournalType, craftJournalTier)}" alt="Jurnal Penuh" onerror="this.style.opacity=.3">
      <span class="cinv-qty">${full}</span>
    </div>`;

    html += `<div class="cinv-slot journal-partial" title="Progress ${Math.round(sisaFame)}/${req} fame" onclick="openJournalPartialPopup()">
      <img src="${journalPartialIconUrl(craftJournalType, craftJournalTier)}" alt="Jurnal Terisi Sebagian" onerror="this.style.opacity=.3">
      <span class="cinv-qty-frac">${Math.round(sisaFame)}/${req}</span>
    </div>`;
  }

  const slots = Math.max(craftInv.length, 10);
  for (let s = 0; s < slots; s++) {
    const inv = craftInv[s];
    if (inv) {
      html += `<div class="cinv-slot filled" title="${inv.name} × ${inv.qty}" onclick="openCraftInvEdit(${s})">
        <img src="${inv.imgUrl || ''}" alt="${inv.name}" onerror="this.style.opacity=.3">
        <span class="cinv-qty">${inv.qty}</span>
      </div>`;
    } else {
      html += '<div class="cinv-slot"></div>';
    }
  }
  grid.innerHTML = html;
  document.getElementById('craftInvCount').textContent = craftInv.length;
}

// ------------------------------------------------------------
// TOMBOL CRAFT
// ------------------------------------------------------------
function getCraftQty() {
  return Math.max(1, parseInt(document.getElementById('craftQty').value) || 1);
}

function updateCraftButtonState() {
  const group = craftRecipes[craftActiveRecipe];
  let canCraft = false;
  if (craftTarget && group && group.length) {
    if (isHabiskanBahanMode()) {
      const retPct = Math.min(100, Math.max(0, parseFloat(document.getElementById('craftReturn').value) || 0));
      canCraft = simulateLoopN(group, retPct).total >= 1;
    } else {
      canCraft = isGroupSatisfied(group, getCraftQty());
    }
  }
  document.getElementById('craftBtn').disabled = !canCraft;
}

function updateCraftModal() {
  const group = craftRecipes[craftActiveRecipe] || [];
  let total = 0;
  if (isHabiskanBahanMode()) {
    const retPct = Math.min(100, Math.max(0, parseFloat(document.getElementById('craftReturn').value) || 0));
    const { stock, invs } = simulateLoopN(group, retPct);
    group.forEach((r, i) => {
      const inv = invs[i];
      if (!inv) return;
      total += Math.max(0, inv.qty - stock[i]) * (inv.harga || 0);
    });
  } else {
    const jumlah = getCraftQty();
    group.forEach(r => {
      const inv = craftInv.find(i => (r.item_id ? i.itemId === r.item_id : i.name === r.name));
      total += (inv?.harga || 0) * r.count * jumlah;
    });
  }
  document.getElementById('craftModalVal').textContent = formatSilver(total);
}

// Simulasi loop craft berulang sampai bahan mentok — generalisasi dari
// simulateLoop() di refine.blade.php (yang hardcode 2 bahan raw+prev)
// ke N bahan sesuai isi 'group' (resep aktif, 2-4 jenis bahan berbeda).
// Tiap putaran: floor(stok/kebutuhan) per bahan -> ambil yang paling
// kecil (bottleneck) -> kurangin stok -> hasil return% masuk balik ke
// stok -> ulangi sampai dapat=0.
function simulateLoopN(group, retPct) {
  const ret  = retPct / 100;
  const invs = group.map(r => craftInv.find(i => (r.item_id ? i.itemId === r.item_id : i.name === r.name)));
  let stock  = invs.map(inv => inv ? inv.qty : 0);
  const req  = group.map(r => r.count);
  let total  = 0;

  while (true) {
    let dapat = Infinity;
    for (let i = 0; i < req.length; i++) {
      if (req[i] > 0) dapat = Math.min(dapat, Math.floor(stock[i] / req[i]));
    }
    if (dapat === 0 || dapat === Infinity) break;

    for (let i = 0; i < req.length; i++) stock[i] -= dapat * req[i];
    total += dapat;
    for (let i = 0; i < req.length; i++) stock[i] += Math.round(dapat * req[i] * ret);
  }

  return { total, stock, invs }; // stock = sisa tiap bahan sesuai urutan 'group'/'invs'
}

function doCraft() {
  if (!craftTarget) return;
  const group = craftRecipes[craftActiveRecipe];
  if (!group || !group.length) return;
  const retPct    = Math.min(100, Math.max(0, parseFloat(document.getElementById('craftReturn').value) || 0));
  const sellPrice = parseFloat(document.getElementById('craftSellPrice').value) || 0;

  // Checkbox 'craftHabiskanBahan' sudah dipasang di bottom bar (mutual
  // exclusive dengan 'Max' — lihat onCraftHabisChange/onCraftHabiskanBahanChange).
  const habisEl = document.getElementById('craftHabiskanBahan');
  const habis   = habisEl ? habisEl.checked : false;

  let jumlah, modal = 0; // modal = total harga bahan yg beneran kepakai (gak balik lewat return%)

  if (habis) {
    const { total, stock, invs } = simulateLoopN(group, retPct);
    if (total < 1) return;
    jumlah = total;
    group.forEach((r, i) => {
      const inv = invs[i];
      if (!inv) return;
      const consumed = Math.max(0, inv.qty - stock[i]);
      modal += consumed * (inv.harga || 0);
      inv.qty = stock[i];
      if (inv.qty <= 0) craftInv.splice(craftInv.indexOf(inv), 1);
    });
  } else {
    jumlah = Math.min(getCraftQty(), getMaxCraftable(group));
    if (jumlah < 1 || !isGroupSatisfied(group, jumlah)) return;
    group.forEach(r => {
      const inv = craftInv.find(i => (r.item_id ? i.itemId === r.item_id : i.name === r.name));
      if (!inv) return;
      const totalCount = r.count * jumlah;
      const returned = Math.round(totalCount * retPct / 100);
      const consumed = totalCount - returned;
      modal += consumed * (inv.harga || 0);
      inv.qty -= consumed;
      if (inv.qty <= 0) craftInv.splice(craftInv.indexOf(inv), 1);
    });
  }

  const exOut = craftInv.find(i => i.itemId === craftTarget.id);
  if (exOut) {
    exOut.qty += jumlah;
  } else {
    craftInv.push({ itemId: craftTarget.id, name: craftTarget.name, imgUrl: craftTarget.img_url, qty: jumlah, harga: sellPrice || 0 });
  }

  // Modal Bahan Crafting — SNAPSHOT sekali di craft PERTAMA sesi ini,
  // gak berubah lagi meski craft berkali-kali sesudahnya.
  if (craftModalLock === null) craftModalLock = modal;

  // Fame numpuk ke totalFameAccumulated CUMA kalau "Gunakan Jurnal" aktif
  // pas craft ini terjadi, pakai fame.F_B (bukan F_C — journal gak kena
  // bonus item_type). Butuh field 'fame' dari itemDetail API (Bagian A).
  if (craftUseJournal && craftTarget.fame && craftTarget.fame.F_B != null) {
    totalFameAccumulated += craftTarget.fame.F_B * jumlah;
  }

  renderCraftInventory();
  renderCraftSlots();
  updateCraftQtyField();
  updateCraftModal();
  updateCraftButtonState();
  renderCraftResultPanel();
  saveCraftState();
  showCraftToast(`⚒️ Berhasil membuat ${jumlah}× ${craftTarget.name}!`);
}

// ============================================================
// PANEL HASIL CRAFT — dipanggil ulang tiap kali: abis Craft,
// checkbox/dropdown journal berubah, atau field Yield% diubah, biar
// angka selalu sinkron.
//
// MODAL: Bahan Crafting (snapshot craftModalLock, FIXED sepanjang sesi)
//        + Jurnal Dibutuhkan (DINAMIS, dari totalFameAccumulated: total
//        journal kosong TERPAKAI dari awal sesi × harga journal kosong).
// PROFIT: Item Hasil Craft (real-time, qty × harga di craftInv) + Sisa
//        Bahan (real-time, calcGroupValue) + Jurnal Penuh (DINAMIS:
//        floor(fame/requirement) × nilai resource balik journal × yield%)
//        + Total Profit (jumlah Profit − jumlah Modal).
// ============================================================
function renderCraftResultPanel() {
  if (craftModalLock === null) return; // belum pernah craft di sesi ini

  document.getElementById('craftResultPanel').style.display = '';
  const group = craftRecipes[craftActiveRecipe] || [];

  // --- MODAL ---
  document.getElementById('crpModalBahan').textContent = formatSilver(craftModalLock);

  const showJurnal = craftUseJournal && craftJournalTier && ALBION_JOURNAL_REQUIREMENT[craftJournalTier];
  document.getElementById('crpModalJurnalRow').style.display  = showJurnal ? '' : 'none';
  document.getElementById('crpProfitJurnalRow').style.display = showJurnal ? '' : 'none';

  let jurnalModalVal = 0, jurnalProfitVal = 0;
  if (showJurnal) {
    const req   = ALBION_JOURNAL_REQUIREMENT[craftJournalTier];
    const harga = ALBION_JOURNAL_PRICE[craftJournalTier] || 0;
    const needed = Math.ceil(totalFameAccumulated / req); // total journal kosong TERPAKAI sejak awal sesi
    const full   = Math.floor(totalFameAccumulated / req);
    jurnalModalVal = needed * harga;
    document.getElementById('crpModalJurnal').textContent = formatSilver(jurnalModalVal);

    // Jurnal Penuh (Profit): kalau user pernah edit harga lewat popup slot
    // Inventory (journalPriceOverride), pakai itu. Kalau belum, pakai
    // hitungan otomatis dari resource_value_by_tier (getJournalResourceValue).
    const unitValue = journalPriceOverride !== null ? journalPriceOverride : getJournalResourceValue();
    document.getElementById('crpProfitJurnal').textContent = unitValue === null ? '—' : formatSilver(full * unitValue);
    if (unitValue !== null) jurnalProfitVal = full * unitValue;
  }

  // --- TOTAL MODAL ---
  const totalModal = craftModalLock + jurnalModalVal;
  document.getElementById('crpTotalModal').textContent = formatSilver(totalModal);

  // --- PROFIT ---
  const exOut = craftTarget ? craftInv.find(i => i.itemId === craftTarget.id) : null;
  const itemHasilVal = exOut ? exOut.qty * (exOut.harga || 0) : 0;
  document.getElementById('crpProfitItem').textContent = formatSilver(itemHasilVal);

  const sisaBahanVal = calcGroupValue(group);
  document.getElementById('crpProfitSisa').textContent = formatSilver(sisaBahanVal);

  // --- HASIL AKHIR (subtotal Profit, sebelum dikurangi Modal) ---
  const hasilAkhir = itemHasilVal + sisaBahanVal + jurnalProfitVal;
  document.getElementById('crpHasilAkhir').textContent = formatSilver(hasilAkhir);

  // --- TOTAL PROFIT ---
  const totalProfit = hasilAkhir - totalModal;
  const totalEl = document.getElementById('crpTotalProfit');
  totalEl.classList.remove('positive', 'negative');
  totalEl.textContent = (totalProfit >= 0 ? '+' : '-') + formatSilver(Math.abs(totalProfit));
  totalEl.classList.add(totalProfit >= 0 ? 'positive' : 'negative');
}

function showCraftToast(msg) {
  const t = document.getElementById('craftToast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2200);
}

// ============================================================
// INIT
// ============================================================

// ============================================================
// INIT
// ============================================================
const STATION = '{{ $station ?? "mage-tower" }}';
const CRAFT_API_BASE = STATION === 'mage-tower' ? '/api/crafting' : `/api/crafting/${STATION}`;

fetch(`${CRAFT_API_BASE}/categories`)
  .then(r => r.json())
  .then(data => {
    CATEGORIES = data;
    restoreFilterState(); // Bug D fix: pulihkan filter kategori/tier/enc/search sebelum build dropdown
    refreshCols();        // buildCol1() + buildCol2() (yg otomatis manggil buildCol3()) — biar dropdown
                           // sub-kategori kolom 2/3 ikut kebangun sesuai state restore, bukan cuma kolom 1
    buildTierDrop();
    buildEncDrop();
    updateCatLabel();
    if (selTier)          setFilterVal('lblTier', 'valTier', TIER_LABEL[selTier]);
    if (selEnc !== null)  setFilterVal('lblEnc', 'valEnc', 'Enc ' + selEnc);
    if (searchQ)          document.getElementById('searchInput').value = searchQ;
    fetchItems(); // load semua item dari awal (Mode Advance), gak perlu pilih kategori dulu
    wizMtBuildCat1(); // siapkan step 1 wizard Mode Simple
    loadCraftState(); // pulihkan target + inventory bahan dari sesi sebelumnya (kalau ada)
  });
renderCraftInventory();
renderCraftSlots();
updateCraftQtyField();
updateCraftModal();
setMTMode(localStorage.getItem('mt_mode') || 'simple');
</script>
<x-comments page="mages-tower" />
@endsection