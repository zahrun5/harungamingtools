@extends('layouts.app')

@section('content')
@php
    $killer = $event['Killer'];
    $victim = $event['Victim'];
    $participants = $event['Participants'] ?? [];

    // urutan grid 3 kolom, null = sel kosong
    $equipSlots = ['Bag', 'Head', 'Cape', 'MainHand', 'Armor', 'OffHand', 'Potion', 'Shoes', 'Food', null, 'Mount', null];

    function iconUrl($item) {
        if (!$item || !isset($item['Type'])) return null;
        return "https://render.albiononline.com/v1/item/{$item['Type']}.png?size=64&quality=" . ($item['Quality'] ?? 1);
    }

    // weapon 2-tangan: kalau OffHand kosong tapi MainHand ada, mirror MainHand ke OffHand
    function resolveSlot($equipment, $slot) {
        if (!$slot) return null;

        if (isset($equipment[$slot])) return $equipment[$slot];

        if ($slot === 'OffHand') {
            $mainHand = $equipment['MainHand'] ?? null;
            if ($mainHand && str_contains($mainHand['Type'] ?? '', '2H_')) {
                return $mainHand; // pinjem icon MainHand buat placeholder
            }
        }

        return null;
    }
@endphp

<div style="max-width:900px;margin:0 auto;padding:20px;">

    <a href="javascript:history.back()" style="color:#4f7cff;font-size:14px;text-decoration:none;">&larr; Kembali</a>

    <div style="text-align:center;margin:20px 0;">
        <div style="color:#fff;font-size:22px;">
            <span style="font-weight:700;">{{ $killer['Name'] }}</span>
            <span style="color:#e74c3c;font-style:italic;"> killed </span>
            <span style="font-weight:700;">{{ $victim['Name'] }}</span>
        </div>
        <div style="color:#888;font-size:13px;margin-top:4px;">
            {{ \Carbon\Carbon::parse($event['TimeStamp'])->format('H:i, d M Y') }}
        </div>
    </div>

    <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:24px;">
        <img src="/images/icons/fame-icon.png" width="28" height="28">
        <span style="color:#fff;font-size:20px;font-weight:700;">{{ number_format($event['TotalVictimKillFame'] ?? 0) }}</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        {{-- KILLER --}}
        <div>
            <div style="text-align:center;margin-bottom:10px;">
                <div style="color:#fff;font-weight:700;">{{ $killer['Name'] }} <span style="color:#888;font-weight:400;font-size:12px;">{{ round($killer['AverageItemPower'] ?? 0) }} IP</span></div>
                <div style="color:#888;font-size:12px;">{{ $killer['GuildName'] ?? '-' }}</div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;background:#1a1d24;padding:12px;border-radius:8px;">
                @foreach ($equipSlots as $slot)
                    @php $item = resolveSlot($killer['Equipment'], $slot); @endphp
                    <div style="aspect-ratio:1;background:#000;border-radius:6px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                        @if ($item)
                            <img src="{{ iconUrl($item) }}" style="width:100%;height:100%;object-fit:contain;" title="{{ $item['Type'] }}">
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- VICTIM --}}
        <div>
            <div style="text-align:center;margin-bottom:10px;">
                <div style="color:#fff;font-weight:700;">{{ $victim['Name'] }} <span style="color:#888;font-weight:400;font-size:12px;">{{ round($victim['AverageItemPower'] ?? 0) }} IP</span></div>
                <div style="color:#888;font-size:12px;">{{ $victim['GuildName'] ?? '-' }}</div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;background:#1a1d24;padding:12px;border-radius:8px;">
                @foreach ($equipSlots as $slot)
                    @php $item = resolveSlot($victim['Equipment'], $slot); @endphp
                    <div style="aspect-ratio:1;background:#000;border-radius:6px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                        @if ($item)
                            <img src="{{ iconUrl($item) }}" style="width:100%;height:100%;object-fit:contain;" title="{{ $item['Type'] }}">
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- INVENTORY VICTIM --}}
    @if (!empty($victim['Inventory']) && count(array_filter($victim['Inventory'])) > 0)
        <div style="margin-top:24px;">
            <div style="color:#fff;font-weight:700;margin-bottom:10px;">Inventory {{ $victim['Name'] }}</div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                @foreach ($victim['Inventory'] as $item)
                    @if ($item)
                        <div style="width:56px;height:56px;background:#000;border-radius:6px;display:flex;align-items:center;justify-content:center;position:relative;">
                            <img src="{{ iconUrl($item) }}" style="width:100%;height:100%;object-fit:contain;" title="{{ $item['Type'] }}">
                            @if (($item['Count'] ?? 1) > 1)
                                <span style="position:absolute;bottom:2px;right:4px;color:#fff;font-size:11px;background:rgba(0,0,0,.7);padding:0 3px;border-radius:3px;">{{ $item['Count'] }}</span>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- PARTICIPANTS (kill grup) --}}
    @if (count($participants) > 0)
        <div style="margin-top:24px;">
            <div style="color:#fff;font-weight:700;margin-bottom:10px;">Participants</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach ($participants as $p)
                    <div style="background:#1a1d24;padding:10px 14px;border-radius:8px;">
                        <div style="color:#fff;font-size:14px;">{{ $p['Name'] }} <span style="color:#888;font-size:12px;">{{ round($p['AverageItemPower'] ?? 0) }} IP</span></div>
                        <div style="color:#888;font-size:12px;">{{ $p['GuildName'] ?? '-' }} &middot; Damage: {{ number_format($p['DamageDone'] ?? 0) }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection