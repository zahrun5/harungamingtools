{{--
    builds/_form.blade.php
    Partial form build — dipakai di builds/create.blade.php dan builds/edit.blade.php.
    Variabel yang harus dikirim dari view pemanggil:
    - $build : instance Build kalau mode edit, biarin gak dikirim / null kalau mode create

    CATATAN: $itemsBySlot udah GAK DIPAKAI LAGI. Item sekarang dicari on-demand
    lewat endpoint AJAX route('builds.items.search'), bukan di-load semua di awal.
    Ini yang bikin form create/edit jauh lebih ringan dibanding versi select lama.
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

{{-- GRID EQUIPMENT — klik kotak buat buka popup search --}}
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
            $selectedName = $current?->name;
        @endphp
        <div class="build-slot-cell" data-slot="{{ $slot }}" data-field="{{ $field }}"
             onclick="openItemPicker('{{ $slot }}', '{{ $field }}')"
             title="{{ $slotLabels[$slot] }}"
             style="aspect-ratio:1;position:relative;cursor:pointer;background:rgba(0,0,0,.4);border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;transition:border-color .15s;"
             onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='var(--border)'">
            <img id="icon-{{ $field }}" alt="" style="width:92%;height:92%;object-fit:contain;{{ !$selectedApiId ? 'display:none;' : '' }}"
                 src="{{ $selectedApiId ? 'https://render.albiononline.com/v1/item/'.$selectedApiId.'.png' : '' }}">
            <span id="placeholder-{{ $field }}" style="color:var(--text-muted);font-size:.58rem;text-align:center;padding:2px;{{ $selectedApiId ? 'display:none;' : '' }}">
                {{ $slotLabels[$slot] }}
            </span>
        </div>
        <input type="hidden" name="{{ $field }}" id="input-{{ $field }}" value="{{ $selectedId }}">
        @error($field)
            {{-- errornya ditampilin di bawah grid biar gak ganggu layout kotak --}}
        @enderror
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

{{-- MODAL: search & pilih item --}}
<div id="item-picker-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;align-items:flex-end;justify-content:center;" onclick="if(event.target===this) closeItemPicker()">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px 16px 0 0;width:100%;max-width:480px;max-height:80vh;display:flex;flex-direction:column;padding:18px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span id="picker-title" style="color:var(--gold);font-weight:700;font-size:.95rem;">Pilih Item</span>
            <button type="button" onclick="closeItemPicker()" style="background:none;border:none;color:var(--text-muted);font-size:1.3rem;cursor:pointer;line-height:1;">&times;</button>
        </div>

        <input type="text" id="picker-search" placeholder="Ketik nama item..." autocomplete="off"
               style="width:100%;background:var(--bg-panel);border:1px solid var(--border);border-radius:8px;color:var(--text);padding:10px 12px;font-size:.88rem;margin-bottom:12px;">

        <button type="button" onclick="clearItemSlot()" style="text-align:left;color:var(--text-muted);font-size:.8rem;background:none;border:none;padding:6px 0;cursor:pointer;border-bottom:1px solid var(--border);margin-bottom:8px;">
            ✕ Kosongkan slot ini
        </button>

        <div id="picker-results" style="overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:6px;">
            {{-- hasil search di-inject via JS --}}
        </div>

        <div id="picker-loading" style="display:none;text-align:center;color:var(--text-muted);font-size:.82rem;padding:16px 0;">
            Mencari...
        </div>
        <div id="picker-empty" style="display:none;text-align:center;color:var(--text-muted);font-size:.82rem;padding:16px 0;">
            Nggak ketemu. Coba kata kunci lain.
        </div>
    </div>
</div>

<script>
(function () {
    const SEARCH_URL = @json(route('builds.items.search'));
    let currentSlot = null;
    let currentField = null;
    let debounceTimer = null;

    window.openItemPicker = function (slot, field) {
        currentSlot = slot;
        currentField = field;
        document.getElementById('picker-title').textContent = 'Pilih ' + (document.querySelector(`[data-field="${field}"]`)?.title || 'Item');
        document.getElementById('picker-search').value = '';
        document.getElementById('picker-results').innerHTML = '';
        document.getElementById('item-picker-overlay').style.display = 'flex';
        document.getElementById('picker-search').focus();
        fetchItems('');
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
        const icon = document.getElementById('icon-' + field);
        const placeholder = document.getElementById('placeholder-' + field);

        if (item) {
            icon.src = `https://render.albiononline.com/v1/item/${item.api_id}.png`;
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

    window.selectItem = function (id, name, apiId) {
        if (!currentField) return;
        setSlotValue(currentField, id, { name, api_id: apiId });
        closeItemPicker();
    };

    function fetchItems(query) {
        if (!currentSlot) return;
        document.getElementById('picker-loading').style.display = 'block';
        document.getElementById('picker-empty').style.display = 'none';
        document.getElementById('picker-results').innerHTML = '';

        fetch(`${SEARCH_URL}?slot=${encodeURIComponent(currentSlot)}&q=${encodeURIComponent(query)}`)
            .then(r => r.json())
            .then(items => {
                document.getElementById('picker-loading').style.display = 'none';
                if (!items.length) {
                    document.getElementById('picker-empty').style.display = 'block';
                    return;
                }
                const html = items.map(item => `
                    <div onclick="selectItem(${item.id}, ${JSON.stringify(item.name)}, ${JSON.stringify(item.api_id)})"
                         style="display:flex;align-items:center;gap:10px;background:var(--bg-panel);border:1px solid var(--border);border-radius:8px;padding:8px 10px;cursor:pointer;">
                        <img src="https://render.albiononline.com/v1/item/${item.api_id}.png" alt=""
                             style="width:36px;height:36px;object-fit:contain;background:rgba(0,0,0,.3);border-radius:6px;flex-shrink:0;">
                        <div style="min-width:0;flex:1;">
                            <div style="color:var(--text);font-size:.85rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.name}</div>
                            <div style="color:var(--text-muted);font-size:.72rem;">T${item.tier}${item.quality && item.quality !== 'Normal' ? ', ' + item.quality : ''}</div>
                        </div>
                    </div>
                `).join('');
                document.getElementById('picker-results').innerHTML = html;
            })
            .catch(() => {
                document.getElementById('picker-loading').style.display = 'none';
                document.getElementById('picker-empty').style.display = 'block';
            });
    }

    document.getElementById('picker-search').addEventListener('input', function (e) {
        clearTimeout(debounceTimer);
        const query = e.target.value;
        debounceTimer = setTimeout(() => fetchItems(query), 300);
    });

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