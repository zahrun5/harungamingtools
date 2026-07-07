@extends('layouts.app')

@section('title', 'Kalkulator Refine - HarunGamingTools')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
<style>


.rw {
  --bd:      #6b4f1a;
  --gold:    #f0c040;
  --gold-dk: #b8860b;
  --lt:      #dcc08a;
  --dim:     #a08040;
  --sbg:     #1a1208;
  --sbd:     #4a3510;
  background: #0d0a05;
  color: var(--lt);
  font-family: 'Crimson Text', Georgia, serif;
  margin: -48px -24px;
  padding: 20px 0 60px;
}
.rw * { box-sizing: border-box; }
.rw .app { max-width: 900px; margin: 0 auto; padding: 0 12px; display: flex; flex-direction: column; gap: 10px; }

/* PANEL */
.rw .panel { background: linear-gradient(180deg,#2e2210,#1e1608); border: 2px solid var(--bd); border-radius: 4px; box-shadow: 0 4px 24px rgba(0,0,0,.7); overflow: hidden; }
.rw .ph { background: linear-gradient(180deg,#3d2e15,#2a1f0e); border-bottom: 1px solid var(--bd); padding: 9px 14px; display: flex; align-items: center; gap: 8px; }
.rw .ph-title { font-family:'Cinzel',serif; font-size:13px; color:var(--gold); letter-spacing:1px; text-transform:uppercase; flex:1; }
.rw .api-status { font-size:10px; font-family:'Cinzel',serif; color:var(--dim); }
.rw .api-status.loading { color:var(--gold); }
.rw .api-status.ok { color:#6b8; }
.rw .api-status.err { color:#f86; }

/* HEADER SEARCH (ala Market) */
.rw .header-search { margin-left:auto; background:linear-gradient(180deg,#1a1208,#110e05); border:1px solid var(--sbd); border-radius:3px; color:var(--lt); font-family:'Crimson Text',serif; font-size:13px; padding:6px 10px; outline:none; transition:border-color .15s; width:170px; }
.rw .header-search:focus { border-color:var(--gold-dk); }
.rw .header-search::placeholder { color:var(--dim); }

/* FILTER BAR (ala Market — tombol + dropdown popover) */
.rw .flt-bar { background: linear-gradient(180deg,#251a08,#1a1005); border-bottom:1px solid var(--bd); padding:10px 12px; display:flex; gap:8px; flex-wrap:nowrap; overflow-x:auto; overflow-y:visible; position:relative; }
.rw .flt-wrap { position:relative; }
.rw .flt-btn { display:flex; align-items:center; gap:6px; background:linear-gradient(180deg,#c8a84a,#a07828); border:1px solid #8b6820; border-radius:3px; color:#2a1800; font-family:'Cinzel',serif; font-size:12px; font-weight:700; letter-spacing:.5px; padding:7px 12px; cursor:pointer; user-select:none; white-space:nowrap; transition:all .1s; justify-content:space-between; }
.rw .flt-btn:hover { background:linear-gradient(180deg,#dabb5a,#b88838); border-color:var(--gold); }
.rw .flt-btn.open { background:linear-gradient(180deg,#b89030,#907020); border-color:var(--gold); box-shadow:0 0 8px rgba(240,192,64,.3); }
.rw .flt-btn .flt-label { flex:1; text-align:left; }
.rw .flt-btn .flt-val { font-size:10px; opacity:.75; max-width:90px; overflow:hidden; text-overflow:ellipsis; }
.rw .flt-btn .flt-arrow { font-size:8px; opacity:.7; margin-left:2px; transition:transform .15s; }
.rw .flt-btn.open .flt-arrow { transform:rotate(180deg); }

.rw .drop-wrap { position:fixed; z-index:9999; display:none; gap:2px; filter:drop-shadow(0 6px 20px rgba(0,0,0,.85)); }
.rw .drop-wrap.show { display:flex; }
.rw .drop-col { min-width:175px; max-height:370px; overflow-y:auto; background:linear-gradient(180deg,#e8cf88,#d4b468); border:1px solid #8b6820; border-radius:3px; padding:4px; display:flex; flex-direction:column; gap:2px; }
.rw .drop-item { display:flex; align-items:center; justify-content:space-between; padding:8px 10px; border-radius:2px; background:transparent; border:1px solid transparent; cursor:pointer; font-family:'Crimson Text',serif; font-size:14px; font-weight:600; color:#2a1800; transition:all .08s; white-space:nowrap; }
.rw .drop-item:hover { background:linear-gradient(180deg,#f2dc9a,#e2c878); border-color:#a07828; }
.rw .drop-item.active { background:linear-gradient(180deg,#b88a28,#906818); border-color:#7a5010; color:#fff8e0; }
.rw .drop-item .di-arrow { font-size:9px; color:#6b4f1a; margin-left:8px; flex-shrink:0; }
.rw .drop-item.active .di-arrow { color:#ffe090; }

/* ITEM LIST (ala Market — icon 48px, cuma nama, harga di kanan) */
.rw .item-list { max-height:420px; overflow-y:auto; }
.rw .item-row { display:flex; align-items:center; gap:10px; padding:8px 10px; border-bottom:1px solid rgba(107,79,26,.3); cursor:pointer; transition:background .1s; }
.rw .item-row:hover { background:rgba(61,46,21,.5); }
.rw .item-icon { width:48px; height:48px; border:1px solid var(--sbd); border-radius:3px; background:var(--sbg); display:block; object-fit:contain; flex-shrink:0; }
.rw .item-name { font-family:'Crimson Text',serif; font-size:15px; color:var(--lt); font-weight:600; flex:1; }
.rw .item-price { font-size:12px; color:var(--gold); font-family:'Cinzel',serif; min-width:55px; text-align:right; }

/* BOTTOM BAR */
.rw .bot-bar { display:flex; gap:6px; align-items:center; padding:8px 12px; border-top:1px solid var(--bd); background:linear-gradient(180deg,#251a08,#1a1005); flex-wrap:wrap; }
.rw .ret-wrap { display:flex; align-items:center; gap:5px; background:var(--sbg); border:1px solid var(--sbd); border-radius:3px; padding:5px 9px; }
.rw .ret-wrap label { font-size:11px; color:var(--dim); white-space:nowrap; }
.rw .ret-inp { width:48px; background:transparent; border:none; color:var(--gold); font-size:14px; font-weight:600; text-align:right; outline:none; }
.rw .prem-wrap { display:flex; align-items:center; gap:5px; background:var(--sbg); border:1px solid var(--sbd); border-radius:3px; padding:5px 9px; cursor:pointer; }
.rw .prem-wrap span { font-size:11px; color:var(--dim); white-space:nowrap; }
.rw .prem-wrap input { width:14px; height:14px; accent-color:var(--gold); cursor:pointer; }
.rw .inv-btn { background:linear-gradient(180deg,#4a3818,#2e2210); border:1px solid var(--bd); border-radius:3px; color:var(--lt); font-size:12px; padding:7px 12px; cursor:pointer; white-space:nowrap; transition:all .15s; flex:1; }
.rw .inv-btn:hover, .rw .inv-btn.active { border-color:var(--gold); color:var(--gold); }
.rw .reset-btn { background:linear-gradient(180deg,#6b1a1a,#4a1010); border:1px solid #8b3030; border-radius:3px; color:#f0c0c0; font-size:12px; padding:7px 12px; cursor:pointer; white-space:nowrap; }
.rw .reset-btn:hover { border-color:#c04040; }

/* INVENTORY */
.rw .inv-section { padding:10px 12px; display:none; border-top:1px solid var(--bd); }
.rw .inv-section.show { display:block; }
.rw .inv-lbl { font-family:'Cinzel',serif; font-size:10px; color:var(--dim); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px; }
.rw .inv-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:4px; }
.rw .slot { aspect-ratio:1; background:var(--sbg); border:1px solid var(--sbd); border-radius:3px; position:relative; cursor:pointer; overflow:hidden; }
.rw .slot.filled:hover { border-color:var(--gold); }
.rw .slot img { width:100%; height:100%; object-fit:contain; display:block; }
.rw .slot .st { position:absolute; top:1px; left:1px; font-family:'Cinzel',serif; font-size:7px; font-weight:700; color:#fff; background:rgba(0,0,0,.7); padding:0 2px; border-radius:1px; }
.rw .slot .sq { position:absolute; bottom:1px; right:2px; font-size:9px; font-weight:700; color:#fff; text-shadow:0 1px 2px #000; }
.rw .slot .se { position:absolute; bottom:1px; left:2px; font-size:7px; font-weight:700; text-shadow:0 1px 2px #000; }
.rw .slot .sh { position:absolute; top:1px; right:2px; font-size:7px; color:var(--gold); font-family:'Cinzel',serif; text-shadow:0 1px 2px #000; }

/* TOMBOL REFINE */
.rw .refine-btns { padding:10px 12px; display:none; border-top:1px solid var(--bd); background:rgba(0,0,0,.2); }
.rw .refine-btns.show { display:block; }
.rw .refine-btns-lbl { font-family:'Cinzel',serif; font-size:10px; color:var(--dim); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; }
.rw .refine-btns-grid { display:flex; flex-wrap:wrap; gap:6px; }
.rw .rbtn { display:flex; flex-direction:column; align-items:center; gap:3px; background:linear-gradient(180deg,#3d2e15,#2a1f0e); border:1px solid var(--bd); border-radius:4px; padding:8px; cursor:pointer; transition:all .15s; min-width:68px; }
.rw .rbtn:hover { border-color:var(--gold); background:linear-gradient(180deg,#5a4520,#3a2c10); }
.rw .rbtn img { width:40px; height:40px; object-fit:contain; }
.rw .rbtn .rb-name { font-size:9px; color:var(--lt); font-family:'Cinzel',serif; text-align:center; line-height:1.2; }
.rw .rbtn .rb-qty { font-size:10px; color:var(--gold); font-family:'Cinzel',serif; font-weight:700; }

/* COIN FOOTER */
.rw .coin-footer { display:none; flex-direction:column; padding:10px 12px; background:rgba(0,0,0,.3); border-top:1px solid var(--bd); gap:8px; }
.rw .coin-footer.show { display:flex; }
.rw .coin-row { display:flex; justify-content:space-between; align-items:center; }
.rw .coin-side { display:flex; align-items:center; gap:8px; }
.rw .coin-icon { width:26px; height:26px; border-radius:50%; background:radial-gradient(circle at 35% 35%,#888,#333); display:flex; align-items:center; justify-content:center; font-size:13px; border:2px solid #555; flex-shrink:0; }
.rw .coin-lbl { font-size:10px; color:var(--dim); display:block; }
.rw .coin-val { font-family:'Cinzel',serif; font-size:15px; font-weight:700; color:var(--gold); }
.rw .tax-row { display:flex; justify-content:space-between; padding-top:6px; border-top:1px solid rgba(107,79,26,.4); }
.rw .tax-item { display:flex; flex-direction:column; }
.rw .tax-item.right { align-items:flex-end; }
.rw .tax-lbl { font-size:10px; color:var(--dim); }
.rw .tax-val { font-family:'Cinzel',serif; font-size:13px; color:var(--lt); }
.rw .profit-val { font-family:'Cinzel',serif; font-size:15px; font-weight:700; }
.rw .profit-val.pos { color:#6f8; }
.rw .profit-val.neg { color:#f86; }

/* POPUP */
.rw .overlay { position:fixed; inset:0; background:rgba(0,0,0,.78); z-index:100; display:none; align-items:center; justify-content:center; }
.rw .overlay.show { display:flex; }
.rw .popup { background:linear-gradient(180deg,#3d2e15,#2a1f0e); border:2px solid var(--bd); border-radius:4px; box-shadow:0 8px 40px rgba(0,0,0,.9); width:310px; max-width:95vw; overflow:hidden; }
.rw .pop-head { display:flex; gap:10px; padding:12px; background:linear-gradient(180deg,#4a3818,#2e2210); border-bottom:1px solid var(--bd); align-items:flex-start; }
.rw .pop-icon { width:52px; height:52px; border:1px solid var(--sbd); border-radius:3px; background:var(--sbg); flex-shrink:0; object-fit:contain; }
.rw .pop-name { font-family:'Cinzel',serif; font-size:14px; color:var(--gold); margin-bottom:3px; }
.rw .pop-desc { font-size:11px; color:var(--dim); font-style:italic; }
.rw .pop-close { margin-left:auto; background:linear-gradient(180deg,#6b1a1a,#4a1010); border:1px solid #8b3030; border-radius:50%; color:#f0c0c0; width:24px; height:24px; font-size:13px; cursor:pointer; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.rw .pop-body { padding:12px; display:flex; flex-direction:column; gap:10px; }
.rw .pop-field label { display:block; font-family:'Cinzel',serif; font-size:10px; color:var(--dim); text-transform:uppercase; letter-spacing:.5px; margin-bottom:5px; }
.rw .pop-field input[type=number] { width:100%; background:var(--sbg); border:1px solid var(--sbd); border-radius:3px; color:var(--lt); font-size:14px; padding:6px 9px; outline:none; }
.rw .pop-field input[type=number]:focus { border-color:var(--gold); }
.rw .slider-wrap { display:flex; align-items:center; gap:7px; }
.rw .slider-wrap input[type=range] { flex:1; accent-color:var(--gold); }
.rw .slider-val { background:var(--sbg); border:1px solid var(--sbd); border-radius:3px; color:var(--gold); font-family:'Cinzel',serif; font-size:13px; font-weight:700; width:52px; text-align:center; padding:4px 5px; outline:none; }
.rw .check-row { display:flex; align-items:center; gap:7px; font-size:12px; color:var(--dim); cursor:pointer; }
.rw .check-row input { width:15px; height:15px; accent-color:var(--gold); cursor:pointer; }
.rw .pop-btn-row { display:flex; gap:7px; }
.rw .btn-add { flex:1; background:linear-gradient(180deg,#4a6b1a,#2e4210); border:1px solid #6b8b30; border-radius:3px; color:#d0f0a0; font-family:'Cinzel',serif; font-size:12px; font-weight:700; letter-spacing:1px; padding:10px; cursor:pointer; text-transform:uppercase; }
.rw .btn-add:hover { border-color:#8bc040; color:#fff; }
.rw .btn-refine { flex:1; background:linear-gradient(180deg,#8b4a00,#5a2e00); border:1px solid #c06010; border-radius:3px; color:var(--gold); font-family:'Cinzel',serif; font-size:12px; font-weight:700; letter-spacing:1px; padding:10px; cursor:pointer; text-transform:uppercase; }
.rw .btn-refine:hover { background:linear-gradient(180deg,#a05800,#703800); }
.rw .btn-del { background:linear-gradient(180deg,#6b1a1a,#4a1010); border:1px solid #8b3030; border-radius:3px; color:#f0c0c0; font-family:'Cinzel',serif; font-size:12px; font-weight:700; padding:10px 13px; cursor:pointer; }
.rw .btn-del:hover { border-color:#c04040; }
.rw .pop-info { font-size:11px; color:var(--dim); background:rgba(0,0,0,.3); border-radius:3px; padding:6px 9px; line-height:1.6; }
.rw .pop-info span { color:var(--lt); font-weight:600; }
.rw .bahan-row { display:flex; gap:8px; align-items:center; margin-bottom:8px; flex-wrap:wrap; }
.rw .bahan-slot { display:flex; flex-direction:column; align-items:center; gap:3px; }
.rw .bahan-slot img { width:44px; height:44px; border:1px solid var(--sbd); border-radius:3px; background:var(--sbg); object-fit:contain; }
.rw .bahan-qty { font-family:'Cinzel',serif; font-size:11px; font-weight:700; text-align:center; }
.rw .bahan-qty .punya { color:#6f8; }
.rw .bahan-qty .slash { color:var(--dim); }
.rw .bahan-qty .butuh { color:var(--dim); }
.rw .bahan-qty .kurang { color:#f86; }
.rw .bahan-name { font-size:9px; color:var(--dim); text-align:center; max-width:52px; line-height:1.2; }
.rw .bahan-arrow { font-size:16px; color:var(--dim); align-self:center; padding-bottom:18px; }
.rw .fallback-tag { font-size:9px; color:var(--dim); font-style:italic; }

.rw .empty-inv { text-align:center; padding:18px; color:var(--dim); font-style:italic; font-size:13px; }
.rw .toast { position:fixed; bottom:20px; left:50%; transform:translateX(-50%) translateY(60px); background:#3d2e15; border:1px solid var(--gold-dk); border-radius:3px; color:var(--gold); font-family:'Cinzel',serif; font-size:11px; padding:7px 14px; transition:transform .25s; z-index:200; white-space:nowrap; }
.rw .toast.show { transform:translateX(-50%) translateY(0); }

/* MODE TOGGLE */
.rw .mode-toggle { display:flex; gap:0; background:var(--sbg); border:1px solid var(--sbd); border-radius:4px; padding:3px; margin-bottom: 4px; }
.rw .mode-btn { flex:1; background:transparent; border:none; color:var(--dim); font-family:'Cinzel',serif; font-size:12px; font-weight:700; letter-spacing:1px; padding:10px; cursor:pointer; border-radius:3px; text-transform:uppercase; transition:all .15s; }
.rw .mode-btn.active { background:linear-gradient(180deg,#4a3818,#2e2210); color:var(--gold); border:1px solid var(--bd); }

/* WIZARD (MODE SIMPLE) */
.rw .wiz-step { padding:14px 0; border-bottom:1px solid rgba(107,79,26,.3); }
.rw .wiz-step:last-child { border-bottom:none; }
.rw .wiz-label { font-family:'Cinzel',serif; font-size:11px; color:var(--dim); text-transform:uppercase; letter-spacing:1px; margin-bottom:10px; }
.rw .wiz-options { display:flex; flex-wrap:wrap; gap:7px; }
.rw .wiz-opt { background:linear-gradient(180deg,#3d2e15,#2a1f0e); border:1px solid var(--bd); border-radius:4px; color:var(--lt); font-family:'Crimson Text',serif; font-size:14px; padding:9px 15px; cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:7px; }
.rw .wiz-opt:hover { border-color:var(--gold); }
.rw .wiz-opt.sel { border-color:var(--gold); background:linear-gradient(180deg,#5a4520,#3a2c10); color:var(--gold); }
.rw .wiz-opt img { width:28px; height:28px; object-fit:contain; }
.rw .wiz-input-row { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.rw .wiz-input-row label { font-size:12px; color:var(--dim); min-width:130px; }
.rw .wiz-input-row input[type=number] { background:var(--sbg); border:1px solid var(--sbd); border-radius:3px; color:var(--lt); font-size:14px; padding:8px 10px; outline:none; width:120px; }
.rw .wiz-input-row input:focus { border-color:var(--gold); }
.rw .wiz-btn-hitung { width:100%; background:linear-gradient(180deg,#8b4a00,#5a2e00); border:1px solid #c06010; border-radius:3px; color:var(--gold); font-family:'Cinzel',serif; font-size:13px; font-weight:700; letter-spacing:1px; padding:12px; cursor:pointer; text-transform:uppercase; margin-top:4px; }
.rw .wiz-btn-hitung:hover { background:linear-gradient(180deg,#a05800,#703800); }
.rw .wiz-result { margin:14px 0; background:linear-gradient(180deg,#2e2210,#1e1608); border:2px solid var(--bd); border-radius:4px; padding:16px; }
.rw .wiz-result-text { font-size:15px; line-height:1.7; color:var(--lt); }
.rw .wiz-result-text b { color:var(--gold); }

  
</style>

<div class="rw">
  <div class="app">

    <div class="mode-toggle">
      <button id="btnModeSimple" class="mode-btn active" onclick="setMode('simple')">📝 Mode Simple</button>
      <button id="btnModeAdvance" class="mode-btn" onclick="setMode('advance')">⚙️ Mode Advance</button>
    </div>

    <div id="modeSimpleWrap">
      <div class="panel">
        <div class="ph">
          <span>📝</span>
          <span class="ph-title">Refine — Mode Simple</span>
        </div>
        <div style="padding:16px;">

          @php
            $simpleStations = [
                ['slug' => 'smelter',    'jenis' => 'logam', 'name' => 'Smelter',    'desc' => 'Olah bijih jadi batangan logam'],
                ['slug' => 'lumbermill', 'jenis' => 'kayu',  'name' => 'Lumbermill', 'desc' => 'Olah kayu jadi papan kayu'],
                ['slug' => 'stonemason', 'jenis' => 'batu',  'name' => 'Stonemason', 'desc' => 'Olah batu jadi batu bata'],
                ['slug' => 'tanner',     'jenis' => 'kulit', 'name' => 'Tannery',    'desc' => 'Olah kulit jadi kulit samak'],
                ['slug' => 'weaver',     'jenis' => 'serat', 'name' => 'Weaver',     'desc' => 'Olah serat jadi kain'],
            ];
          @endphp

          <div class="wiz-step" id="wizNoJenis" style="display:none;">
            <div class="wiz-label">Pilih Stasiun Refine</div>
            <div class="station-grid" style="grid-template-columns:repeat(2,1fr);">
              @foreach ($simpleStations as $s)
                <a href="/kalkulator/refine?jenis={{ $s['jenis'] }}" class="station-card" style="background-image:linear-gradient(rgba(10,8,6,0.15),rgba(10,8,6,0.15)), url('{{ asset('images/'.$s['slug'].'.jpg') }}');">
                  <div class="station-body">
                    <div class="station-name">{{ $s['name'] }}</div>
                    <div class="station-desc">{{ $s['desc'] }}</div>
                  </div>
                </a>
              @endforeach
            </div>
          </div>

          <div class="wiz-step" id="wizStep2" style="display:none;">
            <div class="wiz-label">1. Pilih Tier</div>
            <div class="wiz-options" id="wizStep2Opts"></div>
          </div>

          <div class="wiz-step" id="wizStep3" style="display:none;">
            <div class="wiz-label">2. Pilih Enchant</div>
            <div class="wiz-options" id="wizStep3Opts"></div>
          </div>

          <div class="wiz-step" id="wizStep4" style="display:none;">
            <div class="wiz-label">3. Jumlah &amp; Return Bonus</div>
            <div class="wiz-input-row">
              <label>Mau buat berapa?</label>
              <input type="number" id="wizQty" value="100" min="1">
            </div>
            <div class="wiz-input-row">
              <label>Return bonus (%)</label>
              <input type="number" id="wizReturn" value="36.7" min="0" max="100" step="0.1">
            </div>
            <button class="wiz-btn-hitung" onclick="wizCompute()">⚔️ Refine</button>
          </div>

          <div class="wiz-result" id="wizResult" style="display:none;">
            <div class="wiz-result-text" id="wizResultText"></div>
            <div id="wizResultVisual" style="margin-top:12px;"></div>
            <button class="reset-btn" style="margin-top:14px;width:100%;" onclick="wizReset()">🔄 Hitung Ulang</button>
          </div>

        </div>
      </div>
    </div>

    <div id="modeAdvanceWrap" style="display:none">
    <div class="panel">
      <div class="ph">
        <span>⚙️</span>
        <span class="ph-title">Refine Calculator</span>
        <input type="text" class="header-search" id="searchInputRefine" placeholder="Cari nama item..." oninput="onSearchRefine()">
        <span class="api-status" id="apiStatus"></span>
      </div>

      <div class="flt-bar" id="fltBarRefine">
        <!-- MATERIAL (2 level: Jenis -> Mentah/Hasil) -->
        <div class="flt-wrap">
          <div class="flt-btn" id="btnMat" onclick="toggleDrop('mat')">
            <span class="flt-label" id="lblMat">Material</span>
            <span class="flt-val" id="valMat" style="display:none"></span>
            <span class="flt-arrow">▼</span>
          </div>
          <div class="drop-wrap" id="dropMat">
            <div class="drop-col" id="colMat1"></div>
            <div class="drop-col" id="colMat2" style="display:none"></div>
          </div>
        </div>
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
        <!-- ENCHANT -->
        <div class="flt-wrap">
          <div class="flt-btn" id="btnEnc" onclick="toggleDrop('enc')">
            <span class="flt-label" id="lblEnc">Enchant</span>
            <span class="flt-val" id="valEnc" style="display:none"></span>
            <span class="flt-arrow">▼</span>
          </div>
          <div class="drop-wrap" id="dropEnc">
            <div class="drop-col" id="colEnc"></div>
          </div>
        </div>
        <!-- KOTA -->
        <div class="flt-wrap">
          <div class="flt-btn" id="btnKota" onclick="toggleDrop('kota')">
            <span class="flt-label" id="lblKota" style="display:none">Kota</span>
            <span class="flt-val" id="valKota">Caerleon</span>
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
          <label>♻️ Return</label>
          <input class="ret-inp" type="number" id="returnRate" value="36.7" min="0" max="100" step="0.1">
          <span style="color:var(--dim);font-size:12px">%</span>
        </div>
        <div class="prem-wrap" onclick="document.getElementById('cbPrem').click()">
          <input type="checkbox" id="cbPrem" onclick="event.stopPropagation()" onchange="updateFooter()">
          <span>👑 Premium</span>
        </div>
        <button class="inv-btn" id="invBtn" onclick="toggleInv()">📦 Inventory (<span id="invCount">0</span>)</button>
        <button class="reset-btn" onclick="doReset()">🗑 Reset</button>
      </div>

      <div class="inv-section" id="invSection">
        <div class="inv-lbl">📦 Inventory</div>
        <div class="inv-grid" id="invGrid"></div>
      </div>

      <div class="refine-btns" id="refineBtns">
        <div class="refine-btns-lbl">⚔️ Refine Tersedia</div>
        <div class="refine-btns-grid" id="refineBtnsGrid"></div>
      </div>

      <div class="coin-footer" id="coinFooter">
        <div class="coin-row">
          <div class="coin-side">
            <div class="coin-icon">🪙</div>
            <div>
              <span class="coin-lbl">Modal Awal</span>
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
        <div class="tax-row">
          <div class="tax-item">
            <span class="tax-lbl" id="taxLbl">Pajak (8%)</span>
            <span class="tax-val" id="taxVal">0</span>
          </div>
          <div class="tax-item right">
            <span class="tax-lbl">Profit Bersih</span>
            <span class="profit-val" id="profitVal">0</span>
          </div>
        </div>
      </div>
    </div>
    </div>
  </div>
</div>

{{-- POPUP TAMBAH / EDIT ITEM --}}
<div class="rw">
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
          <label>Harga per unit (silver)</label>
          <input type="number" id="popHarga" placeholder="0" min="0">
        </div>
        <div class="pop-field">
          <label>Jumlah (max 999.999)</label>
          <div class="slider-wrap">
            <input type="range" id="popSlider" min="1" max="999" value="100" oninput="syncQty('s')">
            <input class="slider-val" type="number" id="popQty" value="100" min="1" max="999999" oninput="syncQty('v')">
          </div>
        </div>
        <div class="pop-btn-row" id="popBtnRow"></div>
      </div>
    </div>
  </div>

  {{-- POPUP REFINE --}}
  <div class="overlay" id="overlayRefine" onclick="closeOverlayOutside(event,'overlayRefine')">
    <div class="popup">
      <div class="pop-head">
        <img class="pop-icon" id="rPopIcon" src="" alt="">
        <div>
          <div class="pop-name" id="rPopName">—</div>
          <div class="pop-desc" id="rPopDesc">—</div>
        </div>
        <button class="pop-close" onclick="closeOverlay('overlayRefine')">✕</button>
      </div>
      <div class="pop-body">
        <div class="pop-info" id="rPopInfo"></div>
        <div class="pop-field">
          <label>Return Rate %</label>
          <input type="number" id="rReturnRate" min="0" max="100" step="0.1" value="36.7">
        </div>
        <div class="pop-field" id="rQtyField">
          <label>Jumlah yang di-refine</label>
          <div class="slider-wrap">
            <input type="range" id="rSlider" min="1" max="100" value="100" oninput="syncRQty('s')">
            <input class="slider-val" type="number" id="rQty" value="100" min="1" max="100" oninput="syncRQty('v')">
          </div>
        </div>
        <label class="check-row">
          <input type="checkbox" id="rHabis" onchange="onRHabisChange()">
          Refine Habis (gunakan semua bahan)
        </label>
        <div class="pop-btn-row">
          <button class="btn-refine" onclick="doRefine()">⚔️ Refine</button>
        </div>
      </div>
    </div>
  </div>

  <div class="toast" id="toast"></div>
</div>
<script>

// ===================== DATA =====================
const BATU_ENC_MULT = {0:1, 1:2, 2:4, 3:8};
const FORMULA = {
  T2:{raw:1,prev:0}, T3:{raw:2,prev:1}, T4:{raw:2,prev:1},
  T5:{raw:3,prev:1}, T6:{raw:4,prev:1}, T7:{raw:5,prev:1}, T8:{raw:5,prev:1}
};
const TIER_ORDER = ['T2','T3','T4','T5','T6','T7','T8'];
const TIER_COL   = {T2:'#aaa',T3:'#6b8',T4:'#68f',T5:'#c8f',T6:'#fa8',T7:'#f64',T8:'#ff0'};
const ENC_COL    = ['#888','#4a9','#48f','#a5f','#fa0'];

function buildItems() {
  const its = [];
  function api(base, enc) { return enc===0 ? base : `${base}_LEVEL${enc}@${enc}`; }
  function add(jenis, rawBase, rawName, hasilBase, hasilName, maxRaw, maxHasil) {
    const TIERS = ['T2','T3','T4','T5','T6','T7','T8'];
    for (let ti=0; ti<7; ti++) {
      const tier = TIERS[ti];
      const mR = ti<2 ? 0 : maxRaw;
      const mH = ti<2 ? 0 : maxHasil;
      for (let e=0; e<=mR; e++) its.push({jenis,tipe:'raw',tier,enc:e,name:rawName[ti]+(e>0?` .${e}`:''),api:api(rawBase[ti],e),desc:`${rawName[ti]}${e>0?' .'+e:''}`});
      // T8 hasil tetap dibuat (dipakai utk proses refine & harga), tapi disembunyikan dari tabel di filterItems()
      for (let e=0; e<=mH; e++) its.push({jenis,tipe:'hasil',tier,enc:e,name:hasilName[ti]+(e>0?` .${e}`:''),api:api(hasilBase[ti],e),desc:`${hasilName[ti]}${e>0?' .'+e:''}`});
    }
  }
  add('logam',
    ['T2_ORE','T3_ORE','T4_ORE','T5_ORE','T6_ORE','T7_ORE','T8_ORE'],
    ['Copper Ore','Tin Ore','Iron Ore','Titanium Ore','Runite Ore','Meteorite Ore','Adamantium Ore'],
    ['T2_METALBAR','T3_METALBAR','T4_METALBAR','T5_METALBAR','T6_METALBAR','T7_METALBAR','T8_METALBAR'],
    ['Copper Bar','Bronze Bar','Steel Bar','Titanium Steel Bar','Runite Steel Bar','Meteorite Steel Bar','Adamantium Steel Bar'],
    4, 4);
  add('kayu',
    ['T2_WOOD','T3_WOOD','T4_WOOD','T5_WOOD','T6_WOOD','T7_WOOD','T8_WOOD'],
    ['Birch Logs','Chestnut Logs','Pine Logs','Cedar Logs','Bloodoak Logs','Ashenbark Logs','Whitewood Logs'],
    ['T2_PLANKS','T3_PLANKS','T4_PLANKS','T5_PLANKS','T6_PLANKS','T7_PLANKS','T8_PLANKS'],
    ['Birch Planks','Chestnut Planks','Pine Planks','Cedar Planks','Bloodoak Planks','Ashenbark Planks','Whitewood Planks'],
    4, 4);
  add('serat',
    ['T2_FIBER','T3_FIBER','T4_FIBER','T5_FIBER','T6_FIBER','T7_FIBER','T8_FIBER'],
    ['Cotton','Flax','Hemp','Skyflower','Redleaf Cotton','Sunflax','Ghost Hemp'],
    ['T2_CLOTH','T3_CLOTH','T4_CLOTH','T5_CLOTH','T6_CLOTH','T7_CLOTH','T8_CLOTH'],
    ['Simple Cloth','Neat Cloth','Fine Cloth','Ornate Cloth','Lavish Cloth','Opulent Cloth','Baroque Cloth'],
    4, 4);
  add('kulit',
    ['T2_HIDE','T3_HIDE','T4_HIDE','T5_HIDE','T6_HIDE','T7_HIDE','T8_HIDE'],
    ['Rugged Hide','Thin Hide','Medium Hide','Heavy Hide','Robust Hide','Thick Hide','Resilient Hide'],
    ['T2_LEATHER','T3_LEATHER','T4_LEATHER','T5_LEATHER','T6_LEATHER','T7_LEATHER','T8_LEATHER'],
    ['Stiff Leather','Thick Leather','Worked Leather','Cured Leather','Hardened Leather','Reinforced Leather','Fortified Leather'],
    4, 4);
  add('batu',
    ['T2_ROCK','T3_ROCK','T4_ROCK','T5_ROCK','T6_ROCK','T7_ROCK','T8_ROCK'],
    ['Limestone','Sandstone','Travertine','Granite','Slate','Basalt','Marble'],
    ['T2_STONEBLOCK','T3_STONEBLOCK','T4_STONEBLOCK','T5_STONEBLOCK','T6_STONEBLOCK','T7_STONEBLOCK','T8_STONEBLOCK'],
    ['Limestone Block','Sandstone Block','Travertine Block','Granite Block','Slate Block','Basalt Block','Marble Block'],
    3, 0); // batu hasil selalu enc 0
  return its;
}
const ITEMS = buildItems();

// Jenis material yang dibawa dari homepage (klik card station), kalau ada
const urlJenis = @json(request('jenis'));

// ===================== MODE TOGGLE =====================
function setMode(mode) {
  localStorage.setItem('rw_mode', mode);
  document.getElementById('modeSimpleWrap').style.display  = mode==='simple'  ? '' : 'none';
  document.getElementById('modeAdvanceWrap').style.display = mode==='advance' ? '' : 'none';
  document.getElementById('btnModeSimple').classList.toggle('active', mode==='simple');
  document.getElementById('btnModeAdvance').classList.toggle('active', mode==='advance');
}

// ===================== WIZARD (MODE SIMPLE) =====================
const JENIS_META = {
  logam: {label:'Logam', emoji:'⚙️'},
  kayu:  {label:'Kayu',  emoji:'🪵'},
  serat: {label:'Serat', emoji:'🧵'},
  kulit: {label:'Kulit', emoji:'🐾'},
  batu:  {label:'Batu',  emoji:'🪨'},
};
const SIMPLE_TIERS = ['T2','T3','T4','T5','T6','T7','T8'];
let wiz = { jenis:null, tier:null, enc:0 };

function initWizard() {
  if (!urlJenis || !JENIS_META[urlJenis]) {
    document.getElementById('wizNoJenis').style.display = '';
    return;
  }
  wiz.jenis = urlJenis;
  document.getElementById('wizStep2').style.display = '';
  renderWizStep2();
}

// Semua enchant HASIL (bahan jadi) yang benar-benar ada untuk jenis+tier ini.
// Balok batu otomatis cuma punya 1 opsi (enc 0) karena hasilnya emang gak ber-enchant.
function getHasilEncOptions(jenis, tier) {
  return ITEMS.filter(i=>i.jenis===jenis && i.tipe==='hasil' && i.tier===tier)
              .map(i=>i.enc).sort((a,b)=>a-b);
}

function renderWizStep2() {
  const el = document.getElementById('wizStep2Opts');
  el.innerHTML = SIMPLE_TIERS.map(t => {
    const rep = ITEMS.find(i=>i.jenis===wiz.jenis && i.tipe==='hasil' && i.tier===t && i.enc===0);
    const img = rep ? `<img src="${iconUrl(rep.api)}" alt="">` : '';
    return `<div class="wiz-opt ${wiz.tier===t?'sel':''}" onclick="wizSelectTier('${t}')">${img}${t}</div>`;
  }).join('');
}

function wizSelectTier(t) {
  wiz.tier = t; wiz.enc = 0;
  renderWizStep2();
  const encOptions = getHasilEncOptions(wiz.jenis, t);
  if (encOptions.length <= 1) {
    wiz.enc = encOptions[0] ?? 0;
    document.getElementById('wizStep3').style.display = 'none';
    document.getElementById('wizStep4').style.display = '';
  } else {
    document.getElementById('wizStep3').style.display = '';
    renderWizStep3(encOptions);
    document.getElementById('wizStep4').style.display = 'none';
  }
  document.getElementById('wizResult').style.display = 'none';
}

function renderWizStep3(encOptions) {
  const el = document.getElementById('wizStep3Opts');
  el.innerHTML = encOptions.map(e => {
    const item = ITEMS.find(i=>i.jenis===wiz.jenis && i.tipe==='hasil' && i.tier===wiz.tier && i.enc===e);
    const img  = item ? `<img src="${iconUrl(item.api)}" alt="">` : '';
    return `<div class="wiz-opt ${wiz.enc===e?'sel':''}" onclick="wizSelectEnc(${e})">${img}${e===0?'Normal':'.'+e}</div>`;
  }).join('');
}

function wizSelectEnc(e) {
  wiz.enc = e;
  renderWizStep3(getHasilEncOptions(wiz.jenis, wiz.tier));
  document.getElementById('wizStep4').style.display = '';
  document.getElementById('wizResult').style.display = 'none';
}

function bahanSlotHTML(item, qty, isHasil) {
  return `<div class="bahan-slot">
      <img src="${iconUrl(item.api)}" alt="">
      <div class="bahan-qty"><span class="${isHasil?'punya':'butuh'}">${qty}</span></div>
      <div class="bahan-name">${item.name}</div>
    </div>`;
}

function wizCompute() {
  const qty    = Math.max(1, parseInt(document.getElementById('wizQty').value) || 1);
  const retPct = Math.min(100, Math.max(0, parseFloat(document.getElementById('wizReturn').value) || 0));
  const { jenis, tier, enc } = wiz;
  if (!jenis || !tier) return;

  const f        = FORMULA[tier];
  const prevTier = TIER_ORDER[TIER_ORDER.indexOf(tier)-1];
  const isBatu   = jenis === 'batu';
  const hasilItem = ITEMS.find(i=>i.jenis===jenis && i.tipe==='hasil' && i.tier===tier && i.enc===enc);

  // Balok batu: hasilnya cuma 1 varian, tapi bisa dicapai lewat beberapa
  // enchant batu mentah (masing2 beda rasio efisiensi via BATU_ENC_MULT).
  // Tampilkan semua opsi sekaligus sebagai alternatif.
  if (isBatu) {
    const maxRawEnc = (tier==='T2'||tier==='T3') ? 0 : 3;
    const rows = [];
    for (let e=0; e<=maxRawEnc; e++) {
      const mult = BATU_ENC_MULT[e] || 1;
      const ops  = Math.ceil(qty / mult);
      const actualOutput = ops * mult;
      const rawGross  = ops * f.raw;
      const rawReturn = Math.round(rawGross * retPct/100);
      const rawNeeded = rawGross - rawReturn;
      let prevNeeded = 0, prevItem = null;
      if (f.prev > 0 && prevTier) {
        const prevGross  = ops * f.prev;
        const prevReturn = Math.round(prevGross * retPct/100);
        prevNeeded = prevGross - prevReturn;
        prevItem   = ITEMS.find(i=>i.jenis===jenis && i.tipe==='hasil' && i.tier===prevTier && i.enc===0);
      }
      const rawItem = ITEMS.find(i=>i.jenis===jenis && i.tipe==='raw' && i.tier===tier && i.enc===e);
      rows.push({ enc:e, rawItem, rawNeeded, prevItem, prevNeeded, actualOutput });
    }

    document.getElementById('wizResultText').innerHTML =
      `Untuk membuat <b>${hasilItem.name}</b> sejumlah <b>${qty}</b>, dengan return <b>${retPct}%</b>, dibutuhkan salah satu dari opsi bahan mentah berikut:`;

    document.getElementById('wizResultVisual').innerHTML = rows.map((r,idx) => {
      let row = `<div class="bahan-row">`;
      row += bahanSlotHTML(r.rawItem, r.rawNeeded, false);
      if (r.prevItem) row += `<span class="bahan-arrow">+</span>` + bahanSlotHTML(r.prevItem, r.prevNeeded, false);
      row += `<span class="bahan-arrow">→</span>` + bahanSlotHTML(hasilItem, r.actualOutput, true);
      row += `</div>`;
      if (idx < rows.length-1) {
        row += `<div style="text-align:center;color:var(--dim);font-family:'Cinzel',serif;font-size:11px;letter-spacing:2px;margin:8px 0;">— ATAU —</div>`;
      }
      return row;
    }).join('');

    document.getElementById('wizResult').style.display = '';
    document.getElementById('wizResult').scrollIntoView({behavior:'smooth', block:'nearest'});
    return;
  }

  // Jenis selain batu: 1 jalur lurus (enchant hasil = enchant bahan mentah)
  const rawItem  = ITEMS.find(i=>i.jenis===jenis && i.tipe==='raw' && i.tier===tier && i.enc===enc);
  const rawGross  = qty * f.raw;
  const rawReturn = Math.round(rawGross * retPct/100);
  const rawNeeded = rawGross - rawReturn;

  let prevNeeded = 0, prevItem = null;
  if (f.prev > 0 && prevTier) {
    const prevEnc    = (tier==='T3'||tier==='T4') ? 0 : enc;
    const prevGross  = qty * f.prev;
    const prevReturn = Math.round(prevGross * retPct/100);
    prevNeeded = prevGross - prevReturn;
    prevItem   = ITEMS.find(i=>i.jenis===jenis && i.tipe==='hasil' && i.tier===prevTier && i.enc===prevEnc);
  }

  let kalimat = `Untuk membuat <b>${hasilItem.name}</b> sejumlah <b>${qty}</b>, dengan return <b>${retPct}%</b>, dibutuhkan <b>${rawItem.name}</b> sebanyak <b>${rawNeeded}</b>`;
  if (prevItem) kalimat += ` dan <b>${prevItem.name}</b> sebanyak <b>${prevNeeded}</b>`;
  kalimat += '.';

  let visual = bahanSlotHTML(rawItem, rawNeeded, false);
  if (prevItem) visual += `<span class="bahan-arrow">+</span>` + bahanSlotHTML(prevItem, prevNeeded, false);
  visual += `<span class="bahan-arrow">→</span>` + bahanSlotHTML(hasilItem, qty, true);

  document.getElementById('wizResultText').innerHTML   = kalimat;
  document.getElementById('wizResultVisual').innerHTML = `<div class="bahan-row">${visual}</div>`;
  document.getElementById('wizResult').style.display = '';
  document.getElementById('wizResult').scrollIntoView({behavior:'smooth', block:'nearest'});
}

function wizReset() {
  wiz.tier = null; wiz.enc = 0;
  renderWizStep2();
  document.getElementById('wizStep3').style.display = 'none';
  document.getElementById('wizStep4').style.display = 'none';
  document.getElementById('wizResult').style.display = 'none';
}

// ===================== ADVANCE: FILTER OTOMATIS DARI HOMEPAGE =====================
function applyUrlJenisAdvance() {
  if (!urlJenis || !JENIS_META[urlJenis]) return;
  fJenis = urlJenis; fTipe = null;
  buildMatCol2(); updateMatLabel();
  filterItems();
}

// ===================== STATE =====================
let inventory  = [];   // [{item, qty, harga}]
let priceCache = {};
let modalLock  = null; // null = belum lock, number = sudah lock setelah refine pertama
let invShown   = false;
let popupItem  = null;
let popupInvIdx = null;
let refineTarget = null; // item hasil yang akan di-refine
let sudahCatat = false;

function iconUrl(api) {
  const base = api.includes('@') ? api.split('@')[0] : api;
  return `https://render.albiononline.com/v1/item/${base}.png?size=64&quality=1`;
}

// ===================== FILTER BAR (dropdown ala Market) =====================
// Pohon material 2 level: Jenis -> Mentah/Hasil (subkategori)
const MATERIAL_TREE = [
  { id:'logam', name:'⚙️ Logam', children:[ {id:'raw', name:'Bijih Mentah'}, {id:'hasil', name:'Balok / Batang'} ] },
  { id:'kayu',  name:'🪵 Kayu',  children:[ {id:'raw', name:'Kayu Mentah'},  {id:'hasil', name:'Papan Kayu'}    ] },
  { id:'serat', name:'🧵 Serat', children:[ {id:'raw', name:'Serat Mentah'}, {id:'hasil', name:'Kain'}          ] },
  { id:'kulit', name:'🐾 Kulit', children:[ {id:'raw', name:'Kulit Mentah'}, {id:'hasil', name:'Kulit Samak'}   ] },
  { id:'batu',  name:'🪨 Batu',  children:[ {id:'raw', name:'Batu Mentah'},  {id:'hasil', name:'Batu Bata'}     ] },
];
const KOTA_LIST = ['Caerleon','Bridgewatch','Fort Sterling','Lymhurst','Martlock','Thetford','Brecilien'];

// State filter (menggantikan value dari <select> lama — logic filterItems() di bawah tetap sama)
let fJenis  = null;      // 'logam' | 'kayu' | ...
let fTipe   = null;      // 'raw' | 'hasil'
let fTier   = null;      // 'T2'..'T8'
let fEnc    = null;      // 0..4
let fKota   = 'Caerleon';
let fSearch = '';

let openDropR = null;
function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

function toggleDrop(name) {
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

function closeDrop() {
  if (!openDropR) return;
  document.getElementById('drop' + cap(openDropR)).classList.remove('show');
  document.getElementById('btn'  + cap(openDropR)).classList.remove('open');
  openDropR = null;
}

document.addEventListener('click', e => {
  if (openDropR && !e.target.closest('.flt-wrap')) closeDrop();
});

function setFilterVal(lblId, valId, value) {
  const lbl = document.getElementById(lblId);
  const val = document.getElementById(valId);
  if (value) { lbl.style.display='none'; val.style.display=''; val.textContent=value; }
  else       { lbl.style.display='';     val.style.display='none'; }
}

function makeItem(text, hasArrow, isActive, onClick) {
  const el = document.createElement('div');
  el.className = 'drop-item' + (isActive ? ' active' : '');
  el.innerHTML = text + (hasArrow ? '<span class="di-arrow">▶</span>' : '');
  el.addEventListener('click', e => { e.stopPropagation(); onClick(); });
  return el;
}

// --- MATERIAL (2 kolom) ---
function buildMatCol1() {
  const col = document.getElementById('colMat1');
  col.innerHTML = '';
  col.appendChild(makeItem('Semua', false, !fJenis, () => {
    fJenis = null; fTipe = null;
    buildMatCol2(); updateMatLabel(); closeDrop(); filterItems();
  }));
  MATERIAL_TREE.forEach(j => {
    col.appendChild(makeItem(j.name, true, fJenis === j.id, () => {
      fJenis = j.id; fTipe = null;
      buildMatCol2(); updateMatLabel(); filterItems();
    }));
  });
}

function buildMatCol2() {
  const col2 = document.getElementById('colMat2');
  if (!fJenis) { col2.style.display = 'none'; return; }
  const j = MATERIAL_TREE.find(x => x.id === fJenis);
  col2.style.display = ''; col2.innerHTML = '';
  col2.appendChild(makeItem('Semua', false, !fTipe, () => {
    fTipe = null; updateMatLabel(); closeDrop(); filterItems();
  }));
  j.children.forEach(c => {
    col2.appendChild(makeItem(c.name, false, fTipe === c.id, () => {
      fTipe = c.id; updateMatLabel(); closeDrop(); filterItems();
    }));
  });
}

function updateMatLabel() {
  let label = null;
  if (fJenis) {
    const j = MATERIAL_TREE.find(x => x.id === fJenis);
    label = j.name.replace(/^\S+\s/, '') + (fTipe ? ' — ' + j.children.find(c => c.id === fTipe).name : '');
  }
  setFilterVal('lblMat', 'valMat', label);
}

// --- TIER ---
function buildTierDropRefine() {
  const col = document.getElementById('colTier');
  col.innerHTML = '';
  col.appendChild(makeItem('Semua', false, !fTier, () => {
    fTier = null; setFilterVal('lblTier','valTier',null); closeDrop(); filterItems();
  }));
  SIMPLE_TIERS.forEach(t => col.appendChild(makeItem(t, false, fTier === t, () => {
    fTier = t; setFilterVal('lblTier','valTier', t); closeDrop(); filterItems();
  })));
}

// --- ENCHANT ---
const ENC_LIST_REFINE = [0,1,2,3,4];
function buildEncDropRefine() {
  const col = document.getElementById('colEnc');
  col.innerHTML = '';
  col.appendChild(makeItem('Semua', false, fEnc === null, () => {
    fEnc = null; setFilterVal('lblEnc','valEnc',null); closeDrop(); filterItems();
  }));
  ENC_LIST_REFINE.forEach(e => col.appendChild(makeItem('Enchant .' + e, false, fEnc === e, () => {
    fEnc = e; setFilterVal('lblEnc','valEnc','.' + e); closeDrop(); filterItems();
  })));
}

// --- KOTA ---
function buildKotaDropRefine() {
  const col = document.getElementById('colKota');
  col.innerHTML = '';
  KOTA_LIST.forEach(k => col.appendChild(makeItem(k, false, fKota === k, () => {
    fKota = k; setFilterVal('lblKota','valKota', k); closeDrop(); onKotaChange();
  })));
}

// --- SEARCH ---
let searchTimerRefine = null;
function onSearchRefine() {
  clearTimeout(searchTimerRefine);
  searchTimerRefine = setTimeout(() => {
    fSearch = document.getElementById('searchInputRefine').value.trim();
    filterItems();
  }, 300);
}

// ===================== FILTER & RENDER LIST =====================
function filterItems() {
  const jenis = fJenis || '';
  const tipe  = fTipe  || '';
  const tier  = fTier  || '';
  let filtered = ITEMS.filter(it => {
    if (it.tipe==='hasil' && it.tier==='T8') return false;
    if (jenis && it.jenis!==jenis) return false;
    if (tipe  && it.tipe!==tipe)  return false;
    if (tier  && it.tier!==tier)  return false;
    if (fEnc!==null && it.enc!==fEnc) return false;
    return true;
  });
  if (fSearch) {
    const q = fSearch.toLowerCase();
    filtered = filtered.filter(it => it.name.toLowerCase().includes(q));
  }
  window._fl = filtered;
  const el = document.getElementById('itemList');
  if (!filtered.length) { el.innerHTML='<div class="empty-inv">Tidak ada item yang cocok</div>'; return; }
  el.innerHTML = filtered.map((it,i) => {
    const h = priceCache[it.api];
    return `<div class="item-row" onclick="openAdd(${i})">
      <img class="item-icon" src="${iconUrl(it.api)}" alt="" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2248%22 height=%2248%22><rect width=%2248%22 height=%2248%22 fill=%22%231a1208%22/></svg>'">
      <div class="item-name">${it.name}</div>
      ${h?`<span class="item-price">${fmt(h)}</span>`:''}
    </div>`;
  }).join('');
}

// ===================== API FETCH =====================
const FALLBACK_ORDER = ['Bridgewatch','Fort Sterling','Lymhurst','Martlock','Thetford','Caerleon','Brecilien'];
let priceCityCache = {}; // api_id -> {harga, kota}

async function fetchBatch(ids, kota) {
  const url = `https://west.albion-online-data.com/api/v2/stats/prices/${ids.join(',')}?locations=${encodeURIComponent(kota)}&qualities=1`;
  const res  = await fetch(url);
  const data = await res.json();
  const result = {};
  for (const e of data) if (e.sell_price_min>0) result[e.item_id] = e.sell_price_min;
  return result;
}

async function onKotaChange() {
  const kota = fKota;
  if (!kota) return;
  const st = document.getElementById('apiStatus');
  st.className='api-status loading'; st.textContent='⏳ Mengambil harga...';

  const allKeys = [...new Set(ITEMS.map(it=>it.api))];
  priceCache = {}; priceCityCache = {};

  // Urutan fallback: kota pilihan dulu, lalu 5 royal (skip yg sudah dipilih), lalu Caerleon & Brecilien
  const fallbacks = [kota, ...FALLBACK_ORDER.filter(k=>k!==kota)];

  try {
    // Fetch kota utama dulu (semua batch)
    for (let i=0; i<allKeys.length; i+=200) {
      const batch  = allKeys.slice(i,i+200);
      const result = await fetchBatch(batch, kota);
      for (const [id,harga] of Object.entries(result)) {
        priceCache[id] = harga;
        priceCityCache[id] = {harga, kota};
      }
    }

    // Cari yang masih kosong, fetch fallback satu per satu
    let missing = allKeys.filter(k=>!priceCache[k]);
    for (const fbKota of FALLBACK_ORDER.filter(k=>k!==kota)) {
      if (!missing.length) break;
      for (let i=0; i<missing.length; i+=200) {
        const batch  = missing.slice(i,i+200);
        const result = await fetchBatch(batch, fbKota);
        for (const [id,harga] of Object.entries(result)) {
          priceCache[id] = harga;
          priceCityCache[id] = {harga, kota:fbKota};
        }
      }
      missing = allKeys.filter(k=>!priceCache[k]);
    }

    const fetched  = Object.keys(priceCache).length;
    const fallback = Object.values(priceCityCache).filter(v=>v.kota!==kota).length;
    st.className='api-status ok';
    st.textContent=`✅ ${fetched} harga${fallback>0?` (${fallback} fallback)`:''}`;

    for (const inv of inventory) { const c=priceCache[inv.item.api]; if(c&&c>0) inv.harga=c; }
    filterItems(); renderInventory(); updateFooter();
  } catch(e) { st.className='api-status err'; st.textContent='⚠️ Gagal fetch'; }
}

// ===================== POPUP TAMBAH / EDIT =====================
function openAdd(idx) {
  const it = window._fl[idx];
  popupItem=it; popupInvIdx=null;
  document.getElementById('popIcon').src = iconUrl(it.api);
  document.getElementById('popName').textContent = it.name;
  document.getElementById('popDesc').textContent = it.desc;
  const cached = priceCache[it.api]||0;
  document.getElementById('popHarga').value = cached||'';
  document.getElementById('popSlider').value = 100;
  document.getElementById('popQty').value    = 100;
  document.getElementById('popBtnRow').innerHTML = `<button class="btn-add" onclick="doAdd()">➕ Tambah ke Inventory</button>`;
  openOverlay('overlayItem');
}

function openEdit(i) {
  const inv = inventory[i];
  if (!inv) return;
  popupItem=inv.item; popupInvIdx=i;
  document.getElementById('popIcon').src = iconUrl(inv.item.api);
  document.getElementById('popName').textContent = inv.item.name;
  document.getElementById('popDesc').textContent = inv.item.desc;
  document.getElementById('popHarga').value = inv.harga||'';
  document.getElementById('popSlider').value = inv.qty;
  document.getElementById('popQty').value    = inv.qty;
  // Kalau sudah refine pertama & item ini hasil refine -> hanya edit harga
  const isHasil = inv.item.tipe==='hasil';
  const locked  = modalLock !== null;
  if (locked && isHasil) {
    document.getElementById('popBtnRow').innerHTML = `
      <button class="btn-add" onclick="doEditHarga()">💾 Simpan Harga</button>
      <button class="btn-del" onclick="doHapus()">🗑</button>`;
    // Sembunyikan qty field - harga saja
    document.getElementById('popSlider').closest('.pop-field').style.display='none';
  } else {
    document.getElementById('popSlider').closest('.pop-field').style.display='';
    document.getElementById('popBtnRow').innerHTML = `
      <button class="btn-add" onclick="doEdit()">💾 Simpan</button>
      <button class="btn-del" onclick="doHapus()">🗑</button>`;
  }
  openOverlay('overlayItem');
}

function syncQty(src) {
  const s=document.getElementById('popSlider'), v=document.getElementById('popQty');
  if(src==='s') v.value=s.value; else s.value=Math.min(parseInt(v.value)||1, 999);
}

function doAdd() {
  if (!popupItem) return;
  const qty   = Math.min(parseInt(document.getElementById('popQty').value)||1, 999999);
  const harga = parseFloat(document.getElementById('popHarga').value)||0;
  const key   = `${popupItem.jenis}_${popupItem.tipe}_${popupItem.tier}_${popupItem.enc}`;
  const ex    = inventory.findIndex(inv=>`${inv.item.jenis}_${inv.item.tipe}_${inv.item.tier}_${inv.item.enc}`===key);
  if (ex>=0) {
    inventory[ex].qty   = Math.min(inventory[ex].qty+qty, 999999);
    inventory[ex].harga = harga||inventory[ex].harga;
    showToast(`📦 ${popupItem.name} → ${inventory[ex].qty}`);
  } else {
    inventory.push({item:popupItem, qty, harga});
    showToast(`✅ ${popupItem.name} × ${qty}`);
  }
  if (!invShown) toggleInv();
  closeOverlay('overlayItem');
  renderInventory(); renderRefineBtns(); updateFooter();
}

function doEdit() {
  if (popupInvIdx===null) return;
  inventory[popupInvIdx].qty   = Math.min(parseInt(document.getElementById('popQty').value)||1, 999999);
  inventory[popupInvIdx].harga = parseFloat(document.getElementById('popHarga').value)||0;
  closeOverlay('overlayItem');
  renderInventory(); renderRefineBtns(); updateFooter();
  showToast('✏️ Diperbarui');
}

function doEditHarga() {
  if (popupInvIdx===null) return;
  inventory[popupInvIdx].harga = parseFloat(document.getElementById('popHarga').value)||0;
  closeOverlay('overlayItem');
  updateFooter();
  showToast('💰 Harga diperbarui');
}

function doHapus() {
  if (popupInvIdx===null) return;
  const nama = inventory[popupInvIdx].item.name;
  inventory.splice(popupInvIdx,1);
  closeOverlay('overlayItem');
  renderInventory(); renderRefineBtns(); updateFooter();
  showToast(`🗑 ${nama} dihapus`);
}

// ===================== INVENTORY =====================
function toggleInv() {
  invShown=!invShown;
  document.getElementById('invSection').classList.toggle('show',invShown);
  document.getElementById('invBtn').classList.toggle('active',invShown);
}

function renderInventory() {
  // Hitung total slot visual (tiap 999 = 1 slot)
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
      const tc = TIER_COL[inv.item.tier]||'#fff';
      const ec = ENC_COL[inv.item.enc]||'#888';
      html += `<div class="slot filled" title="${inv.item.name} × ${inv.qty}" onclick="openEdit(${vs.i})">
        <img src="${iconUrl(inv.item.api)}" alt="">
        <span class="st" style="color:${tc}">${inv.item.tier[1]}</span>
        ${inv.item.enc>0?`<span class="se" style="color:${ec}">.${inv.item.enc}</span>`:''}
        <span class="sq">${vs.qty}</span>
        ${inv.harga>0?`<span class="sh">${fmt(inv.harga)}</span>`:''}
      </div>`;
    } else html += `<div class="slot"></div>`;
  }
  grid.innerHTML = html;
}

// ===================== TOMBOL REFINE =====================
function renderRefineBtns() {
  const grid = document.getElementById('refineBtnsGrid');
  const wrap = document.getElementById('refineBtns');
  const btns = getAvailableRefines();
  if (!btns.length) { wrap.classList.remove('show'); return; }
  wrap.classList.add('show');
  grid.innerHTML = btns.map((b,i) => `
    <div class="rbtn" onclick="openRefinePopup(${i})">
      <img src="${iconUrl(b.hasilItem.api)}" alt="">
      <div class="rb-name">${b.hasilItem.name}</div>
      <div class="rb-qty">max ${b.maxOutput}</div>
    </div>`).join('');
  window._refineBtns = btns;
}

function getAvailableRefines() {
  const btns = [];
  // Cek setiap kombinasi jenis x tier x enc yang bisa direfine
  const RAW_TIERS = ['T2','T3','T4','T5','T6','T7','T8'];
  const jienis = [...new Set(inventory.map(i=>i.item.jenis))];
  for (const jenis of jienis) {
    for (const tier of RAW_TIERS) {
      const f = FORMULA[tier];
      const prevTier = RAW_TIERS[RAW_TIERS.indexOf(tier)-1];
      const maxEncRaw = (tier==='T2'||tier==='T3') ? 0 : (jenis==='batu'?3:4);
      for (let enc=0; enc<=maxEncRaw; enc++) {
        const rawInv = inventory.find(i=>i.item.jenis===jenis&&i.item.tipe==='raw'&&i.item.tier===tier&&i.item.enc===enc);
        if (!rawInv || rawInv.qty<f.raw) continue;
        // Cek prev material
        let prevInv=null, prevEnc=enc;
        if (f.prev>0 && prevTier) {
          prevEnc = (tier==='T3'||tier==='T4'||jenis==='batu') ? 0 : enc;
          prevInv = inventory.find(i=>i.item.jenis===jenis&&i.item.tipe==='hasil'&&i.item.tier===prevTier&&i.item.enc===prevEnc);
          if (!prevInv || prevInv.qty<f.prev) continue;
        }
        // Cari item hasil
        const hasilEnc = jenis==='batu' ? 0 : enc;
        const hasilTier = tier; // hasil selalu tier yang sama
        const hasilItem = ITEMS.find(i=>i.jenis===jenis&&i.tipe==='hasil'&&i.tier===hasilTier&&i.enc===hasilEnc);
        if (!hasilItem) continue;
        // Hitung max output
        const fromRaw  = Math.floor(rawInv.qty/f.raw);
        const fromPrev = f.prev>0&&prevInv ? Math.floor(prevInv.qty/f.prev) : fromRaw;
        const maxOut   = f.prev>0 ? Math.min(fromRaw,fromPrev) : fromRaw;
        if (maxOut<1) continue;
        btns.push({jenis,tier,enc,hasilItem,maxOutput:maxOut,rawInv,prevInv,prevEnc,f});
      }
    }
  }
  return btns;
}

// ===================== POPUP REFINE =====================
function openRefinePopup(i) {
  const b = window._refineBtns[i];
  refineTarget = b;
  document.getElementById('rPopIcon').src = iconUrl(b.hasilItem.api);
  document.getElementById('rPopName').textContent = b.hasilItem.name;
  document.getElementById('rPopDesc').textContent = b.hasilItem.desc;

  // Visual bahan — format punya/butuh per 1x refine
  const rawPunya  = b.rawInv.qty;
  const rawButuh  = b.f.raw;
  const rawCukup  = rawPunya >= rawButuh;
  const rawCityInfo = priceCityCache[b.rawInv.item.api];
  const rawFallback = rawCityInfo && rawCityInfo.kota !== (fKota||'')
    ? `<div class="fallback-tag">${rawCityInfo.kota}</div>` : '';

  let bahanHTML = `
    <div class="bahan-slot">
      <img src="${iconUrl(b.rawInv.item.api)}" alt="">
      <div class="bahan-qty">
        <span class="${rawCukup?'punya':'kurang'}">${rawPunya}</span>
        <span class="slash">/</span>
        <span class="butuh">${rawButuh}</span>
      </div>
      <div class="bahan-name">${b.rawInv.item.name}</div>
      ${rawFallback}
    </div>`;

  if (b.prevInv) {
    const prevPunya = b.prevInv.qty;
    const prevButuh = b.f.prev;
    const prevCukup = prevPunya >= prevButuh;
    const prevCityInfo = priceCityCache[b.prevInv.item.api];
    const prevFallback = prevCityInfo && prevCityInfo.kota !== (fKota||'')
      ? `<div class="fallback-tag">${prevCityInfo.kota}</div>` : '';
    bahanHTML += `
    <span class="bahan-arrow">+</span>
    <div class="bahan-slot">
      <img src="${iconUrl(b.prevInv.item.api)}" alt="">
      <div class="bahan-qty">
        <span class="${prevCukup?'punya':'kurang'}">${prevPunya}</span>
        <span class="slash">/</span>
        <span class="butuh">${prevButuh}</span>
      </div>
      <div class="bahan-name">${b.prevInv.item.name}</div>
      ${prevFallback}
    </div>`;
  }

  bahanHTML += `
    <span class="bahan-arrow">→</span>
    <div class="bahan-slot">
      <img src="${iconUrl(b.hasilItem.api)}" alt="">
      <div class="bahan-qty"><span class="punya">max ${b.maxOutput}</span></div>
      <div class="bahan-name">${b.hasilItem.name}</div>
    </div>`;

  document.getElementById('rPopInfo').innerHTML = `<div class="bahan-row">${bahanHTML}</div>`;

  // Return rate default dari global
  document.getElementById('rReturnRate').value = document.getElementById('returnRate').value||36.7;

  // Slider
  const sl=document.getElementById('rSlider'), rv=document.getElementById('rQty');
  sl.max=b.maxOutput; sl.value=b.maxOutput; rv.max=b.maxOutput; rv.value=b.maxOutput;
  document.getElementById('rHabis').checked=true;
  document.getElementById('rQtyField').style.opacity='0.4';
  sl.disabled=true; rv.disabled=true;

  openOverlay('overlayRefine');
}

function syncRQty(src) {
  const s=document.getElementById('rSlider'), v=document.getElementById('rQty');
  const max=parseInt(s.max)||1;
  if(src==='s') v.value=s.value;
  else s.value=Math.min(Math.max(parseInt(v.value)||1,1),max);
}

function onRHabisChange() {
  const checked=document.getElementById('rHabis').checked;
  const sl=document.getElementById('rSlider'), rv=document.getElementById('rQty');
  document.getElementById('rQtyField').style.opacity=checked?'0.4':'1';
  sl.disabled=checked; rv.disabled=checked;
  if (checked && refineTarget) { sl.value=refineTarget.maxOutput; rv.value=refineTarget.maxOutput; }
}

function simulateLoop(qtyRaw, qtyPrev, rawQty, prevQty, retPct) {
  const ret = retPct / 100;
  let sRaw = qtyRaw, sPrev = qtyPrev, total = 0;
  while (true) {
    const fR   = Math.floor(sRaw / rawQty);
    const fP   = prevQty > 0 ? Math.floor(sPrev / prevQty) : fR;
    const dapat = prevQty > 0 ? Math.min(fR, fP) : fR;
    if (dapat === 0) break;
    sRaw  -= dapat * rawQty;
    sPrev -= prevQty > 0 ? dapat * prevQty : 0;
    total += dapat;
    sRaw  += Math.round(dapat * rawQty  * ret);
    sPrev += prevQty > 0 ? Math.round(dapat * prevQty * ret) : 0;
  }
  return { total, sRaw, sPrev };
}

function doRefine() {
  if (!refineTarget) return;
  const b      = refineTarget;
  const habis  = document.getElementById('rHabis').checked;
  const retPct = parseFloat(document.getElementById('rReturnRate').value)||36.7;
  const isBatu = b.jenis==='batu';
  const hasilEnc = isBatu ? 0 : b.enc;

  // Snapshot modal awal SEBELUM bahan mentah dikurangi/dihapus
  if (modalLock === null) {
    modalLock = calcNilaiInventory('raw');
    document.getElementById('coinFooter').classList.add('show');
  }

  let totalOutput = 0;
  let sisaRaw  = b.rawInv.qty;
  let sisaPrev = b.prevInv ? b.prevInv.qty : 0;

  if (habis) {
    // Simulasi loop sampai mentok (return bahan masuk kembali tiap putaran)
    const { total, sRaw, sPrev } = simulateLoop(sisaRaw, sisaPrev, b.f.raw, b.f.prev, retPct);
    totalOutput = total;
    sisaRaw     = sRaw;
    sisaPrev    = sPrev;
  } else {
    // Refine manual sejumlah yang dipilih — satu kali, tanpa loop
    const jumlah    = Math.min(parseInt(document.getElementById('rQty').value)||1, b.maxOutput);
    const rawPakai  = jumlah * b.f.raw;
    const prevPakai = b.f.prev > 0 ? jumlah * b.f.prev : 0;
    totalOutput = jumlah;
    sisaRaw     = sisaRaw  - rawPakai  + Math.round(rawPakai  * retPct/100);
    sisaPrev    = sisaPrev - prevPakai + Math.round(prevPakai * retPct/100);
  }

  // Update stok raw
  b.rawInv.qty = sisaRaw;
  if (b.rawInv.qty <= 0) inventory.splice(inventory.indexOf(b.rawInv), 1);

  // Update stok prev
  if (b.prevInv) {
    b.prevInv.qty = sisaPrev;
    if (b.prevInv.qty <= 0) inventory.splice(inventory.indexOf(b.prevInv), 1);
  }

  // Hitung output (batu pakai multiplier)
  const outQty = isBatu ? totalOutput * (BATU_ENC_MULT[b.enc]||1) : totalOutput;

  // Tambah hasil ke inventory
  const exHasil = inventory.find(i=>i.item.jenis===b.jenis&&i.item.tipe==='hasil'&&i.item.tier===b.hasilItem.tier&&i.item.enc===hasilEnc);
  const hHarga  = priceCache[b.hasilItem.api]||0;
  if (exHasil) {
    exHasil.qty += outQty;
  } else {
    inventory.push({item:b.hasilItem, qty:outQty, harga:hHarga});
  }

  closeOverlay('overlayRefine');
  renderInventory();
  renderRefineBtns();
  updateFooter();
  showToast(`⚔️ Refine → ${outQty} ${b.hasilItem.name}`);

  if (!sudahCatat) { sudahCatat=true; catatAktivitas(); }
}

// ===================== FOOTER =====================
function calcNilaiInventory(tipe) {
  return inventory.filter(i=>tipe==='all'||i.item.tipe===tipe).reduce((s,i)=>s+i.qty*(i.harga||0),0);
}

function updateFooter() {
  if (modalLock===null) return; // belum refine pertama
  const nilaiSekarang = calcNilaiInventory('all');
  const premium = document.getElementById('cbPrem').checked;
  const taxPct  = premium?4:8;
  // Pajak hanya pada item hasil
  const nilaiHasil = calcNilaiInventory('hasil');
  const tax        = nilaiHasil * taxPct/100;
  const nilaiNet   = nilaiSekarang - tax;
  const profit     = nilaiNet - modalLock;

  document.getElementById('coinModal').textContent = fmt(modalLock);
  document.getElementById('coinNilai').textContent = fmt(nilaiSekarang);
  document.getElementById('taxLbl').textContent    = `Pajak ${taxPct}% (hasil)`;
  document.getElementById('taxVal').textContent    = fmt(tax);
  const pEl = document.getElementById('profitVal');
  pEl.textContent = (profit>=0?'+':'')+fmt(profit);
  pEl.className   = 'profit-val '+(profit>=0?'pos':'neg');
}

// ===================== RESET =====================
function doReset() {
  if (inventory.length===0 && modalLock===null) return;
  if (!confirm('Reset semua? Inventory dan data refine akan dihapus.')) return;
  inventory=[]; modalLock=null; sudahCatat=false;
  renderInventory(); renderRefineBtns();
  document.getElementById('coinFooter').classList.remove('show');
  document.getElementById('refineBtns').classList.remove('show');
  showToast('🗑 Reset selesai');
}

// ===================== UTILS =====================
function openOverlay(id)  { document.getElementById(id).classList.add('show'); }
function closeOverlay(id) { document.getElementById(id).classList.remove('show'); }
function closeOverlayOutside(e,id) { if(e.target===document.getElementById(id)) closeOverlay(id); }

function fmt(v) {
  if (!v) return '0';
  const a=Math.abs(v), s=v<0?'-':'';
  if(a>=1e6) return s+(a/1e6).toFixed(2)+'M';
  if(a>=1e3) return s+(a/1e3).toFixed(1)+'K';
  return s+Math.round(a).toLocaleString();
}

function showToast(msg) {
  const t=document.getElementById('toast');
  t.textContent=msg; t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),2200);
}

async function catatAktivitas() {
  const loggedIn = @json(auth()->check());
  if (!loggedIn) return;
  try {
    await fetch('/api/catat-aktivitas',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body:JSON.stringify({type:'refine'})
    });
  } catch(e){}
}

// ===================== INIT =====================
buildMatCol1();
buildMatCol2();
buildTierDropRefine();
buildEncDropRefine();
buildKotaDropRefine();
filterItems();
renderInventory();
onKotaChange();
initWizard();
applyUrlJenisAdvance();
setMode(localStorage.getItem('rw_mode') || 'simple');

</script>

<x-comments page="refine" />
@endsection