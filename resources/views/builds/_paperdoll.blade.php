{{--
    builds/_paperdoll.blade.php
    Grid equipment yang BISA DIKLIK — dipakai di profile/show.blade.php.
    Klik kotak slot -> popup search -> pilih item -> tersimpan langsung lewat AJAX
    (PATCH ke route('builds.slot.update')), tanpa reload halaman.

    Variabel wajib: $build (instance Build|null)
--}}
@php
    $equipSlots = ['Bag', 'Head', 'Cape', 'MainHand', 'Armor', 'OffHand', 'Potion', 'Shoes', 'Food', null, 'Mount', null];

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

    // 2H weapon: kalau OffHand kosong tapi MainHand item-nya 2 tangan (api_id ada '_2H_'),
    // pinjem icon MainHand buat OffHand dengan opacity redup
    function resolvePaperdollSlot($build, $slot) {
        if (!$slot || !$build) return ['item' => null, 'mirrored' => false];

        $item = $build->slot($slot);
        if ($item) return ['item' => $item, 'mirrored' => false];

        if ($slot === 'OffHand') {
            $mainHand = $build->slot('MainHand');
            if ($mainHand && str_contains($mainHand->api_id ?? '', '_2H_')) {
                return ['item' => $mainHand, 'mirrored' => true];
            }
        }

        return ['item' => null, 'mirrored' => false];
    }
@endphp

@if($build)
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;background:rgba(0,0,0,.25);padding:14px;border-radius:12px;">
        @foreach($equipSlots as $slot)
            @if(!$slot)
                <div></div>
                @continue
            @endif
            @php
                $field = $fieldMap[$slot];
                $resolved = resolvePaperdollSlot($build, $slot);
                $item = $resolved['item'];
                $mirrored = $resolved['mirrored'];
                $isOffHandLocked = $slot === 'OffHand' && $mirrored;
            @endphp
            <div class="paperdoll-slot-cell" data-slot="{{ $slot }}" data-field="{{ $field }}"
                 onclick="{{ $isOffHandLocked ? '' : "openProfileItemPicker('{$slot}', '{$field}')" }}"
                 title="{{ $isOffHandLocked ? 'Terpakai senjata 2 tangan' : $slotLabels[$slot] }}"
                 style="aspect-ratio:1;position:relative;{{ $isOffHandLocked ? 'cursor:default;' : 'cursor:pointer;' }}background:rgba(0,0,0,.4);border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;transition:border-color .15s;"
                 @if(!$isOffHandLocked) onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='var(--border)'" @endif>
                <img id="pd-icon-{{ $field }}" alt="{{ $item->name ?? '' }}"
                     src="{{ $item ? 'https://render.albiononline.com/v1/item/'.$item->api_id.'.png' : '' }}"
                     style="width:92%;height:92%;object-fit:contain;{{ $mirrored ? 'opacity:.45;' : '' }}{{ !$item ? 'display:none;' : '' }}">
                <span id="pd-placeholder-{{ $field }}" style="color:var(--text-muted);font-size:.58rem;text-align:center;padding:2px;{{ $item ? 'display:none;' : '' }}">
                    {{ $slotLabels[$slot] }}
                </span>
                @isset($item->enchantment_level)
                    @if($item->enchantment_level > 0)
                        <span style="position:absolute;bottom:3px;right:4px;background:rgba(0,0,0,.6);color:var(--gold);font-size:.6rem;font-weight:700;border-radius:4px;padding:1px 4px;">
                            .{{ $item->enchantment_level }}
                        </span>
                    @endif
                @endisset
            </div>
        @endforeach
    </div>

    {{-- MODAL: search & pilih item, hasil pilihan langsung tersimpan via AJAX --}}
    <div id="pd-picker-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;align-items:flex-end;justify-content:center;" onclick="if(event.target===this) closeProfileItemPicker()">
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px 16px 0 0;width:100%;max-width:480px;max-height:80vh;display:flex;flex-direction:column;padding:18px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <span id="pd-picker-title" style="color:var(--gold);font-weight:700;font-size:.95rem;">Pilih Item</span>
                <button type="button" onclick="closeProfileItemPicker()" style="background:none;border:none;color:var(--text-muted);font-size:1.3rem;cursor:pointer;line-height:1;">&times;</button>
            </div>

            <div style="display:flex;gap:8px;margin-bottom:10px;">
                <select id="pd-picker-tier" style="flex:1;background:var(--bg-panel);border:1px solid var(--border);border-radius:8px;color:var(--text);padding:9px 8px;font-size:.82rem;">
                    <option value="">Semua Tier</option>
                    @for($t = 4; $t <= 8; $t++)
                        <option value="{{ $t }}">Tier {{ $t }}</option>
                    @endfor
                </select>
                <select id="pd-picker-enchant" style="flex:1;background:var(--bg-panel);border:1px solid var(--border);border-radius:8px;color:var(--text);padding:9px 8px;font-size:.82rem;">
                    <option value="">Semua Enchant</option>
                    @for($e = 0; $e <= 4; $e++)
                        <option value="{{ $e }}">Enchant {{ $e }}</option>
                    @endfor
                </select>
            </div>

            <button type="button" onclick="clearProfileItemSlot()" style="text-align:left;color:var(--text-muted);font-size:.8rem;background:none;border:none;padding:6px 0;cursor:pointer;border-bottom:1px solid var(--border);margin-bottom:8px;">
                ✕ Kosongkan slot ini
            </button>

            <div id="pd-picker-results" style="overflow-y:auto;flex:1;display:grid;grid-template-columns:repeat(4,1fr);gap:8px;align-content:start;"></div>

            <div id="pd-picker-loading" style="display:none;text-align:center;color:var(--text-muted);font-size:.82rem;padding:16px 0;">Mencari...</div>
            <div id="pd-picker-empty" style="display:none;text-align:center;color:var(--text-muted);font-size:.82rem;padding:16px 0;">Nggak ada item. Coba ubah filter Tier / Enchant.</div>
            <div id="pd-picker-saving" style="display:none;text-align:center;color:var(--gold);font-size:.82rem;padding:16px 0;">Menyimpan...</div>
        </div>
    </div>

    <script>
    (function () {
        const SEARCH_URL = @json(route('builds.items.search'));
        const UPDATE_URL = @json(route('builds.slot.update'));
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;

        let currentSlot = null;
        let currentField = null;

        window.openProfileItemPicker = function (slot, field) {
            currentSlot = slot;
            currentField = field;
            const cell = document.querySelector(`.paperdoll-slot-cell[data-field="${field}"]`);
            document.getElementById('pd-picker-title').textContent = 'Pilih ' + (cell?.title || 'Item');
            document.getElementById('pd-picker-tier').value = '';
            document.getElementById('pd-picker-enchant').value = '';
            document.getElementById('pd-picker-results').innerHTML = '';
            document.getElementById('pd-picker-overlay').style.display = 'flex';
            fetchItems();
        };

        window.closeProfileItemPicker = function () {
            document.getElementById('pd-picker-overlay').style.display = 'none';
            currentSlot = null;
            currentField = null;
        };

        window.clearProfileItemSlot = function () {
            if (!currentField) return;
            saveSlot(currentField, null, null);
        };

        function updateCellUI(field, item, mirrored) {
            const icon = document.getElementById('pd-icon-' + field);
            const placeholder = document.getElementById('pd-placeholder-' + field);
            if (item) {
                icon.src = `https://render.albiononline.com/v1/item/${item.api_id}.png`;
                icon.style.display = 'block';
                icon.style.opacity = mirrored ? '.45' : '1';
                placeholder.style.display = 'none';
            } else {
                icon.removeAttribute('src');
                icon.style.display = 'none';
                placeholder.style.display = 'block';
            }
        }

        function setOffHandLock(locked, mirrorItem) {
            const offHandCell = document.querySelector('.paperdoll-slot-cell[data-field="off_hand_id"]');
            if (!offHandCell) return;
            if (locked) {
                offHandCell.setAttribute('onclick', '');
                offHandCell.style.cursor = 'default';
                offHandCell.title = 'Terpakai senjata 2 tangan';
                updateCellUI('off_hand_id', mirrorItem, true);
            } else {
                offHandCell.setAttribute('onclick', "openProfileItemPicker('OffHand', 'off_hand_id')");
                offHandCell.style.cursor = 'pointer';
                offHandCell.title = 'Senjata Kedua / Shield';
            }
        }

        function saveSlot(field, id, item) {
            document.getElementById('pd-picker-saving').style.display = 'block';
            document.getElementById('pd-picker-results').innerHTML = '';
            document.getElementById('pd-picker-loading').style.display = 'none';
            document.getElementById('pd-picker-empty').style.display = 'none';

            fetch(UPDATE_URL, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ field: field, item_id: id }),
            })
                .then(async r => {
                    if (!r.ok) {
                        const bodyText = await r.text();
                        alert(`DEBUG — HTTP ${r.status}: ${bodyText}`);
                        throw new Error('save failed');
                    }
                    return r.json();
                })
                .then(() => {
                    updateCellUI(field, item, false);
            
                    if (field === 'main_hand_id') {
                        const is2H = item && item.api_id && item.api_id.includes('_2H_');
                        if (is2H) {
                            setOffHandLock(true, item);
                        } else {
                            setOffHandLock(false, null);
                        }
                    }
            
                    closeProfileItemPicker();
                })
                .catch(() => {
                    document.getElementById('pd-picker-saving').style.display = 'none';
                    alert('Gagal menyimpan, coba lagi.');
                });
        }

        window.selectItem = function (id, name, apiId) {
            if (!currentField) return;
            saveSlot(currentField, id, { name: name, api_id: apiId });
        };

        function fetchItems() {
            if (!currentSlot) return;
            document.getElementById('pd-picker-loading').style.display = 'block';
            document.getElementById('pd-picker-empty').style.display = 'none';
            document.getElementById('pd-picker-results').innerHTML = '';

            const tier = document.getElementById('pd-picker-tier').value;
            const enchant = document.getElementById('pd-picker-enchant').value;
            const params = new URLSearchParams({ slot: currentSlot });
            if (tier !== '') params.set('tier', tier);
            if (enchant !== '') params.set('enchant', enchant);

            fetch(`${SEARCH_URL}?${params.toString()}`)
                .then(r => r.json())
                .then(items => {
                    document.getElementById('pd-picker-loading').style.display = 'none';
                    if (!items.length) {
                        document.getElementById('pd-picker-empty').style.display = 'block';
                        return;
                    }
                    const html = items.map(item => `
                        <div onclick="selectItem(${item.id}, ${JSON.stringify(item.name)}, ${JSON.stringify(item.api_id)})"
                             title="${item.name}"
                             style="aspect-ratio:1;background:rgba(0,0,0,.4);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;cursor:pointer;transition:border-color .15s;"
                             onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='var(--border)'">
                            <img src="https://render.albiononline.com/v1/item/${item.api_id}.png" alt="${item.name}" loading="lazy"
                                 style="width:88%;height:88%;object-fit:contain;">
                        </div>
                    `).join('');
                    document.getElementById('pd-picker-results').innerHTML = html;
                })
                .catch(() => {
                    document.getElementById('pd-picker-loading').style.display = 'none';
                    document.getElementById('pd-picker-empty').style.display = 'block';
                });
        }

        document.getElementById('pd-picker-tier').addEventListener('change', fetchItems);
        document.getElementById('pd-picker-enchant').addEventListener('change', fetchItems);
    })();
    </script>
@else
    <div style="text-align:center;color:var(--text-muted);font-size:.85rem;padding:24px 0;">
        Belum punya build.
        <a href="{{ route('builds.create') }}" style="color:var(--gold);">Bikin sekarang →</a>
    </div>
@endif