@extends('layouts.app')
@php
    // Nama station terlokalisasi kalau ada key-nya (pola sama kayak home.blade.php),
    // fallback ke name dari DB.
    $stationName = \Illuminate\Support\Facades\Lang::has('home.crafting_stations.'.$station.'.name')
        ? __('home.crafting_stations.'.$station.'.name')
        : ($stationName ?? ucwords(str_replace('-', ' ', $station)));
@endphp
@section('title', $stationName . ' — Albion Online Tools')

@section('meta_description', 'Calculate crafting costs and profit for ' . $stationName . ' items in Albion Online. Real-time material prices, recipe viewer, quality system, resource calculator.')

@section('meta_keywords', 'albion crafting calculator, ' . strtolower($stationName) . ', crafting profit albion, recipe calculator, crafting cost')

@section('og_description', 'Albion Online ' . $stationName . ' Calculator: Calculate crafting costs, material requirements, profit margins. Real-time prices from Albion Data API.')

@section('content')
@vite(['resources/css/kalkulator/crafting-mage-tower.css'])
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">


<div class="mode-toggle">
  <button id="btnModeSimple" class="mode-btn active" onclick="setMTMode('simple')">🧙 {{ __('crafting.mode_simple') }}</button>
  <button id="btnModeAdvance" class="mode-btn" onclick="setMTMode('advance')">⚙️ {{ __('crafting.mode_advance') }}</button>
</div>

<!-- ====== MODE SIMPLE (dulunya Mode Advance) ====== -->
<div id="mtSimpleWrap">
<div>
  <div class="panel">
    <div class="panel-header">
      <span>🪄</span>
      <span class="panel-title">{{ $stationName }}</span>
      <input type="text" class="header-search" id="searchInput" placeholder="{{ __('crafting.search_placeholder') }}" oninput="onSearch()">
    </div>

    <div class="filter-bar" id="filterBar">
      <!-- CATEGORY -->
      <div class="flt-wrap">
        <div class="flt-btn" id="btnCategory" onclick="toggleDrop('category')">
          <span class="flt-label" id="lblCategory">{{ __('crafting.filter.category') }}</span>
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
          <span class="flt-label" id="lblTier">{{ __('crafting.filter.tier') }}</span>
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
          <span class="flt-label" id="lblEnc">{{ __('crafting.filter.enchant') }}</span>
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
          <div class="item-list-empty" id="emptyMsg">{{ __('crafting.empty_select_category') }}</div>
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
            <div class="craft-slots-empty">{{ __('crafting.select_item_to_start') }}</div>
          </div>
        </div>
        <div class="craft-reset-row" id="craftResetRow" style="display:none">
          <button class="reset-btn" onclick="resetCraftTarget()">🔄 {{ __('crafting.change_item') }}</button>
        </div>

        <!-- JURNAL — toggle tombol (bukan checkbox), pilihan jenis/tier
             pakai chip tombol (bukan dropdown select). Jurnal TIDAK perlu
             dimasukkan ke Inventory bahan; status penuh/progress-nya
             tampil sebagai badge di sini juga. -->
        <div class="craft-journal-section" id="craftJournalSection" style="display:none">
          <button type="button" class="craft-journal-toggle-btn" id="useJournalBtn" onclick="onUseJournalToggle()">📔 {{ __('crafting.use_journal') }}</button>
          <div class="craft-journal-picker" id="journalPickerRow" style="display:none">
            <img id="journalIcon" src="" alt="Jurnal" onerror="this.style.opacity=.3">
            <div class="craft-journal-picker-body">
              <div class="craft-journal-dd-row">
                <div class="flt-wrap">
                  <div class="flt-btn" id="btnJtype" onclick="toggleDrop('jtype')">
                    <span class="flt-label" id="lblJtype">{{ __('crafting.journal_type') }}</span>
                    <span class="flt-val"   id="valJtype" style="display:none"></span>
                    <span class="flt-arrow">▼</span>
                  </div>
                  <div class="drop-wrap" id="dropJtype">
                    <div class="drop-col" id="colJtype"></div>
                  </div>
                </div>
                <div class="flt-wrap">
                  <div class="flt-btn" id="btnJtier" onclick="toggleDrop('jtier')">
                    <span class="flt-label" id="lblJtier">{{ __('crafting.filter.tier') }}</span>
                    <span class="flt-val"   id="valJtier" style="display:none"></span>
                    <span class="flt-arrow">▼</span>
                  </div>
                  <div class="drop-wrap" id="dropJtier">
                    <div class="drop-col" id="colJtier"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- INVENTORY (murni bahan crafting — jurnal gak ikut di sini) -->
        <div class="craft-inv-section">
          <div class="craft-inv-lbl">📦 {{ __('crafting.inventory') }} (<span id="craftInvCount">0</span>)</div>
          <div class="cinv-grid" id="craftInvGrid"></div>
        </div>

        <!-- BOTTOM BAR: Return % + Harga Jual + Modal + Tombol Craft -->
        <div class="craft-bottom-bar">
          <div class="craft-ret-wrap">
            <label>♻️ {{ __('crafting.return_label') }}</label>
            <input class="craft-ret-inp" type="number" id="craftReturn" value="21.5" min="0" max="100" step="0.1">
            <span style="color:var(--text-dim);font-size:12px">%</span>
          </div>
          <div class="craft-qty-wrap">
            <label>🔢 {{ __('crafting.quantity_label') }}</label>
            <input class="craft-qty-inp" type="number" id="craftQty" value="1" min="1" disabled>
            <label class="craft-max-chk"><input type="checkbox" id="craftHabis" onchange="onCraftHabisChange()"> {{ __('crafting.craft_all_checkbox') }}</label>
          </div>
          <div class="craft-sell-wrap">
            <label>💵 {{ __('crafting.sell_price_label') }}</label>
            <input class="craft-sell-inp" type="number" id="craftSellPrice" placeholder="0" min="0" oninput="craftSellPriceIsDefault=false">
            <label class="craft-max-chk"><input type="checkbox" id="craftPremium" onchange="renderCraftResultPanel()"> {{ __('crafting.premium') }}</label>
            <label class="craft-max-chk"><input type="checkbox" id="craftOrderCost" onchange="renderCraftResultPanel()"> {{ __('crafting.sell_order_label') }}</label>
          </div>
          <button class="craft-btn" id="craftBtn" disabled onclick="doCraft()">⚒️ {{ __('crafting.craft_btn') }}</button>
        </div>

        <!-- HASIL CRAFT — muncul begitu tombol Craft berhasil ditekan.
             MODAL: bahan crafting (SNAPSHOT sekali di craft pertama sesi ini)
                    + jurnal dibutuhkan (DINAMIS, dari totalFameAccumulated).
             PROFIT: item hasil craft (real-time) + sisa bahan (real-time)
                    + jurnal penuh (dinamis) + total profit. -->
        <div class="craft-result-panel" id="craftResultPanel" style="display:none">
          <div class="crp-group-lbl">💰 {{ __('crafting.modal_group') }}</div>
          <div class="crp-row"><span class="crp-label">{{ __('crafting.crafting_materials') }}</span><span class="crp-val" id="crpModalBahan">0</span></div>
          <div class="crp-row" id="crpModalJurnalRow" style="display:none"><span class="crp-label">{{ __('crafting.journal_needed') }}</span><span class="crp-val" id="crpModalJurnal">0</span></div>
          <div class="crp-row crp-subtotal"><span class="crp-label">{{ __('crafting.total_modal') }}</span><span class="crp-val" id="crpTotalModal">0</span></div>

          <div class="crp-group-lbl" id="crpProfitGroupLbl">📈 {{ __('crafting.profit_group') }}</div>
          <div class="crp-row" id="crpRowProfitItem"><span class="crp-label">{{ __('crafting.result_item') }}</span><span class="crp-val" id="crpProfitItem">0</span></div>
          <div class="crp-row" id="crpRowPajak"><span class="crp-label">{{ __('crafting.tax_and_order_fee') }}</span><span class="crp-val negative" id="crpPajak">0</span></div>
          <div class="crp-row" id="crpRowProfitSisa"><span class="crp-label">{{ __('crafting.remaining_materials') }}</span><span class="crp-val" id="crpProfitSisa">0</span></div>
          <div class="crp-row" id="crpProfitJurnalRow" style="display:none"><span class="crp-label">{{ __('crafting.journal_full') }}</span><span class="crp-val" id="crpProfitJurnal">0</span></div>
          <div class="crp-row crp-subtotal" id="crpRowHasilAkhir"><span class="crp-label">{{ __('crafting.final_result') }}</span><span class="crp-val" id="crpHasilAkhir">0</span></div>

          <div class="crp-row crp-total" id="crpRowTotalProfit"><span class="crp-label">{{ __('crafting.total_profit') }}</span><span class="crp-val" id="crpTotalProfit">0</span></div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>

