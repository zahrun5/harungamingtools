@extends('layouts.app')

@section('title', __('home.title'))

@section('content')
    <h1>{{ __('home.welcome_title') }}</h1>
    <p>{{ __('home.welcome_sub') }}</p>

    {{-- ===== STASIUN REFINE ===== --}}
    <h2 class="section-title">{{ __('home.sections.refine.title') }}</h2>
    <p class="section-sub">{{ __('home.sections.refine.sub') }}</p>

    <div class="station-grid">
        @php
            $stations = [
                ['slug' => 'smelter',    'jenis' => 'logam'],
                ['slug' => 'lumbermill', 'jenis' => 'kayu'],
                ['slug' => 'stonemason', 'jenis' => 'batu'],
                ['slug' => 'tanner',     'jenis' => 'kulit'],
                ['slug' => 'weaver',     'jenis' => 'serat'],
            ];
        @endphp

        @foreach ($stations as $station)
            <a href="/kalkulator/refine?jenis={{ $station['jenis'] }}" class="station-card" style="background-image:linear-gradient(rgba(10,8,6,0.15),rgba(10,8,6,0.15)), url('{{ asset('images/'.$station['slug'].'.jpg') }}');">
                <div class="station-body">
                    <div class="station-name">{{ __('home.stations.'.$station['slug'].'.name') }}</div>
                    <div class="station-desc">{{ __('home.stations.'.$station['slug'].'.desc') }}</div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- ===== CRAFTING STATION ===== --}}
    <h2 class="section-title">{{ __('home.sections.crafting.title') }}</h2>
    <p class="section-sub">{{ __('home.sections.crafting.sub') }}</p>

    <div class="station-grid">
        @php
            $craftingStations = \App\Models\CraftingStation::orderBy('id')->get();
        @endphp

        @foreach ($craftingStations as $cs)
            @php
                $hasImage = file_exists(public_path('images/'.$cs->slug.'.jpg'));
                $bg = 'linear-gradient(rgba(10,8,6,0.15),rgba(10,8,6,0.15))' . ($hasImage ? ", url('".asset('images/'.$cs->slug.'.jpg')."')" : '');
                $name = \Illuminate\Support\Facades\Lang::has('home.crafting_stations.'.$cs->slug.'.name')
                    ? __('home.crafting_stations.'.$cs->slug.'.name')
                    : $cs->name;
                $desc = \Illuminate\Support\Facades\Lang::has('home.crafting_stations.'.$cs->slug.'.desc')
                    ? __('home.crafting_stations.'.$cs->slug.'.desc')
                    : null;
            @endphp
            <a href="/crafting/{{ $cs->slug }}" class="station-card" style="background-image:{{ $bg }};">
                <div class="station-body">
                    <div class="station-name">{{ $name }}</div>
                    @if ($desc)
                        <div class="station-desc">{{ $desc }}</div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>

    {{-- ===== TOOLS LAINNYA ===== --}}
    <h2 class="section-title">{{ __('home.sections.tools.title') }}</h2>
    <p class="section-sub">{{ __('home.sections.tools.sub') }}</p>

    <div class="station-grid">
        @php
            $tools = [
                ['slug' => 'fishing',      'href' => '/kalkulator/fishing'],
                ['slug' => 'flip',         'href' => '/kalkulator/flip'],
                ['slug' => 'market',       'href' => '/market'],
                ['slug' => 'death-recap',  'href' => '/death-recap'],
            ];
        @endphp

        @foreach ($tools as $tool)
            <a href="{{ $tool['href'] }}" class="station-card" style="background-image:linear-gradient(rgba(10,8,6,0.15),rgba(10,8,6,0.15)), url('{{ asset('images/'.$tool['slug'].'.jpg') }}');">
                <div class="station-body">
                    <div class="station-name">{{ __('home.tools_list.'.$tool['slug'].'.name') }}</div>
                    <div class="station-desc">{{ __('home.tools_list.'.$tool['slug'].'.desc') }}</div>
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

    <h2 class="section-title">{{ __('home.sections.support.title') }}</h2>
    <p class="section-sub">{{ __('home.sections.support.sub') }}</p>

    <div class="support-row">
        <a href="https://saweria.co/Mamangharun" target="_blank" class="support-btn saweria">{{ __('home.support.saweria') }}</a>
        <a href="https://trakteer.id/sahabat%20sambungng" target="_blank" class="support-btn">{{ __('home.support.trakteer') }}</a>
        <a href="https://t.me/HarunGamingTools" target="_blank" class="support-btn">{{ __('home.support.channel') }}</a>
        <a href="https://t.me/HGTCommunity" target="_blank" class="support-btn">{{ __('home.support.group') }}</a>
    </div>

@endsection