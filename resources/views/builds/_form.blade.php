{{--
    builds/_form.blade.php
    Partial form build — dipakai di builds/create.blade.php dan builds/edit.blade.php.
    Variabel yang harus dikirim dari view pemanggil:
    - $build : instance Build kalau mode edit, biarin gak dikirim / null kalau mode create

    CATATAN (diperbaiki): sebelumnya popup ini pakai search box teks (?q=...), tapi
    BuildController@searchItems cuma pernah dukung filter Tier+Enchant — jadi kotak
    pencarian gak pernah ngefek. Sekarang diseragamkan pakai dropdown Tier+Enchant,
    sama persis kayak builds/_paperdoll.blade.php yang sudah terbukti jalan.
--}}

@php
    $build = $build ?? null;

    // urutan grid sama kayak builds/_paperdoll.blade.php, biar visualnya konsisten
    $gridSlots = ['Bag', 'Head', 'Cape', 'MainHand', 'Armor', 'OffHand', 'Potion', 'Shoes', 'Food', null, 'Mount', null];

    $fieldMap = [
        'MainHand' => 'main_hand_id', 'OffHand' => 'off_hand_id', 'Head' => 'head_id',
        'Armor' => 'armor_id', 'Shoes' => 'shoes_id', 'Cape' => 'cape_id',
        'Bag' => 'bag_id', 'Mount' => 'mount_id', 'Potion' => 'potion_id', 'Food' => 'food_id',
    ];

    $slotLabels = [
        'Bag' => 'Tas', 'Head' => 'Kepala', 'Cape' => 'Cape',
        'MainHand' => 'Senjata Utama', 'Armor' => 'Armor', 'OffHand' => 'Senjata Kedua / Shield',
        'Potion' => 'Potion', 'Shoes' => 'Sepatu', 'Food' => 'Makanan', 'Mount' => 'Mount',
    ];

    // gambar placeholder buat slot kosong, sama kayak builds/show.blade.php (death-recap)
    // & builds/_paperdoll.blade.php: asset('images/equipment/{slot}.png'), nama file huruf kecil
    function formPlaceholderUrl($slot) {
        if (!$slot) return null;
        return asset('images/equipment/' . strtolower($slot) . '.png');
    }
@endphp

{{-- Nama & catatan build --}}
<div style="margin-bottom:20px;">
    <label style="display:block;color:var(--text-muted);font-size:.78rem;margin-bottom:6px;">Nama Build</label>
    <input type="text" name="name" maxlength="100"
           value="{{ old('name', $build->name ?? '') }}"
           placeholder="Misal: PvP Bow Ganker"
           style="width:100%;background:var(--bg-panel);border:1px solid var(--border);border-radius:8px;color:var(--text);padding:10px 12px;font-size:.9rem;">
    @error('name')
        <div style="color:#e5484d;font-size:.75rem;margin-top:4px;">{{ $message }}</div>
    @enderror
</div>

<div style="margin-bottom:24px;">
    <label style="display:block;color:var(--text-muted);font-size:.78rem;margin-bottom:6px;">Catatan (opsional)</label>
    <textarea name="notes" maxlength="1000" rows="3"
              placeholder="Tips pemakaian, kapan dipakai, dll."
              style="width:100%;background:var(--bg-panel);border:1px solid var(--border);border-radius:8px;color:var(--text);padding:10px 12px;font-size:.85rem;resize:vertical;">{{ old('notes', $build->notes ?? '') }}</textarea>
    @error('notes')
        <div style="color:#e5484d;font-size:.75rem;margin-top:4px;">{{ $message }}</div>
    @enderror
</div>

{{-- GRID EQUIPMENT — klik kotak buat buka popup Tier+Enchant --}}
<div style="color:var(--text-muted);font-size:.78rem;margin-bottom:10px;">Equipment (klik buat pilih item)</div>