<!-- ====== MODE ADVANCE (mirip Refining Calculator) ====== -->
<div id="mtAdvanceWrap" style="display:none">
  <div class="panel">
    <div class="panel-header">
      <span>⚙️</span>
      <span class="panel-title">{{ $stationName }} — {{ __('crafting.mode_advance') }}</span>
      <input type="text" class="header-search" id="advSearchInput" placeholder="{{ __('crafting.search_placeholder') }}" oninput="onAdvSearch()">
    </div>

    <div class="rw-main">
      <div class="rw-col-left">
        <div class="filter-bar" id="advFilterBar">
          <!-- TIER -->
          <div class="flt-wrap">
            <div class="flt-btn" id="btnAdvTier" onclick="toggleDrop('advTier')">
              <span class="flt-label" id="lblAdvTier">{{ __('crafting.filter.tier') }}</span>
              <span class="flt-val" id="valAdvTier" style="display:none"></span>
              <span class="flt-arrow">▼</span>
            </div>
            <div class="drop-wrap" id="dropAdvTier">
              <div class="drop-col" id="colAdvTier"></div>
            </div>
          </div>
          <!-- ENCHANTMENT -->
          <div class="flt-wrap">
            <div class="flt-btn" id="btnAdvEnc" onclick="toggleDrop('advEnc')">
              <span class="flt-label" id="lblAdvEnc">{{ __('crafting.filter.enchant') }}</span>
              <span class="flt-val" id="valAdvEnc" style="display:none"></span>
              <span class="flt-arrow">▼</span>
            </div>
            <div class="drop-wrap" id="dropAdvEnc">
              <div class="drop-col" id="colAdvEnc"></div>
            </div>
          </div>
        </div>

        <!-- Item List (Materials) -->
        <div class="item-list" id="advItemList">
          <div class="item-list-empty" id="advEmptyMsg">{{ __('crafting.loading_items') }}</div>
          <div class="item-table-wrap" id="advItemTableWrap" style="display:none">
            <div id="advItemGrid"></div>
          </div>
        </div>

        <div class="bot-bar">
          <button class="reset-btn" onclick="doAdvReset()">🗑 {{ __('crafting.reset') }}</button>
        </div>
      </div>

      <div class="rw-col-right">
        <!-- Journal Section (sama seperti Simple Mode) -->
        <div class="craft-journal-section" id="advJournalSection" style="display:none">
          <button type="button" class="craft-journal-toggle-btn" id="advUseJournalBtn" onclick="onAdvUseJournalToggle()">📔 {{ __('crafting.use_journal') }}</button>
          <div class="craft-journal-picker" id="advJournalPickerRow" style="display:none">
            <img id="advJournalIcon" src="" alt="Jurnal" onerror="this.style.opacity=.3">
            <div class="craft-journal-picker-body">
              <div class="craft-journal-dd-row">
                <div class="flt-wrap">
                  <div class="flt-btn" id="btnAdvJtype" onclick="toggleDrop('advJtype')">
                    <span class="flt-label" id="lblAdvJtype">{{ __('crafting.journal_type') }}</span>
                    <span class="flt-val" id="valAdvJtype" style="display:none"></span>
                    <span class="flt-arrow">▼</span>
                  </div>
                  <div class="drop-wrap" id="dropAdvJtype">
                    <div class="drop-col" id="colAdvJtype"></div>
                  </div>
                </div>
                <div class="flt-wrap">
                  <div class="flt-btn" id="btnAdvJtier" onclick="toggleDrop('advJtier')">
                    <span class="flt-label" id="lblAdvJtier">{{ __('crafting.filter.tier') }}</span>
                    <span class="flt-val" id="valAdvJtier" style="display:none"></span>
                    <span class="flt-arrow">▼</span>
                  </div>
                  <div class="drop-wrap" id="dropAdvJtier">
                    <div class="drop-col" id="colAdvJtier"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Inventory Section -->
        <div class="craft-inv-section" id="advInvSection">
          <div class="craft-inv-lbl">📦 {{ __('crafting.inventory') }} (<span id="advInvCount2">0</span>)</div>
          <div class="cinv-grid" id="advInvGrid"></div>
        </div>

        <!-- Craft Buttons (item yang bisa di-craft) -->
        <div class="craft-craftable-section" id="advCraftableSection" style="display:none">
          <div class="craft-inv-lbl">⚒️ {{ __('crafting.craftable_items') }}</div>
          <div class="craft-craftable-grid" id="advCraftableGrid"></div>
        </div>

        <!-- Bottom Bar: Return % + Quantity + Sell Price + Craft Button -->
        <div class="craft-bottom-bar" id="advBottomBar" style="display:none">
          <div class="craft-ret-wrap">
            <label>♻️ {{ __('crafting.return_label') }}</label>
            <input class="craft-ret-inp" type="number" id="advCraftReturn" value="21.5" min="0" max="100" step="0.1">
            <span style="color:var(--text-dim);font-size:12px">%</span>
          </div>
          <div class="craft-qty-wrap">
            <label>🔢 {{ __('crafting.quantity_label') }}</label>
            <input class="craft-qty-inp" type="number" id="advCraftQty" value="1" min="1" disabled>
            <label class="craft-max-chk"><input type="checkbox" id="advCraftHabis" onchange="onAdvCraftHabisChange()"> {{ __('crafting.craft_all_checkbox') }}</label>
          </div>
          <div class="craft-sell-wrap">
            <label>💵 {{ __('crafting.sell_price_label') }}</label>
            <input class="craft-sell-inp" type="number" id="advCraftSellPrice" placeholder="0" min="0">
            <label class="craft-max-chk"><input type="checkbox" id="advCraftPremium" onchange="renderAdvCraftResultPanel()"> {{ __('crafting.premium') }}</label>
            <label class="craft-max-chk"><input type="checkbox" id="advCraftOrderCost" onchange="renderAdvCraftResultPanel()"> {{ __('crafting.sell_order_label') }}</label>
          </div>
          <button class="craft-btn" id="advCraftBtn" disabled onclick="doAdvCraft()">⚒️ {{ __('crafting.craft_btn') }}</button>
        </div>

        <!-- Craft Result Panel -->
        <div class="craft-result-panel" id="advResultPanel" style="display:none">
          <div class="crp-group-lbl">💰 {{ __('crafting.modal_group') }}</div>
          <div class="crp-row"><span class="crp-label">{{ __('crafting.crafting_materials') }}</span><span class="crp-val" id="advModalBahan">0</span></div>
          <div class="crp-row" id="advModalJurnalRow" style="display:none"><span class="crp-label">{{ __('crafting.journal_needed') }}</span><span class="crp-val" id="advModalJurnal">0</span></div>
          <div class="crp-row crp-subtotal"><span class="crp-label">{{ __('crafting.total_modal') }}</span><span class="crp-val" id="advTotalModal">0</span></div>

          <div class="crp-group-lbl" id="advProfitGroupLbl">📈 {{ __('crafting.profit_group') }}</div>
          <div class="crp-row" id="advRowProfitItem"><span class="crp-label">{{ __('crafting.result_item') }}</span><span class="crp-val" id="advProfitItem">0</span></div>
          <div class="crp-row" id="advRowPajak"><span class="crp-label">{{ __('crafting.tax_and_order_fee') }}</span><span class="crp-val negative" id="advPajak">0</span></div>
          <div class="crp-row" id="advRowProfitSisa"><span class="crp-label">{{ __('crafting.remaining_materials') }}</span><span class="crp-val" id="advProfitSisa">0</span></div>
          <div class="crp-row" id="advProfitJurnalRow" style="display:none"><span class="crp-label">{{ __('crafting.journal_full') }}</span><span class="crp-val" id="advProfitJurnal">0</span></div>
          <div class="crp-row crp-subtotal" id="advRowHasilAkhir"><span class="crp-label">{{ __('crafting.final_result') }}</span><span class="crp-val" id="advHasilAkhir">0</span></div>

          <div class="crp-row crp-total" id="advRowTotalProfit"><span class="crp-label">{{ __('crafting.total_profit') }}</span><span class="crp-val" id="advTotalProfit">0</span></div>
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
        <label style="display:block;font-family:'Cinzel',serif;font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">{{ __('crafting.price_per_unit_optional') }}</label>
        <input type="number" id="caHarga" placeholder="0" min="0" style="width:100%;background:var(--slot-bg);border:1px solid var(--slot-bd);border-radius:3px;color:var(--text-lt);font-size:14px;padding:8px 10px;outline:none;">
      </div>
      <div id="caQtyField">
        <label style="display:block;font-family:'Cinzel',serif;font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">{{ __('crafting.quantity_label') }}</label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input type="range" id="caQtySlider" min="1" max="999" value="100" oninput="syncCaQty('s')" style="flex:1;">
          <input type="number" id="caQty" value="100" min="1" max="999999" oninput="syncCaQty('v')" style="width:80px;background:var(--slot-bg);border:1px solid var(--slot-bd);border-radius:3px;color:var(--text-lt);font-size:14px;padding:8px 10px;outline:none;">
        </div>
      </div>
      <div class="pop-btn-row" id="caBtnRow" style="display:flex;gap:7px;">
        <button class="wiz-btn-hitung" style="flex:1" onclick="doCraftAddResource()">➕ {{ __('crafting.add_to_inventory') }}</button>
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
        <label style="display:block;font-family:'Cinzel',serif;font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">{{ __('crafting.price_per_full_journal') }}</label>
        <input type="number" id="jpHarga" placeholder="0" min="0" style="width:100%;background:var(--slot-bg);border:1px solid var(--slot-bd);border-radius:3px;color:var(--text-lt);font-size:14px;padding:8px 10px;outline:none;" oninput="onJournalPriceOverrideInput()">
      </div>
      <!-- Jurnal TERISI SEBAGIAN — cuma info progress fame, read-only, gak ikut Profit -->
      <div id="jpPartialFields" style="display:none;">
        <div style="font-family:'Crimson Text',serif;font-size:14px;color:var(--text-lt);text-align:center;padding:6px 0;">
          {{ __('crafting.progress_label') }} <span id="jpFameProgress" style="color:var(--gold);font-weight:700;">0 / 0</span> {{ __('crafting.fame_unit') }}
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ====== POPUP ADD MATERIAL ADVANCE MODE ====== -->
<div class="popup-overlay" id="advAddOverlay" onclick="closeAdvAddOnBg(event)">
  <div class="popup-box" id="advAddBox" style="max-width:340px;">
    <button class="popup-close" onclick="closeAdvAddOverlay()">✕</button>
    <div class="popup-head">
      <img id="advAddIcon" src="" alt="" onerror="this.style.opacity=.3">
      <div>
        <div class="popup-item-name" id="advAddName">—</div>
        <div class="popup-item-sub" id="advAddDesc">—</div>
      </div>
    </div>
    <div style="padding:14px 16px;display:flex;flex-direction:column;gap:10px;">
      <div>
        <label style="display:block;font-family:'Cinzel',serif;font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">{{ __('crafting.price_per_unit_optional') }}</label>
        <input type="number" id="advAddHarga" placeholder="0" min="0" style="width:100%;background:var(--slot-bg);border:1px solid var(--slot-bd);border-radius:3px;color:var(--text-lt);font-size:14px;padding:8px 10px;outline:none;">
      </div>
      <div>
        <label style="display:block;font-family:'Cinzel',serif;font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">{{ __('crafting.quantity_label') }}</label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input type="range" id="advAddQtySlider" min="1" max="999" value="100" oninput="syncAdvAddQty('s')" style="flex:1;">
          <input type="number" id="advAddQty" value="100" min="1" max="999999" oninput="syncAdvAddQty('v')" style="width:80px;background:var(--slot-bg);border:1px solid var(--slot-bd);border-radius:3px;color:var(--text-lt);font-size:14px;padding:8px 10px;outline:none;">
        </div>
      </div>
      <div class="pop-btn-row" style="display:flex;gap:7px;">
        <button class="wiz-btn-hitung" style="flex:1" onclick="doAdvAddMaterial()">➕ {{ __('crafting.add_to_inventory') }}</button>
      </div>
    </div>
  </div>
