@extends('layouts.app')

@section('title', __('home.title'))

@section('meta_description', 'Free Albion Online calculators and tools. Calculate refining profit, browse real-time market prices, find flip opportunities, plan crafting costs. Trusted by 1000+ players worldwide.')

@section('meta_keywords', 'albion online calculator, refining profit calculator, market prices albion, crafting calculator, flip calculator, albion tools, albion data, free tools')

@section('og_description', 'Free Albion Online tools: Refining calculator, market browser, flip scanner, crafting planner. Real-time prices from Albion Data API. 7 languages, 3 server regions.')

@section('content')
    <h1>{{ __('home.welcome_title') }}</h1>
    <p>{{ __('home.welcome_sub') }}</p>

    {{-- ===== STASIUN REFINE ===== --}}
    <h2 class="section-title">{{ __('home.sections.refine.title') }}</h2>
    <p class="section-sub">{{ __('home.sections.refine.sub') }}</p>

    <div class="station-grid">
        @php
            $stations = [
                ['slug' => 'all-refining', 'jenis' => null], // All resources
                ['slug' => 'smelter',      'jenis' => 'logam'],
                ['slug' => 'lumbermill',   'jenis' => 'kayu'],
                ['slug' => 'stonemason',   'jenis' => 'batu'],
                ['slug' => 'tanner',       'jenis' => 'kulit'],
                ['slug' => 'weaver',       'jenis' => 'serat'],
            ];
        @endphp

        @foreach ($stations as $station)
            @php
                $href = $station['jenis'] 
                    ? "/kalkulator/refine?jenis={$station['jenis']}" 
                    : "/kalkulator/refine";
            @endphp
            <a href="{{ $href }}" class="station-card" style="background-image:linear-gradient(rgba(10,8,6,0.15),rgba(10,8,6,0.15)), url('{{ asset('images/'.$station['slug'].'.jpg') }}');">
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

    @php
        $craftingStations = \App\Models\CraftingStation::orderBy('id')->get();
        $mainStations = $craftingStations->take(3);
        $extraStations = $craftingStations->skip(3);
    @endphp

    <div class="station-grid">
        @foreach ($mainStations as $cs)
            @php
                $imageFile = null;
                if (file_exists(public_path('images/'.$cs->slug.'.jpg'))) {
                    $imageFile = 'images/'.$cs->slug.'.jpg';
                } elseif (file_exists(public_path('images/'.$cs->slug.'.svg'))) {
                    $imageFile = 'images/'.$cs->slug.'.svg';
                }
                $bg = 'linear-gradient(rgba(10,8,6,0.15),rgba(10,8,6,0.15))' . ($imageFile ? ", url('".asset($imageFile)."')" : '');
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

    @if ($extraStations->isNotEmpty())
        <button type="button" id="toggle-crafting-stations" class="toggle-stations-btn">
            <span class="toggle-label-show">{{ __('home.crafting_toggle.show') }}</span>
            <span class="toggle-label-hide">{{ __('home.crafting_toggle.hide') }}</span>
        </button>

        <div class="station-grid" id="extra-crafting-stations" hidden>
            @foreach ($extraStations as $cs)
                @php
                    $imageFile = null;
                    if (file_exists(public_path('images/'.$cs->slug.'.jpg'))) {
                        $imageFile = 'images/'.$cs->slug.'.jpg';
                    } elseif (file_exists(public_path('images/'.$cs->slug.'.svg'))) {
                        $imageFile = 'images/'.$cs->slug.'.svg';
                    }
                    $bg = 'linear-gradient(rgba(10,8,6,0.15),rgba(10,8,6,0.15))' . ($imageFile ? ", url('".asset($imageFile)."')" : '');
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

        <script>
            (function () {
                var btn = document.getElementById('toggle-crafting-stations');
                var grid = document.getElementById('extra-crafting-stations');
                var key = 'hgt_show_crafting_stations';

                function apply(open) {
                    grid.hidden = !open;
                    btn.classList.toggle('open', open);
                }

                apply(localStorage.getItem(key) === '1');

                btn.addEventListener('click', function () {
                    var open = grid.hidden;
                    localStorage.setItem(key, open ? '1' : '0');
                    apply(open);
                });
            })();
        </script>

        <style>
            .toggle-stations-btn{display:inline-flex;align-items:center;gap:6px;margin:10px 0 16px;font-size:0.85rem;font-weight:600;padding:8px 18px;border-radius:30px;background:var(--bg-card);border:1px solid var(--border);color:var(--text-muted);cursor:pointer;transition:border-color .2s,color .2s,transform .15s;}
            .toggle-stations-btn:hover{transform:translateY(-2px);border-color:var(--gold);color:var(--gold);}
            .toggle-stations-btn::after{content:'▾';transition:transform .2s;}
            .toggle-stations-btn.open::after{transform:rotate(180deg);}
            .toggle-label-hide{display:none;}
            .toggle-stations-btn.open .toggle-label-hide{display:inline;}
            .toggle-stations-btn.open .toggle-label-show{display:none;}
        </style>
    @endif

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