{{--
    builds/_paperdoll.blade.php
    Grid equipment READ-ONLY — dipakai di profile/show.blade.php buat nampilin
    BUILD AKTIF milik user (baik profil sendiri maupun profil publik user lain).

    Semua proses edit (ganti item, atur quality/tier/enchant, dst) sekarang
    HANYA lewat halaman "Edit build" (route builds.edit) — partial ini murni
    display, gak ada popup/AJAX apapun.

    Variabel wajib: $build (instance Build|null — build yang is_active=true milik user)
    Variabel opsional: $editable (bool, default false) — kalau true, nampilin
    tombol "Edit build" di atas grid buat pemilik build.
--}}
@php
    $editable = $editable ?? false;

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

    // gambar placeholder buat slot kosong, sama kayak builds/show.blade.php (death-recap):
    // asset('images/equipment/{slot}.png'), nama file huruf kecil
    function paperdollPlaceholderUrl($slot) {
        if (!$slot) return null;
        return asset('images/equipment/' . strtolower($slot) . '.png');
    }
@endphp

@if($build)
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
        @if($build->name)
            <div style="color:var(--text-muted);font-size:.75rem;">
                Build aktif: <span style="color:var(--gold);font-weight:600;">{{ $build->name }}</span>
            </div>
        @else
            <div></div>
        @endif

        @if($editable)
            <a href="{{ route('builds.edit', $build) }}" style="color:var(--gold);font-size:.75rem;text-decoration:underline;">
                Edit build →
            </a>
        @endif
    </div>

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
                $itemEnc = (int) ($item->enc ?? 0);
                $itemQuality = $item ? $build->qualityFor($field) : 1;
                $itemImgUrl = $item
                    ? 'https://render.albiononline.com/v1/item/' . ($itemEnc > 0 ? "{$item->api_id}@{$itemEnc}" : $item->api_id) . '.png' . ($itemQuality > 1 ? "?quality={$itemQuality}" : '')
                    : '';
                $cellTitle = $isOffHandLocked
                    ? 'Terpakai senjata 2 tangan'
                    : ($item->name ?? $slotLabels[$slot]);
            @endphp
            {{-- Read-only: gak ada onclick/popup sama sekali. Info item cukup lewat title (hover tooltip). --}}
            <div class="paperdoll-slot-cell" data-slot="{{ $slot }}" data-field="{{ $field }}"
                 title="{{ $cellTitle }}"
                 style="aspect-ratio:1;position:relative;cursor:default;background:rgba(0,0,0,.4);border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;">
                <img alt="{{ $item->name ?? '' }}"
                     src="{{ $itemImgUrl }}"
                     style="width:92%;height:92%;object-fit:contain;{{ $mirrored ? 'opacity:.45;' : '' }}{{ !$item ? 'display:none;' : '' }}">
                <img src="{{ paperdollPlaceholderUrl($slot) }}" alt=""
                     style="width:60%;height:60%;object-fit:contain;opacity:.3;{{ $item ? 'display:none;' : '' }}">
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
@else
    <div style="text-align:center;color:var(--text-muted);font-size:.85rem;padding:24px 0;">
        @if($editable)
            Belum punya build.
            <a href="{{ route('builds.create') }}" style="color:var(--gold);">Bikin sekarang →</a>
        @else
            Belum ada build.
        @endif
    </div>
@endif