<div id="build-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;background:rgba(0,0,0,.25);padding:14px;border-radius:12px;margin-bottom:8px;">
    @foreach($gridSlots as $slot)
        @if(!$slot)
            <div></div>
            @continue
        @endif
        @php
            $field = $fieldMap[$slot];
            $current = $build?->slot($slot);
            $selectedId = old($field, $current?->id);
            $selectedApiId = $current?->api_id;
            $selectedEnc = (int) ($current?->enc ?? 0);
            $selectedQuality = (int) old("quality.$field", $current ? $build?->qualityFor($field) : 1) ?: 1;
            // Sama kayak MarketController@items: enc > 0 wajib nambah suffix @{enc},
            // kalau nggak icon yang dirender selalu versi enchant 0 (T8.0) walaupun
            // item yang tersimpan di build itu enchant 4. Quality juga sama pola-nya:
            // item di DB selalu Normal, jadi suffix ?quality=N dari kolom qualities.
            $selectedImgUrl = $selectedApiId
                ? 'https://render.albiononline.com/v1/item/' . ($selectedEnc > 0 ? "{$selectedApiId}@{$selectedEnc}" : $selectedApiId) . '.png' . ($selectedQuality > 1 ? "?quality={$selectedQuality}" : '')
                : '';
        @endphp
        <div class="build-slot-cell" data-slot="{{ $slot }}" data-field="{{ $field }}"
             onclick="openItemPicker('{{ $slot }}', '{{ $field }}')"
             title="{{ $slotLabels[$slot] }}"
             style="aspect-ratio:1;position:relative;cursor:pointer;background:rgba(0,0,0,.4);border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;transition:border-color .15s;"
             onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='var(--border)'">
            <img id="icon-{{ $field }}" alt="" style="width:92%;height:92%;object-fit:contain;{{ !$selectedApiId ? 'display:none;' : '' }}"
                 src="{{ $selectedImgUrl }}">
            <img id="placeholder-{{ $field }}" src="{{ formPlaceholderUrl($slot) }}" alt=""
                 style="width:60%;height:60%;object-fit:contain;opacity:.3;{{ $selectedApiId ? 'display:none;' : '' }}">
        </div>
        <input type="hidden" name="quality[{{ $field }}]" id="quality-{{ $field }}" value="{{ $selectedQuality }}">

        <input type="hidden" name="{{ $field }}" id="input-{{ $field }}" value="{{ $selectedId }}">
    @endforeach
</div>

@php $anyFieldError = collect($fieldMap)->first(fn ($field) => $errors->has($field)); @endphp
@if($anyFieldError)
    <div style="color:#e5484d;font-size:.75rem;margin-bottom:16px;">
        @foreach($fieldMap as $slot => $field)
            @error($field)
                <div>{{ $slotLabels[$slot] }}: {{ $message }}</div>
            @enderror
        @endforeach
    </div>
@endif

