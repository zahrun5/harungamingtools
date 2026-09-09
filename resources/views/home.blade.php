@extends('layouts.app')

@section('title', __('home.title'))

@section('meta_description', 'Free Albion Online calculators and tools. Calculate refining profit, browse real-time market prices, find flip opportunities, plan crafting costs. Trusted by 1000+ players worldwide.')

@section('meta_keywords', 'albion online calculator, refining profit calculator, market prices albion, crafting calculator, flip calculator, albion tools, albion data, free tools')

@section('og_description', 'Free Albion Online tools: Refining calculator, market browser, flip scanner, crafting planner. Real-time prices from Albion Data API. 7 languages, 3 server regions.')

@section('content')
    {{-- ===== HERO SECTION ===== --}}
    <div class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">{{ __('home.hero.title') }}</h1>
            <p class="hero-subtitle">{{ __('home.hero.subtitle') }}</p>
            
            <div class="hero-features">
                <div class="hero-feature">
                    <span class="hero-feature-icon">⚡</span>
                    <span class="hero-feature-text">{{ __('home.hero.features.realtime') }}</span>
                </div>
                <div class="hero-feature">
                    <span class="hero-feature-icon">🌍</span>
                    <span class="hero-feature-text">{{ __('home.hero.features.multilang') }}</span>
                </div>
                <div class="hero-feature">
                    <span class="hero-feature-icon">💯</span>
                    <span class="hero-feature-text">{{ __('home.hero.features.free') }}</span>
                </div>
            </div>

            <div class="hero-cta">
                <a href="#tools" class="hero-btn hero-btn-primary">{{ __('home.hero.cta_primary') }}</a>
                <a href="/market" class="hero-btn hero-btn-secondary">{{ __('home.hero.cta_secondary') }}</a>
            </div>

            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-value">10+</div>
                    <div class="hero-stat-label">{{ __('home.hero.stats.tools') }}</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value">1000+</div>
                    <div class="hero-stat-label">{{ __('home.hero.stats.users') }}</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value">7</div>
                    <div class="hero-stat-label">{{ __('home.hero.stats.languages') }}</div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hero-section {
            background: linear-gradient(135deg, rgba(217, 166, 83, 0.05) 0%, rgba(20, 17, 15, 0) 100%);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 48px 24px;
            margin-bottom: 48px;
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--gold);
            margin: 0 0 16px 0;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.15rem;
            color: var(--text-muted);
            margin: 0 0 32px 0;
            line-height: 1.6;
        }

        .hero-features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 32px;
        }

        .hero-feature {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 30px;
            font-size: 0.9rem;
            color: var(--text);
            transition: transform 0.2s, border-color 0.2s;
        }

        .hero-feature:hover {
            transform: translateY(-2px);
            border-color: var(--gold);
        }

        .hero-feature-icon {
            font-size: 1.2rem;
        }

        .hero-cta {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-bottom: 40px;
        }

        .hero-btn {
            display: inline-block;
            padding: 14px 32px;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
        }

        .hero-btn-primary {
            background: linear-gradient(135deg, var(--gold) 0%, #c89a4e 100%);
            color: #14110F;
            border: none;
        }

        .hero-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(217, 166, 83, 0.4);
        }

        .hero-btn-secondary {
            background: transparent;
            color: var(--gold);
            border: 2px solid var(--gold);
        }

        .hero-btn-secondary:hover {
            background: var(--gold);
            color: #14110F;
            transform: translateY(-2px);
        }

        .hero-stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 40px;
            padding-top: 32px;
            border-top: 1px solid var(--border);
        }

        .hero-stat {
            text-align: center;
        }

        .hero-stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gold);
            margin-bottom: 4px;
        }

        .hero-stat-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-section {
                padding: 32px 20px;
                margin-bottom: 32px;
            }

            .hero-title {
                font-size: 1.8rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .hero-features {
                gap: 12px;
            }

            .hero-feature {
                font-size: 0.85rem;
                padding: 8px 16px;
            }

            .hero-btn {
                padding: 12px 24px;
                font-size: 0.9rem;
            }

            .hero-stats {
                gap: 24px;
            }

            .hero-stat-value {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 1.5rem;
            }

            .hero-features {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-feature {
                justify-content: center;
            }

            .hero-cta {
                flex-direction: column;
            }

            .hero-btn {
                width: 100%;
            }
        }

        /* Smooth scroll for anchor link */
        html {
            scroll-behavior: smooth;
        }
    </style>

    {{-- ===== STASIUN REFINE ===== --}}
    <div id="tools"></div>
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