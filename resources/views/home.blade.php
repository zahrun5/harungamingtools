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

    @livewire('leaderboard')

@endsection