</div>

<!-- ====== POPUP CRAFT ADVANCE MODE ====== -->
<div class="popup-overlay" id="advCraftPopupOverlay" onclick="closeAdvCraftPopupOnBg(event)">
  <div class="popup-box" id="advCraftPopupBox" style="max-width:500px;">
    <button class="popup-close" onclick="closeAdvCraftPopup()">✕</button>
    <div class="popup-head">
      <img id="advCraftIcon" src="" alt="" onerror="this.style.opacity=.3">
      <div>
        <div class="popup-item-name" id="advCraftName">—</div>
        <div class="popup-item-sub" id="advCraftDesc">—</div>
      </div>
    </div>
    <div style="padding:14px 16px;display:flex;flex-direction:column;gap:12px;">
      <!-- Bahan yang dibutuhkan -->
      <div id="advCraftMaterialsInfo" style="background:var(--slot-bg);border:1px solid var(--slot-bd);border-radius:4px;padding:10px;"></div>
      
      <!-- Return Rate -->
      <div>
        <label style="display:block;font-family:'Cinzel',serif;font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">♻️ {{ __('crafting.return_label') }}</label>
        <input type="number" id="advCraftReturnRate" value="21.5" min="0" max="100" step="0.1" style="width:100%;background:var(--slot-bg);border:1px solid var(--slot-bd);border-radius:3px;color:var(--text-lt);font-size:14px;padding:8px 10px;outline:none;">
      </div>
      
      <!-- Quantity Slider -->
      <div id="advCraftQtyField">
        <label style="display:block;font-family:'Cinzel',serif;font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">🔢 {{ __('crafting.quantity_label') }}</label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input type="range" id="advCraftSlider" min="1" max="100" value="1" oninput="syncAdvCraftQty('s')" style="flex:1;">
          <input type="number" id="advCraftQtyInput" value="1" min="1" max="100" oninput="syncAdvCraftQty('v')" style="width:80px;background:var(--slot-bg);border:1px solid var(--slot-bd);border-radius:3px;color:var(--text-lt);font-size:14px;padding:8px 10px;outline:none;">
        </div>
      </div>
      
      <!-- Checkbox Habis -->
      <label style="display:flex;align-items:center;gap:8px;font-family:'Crimson Text',serif;font-size:14px;color:var(--text-lt);cursor:pointer;">
        <input type="checkbox" id="advCraftHabisCheckbox" onchange="onAdvCraftHabisCheckboxChange()">
        {{ __('crafting.craft_all_checkbox') }}
      </label>
      
      <!-- Button Craft -->
      <div class="pop-btn-row" style="display:flex;gap:7px;">
        <button class="wiz-btn-hitung" style="flex:1" onclick="doAdvCraftExecute()">⚒️ {{ __('crafting.craft_btn') }}</button>
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
      <div class="popup-loading">{{ __('crafting.loading') }}</div>
    </div>
  </div>
</div>

<script>

