@extends('layouts.app')

@section('title', 'Beranda - HarunGamingTools')

@section('content')
    <h1>Selamat datang di HarunGamingTools 👋</h1>
    <p>Tempat kumpulnya pemain Albion Online — kalkulator, leaderboard, dan komunitas.</p>

    {{-- ===== STASIUN REFINE ===== --}}
    <h2 class="section-title">⚒️ Stasiun Refine</h2>
    <p class="section-sub">Pilih stasiun sesuai jenis resource yang mau kamu olah.</p>

    <div class="station-grid">
        @php
            $stations = [
                ['slug' => 'smelter',    'jenis' => 'logam', 'name' => 'Smelter',    'desc' => 'Olah bijih jadi batangan logam',   'badge' => null],
                ['slug' => 'lumbermill', 'jenis' => 'kayu',  'name' => 'Lumbermill', 'desc' => 'Olah kayu jadi papan kayu',        'badge' => null],
                ['slug' => 'stonemason', 'jenis' => 'batu',  'name' => 'Stonemason', 'desc' => 'Olah batu jadi batu bata',         'badge' => null],
                ['slug' => 'tanner',     'jenis' => 'kulit', 'name' => 'Tannery',    'desc' => 'Olah kulit jadi kulit samak',      'badge' => null],
                ['slug' => 'weaver',     'jenis' => 'serat', 'name' => 'Weaver',     'desc' => 'Olah serat jadi kain',             'badge' => null],
            ];
        @endphp

        @foreach ($stations as $station)
            <a href="/kalkulator/refine?jenis={{ $station['jenis'] }}" class="station-card" style="background-image:linear-gradient(rgba(10,8,6,0.15),rgba(10,8,6,0.15)), url('{{ asset('images/'.$station['slug'].'.jpg') }}');">
                @if ($station['badge'])
                    <span class="station-badge">{{ $station['badge'] }}</span>
                @endif
                <div class="station-body">
                    <div class="station-name">{{ $station['name'] }}</div>
                    <div class="station-desc">{{ $station['desc'] }}</div>
                </div>
            </a>
        @endforeach
    </div>


        {{-- ===== CRAFTING STATION ===== --}}
    <h2 class="section-title">🛠️ Crafting Station</h2>
    <p class="section-sub">Cari tahu berapa biaya craft equipment favoritmu.</p>

    <div class="station-grid">
        @php
            $craftingStations = [
                ['slug' => 'mage-tower',    'name' => "Mage's Tower",    'desc' => 'Mage & caster',      'badge' => null],
                ['slug' => 'hunters-lodge', 'name' => "Hunter's Lodge",  'desc' => 'Senjata & Pakaian Pembunuh', 'badge' => null],
                ['slug' => 'warriors-forge','name' => "Warrior's Forge", 'desc' => 'Pedang & Zirah Warrior', 'badge' => null],
            ];
        @endphp

        @foreach ($craftingStations as $cs)
            <a href="/crafting/{{ $cs['slug'] }}" class="station-card" style="background-image:linear-gradient(rgba(10,8,6,0.15),rgba(10,8,6,0.15)), url('{{ asset('images/'.$cs['slug'].'.jpg') }}');">
                @if ($cs['badge'])
                    <span class="station-badge">{{ $cs['badge'] }}</span>
                @endif
                <div class="station-body">
                    <div class="station-name">{{ $cs['name'] }}</div>
                    <div class="station-desc">{{ $cs['desc'] }}</div>
                </div>
            </a>
        @endforeach
    </div>


    
    {{-- ===== TOOLS LAINNYA ===== --}}
    <h2 class="section-title">🧰 Tools Lainnya</h2>
    <p class="section-sub">Cek harga market real-time atau lihat siapa yang baru gugur di dunia Albion.</p>

    <div class="station-grid">
        @php
            $tools = [
                ['slug' => 'fishing',      'href' => '/kalkulator/fishing',  'name' => 'Kalkulator Mancing',  'desc' => 'Jual ikan atau dicincang, mana lebih untung?', 'badge' => null],
                ['slug' => 'flip',         'href' => '/kalkulator/flip',     'name' => 'Kalkulator Flipping', 'desc' => 'Hitung batas harga jual/beli biar gak rugi pajak.', 'badge' => null],
                ['slug' => 'market',       'href' => '/market',              'name' => 'Cek Harga Market',    'desc' => 'Pantau harga item terkini di seluruh kota.', 'badge' => null],
                ['slug' => 'death-recap',  'href' => '/death-recap',         'name' => 'Rekap Kematian',      'desc' => 'Lihat detail kematian & equipment terakhir player.', 'badge' => null],
            ];
        @endphp

        @foreach ($tools as $tool)
            <a href="{{ $tool['href'] }}" class="station-card" style="background-image:linear-gradient(rgba(10,8,6,0.15),rgba(10,8,6,0.15)), url('{{ asset('images/'.$tool['slug'].'.jpg') }}');">
                @if ($tool['badge'])
                    <span class="station-badge">{{ $tool['badge'] }}</span>
                @endif
                <div class="station-body">
                    <div class="station-name">{{ $tool['name'] }}</div>
                    <div class="station-desc">{{ $tool['desc'] }}</div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- ===== DUKUNG HGT ===== --}}
    <style>
        .support-row{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:20px;}
        .support-btn{display:flex;align-items:center;gap:8px;font-size:0.85rem;font-weight:600;padding:12px 20px;border-radius:30px;background:var(--bg-card);border:1px solid var(--border);color:var(--text-muted);transition:border-color .2s,color .2s,transform .15s;}
        .support-btn:hover{transform:translateY(-2px);border-color:var(--gold);color:var(--gold);}
        .support-btn.saweria:hover{border-color:var(--teal);color:var(--teal);}
    </style>

    <h2 class="section-title">💛 Dukung HGT</h2>
    <p class="section-sub">Suka sama tools ini? Traktir kopi atau gabung komunitas kita.</p>

    <div class="support-row">
        <a href="https://saweria.co/Mamangharun" target="_blank" class="support-btn saweria">☕ Saweria</a>
        <a href="https://trakteer.id/sahabat%20sambungng" target="_blank" class="support-btn">🎁 Trakteer</a>
        <a href="https://t.me/HarunGamingTools" target="_blank" class="support-btn">📢 Channel</a>
        <a href="https://t.me/HGTCommunity" target="_blank" class="support-btn">👥 Grup</a>
    </div>

@endsection