{{-- MODAL: filter Tier + Enchant & pilih item --}}
<div id="item-picker-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;align-items:flex-end;justify-content:center;" onclick="if(event.target===this) closeItemPicker()">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px 16px 0 0;width:100%;max-width:480px;max-height:80vh;display:flex;flex-direction:column;padding:18px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span id="picker-title" style="color:var(--gold);font-weight:700;font-size:.95rem;">Pilih Item</span>
            <button type="button" onclick="closeItemPicker()" style="background:none;border:none;color:var(--text-muted);font-size:1.3rem;cursor:pointer;line-height:1;">&times;</button>
        </div>

        <div style="display:flex;gap:8px;margin-bottom:10px;">
            <select id="picker-tier" style="flex:1;background:var(--bg-panel);border:1px solid var(--border);border-radius:8px;color:var(--text);padding:9px 8px;font-size:.82rem;">
                <option value="">Semua Tier</option>
                @for($t = 4; $t <= 8; $t++)
                    <option value="{{ $t }}">Tier {{ $t }}</option>
                @endfor
            </select>
            <select id="picker-enchant" style="flex:1;background:var(--bg-panel);border:1px solid var(--border);border-radius:8px;color:var(--text);padding:9px 8px;font-size:.82rem;">
                <option value="">Semua Enchant</option>
                @for($e = 0; $e <= 4; $e++)
                    <option value="{{ $e }}">Enchant {{ $e }}</option>
                @endfor
            </select>
        </div>

        {{-- Quality: value di DB kamu baru "Normal" (lihat catatan di BuildController@searchItems).
             Opsi lain disiapkan sekarang biar gak perlu ubah blade lagi begitu data quality
             lain (Good/Outstanding/dst) udah di-normalize & lengkap. --}}
        <div style="margin-bottom:10px;">
            <select id="picker-quality" style="width:100%;background:var(--bg-panel);border:1px solid var(--border);border-radius:8px;color:var(--text);padding:9px 8px;font-size:.82rem;">
                <option value="">Semua Quality</option>
                <option value="Normal">Normal</option>
                <option value="Good">Good</option>
                <option value="Outstanding">Outstanding</option>
                <option value="Excellent">Excellent</option>
                <option value="Masterpiece">Masterpiece</option>
            </select>
        </div>

        <button type="button" onclick="clearItemSlot()" style="text-align:left;color:var(--text-muted);font-size:.8rem;background:none;border:none;padding:6px 0;cursor:pointer;border-bottom:1px solid var(--border);margin-bottom:8px;">
            ✕ Kosongkan slot ini
        </button>

        <div id="picker-results" style="overflow-y:auto;flex:1;display:grid;grid-template-columns:repeat(4,1fr);gap:8px;align-content:start;"></div>

        <div id="picker-loading" style="display:none;text-align:center;color:var(--text-muted);font-size:.82rem;padding:16px 0;">Mencari...</div>
        <div id="picker-empty" style="display:none;text-align:center;color:var(--text-muted);font-size:.82rem;padding:16px 0;">Nggak ada item. Coba ubah filter Tier / Enchant.</div>
    </div>
</div>