// ===================== I18N =====================
const CRAFTING_I18N = @json(__('crafting'), JSON_UNESCAPED_UNICODE);
function t(key, rep = {}) {
  let s = key.split('.').reduce((o, k) => (o == null ? undefined : o[k]), CRAFTING_I18N);
  if (typeof s !== 'string') return key;
  for (const k in rep) s = s.replace(':' + k, rep[k]);
  return s;
}

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
const TIER_LABEL = {1:t('filter.tier_label',{n:1}),2:t('filter.tier_label',{n:2}),3:t('filter.tier_label',{n:3}),4:t('filter.tier_label',{n:4}),5:t('filter.tier_label',{n:5}),6:t('filter.tier_label',{n:6}),7:t('filter.tier_label',{n:7}),8:t('filter.tier_label',{n:8})};
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
  col.appendChild(makeItem(t('filter.all'), false, !selKat1, () => {
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
  col2.appendChild(makeItem(t('filter.all'), false, !selKat2, () => {
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
  col3.appendChild(makeItem(t('filter.all'), false, !selKat3, () => {
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
  col.appendChild(makeItem(t('filter.all'), false, !selTier, () => {
    selTier = null; setFilterVal('lblTier','valTier',null); closeDrop(); fetchItems();
  }));
  TIERS.forEach(t => col.appendChild(makeItem(TIER_LABEL[t], false, selTier === t, () => {
    selTier = t; setFilterVal('lblTier','valTier',TIER_LABEL[t]); closeDrop(); fetchItems();
  })));
}

function buildEncDrop() {
  const col = document.getElementById('colEnc');
  col.innerHTML = '';
  col.appendChild(makeItem(t('filter.all'), false, selEnc === null, () => {
    selEnc = null; setFilterVal('lblEnc','valEnc',null); closeDrop(); fetchItems();
  }));
  ENCS.forEach(e => col.appendChild(makeItem(t('filter.enchant_option', {n: e}), false, selEnc === e, () => {
    selEnc = e; setFilterVal('lblEnc','valEnc', t('filter.enchant_short', {n: e})); closeDrop(); fetchItems();
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
  showEmpty(t('loading_items'));
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
      if (!filtered.length) { showEmpty(t('no_items_found')); return; }
      renderItems(filtered);
    })
    .catch(() => showEmpty(t('failed_load_items')));
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
  document.getElementById('popupContent').innerHTML = '<div class="popup-loading">' + t('loading') + '</div>';
  document.getElementById('popupOverlay').classList.add('show');

  fetch('/api/crafting/item/' + itemId)
    .then(r => r.json())
    .then(item => {
      renderPopup(item);          // instan, pakai harga cache yang sudah ada
      refreshPopupPrices(itemId); // lalu coba real-time di background, timpa kalau berhasil
    })
    .catch(() => {
      document.getElementById('popupContent').innerHTML = '<div class="popup-loading">' + t('failed_load_data') + '</div>';
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
    : '<div style="color:var(--text-dim);font-style:italic;font-size:13px">' + t('no_recipe') + '</div>';

  document.getElementById('popupContent').innerHTML = `
    <div class="popup-head">
      <img src="${item.img_url}" alt="${item.name}" onerror="this.style.opacity=0.3">
      <div>
        <div class="popup-item-name">${item.name}</div>
        <div class="popup-item-sub">${tierText}</div>
      </div>
    </div>
    <div class="popup-prices">
      <div class="popup-prices-label">${t('price_per_city')}</div>
      <div class="city-prices-grid">${cityBoxes}</div>
    </div>
    <div class="popup-resources">
      <div class="popup-resources-label">${t('crafting_materials')}</div>
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
  
  // Initialize advance mode if switched to advance
  if (!isSimple && advMaterials.length === 0) {
    initAdvanceMode();
  }
}

// ============================================================
// CRAFTING (sekarang Mode Simple) — pilih item target dari tabel,
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
// CATATAN: max fame per journal SENGAJA TIDAK di sini lagi (dulu
// ALBION_JOURNAL_REQUIREMENT, global per tier doang) — beda journal_name
// bisa punya kurva fame beda (mis. Generalist's Journal T4 = 5400,
// sedangkan Fletcher's/Imbuer's/dll T4 = 3600). Sekarang dikirim backend
// per journal type lewat journal_options[].max_fame_by_tier — lihat
// getJournalMaxFame().
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

// Max fame buat isi 1 journal PENUH — per journal type & tier, dari
// journal_options[].max_fame_by_tier (dikirim backend, sumbernya tabel
// journal_requirements hasil sync items.xml). GANTI dari
// ALBION_JOURNAL_REQUIREMENT lama yang global per tier doang, gak lihat
// journal_name — itu yang bikin Tier 4 Fletcher's kepake 1200 padahal
// 3600, dan Generalist's Journal ikut kepake angka journal crafting
// padahal sebenarnya beda kurva (5400 di T4).
function getJournalMaxFame() {
  if (!craftJournalType || !craftJournalTier) return null;
  const opt = craftJournalOptions.find(o => o.name === craftJournalType);
  const maxByTier = opt && opt.max_fame_by_tier;
  const val = maxByTier ? maxByTier[craftJournalTier] : null;
  if (val === undefined || val === null) return null;
  return val;
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

// Field Jumlah SELALU otomatis (gak bisa diketik manual) — cuma ada satu
// checkbox "Crafting Habis":
//  - TIDAK dicentang -> craft SEKALI dengan jumlah maksimal yang bisa
//    dibikin dari bahan yang ada sekarang (getMaxCraftable).
//  - Dicentang -> loop craft berulang sampai bahan mentok, bahan hasil
//    return% ikut dipakai lagi buat putaran berikutnya (simulateLoopN).
function updateCraftQtyField() {
  const group  = craftRecipes[craftActiveRecipe] || [];
  const habis  = isHabiskanBahanMode();
  const qtyInp = document.getElementById('craftQty');
  qtyInp.disabled = true;

  if (habis) {
    const retPct = Math.min(100, Math.max(0, parseFloat(document.getElementById('craftReturn').value) || 0));
    const { total } = simulateLoopN(group, retPct);
    qtyInp.value = total;
  } else {
    const max = getMaxCraftable(group);
    qtyInp.value = max;
  }
}

function onCraftHabisChange() {
  updateCraftQtyField();
  updateCraftModal();
  updateCraftButtonState();
}

function isHabiskanBahanMode() {
  const el = document.getElementById('craftHabis');
  return el ? el.checked : false;
}

// Dropdown jenis jurnal — pakai komponen dropdown yang sama kayak filter
// tabel (toggleDrop/makeItem/setFilterVal, lihat CATEGORY/TIER/ENC di atas).
function buildJournalTypeDrop() {
  const col = document.getElementById('colJtype');
  col.innerHTML = '';
  craftJournalOptions.forEach(o => {
    col.appendChild(makeItem(o.name, false, o.name === craftJournalType, () => {
      craftJournalType = o.name;
      setFilterVal('lblJtype', 'valJtype', o.name);
      closeDrop();
      const tiers = o.tiers || [];
      if (!tiers.some(t => t == craftJournalTier)) craftJournalTier = tiers[tiers.length - 1] ?? craftJournalTier;
      buildJournalTypeDrop();
      buildJournalTierDrop();
      setFilterVal('lblJtier', 'valJtier', t('filter.tier_label', {n: craftJournalTier}));
      updateJournalIcon();
      renderCraftInventory();
      renderCraftResultPanel();
      saveCraftState();
    }));
  });
}

// Dropdown tier jurnal, mengikuti jenis journal yg aktif.
function buildJournalTierDrop() {
  const opt   = craftJournalOptions.find(o => o.name === craftJournalType);
  const tiers = opt ? opt.tiers : [];
  const col   = document.getElementById('colJtier');
  col.innerHTML = '';
  tiers.forEach(tr => {
    col.appendChild(makeItem(t('filter.tier_label', {n: tr}), false, tr == craftJournalTier, () => {
      craftJournalTier = tr;
      setFilterVal('lblJtier', 'valJtier', t('filter.tier_label', {n: tr}));
      closeDrop();
      buildJournalTierDrop();
      updateJournalIcon();
      renderCraftInventory();
      renderCraftResultPanel();
      saveCraftState();
    }));
  });
}

function updateJournalIcon() {
  document.getElementById('journalIcon').src = journalIconUrl(craftJournalType, craftJournalTier);
}

// Tombol toggle "Gunakan Jurnal" (ganti checkbox). Jurnal TIDAK perlu
// dimasukkan ke Inventory bahan — cuma nge-switch mode fame-tracking.
function onUseJournalToggle() {
  craftUseJournal = !craftUseJournal;
  document.getElementById('useJournalBtn').classList.toggle('active', craftUseJournal);
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

  craftJournalType = (savedJournal && savedJournal.type) || item.default_journal || craftJournalOptions[0].name;
  setFilterVal('lblJtype', 'valJtype', craftJournalType);
  buildJournalTypeDrop();

  const opt   = craftJournalOptions.find(o => o.name === craftJournalType);
  const tiers = opt ? opt.tiers : [];
  craftJournalTier = (savedJournal && savedJournal.tier) || item.default_journal_tier || tiers[tiers.length - 1];
  setFilterVal('lblJtier', 'valJtier', t('filter.tier_label', {n: craftJournalTier}));
  buildJournalTierDrop();

  craftUseJournal = savedJournal ? !!savedJournal.use : false;
  document.getElementById('useJournalBtn').classList.toggle('active', craftUseJournal);
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
  const req = getJournalMaxFame();
  if (!craftJournalTier || !req) return;
  journalPopupMode = 'full';
  const full = Math.floor(totalFameAccumulated / req);
  const autoVal = getJournalResourceValue();

  document.getElementById('jpIcon').src = journalFullIconUrl(craftJournalType, craftJournalTier);
  document.getElementById('jpName').textContent = t('journal_full');
  document.getElementById('jpSub').textContent = `× ${full}`;
  document.getElementById('jpFullFields').style.display = '';
  document.getElementById('jpPartialFields').style.display = 'none';
  document.getElementById('jpHarga').value = journalPriceOverride !== null
    ? journalPriceOverride
    : (autoVal !== null ? Math.round(autoVal) : '');
  document.getElementById('journalPopupOverlay').classList.add('show');
}

function openJournalPartialPopup() {
  const req = getJournalMaxFame();
  if (!craftJournalTier || !req) return;
  journalPopupMode = 'partial';
  const full     = Math.floor(totalFameAccumulated / req);
  const sisaFame = totalFameAccumulated - (full * req);

  document.getElementById('jpIcon').src = journalPartialIconUrl(craftJournalType, craftJournalTier);
  document.getElementById('jpName').textContent = t('journal_partial');
  document.getElementById('jpSub').textContent = t('progress_next_journal');
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
    .catch(() => showCraftToast('❌ ' + t('failed_load_recipe_toast')));
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
  showCraftToast('🔄 ' + t('switch_recipe_toast', {n: gi + 1}));
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
  document.getElementById('useJournalBtn').classList.remove('active');
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
    gridWrap.innerHTML = '<div class="craft-slots-empty">' + t('select_item_to_start') + '</div>';
    return;
  }

  // Tab Resep 1 / Resep 2 — cuma ditampilin kalau emang ada >1 alternatif resep
  tabsWrap.innerHTML = craftRecipes.length > 1
    ? craftRecipes.map((_, gi) => `
        <button class="craft-tab ${gi === craftActiveRecipe ? 'active' : ''}" onclick="switchCraftRecipe(${gi})">${t('recipe_tab', {n: gi + 1})}</button>
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
// POPUP TAMBAH / EDIT BAHAN (Simple Mode)
// ------------------------------------------------------------

// Sync qty slider and input for Simple Mode popup
function syncCaQty(src) {
  const slider = document.getElementById('caQtySlider');
  const input = document.getElementById('caQty');
  if (!slider || !input) return;
  const max = parseInt(slider.max) || 999;
  if (src === 's') {
    input.value = slider.value;
  } else {
    const val = Math.min(Math.max(parseInt(input.value) || 1, 1), 999999);
    if (val <= max) slider.value = val;
    input.value = val;
  }
}

function openResourceAdd(gi, ri) {
  const r = craftRecipes[gi][ri];
  craftPending = r;
  craftEditIdx = null;
  const existing = craftInv.find(i => (r.item_id ? i.itemId === r.item_id : i.name === r.name));

  document.getElementById('caIcon').src = r.img_url || '';
  document.getElementById('caName').textContent = r.name;
  document.getElementById('caNeed').textContent = t('resource_needed', {count: r.count});
  document.getElementById('caHarga').value = existing ? (existing.harga || '') : (bahanPriceCache[r.item_id] || '');
  
  // Set both slider and input
  const slider = document.getElementById('caQtySlider');
  const qtyInput = document.getElementById('caQty');
  if (slider) slider.value = r.count;
  if (qtyInput) qtyInput.value = r.count;
  
  document.getElementById('caQtyField').style.display = '';
  document.getElementById('caBtnRow').innerHTML = `<button class="wiz-btn-hitung" style="flex:1" onclick="doCraftAddResource()">➕ ${t('add_to_inventory')}</button>`;
  document.getElementById('craftAddOverlay').classList.add('show');
}

function openCraftInvEdit(idx) {
  const inv = craftInv[idx];
  if (!inv) return;
  craftPending = null;
  craftEditIdx = idx;

  document.getElementById('caIcon').src = inv.imgUrl || '';
  document.getElementById('caName').textContent = inv.name;
  document.getElementById('caNeed').textContent = t('edit_price_or_delete');
  document.getElementById('caHarga').value = inv.harga || bahanPriceCache[inv.itemId] || '';
  document.getElementById('caQty').value = inv.qty;
  // Edit dari inventory cuma boleh ubah harga — jumlah cuma bisa nambah lewat tabel bahan,
  // biar modal gampang dilacak.
  document.getElementById('caQtyField').style.display = 'none';
  document.getElementById('caBtnRow').innerHTML = `
    <button class="wiz-btn-hitung" style="flex:1" onclick="doCraftEditHarga()">💾 ${t('save_price')}</button>
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

function doCraftEditHarga() {
  if (craftEditIdx === null) return;
  craftInv[craftEditIdx].harga = parseFloat(document.getElementById('caHarga').value) || 0;
  closeCraftAddOverlay();
  updateCraftModal();
  saveCraftState();
  showCraftToast('💰 ' + t('price_updated'));
}

function doCraftDeleteResource() {
  if (craftEditIdx === null) return;
  craftInv.splice(craftEditIdx, 1);
  closeCraftAddOverlay();
  renderCraftInventory(); renderCraftSlots(); updateCraftQtyField(); updateCraftModal(); updateCraftButtonState();
  saveCraftState();
  showCraftToast('🗑 ' + t('deleted_toast'));
}

// ------------------------------------------------------------
// INVENTORY GRID — bahan crafting + slot jurnal (Penuh / Terisi
// Sebagian). Jurnal BARU muncul di sini SETELAH crafting pertama kali
// beneran ditekan (craftModalLock !== null) — sebelum itu fame masih 0
// dan jurnal belum relevan buat dimasukkan. Begitu sudah pernah craft,
// cuma yang benar-benar ADA yang ditampilkan: kalau cuma ada progress
// sebagian ya cuma slot itu, kalau udah ada yang penuh ya slot itu,
// kalau dua-duanya ada ya dua-duanya muncul.
// ------------------------------------------------------------
function renderCraftInventory() {
  const grid = document.getElementById('craftInvGrid');
  let html = '';

  const sudahCraft = craftModalLock !== null;
  const journalMaxFame = getJournalMaxFame();
  if (craftUseJournal && craftJournalTier && journalMaxFame && sudahCraft) {
    const req      = journalMaxFame;
    const full     = Math.floor(totalFameAccumulated / req);
    const sisaFame = totalFameAccumulated - (full * req);

    if (full > 0) {
      html += `<div class="cinv-slot journal-full" title="${t('journal_full_tooltip', {count: full})}" onclick="openJournalFullPopup()">
        <img src="${journalFullIconUrl(craftJournalType, craftJournalTier)}" alt="${t('journal_full')}" onerror="this.style.opacity=.3">
        <span class="cinv-qty">${full}</span>
      </div>`;
    }
    if (sisaFame > 0) {
      html += `<div class="cinv-slot journal-partial" title="${t('journal_partial_tooltip', {fame: Math.round(sisaFame), req})}" onclick="openJournalPartialPopup()">
        <img src="${journalPartialIconUrl(craftJournalType, craftJournalTier)}" alt="${t('journal_partial')}" onerror="this.style.opacity=.3">
        <span class="cinv-qty-frac">${Math.round(sisaFame)}/${req}</span>
      </div>`;
    }
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
  // Tombol "Modal" kecil di bottom bar udah dihapus (nilainya kepakai
  // dobel/gak konsisten sama panel breakdown "Total Modal" di bawah).
  // Fungsi ini dipertahankan namanya krn dipanggil di banyak tempat,
  // tapi sekarang cuma nge-trigger update panel detail.
  updateCraftResultPreview();
}

// ============================================================
// PREVIEW PANEL HASIL CRAFT — begitu bahan mulai dimasukin ke
// inventory (SEBELUM tombol Craft ditekan), panel craftResultPanel
// langsung nongol nampilin bagian Modal aja (Bahan Crafting +
// Total Modal). Grup Profit (Item Hasil, Sisa Bahan, Hasil Akhir,
// Total Profit) masih disembunyikan karena belum ada craft yang
// beneran kejadian — Item Hasil masih 0 dan "Sisa Bahan" belum
// valid sebagai konsep (bahan yg ada sekarang itu bahan AWAL, bukan
// sisa). Baris Jurnal Dibutuhkan juga masih disembunyikan karena
// itu dihitung dari totalFameAccumulated, yang cuma numpuk stlh
// craft pertama beneran terjadi.
//
// PENTING soal sumber angka Modal: PAKAI calcGroupValue(group)
// (nilai riil bahan yg ADA di inventory sekarang), BUKAN total dari
// updateCraftModal() (yg dihitung dari simulasi "berapa yg bakal
// KEPAKE kalau Craft ditekan sekarang"). Simulasi itu butuh SEMUA
// bahan resep lengkap dulu buat bisa hasilin output — begitu ada 1
// bahan yg masih 0 (belum sempat ditambahin), hasil simulasinya 0
// craft bisa dibuat, jadi SEMUA bahan lain yg udah ditambahin ikut
// keitung 0 juga (padahal udah beneran dibeli/ditaro user). Bug ini
// sempet kejadian pas modal-preview masih numpang hitungan
// updateCraftModal() — makanya sekarang dipisah, pakai
// calcGroupValue() yg gak peduli lengkap/belum resepnya.
//
// Begitu craftModalLock kekunci (= sudah pernah craft), fungsi ini
// gak ngapa2in lagi (return awal) — renderCraftResultPanel() yang
// pegang kendali penuh, termasuk munculin lagi grup Profit.
// ============================================================
function updateCraftResultPreview() {
  if (craftModalLock !== null) return; // sudah pernah craft, biar renderCraftResultPanel() yg urus

  const panel = document.getElementById('craftResultPanel');
  const adaBahan = craftInv.length > 0 || craftUseJournal;
  if (!adaBahan) { panel.style.display = 'none'; return; }

  const group = craftRecipes[craftActiveRecipe] || [];
  const modalBahan = calcGroupValue(group); // nilai bahan yg ADA di inventory, apa adanya

  panel.style.display = '';
  document.getElementById('crpModalBahan').textContent = formatSilver(modalBahan);
  document.getElementById('crpModalJurnalRow').style.display = 'none';
  document.getElementById('crpTotalModal').textContent = formatSilver(modalBahan);

  // Sembunyikan grup Profit selama masih preview (belum craft)
  document.getElementById('crpProfitGroupLbl').style.display  = 'none';
  document.getElementById('crpRowProfitItem').style.display   = 'none';
  document.getElementById('crpRowPajak').style.display        = 'none';
  document.getElementById('crpRowProfitSisa').style.display   = 'none';
  document.getElementById('crpProfitJurnalRow').style.display = 'none';
  document.getElementById('crpRowHasilAkhir').style.display   = 'none';
  document.getElementById('crpRowTotalProfit').style.display  = 'none';
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

  const habis = isHabiskanBahanMode();

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
  showCraftToast('⚒️ ' + t('craft_success_toast', {qty: jumlah, name: craftTarget.name}));
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
// Pajak market (4% premium / 8% non-premium) + biaya pesanan jual 2,5%
// (cuma kalau jual pakai Pesanan Jual, kalau dijual langsung ke buy
// order yang ada gak kena biaya ini). Dua-duanya potong dari Harga Jual
// yang diinput user, real-time ngikutin checkbox Premium/Pesanan Jual.
function getSellFeeMultiplier() {
  const premium   = document.getElementById('craftPremium').checked;
  const pakaiOrder = document.getElementById('craftOrderCost').checked;
  const taxPct   = premium ? 0.04 : 0.08;
  const orderPct = pakaiOrder ? 0.025 : 0;
  return (1 - taxPct) * (1 - orderPct);
}

function renderCraftResultPanel() {
  if (craftModalLock === null) {
    // Belum pernah craft di sesi ini -> masih mode preview (cuma Modal).
    // updateCraftModal() itung ulang nilai modal skrg & manggil
    // updateCraftResultPreview() di baliknya — jadi titik panggil manapun
    // (toggle jurnal, ganti tier jurnal, dll) ikut konsisten munculin panel.
    updateCraftModal();
    return;
  }

  document.getElementById('craftResultPanel').style.display = '';
  // Keluar dari mode preview (lihat updateCraftResultPreview) — tampilkan
  // lagi grup Profit yang sempat disembunyikan sebelum craft pertama.
  document.getElementById('crpProfitGroupLbl').style.display = '';
  document.getElementById('crpRowProfitItem').style.display  = '';
  document.getElementById('crpRowPajak').style.display       = '';
  document.getElementById('crpRowProfitSisa').style.display  = '';
  document.getElementById('crpRowHasilAkhir').style.display  = '';
  document.getElementById('crpRowTotalProfit').style.display = '';
  const group = craftRecipes[craftActiveRecipe] || [];

  // --- MODAL ---
  document.getElementById('crpModalBahan').textContent = formatSilver(craftModalLock);

  const journalMaxFameProfit = getJournalMaxFame();
  const showJurnal = craftUseJournal && craftJournalTier && journalMaxFameProfit;
  document.getElementById('crpModalJurnalRow').style.display  = showJurnal ? '' : 'none';
  document.getElementById('crpProfitJurnalRow').style.display = showJurnal ? '' : 'none';

  let jurnalModalVal = 0, jurnalProfitVal = 0;
  if (showJurnal) {
    const req   = journalMaxFameProfit;
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
  const itemHasilGross = exOut ? exOut.qty * (exOut.harga || 0) : 0;
  const feeMultiplier  = getSellFeeMultiplier();
  const itemHasilVal   = itemHasilGross * feeMultiplier; // NETO, sudah dipotong pajak + biaya pesanan
  document.getElementById('crpProfitItem').textContent = formatSilver(itemHasilVal);
  document.getElementById('crpPajak').textContent = '-' + formatSilver(itemHasilGross - itemHasilVal);

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
// ADVANCE MODE
// ============================================================
let advMaterials = [];      // All materials for this station
let advInv = [];            // Inventory: [{item, qty, harga}]
let advSelTier = null;      // Selected tier filter
let advSelEnc = null;       // Selected enchantment filter
let advSearchQ = '';        // Search query
let advCraftTarget = null;  // Item yang mau di-craft
let advRecipes = {};        // Recipes cache

// Load materials from backend
function loadAdvMaterials() {
  showAdvEmpty(t('loading_items'));
  const params = new URLSearchParams();
  if (advSelTier) params.set('tier', advSelTier);
  if (advSelEnc !== null) params.set('enc', advSelEnc);
  
  fetch(`${CRAFT_API_BASE}/advance/materials?` + params.toString())
    .then(r => r.json())
    .then(items => {
      advMaterials = items;
      filterAdvMaterials();
    })
    .catch(() => showAdvEmpty(t('failed_load_items')));
}

// Filter & render materials
function filterAdvMaterials() {
  let filtered = advMaterials;
  if (advSearchQ) {
    const q = advSearchQ.toLowerCase();
    filtered = advMaterials.filter(i => i.name.toLowerCase().includes(q));
  }
  if (!filtered.length) { showAdvEmpty(t('no_items_found')); return; }
  renderAdvMaterials(filtered);
}

function showAdvEmpty(msg) {
  document.getElementById('advEmptyMsg').textContent = msg;
  document.getElementById('advEmptyMsg').style.display = '';
  document.getElementById('advItemTableWrap').style.display = 'none';
}

function renderAdvMaterials(items) {
  const grid = document.getElementById('advItemGrid');
  grid.innerHTML = '';
  document.getElementById('advItemTableWrap').style.display = '';
  document.getElementById('advEmptyMsg').style.display = 'none';
  
  items.forEach((item, idx) => {
    const row = document.createElement('div');
    row.className = 'item-row';
    row.innerHTML = `
      <div class="item-icon-wrap">
        ${item.img_url ? `<img class="item-icon" src="${item.img_url}" alt="${item.name}" loading="lazy" onerror="this.style.display='none'">` : `<div class="item-icon">?</div>`}
      </div>
      <div class="item-info">
        <span class="item-name">${item.name}</span>
      </div>`;
    row.addEventListener('click', () => openAdvAdd(idx, items));
    grid.appendChild(row);
  });
}

// Build tier dropdown for advance mode
function buildAdvTierDrop() {
  const col = document.getElementById('colAdvTier');
  if (!col) return;
  col.innerHTML = '';
  col.appendChild(makeItem(t('filter.all'), false, !advSelTier, () => {
    advSelTier = null; setFilterVal('lblAdvTier','valAdvTier',null); closeDrop(); loadAdvMaterials();
  }));
  TIERS.forEach(tier => col.appendChild(makeItem(TIER_LABEL[tier], false, advSelTier === tier, () => {
    advSelTier = tier; setFilterVal('lblAdvTier','valAdvTier',TIER_LABEL[tier]); closeDrop(); loadAdvMaterials();
  })));
}

// Build enchantment dropdown for advance mode
function buildAdvEncDrop() {
  const col = document.getElementById('colAdvEnc');
  if (!col) return;
  col.innerHTML = '';
  col.appendChild(makeItem(t('filter.all'), false, advSelEnc === null, () => {
    advSelEnc = null; setFilterVal('lblAdvEnc','valAdvEnc',null); closeDrop(); loadAdvMaterials();
  }));
  ENCS.forEach(e => col.appendChild(makeItem(t('filter.enchant_option', {n: e}), false, advSelEnc === e, () => {
    advSelEnc = e; setFilterVal('lblAdvEnc','valAdvEnc', t('filter.enchant_short', {n: e})); closeDrop(); loadAdvMaterials();
  })));
}

// Search materials
function onAdvSearch() {
  clearTimeout(window.advSearchTimer);
  window.advSearchTimer = setTimeout(() => {
    advSearchQ = document.getElementById('advSearchInput').value.trim();
    filterAdvMaterials();
  }, 400);
}

// Open add material popup
let advPendingItem = null;
let advPendingItems = null;
let advPendingIdx = null;

// Sync qty slider and input for add material popup (Advance Mode)
function syncAdvAddQty(src) {
  const slider = document.getElementById('advAddQtySlider');
  const input = document.getElementById('advAddQty');
  if (!slider || !input) return;
  const max = parseInt(slider.max) || 999;
  if (src === 's') {
    input.value = slider.value;
  } else {
    const val = Math.min(Math.max(parseInt(input.value) || 1, 1), 999999);
    if (val <= max) slider.value = val;
    input.value = val;
  }
}

function openAdvAdd(idx, items) {
  const item = items[idx];
  advPendingItem = item;
  advPendingItems = items;
  advPendingIdx = idx;
  
  // Populate popup
  document.getElementById('advAddIcon').src = item.img_url || '';
  document.getElementById('advAddName').textContent = item.name;
  document.getElementById('advAddDesc').textContent = `Tier ${item.tier}${item.enc > 0 ? ` .${item.enc}` : ''}`;
  document.getElementById('advAddHarga').value = '';
  document.getElementById('advAddQtySlider').value = '100';
  document.getElementById('advAddQty').value = '100';
  
  document.getElementById('advAddOverlay').classList.add('show');
}

function closeAdvAddOverlay() {
  document.getElementById('advAddOverlay').classList.remove('show');
  advPendingItem = null;
  advPendingIdx = null;
}

function closeAdvAddOnBg(e) {
  if (e.target === document.getElementById('advAddOverlay')) {
    closeAdvAddOverlay();
  }
}

function doAdvAddMaterial() {
  console.log('🔍 doAdvAddMaterial called');
  console.log('advPendingItem:', advPendingItem);
  
  if (!advPendingItem) {
    console.error('❌ No item selected');
    showCraftToast('❌ Error: No item selected');
    return;
  }
  
  const qtyInput = document.getElementById('advAddQty');
  const hargaInput = document.getElementById('advAddHarga');
  
  console.log('qty input:', qtyInput, qtyInput?.value);
  console.log('harga input:', hargaInput, hargaInput?.value);
  
  const qty = parseInt(qtyInput?.value) || 1;
  const harga = parseInt(hargaInput?.value) || 0;
  
  console.log('Parsed - qty:', qty, 'harga:', harga);
  
  if (qty < 1) {
    console.error('❌ Invalid quantity');
    showCraftToast('❌ ' + t('invalid_quantity'));
    return;
  }
  
  console.log('✅ Adding to inventory...');
  addAdvToInventory(advPendingItem, qty, harga);
  closeAdvAddOverlay();
  showCraftToast('✅ ' + advPendingItem.name + ' ditambahkan');
  console.log('✅ Done!');
}

// Add to inventory
function addAdvToInventory(item, qty, harga) {
  const existing = advInv.find(i => i.item.id === item.id);
  if (existing) {
    existing.qty += qty;
  } else {
    advInv.push({ item, qty, harga });
  }
  renderAdvInventory();
  saveAdvState();
  checkAdvCraftable();
}

// Render inventory
function renderAdvInventory() {
  const grid = document.getElementById('advInvGrid');
  const count2 = document.getElementById('advInvCount2');
  if (count2) count2.textContent = advInv.length;
  
  if (advInv.length === 0) {
    grid.innerHTML = '<div style="text-align:center;padding:20px;color:var(--text-dim);font-size:13px">' + t('inventory_empty') + '</div>';
    return;
  }
  
  grid.innerHTML = advInv.map((inv, idx) => `
    <div class="cinv-slot filled" onclick="editAdvInvItem(${idx})">
      <img src="${inv.item.img_url || ''}" alt="${inv.item.name}" onerror="this.style.opacity=0.3">
      <div class="cinv-qty">${inv.qty}</div>
    </div>
  `).join('');
}

// Check craftable items - Recipe matching algorithm
async function checkAdvCraftable() {
  if (advInv.length === 0) {
    document.getElementById('advCraftableSection').style.display = 'none';
    document.getElementById('advBottomBar').style.display = 'none';
    return;
  }
  
  // Prepare materials array for backend
  // Use base API ID only (enc is handled separately in recipes table)
  const materials = advInv.map(inv => ({
    api_id: inv.item.api_id,  // Base API ID only, enc is in recipes table
    qty: inv.qty
  }));
  
  try {
    const response = await fetch(`${CRAFT_API_BASE}/advance/check-craftable`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ materials })
    });
    
    const craftable = await response.json();
    
    if (craftable.length === 0) {
      document.getElementById('advCraftableSection').style.display = 'none';
      document.getElementById('advBottomBar').style.display = 'none';
      return;
    }
    
    renderAdvCraftableItems(craftable);
    document.getElementById('advCraftableSection').style.display = '';
  } catch (error) {
    console.error('Failed to check craftable items:', error);
    document.getElementById('advCraftableSection').style.display = 'none';
  }
}

// Render craftable items
function renderAdvCraftableItems(craftable) {
  const grid = document.getElementById('advCraftableGrid');
  
  grid.innerHTML = craftable.map(item => `
    <div class="craft-craftable-item" onclick="selectAdvCraftTarget(${item.id})">
      <img src="${item.img_url || ''}" alt="${item.name}" onerror="this.style.opacity=0.3">
      <div class="cc-name">${item.name}</div>
      <div class="cc-max">${t('max')}: ${item.maxQty}</div>
    </div>
  `).join('');
}

// Select craft target - load recipe and show craft popup
async function selectAdvCraftTarget(itemId) {
  try {
    showCraftToast('⏳ ' + t('loading_recipe'));
    
    const response = await fetch(`${CRAFT_API_BASE}/advance/recipe?item_id=${itemId}`);
    const data = await response.json();
    
    if (data.error) {
      showCraftToast('❌ ' + data.error);
      return;
    }
    
    advCraftTarget = data.item;
    advCraftMaterials = data.materials;
    advSilverCost = data.silver_cost || 0;
    
    // Open craft popup
    openAdvCraftPopup();
    
  } catch (error) {
    console.error('Failed to load recipe:', error);
    showCraftToast('❌ ' + t('failed_load_recipe'));
  }
}

// Open craft popup
function openAdvCraftPopup() {
  if (!advCraftTarget || !advCraftMaterials) return;
  
  // Populate popup header
  document.getElementById('advCraftIcon').src = advCraftTarget.img_url || '';
  document.getElementById('advCraftName').textContent = advCraftTarget.name;
  document.getElementById('advCraftDesc').textContent = `Tier ${advCraftTarget.tier}${advCraftTarget.enc > 0 ? ` .${advCraftTarget.enc}` : ''}`;
  
  // Build materials info (punya/butuh)
  let materialsHTML = '<div style="display:flex;flex-direction:column;gap:8px;">';
  advCraftMaterials.forEach(mat => {
    const invItem = advInv.find(i => i.item.api_id === mat.api_id);
    const have = invItem ? invItem.qty : 0;
    const need = mat.count;
    const enough = have >= need;
    materialsHTML += `
      <div style="display:flex;align-items:center;gap:8px;">
        <img src="${mat.img_url || ''}" alt="${mat.name}" style="width:32px;height:32px;" onerror="this.style.opacity=0.3">
        <div style="flex:1;font-family:'Crimson Text',serif;font-size:14px;color:var(--text-lt);">
          ${mat.name}
        </div>
        <div style="font-family:'Cinzel',serif;font-size:13px;">
          <span style="color:${enough ? 'var(--green)' : 'var(--red)'}">${have}</span>
          <span style="color:var(--text-dim)"> / </span>
          <span style="color:var(--text-lt)">${need}</span>
        </div>
      </div>`;
  });
  materialsHTML += '</div>';
  document.getElementById('advCraftMaterialsInfo').innerHTML = materialsHTML;
  
  // Calculate max craftable
  const maxQty = getAdvMaxCraftable();
  
  // Setup slider
  const slider = document.getElementById('advCraftSlider');
  const qtyInput = document.getElementById('advCraftQtyInput');
  slider.max = maxQty;
  slider.value = maxQty;
  qtyInput.max = maxQty;
  qtyInput.value = maxQty;
  
  // Reset return rate to default
  document.getElementById('advCraftReturnRate').value = 21.5;
  
  // Reset checkbox
  document.getElementById('advCraftHabisCheckbox').checked = false;
  document.getElementById('advCraftQtyField').style.opacity = '1';
  slider.disabled = false;
  qtyInput.disabled = false;
  
  // Show popup
  document.getElementById('advCraftPopupOverlay').classList.add('show');
}

function closeAdvCraftPopup() {
  document.getElementById('advCraftPopupOverlay').classList.remove('show');
}

function closeAdvCraftPopupOnBg(e) {
  if (e.target === document.getElementById('advCraftPopupOverlay')) {
    closeAdvCraftPopup();
  }
}

// Sync qty slider and input
function syncAdvCraftQty(src) {
  const slider = document.getElementById('advCraftSlider');
  const input = document.getElementById('advCraftQtyInput');
  const max = parseInt(slider.max) || 1;
  if (src === 's') {
    input.value = slider.value;
  } else {
    const val = Math.min(Math.max(parseInt(input.value) || 1, 1), max);
    slider.value = val;
    input.value = val;
  }
}

// Checkbox "Habis" change
function onAdvCraftHabisCheckboxChange() {
  const checkbox = document.getElementById('advCraftHabisCheckbox');
  const slider = document.getElementById('advCraftSlider');
  const qtyInput = document.getElementById('advCraftQtyInput');
  const qtyField = document.getElementById('advCraftQtyField');
  
  if (checkbox.checked) {
    // Disable manual input, will use simulation
    qtyField.style.opacity = '0.4';
    slider.disabled = true;
    qtyInput.disabled = true;
    
    // Calculate with simulation loop
    const simQty = simulateAdvCraftLoop();
    slider.value = simQty;
    qtyInput.value = simQty;
  } else {
    // Enable manual input
    qtyField.style.opacity = '1';
    slider.disabled = false;
    qtyInput.disabled = false;
    
    // Reset to max
    const maxQty = getAdvMaxCraftable();
    slider.value = maxQty;
    qtyInput.value = maxQty;
  }
}

// Simulate craft loop (like refine)
function simulateAdvCraftLoop() {
  if (!advCraftMaterials || advCraftMaterials.length === 0) return 0;
  
  const returnRate = parseFloat(document.getElementById('advCraftReturnRate').value) || 0;
  const ret = returnRate / 100;
  
  // Create stock array from inventory
  let stock = advCraftMaterials.map(mat => {
    const invItem = advInv.find(i => i.item.api_id === mat.api_id);
    return invItem ? invItem.qty : 0;
  });
  
  const req = advCraftMaterials.map(mat => mat.count);
  let total = 0;
  
  while (true) {
    // Find bottleneck (minimum craftable from all materials)
    let canCraft = Infinity;
    for (let i = 0; i < req.length; i++) {
      if (req[i] > 0) {
        canCraft = Math.min(canCraft, Math.floor(stock[i] / req[i]));
      }
    }
    
    if (canCraft === 0 || canCraft === Infinity) break;
    
    // Deduct materials
    for (let i = 0; i < req.length; i++) {
      stock[i] -= canCraft * req[i];
    }
    
    total += canCraft;
    
    // Add return materials
    for (let i = 0; i < req.length; i++) {
      stock[i] += Math.round(canCraft * req[i] * ret);
    }
  }
  
  return total;
}

// Recipe materials loaded from backend
let advCraftMaterials = [];
let advSilverCost = 0;

// Craft calculation functions
function getAdvSellFeeMultiplier() {
  let mult = 1;
  if (document.getElementById('advCraftPremium').checked) mult *= 0.97; // 3% tax
  if (document.getElementById('advCraftOrderCost').checked) mult *= 0.955; // 4.5% order fee
  return mult;
}

function getAdvReturnRate() {
  return parseFloat(document.getElementById('advCraftReturn').value) || 0;
}

function getAdvCraftQty() {
  const qty = parseInt(document.getElementById('advCraftQty').value) || 1;
  const maxQty = advCraftTarget ? Math.min(qty, getAdvMaxCraftable()) : qty;
  return Math.max(1, Math.min(qty, maxQty));
}

function getAdvMaxCraftable() {
  if (!advCraftTarget || advCraftMaterials.length === 0) return 0;
  
  let maxQty = Infinity;
  for (const mat of advCraftMaterials) {
    const invItem = advInv.find(i => i.item.api_id === mat.api_id);
    if (!invItem) return 0;
    maxQty = Math.min(maxQty, Math.floor(invItem.qty / mat.count));
  }
  return maxQty;
}

function onAdvCraftHabisChange() {
  const checkbox = document.getElementById('advCraftHabis');
  const qtyInput = document.getElementById('advCraftQty');
  if (checkbox.checked) {
    qtyInput.value = getAdvMaxCraftable();
    qtyInput.disabled = true;
  } else {
    qtyInput.disabled = false;
  }
  renderAdvCraftResultPanel();
}

// Render craft result panel (preview + final)
function renderAdvCraftResultPanel() {
  if (!advCraftTarget || advCraftMaterials.length === 0) {
    document.getElementById('advResultPanel').style.display = 'none';
    return;
  }
  
  const qty = getAdvCraftQty();
  const returnRate = getAdvReturnRate() / 100;
  const feeMult = getAdvSellFeeMultiplier();
  
  // Calculate material costs
  let totalMaterialCost = 0;
  let totalMaterialValue = 0;
  
  for (const mat of advCraftMaterials) {
    const invItem = advInv.find(i => i.item.api_id === mat.api_id);
    if (!invItem) continue;
    
    const needed = mat.count * qty;
    const unitPrice = invItem.harga || 0;
    totalMaterialCost += needed * unitPrice;
    
    // Value of remaining materials
    const remaining = invItem.qty - needed;
    if (remaining > 0) {
      totalMaterialValue += remaining * unitPrice;
    }
  }
  
  // Add silver cost
  totalMaterialCost += advSilverCost * qty;
  
  // Result item value
  const craftItem = advInv.find(i => i.item.id === advCraftTarget.id);
  const sellPrice = parseFloat(document.getElementById('advCraftSellPrice').value) || 0;
  const resultValue = sellPrice * qty * feeMult;
  
  // Calculate return value
  const returnValue = totalMaterialCost * returnRate;
  
  // Profit calculation
  const profit = resultValue + returnValue + totalMaterialValue - totalMaterialCost;
  
  // Update UI
  document.getElementById('advResultPanel').style.display = '';
  document.getElementById('advModalBahan').textContent = formatSilver(totalMaterialCost);
  document.getElementById('advTotalModal').textContent = formatSilver(totalMaterialCost);
  document.getElementById('advProfitItem').textContent = formatSilver(resultValue);
  document.getElementById('advPajak').textContent = formatSilver(sellPrice * qty * (1 - feeMult));
  document.getElementById('advProfitSisa').textContent = formatSilver(totalMaterialValue);
  document.getElementById('advHasilAkhir').textContent = formatSilver(resultValue + returnValue + totalMaterialValue);
  document.getElementById('advTotalProfit').textContent = formatSilver(profit);
  document.getElementById('advTotalProfit').className = 'crp-val ' + (profit >= 0 ? 'positive' : 'negative');
}

// Old function removed - using doAdvCraftExecute() from popup instead

// Old function removed - inventory always visible in Advance Mode

// Reset advance mode
function doAdvReset() {
  if (!confirm(t('confirm_reset'))) return;
  advInv = [];
  advCraftTarget = null;
  renderAdvInventory();
  checkAdvCraftable();
  saveAdvState();
}

// Save state to localStorage
function saveAdvState() {
  try {
    localStorage.setItem('ct_adv_inv_' + STATION, JSON.stringify({
      inv: advInv.map(i => ({ itemId: i.item.id, qty: i.qty, harga: i.harga }))
    }));
  } catch (e) {}
}

// Load state from localStorage
function loadAdvState() {
  try {
    const raw = localStorage.getItem('ct_adv_inv_' + STATION);
    if (!raw) return;
    const data = JSON.parse(raw);
    if (!data || !data.inv) return;
    
    // Restore inventory - need to fetch item details for each saved item
    const itemIds = data.inv.map(i => i.itemId).filter(Boolean);
    if (itemIds.length === 0) return;
    
    // Fetch all items in parallel
    Promise.all(itemIds.map(id => 
      fetch(`${CRAFT_API_BASE}/advance/item-detail?item_id=${id}`)
        .then(r => r.json())
        .catch(() => null)
    )).then(items => {
      items.forEach((item, idx) => {
        if (!item) return;
        const saved = data.inv[idx];
        advInv.push({
          item: item,
          qty: saved.qty,
          harga: saved.harga || 0
        });
      });
      
      renderAdvInventory();
      checkAdvCraftable();
    });
  } catch (e) {
    console.error('Failed to load advance state:', e);
  }
}

// Journal functions (skip for now)
function onAdvUseJournalToggle() {
  // Skip journal for now
}

// Edit inventory item
function editAdvInvItem(idx) {
  const inv = advInv[idx];
  if (!inv) return;
  
  advPendingItem = inv.item;
  advPendingIdx = idx;
  
  document.getElementById('caIcon').src = inv.item.img_url || '';
  document.getElementById('caName').textContent = inv.item.name;
  document.getElementById('caNeed').textContent = t('edit_price_or_delete');
  document.getElementById('caHarga').value = inv.harga || '';
  document.getElementById('caQty').value = inv.qty;
  document.getElementById('caQtyField').style.display = 'none';
  document.getElementById('caBtnRow').innerHTML = `
    <button class="wiz-btn-hitung" style="flex:1" onclick="doAdvEditHarga()">💾 ${t('save_price')}</button>
    <button class="reset-btn" onclick="doAdvDeleteResource()">🗑</button>`;
  document.getElementById('craftAddOverlay').classList.add('show');
}

function doAdvEditHarga() {
  if (advPendingIdx === null) return;
  advInv[advPendingIdx].harga = parseFloat(document.getElementById('caHarga').value) || 0;
  closeCraftAddOverlay();
  renderAdvInventory();
  saveAdvState();
  showCraftToast('💰 ' + t('price_updated'));
}

function doAdvDeleteResource() {
  if (advPendingIdx === null) return;
  const nama = advInv[advPendingIdx].item.name;
  advInv.splice(advPendingIdx, 1);
  closeCraftAddOverlay();
  renderAdvInventory();
  checkAdvCraftable();
  saveAdvState();
  showCraftToast('🗑 ' + t('item_deleted', {name: nama}));
}

// Initialize advance mode on toggle
function initAdvanceMode() {
  buildAdvTierDrop();
  buildAdvEncDrop();
  loadAdvMaterials();
  loadAdvState();
}

// ============================================================
// INIT
// ============================================================

// ============================================================
// INIT
// ============================================================
const STATION = '{{ $station ?? "mage-tower" }}';
const CRAFT_API_BASE = STATION === 'mage-tower' ? '/api/crafting' : `/api/crafting/${STATION}`;

function loadCategoriesAndInit(isRetry) {
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
      if (selEnc !== null)  setFilterVal('lblEnc', 'valEnc', t('filter.enchant_short', {n: selEnc}));
      if (searchQ)          document.getElementById('searchInput').value = searchQ;
      fetchItems(); // load semua item dari awal, gak perlu pilih kategori dulu
      loadCraftState(); // pulihkan target + inventory bahan dari sesi sebelumnya (kalau ada)
    })
    .catch(() => {
      // Sebelumnya gak ada .catch() di sini -> kalau fetch categories gagal/telat
      // (mis. server lagi hiccup), restoreFilterState() ikut gak pernah kepanggil,
      // dan dari sisi user kelihatan kayak "filter kategori/tier/enchant ke-reset".
      // Fix: auto-retry sekali (1.5 detik), baru kasih tau user kalau tetap gagal.
      if (!isRetry) { setTimeout(() => loadCategoriesAndInit(true), 1500); return; }
      showEmpty(t('failed_load_categories'));
      showCraftToast('❌ ' + t('failed_load_categories_toast'));
    });
}
loadCategoriesAndInit(false);
renderCraftInventory();
renderCraftSlots();
updateCraftQtyField();
updateCraftModal();
setMTMode(localStorage.getItem('mt_mode') || 'simple');
</script>
<x-comments page="mages-tower" />
@endsection