<script>
(function () {
    const SEARCH_URL = @json(route('builds.items.search'));
    // samain sama Item::QUALITY_MAP di backend (string label -> integer 1-5)
    const QUALITY_MAP = { '': 1, 'Normal': 1, 'Good': 2, 'Outstanding': 3, 'Excellent': 4, 'Masterpiece': 5 };
    let currentSlot = null;
    let currentField = null;

    window.openItemPicker = function (slot, field) {
        currentSlot = slot;
        currentField = field;
        document.getElementById('picker-title').textContent = 'Pilih ' + (document.querySelector(`[data-field="${field}"]`)?.title || 'Item');
        document.getElementById('picker-tier').value = '';
        document.getElementById('picker-enchant').value = '';
        document.getElementById('picker-quality').value = '';
        document.getElementById('picker-results').innerHTML = '';
        document.getElementById('item-picker-overlay').style.display = 'flex';
        fetchItems();
    };

    window.closeItemPicker = function () {
        document.getElementById('item-picker-overlay').style.display = 'none';
        currentSlot = null;
        currentField = null;
    };

    window.clearItemSlot = function () {
        if (!currentField) return;
        setSlotValue(currentField, null, null);
        closeItemPicker();
    };

    function setSlotValue(field, id, item) {
        document.getElementById('input-' + field).value = id ?? '';
        const qualityInput = document.getElementById('quality-' + field);
        if (qualityInput) qualityInput.value = item ? (item.quality ?? 1) : 1;

        const icon = document.getElementById('icon-' + field);
        const placeholder = document.getElementById('placeholder-' + field);

        if (item) {
            icon.src = item.img_url;
            icon.style.display = 'block';
            placeholder.style.display = 'none';
        } else {
            icon.removeAttribute('src');
            icon.style.display = 'none';
            placeholder.style.display = 'block';
        }

        // logic 2H weapon: kalau MainHand yang dipilih itu 2 tangan, OffHand otomatis dikosongin & di-lock
        if (field === 'main_hand_id') {
            const is2H = item && item.api_id && item.api_id.includes('_2H_');
            const offHandCell = document.querySelector('[data-field="off_hand_id"]');
            if (is2H) {
                setSlotValue('off_hand_id', null, null);
                offHandCell.style.opacity = '.4';
                offHandCell.style.pointerEvents = 'none';
                offHandCell.title = 'Terpakai senjata 2 tangan';
            } else if (offHandCell) {
                offHandCell.style.opacity = '1';
                offHandCell.style.pointerEvents = 'auto';
            }
        }
    }

    window.selectItem = function (id, name, apiId, imgUrl) {
        if (!currentField) return;
        // quality diambil dari dropdown yang lagi aktif pas item ini di-klik, karena
        // hasil grid (imgUrl) memang di-render server pakai quality dropdown tsb
        const quality = QUALITY_MAP[document.getElementById('picker-quality').value] || 1;
        setSlotValue(currentField, id, { name, api_id: apiId, img_url: imgUrl, quality });
        closeItemPicker();
    };

    function fetchItems() {
        if (!currentSlot) return;
        document.getElementById('picker-loading').style.display = 'block';
        document.getElementById('picker-empty').style.display = 'none';
        document.getElementById('picker-results').innerHTML = '';

        const tier = document.getElementById('picker-tier').value;
        const enchant = document.getElementById('picker-enchant').value;
        const quality = document.getElementById('picker-quality').value;
        const params = new URLSearchParams({ slot: currentSlot });
        if (tier !== '') params.set('tier', tier);
        if (enchant !== '') params.set('enchant', enchant);
        if (quality !== '') params.set('quality', quality);

        fetch(`${SEARCH_URL}?${params.toString()}`)
            .then(r => r.json())
            .then(items => {
                document.getElementById('picker-loading').style.display = 'none';
                if (!items.length) {
                    document.getElementById('picker-empty').style.display = 'block';
                    return;
                }
                const resultsEl = document.getElementById('picker-results');
                resultsEl.innerHTML = '';
                items.forEach(item => {
                    const cell = document.createElement('div');
                    cell.title = item.name;
                    cell.style.cssText = 'aspect-ratio:1;background:rgba(0,0,0,.4);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;cursor:pointer;transition:border-color .15s;';
                    cell.addEventListener('mouseover', () => cell.style.borderColor = 'var(--gold)');
                    cell.addEventListener('mouseout', () => cell.style.borderColor = 'var(--border)');
                    cell.addEventListener('click', () => selectItem(item.id, item.name, item.api_id, item.img_url));

                    const img = document.createElement('img');
                    img.src = item.img_url;
                    img.alt = item.name;
                    img.loading = 'lazy';
                    img.style.cssText = 'width:88%;height:88%;object-fit:contain;';

                    cell.appendChild(img);
                    resultsEl.appendChild(cell);
                });
            })
            .catch(() => {
                document.getElementById('picker-loading').style.display = 'none';
                document.getElementById('picker-empty').style.display = 'block';
            });
    }

    document.getElementById('picker-tier').addEventListener('change', fetchItems);
    document.getElementById('picker-enchant').addEventListener('change', fetchItems);
    document.getElementById('picker-quality').addEventListener('change', fetchItems);

    // jalanin sekali pas load, buat kasus edit yang udah ada 2H weapon dari awal
    const mainHandField = document.getElementById('input-main_hand_id');
    if (mainHandField && mainHandField.value) {
        const icon = document.getElementById('icon-main_hand_id');
        if (icon && icon.src && icon.src.includes('_2H_')) {
            const offHandCell = document.querySelector('[data-field="off_hand_id"]');
            if (offHandCell) {
                offHandCell.style.opacity = '.4';
                offHandCell.style.pointerEvents = 'none';
                offHandCell.title = 'Terpakai senjata 2 tangan';
            }
        }
    }
})();
</script>
