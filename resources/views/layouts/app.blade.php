<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#1a1410">
<link rel="apple-touch-icon" href="/images/icons/icon-192.png">
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="@yield('meta_description', 'Free Albion Online calculators and tools. Real-time market prices, refining calculator, crafting planner, flip scanner. Trusted by 1000+ players.')">
<meta name="keywords" content="@yield('meta_keywords', 'albion online calculator, market prices, refining calculator, crafting calculator, flip calculator, albion tools')">
<link rel="canonical" href="{{ url()->current() }}">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="@yield('title', 'Albion Online Tools — Hitung, Catat, Naik Peringkat')">
<meta property="og:description" content="@yield('og_description', 'Free Albion Online calculators: refining profit, market prices, crafting costs, flip opportunities. Real-time data, 7 languages.')">
<meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
<meta property="og:site_name" content="HGT - Harun Gaming Tools">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ url()->current() }}">
<meta name="twitter:title" content="@yield('title', 'Albion Online Tools — Hitung, Catat, Naik Peringkat')">
<meta name="twitter:description" content="@yield('og_description', 'Free Albion Online calculators: refining profit, market prices, crafting costs, flip opportunities. Real-time data, 7 languages.')">
<meta name="twitter:image" content="@yield('og_image', asset('images/og-default.jpg'))">

<link rel="icon" type="image/png" href="{{ asset('images/icons/page-icon.png') }}">
@if(config('services.analytics.google_analytics_id') && app()->environment('production'))
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.analytics.google_analytics_id') }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ config('services.analytics.google_analytics_id') }}');
</script>
@endif
<script>
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js');
  }
</script>
<title>@yield('title', 'Albion Online Tools — Hitung, Catat, Naik Peringkat')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,900&family=Sora:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#14110F; --bg-panel:#1C1712; --bg-card:#221C15;
    --gold:#D9A653; --gold-dim:#A9803F; --teal:#5FB3A8;
    --text:#EFE7D8; --text-muted:#9C9181; --border:#332B21;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:'Sora',sans-serif;line-height:1.5;}
  a{color:inherit;text-decoration:none;}
  .wrap{max-width:1180px;margin:0 auto;padding:0 24px;}

  header{position:sticky;top:0;z-index:50;background:rgba(20,17,15,0.92);backdrop-filter:blur(8px);border-bottom:1px solid var(--border);}
  .nav-row{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;max-width:1180px;margin:0 auto;}
  .logo{display:flex;align-items:center;gap:10px;font-family:'Fraunces',serif;font-weight:700;font-size:1.25rem;}
  .logo-mark{width:34px;height:34px;border-radius:8px;object-fit:cover;flex-shrink:0;}

  .btn-login{border:1px solid var(--border);color:var(--text);padding:8px 18px;border-radius:6px;font-size:0.88rem;font-weight:500;transition:border-color 0.2s,color 0.2s;}
  .btn-login:hover{border-color:var(--gold);color:var(--gold);}

  .profile{display:flex;align-items:center;gap:8px;background:var(--bg-panel);border:1px solid var(--border);padding:6px 12px 6px 6px;border-radius:30px;}
  .profile img{width:26px;height:26px;border-radius:50%;}
  .profile span{font-size:0.85rem;font-weight:600;}
  .profile button{background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:0.85rem;}
  .profile button:hover{color:var(--gold);}

  /* ===== Topbar Dropdown (Server & Bahasa) ===== */
  .topbar-actions{display:flex;align-items:center;gap:8px;}
  .topbar-dropdown{position:relative;}
  .topbar-btn{display:flex;align-items:center;gap:5px;background:var(--bg-panel);border:1px solid var(--border);color:var(--text);padding:7px 12px;border-radius:20px;font-size:0.8rem;font-weight:600;font-family:'Sora',sans-serif;cursor:pointer;transition:border-color .2s,color .2s;}
  .topbar-btn:hover{border-color:var(--gold);color:var(--gold);}
  .topbar-btn svg{width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2;transition:transform .15s;}
  .topbar-dropdown.open .topbar-btn svg{transform:rotate(180deg);}
  .topbar-menu{position:absolute;top:calc(100% + 6px);right:0;min-width:150px;background:var(--bg-panel);border:1px solid var(--border);border-radius:10px;padding:6px;display:none;box-shadow:0 8px 24px rgba(0,0,0,.35);z-index:60;}
  .topbar-dropdown.open .topbar-menu{display:block;}
  .topbar-menu a{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:6px;font-size:0.82rem;color:var(--text);}
  .topbar-menu a:hover{background:var(--bg-card);color:var(--gold);}
  .topbar-menu a.active{color:var(--gold);font-weight:600;}

  main{padding:48px 0;}
  body{padding-bottom:82px;transition:padding-bottom .25s ease,padding-left .25s ease;}
  body.nav-collapsed{padding-bottom:0;}

  /* ===== Bottom Navigation ===== */
  .bottom-nav{position:fixed;left:0;right:0;bottom:0;z-index:55;display:flex;justify-content:space-around;align-items:stretch;background:rgba(20,17,15,0.96);backdrop-filter:blur(8px);border-top:1px solid var(--border);padding:6px 4px calc(6px + env(safe-area-inset-bottom));transition:transform .25s ease;}
  .bottom-nav.is-hidden{transform:translateY(100%);}
  .bottom-nav-item{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;padding:6px 4px;color:var(--text-muted);font-size:0.68rem;font-weight:500;transition:color .2s;}
  .bottom-nav-item svg{width:22px;height:22px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
  .bottom-nav-item:hover{color:var(--gold);}
  .bottom-nav-item.active{color:var(--gold);}

  /* ===== Bottom Nav Toggle Button (mobile only) ===== */
  .nav-toggle-btn{position:fixed;right:14px;bottom:76px;z-index:56;width:40px;height:40px;border-radius:50%;background:var(--bg-panel);border:1px solid var(--gold-dim);color:var(--gold);display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,.4);transition:bottom .25s ease,background .2s,border-color .2s;}
  .nav-toggle-btn:hover{border-color:var(--gold);}
  .nav-toggle-btn svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round;transition:transform .25s ease;}
  .nav-toggle-btn.is-collapsed{bottom:14px;}
  .nav-toggle-btn.is-collapsed svg{transform:rotate(180deg);}

  /* ===== Desktop: bottom nav becomes a left sidebar ===== */
  @media (min-width:960px){
    body{padding-bottom:0;padding-left:76px;}
    body.nav-collapsed{padding-left:76px;}
    .bottom-nav{left:0;right:auto;top:0;bottom:0;width:76px;flex-direction:column;justify-content:flex-start;align-items:stretch;gap:6px;padding:24px 6px;border-top:none;border-right:1px solid var(--border);transform:none !important;}
    .bottom-nav-item{flex:none;}
    .nav-toggle-btn{display:none;}
  }

  /* ===== Station cards (halaman utama) ===== */
  .section-title{font-family:'Fraunces',serif;color:var(--gold);font-size:1.15rem;margin:36px 0 16px;display:flex;align-items:center;gap:8px;}
  .section-sub{font-size:0.85rem;color:var(--text-muted);margin-top:-10px;margin-bottom:18px;}

  .station-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;}
  @media (min-width:640px){.station-grid{grid-template-columns:repeat(3,1fr);}}
  @media (min-width:960px){.station-grid{grid-template-columns:repeat(4,1fr);}}

  .station-card{position:relative;display:block;border-radius:12px;overflow:hidden;aspect-ratio:4/3;border:1px solid var(--border);background-color:var(--bg-card);background-size:cover;background-position:center;transition:transform .18s ease,border-color .18s ease;}
  .station-card:hover{transform:translateY(-3px);border-color:var(--gold);}
  .station-card::after{content:"";position:absolute;inset:0;background:linear-gradient(to top,rgba(10,8,6,0.92) 0%,rgba(10,8,6,0.45) 45%,rgba(10,8,6,0.05) 75%);}
  .station-card .station-badge{position:absolute;top:10px;right:10px;z-index:2;background:rgba(20,17,15,0.75);border:1px solid var(--border);color:var(--gold);font-size:0.68rem;font-weight:600;padding:3px 9px;border-radius:20px;font-family:'JetBrains Mono',monospace;}
  .station-card .station-body{position:absolute;left:0;right:0;bottom:0;z-index:2;padding:14px;}
  .station-card .station-name{font-family:'Fraunces',serif;font-weight:700;font-size:1.05rem;color:var(--text);margin-bottom:3px;}
  .station-card .station-desc{font-size:0.74rem;color:var(--text-muted);line-height:1.3;}

  .tool-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:30px;}
  .tool-card{background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:18px;display:block;transition:border-color .2s;}
  .tool-card:hover{border-color:var(--gold);}
  .tool-icon{font-size:1.6rem;margin-bottom:8px;}
  .tool-name{font-weight:600;margin-bottom:4px;}
  .tool-desc{font-size:.8rem;color:var(--text-muted);}

  footer{border-top:1px solid var(--border);padding:32px 0;margin-top:48px;}
  .footer-links{display:flex;gap:20px;flex-wrap:wrap;font-size:0.85rem;color:var(--text-muted);}
  .footer-links a:hover{color:var(--gold);}
  .footer-credit{margin-top:16px;font-size:0.8rem;color:var(--text-muted);}
</style>
</head>
<body>
<header>
  <div class="nav-row">
    <a href="/" class="logo"><img src="{{ asset('images/icons/icon-192.png') }}" alt="Albion Online Tools" class="logo-mark"> Albion Online Tools</a>
    <div class="topbar-actions">
      {{-- Server Selector --}}
      <div class="topbar-dropdown" id="server-dropdown">
        <button class="topbar-btn" onclick="toggleTopbarDropdown('server-dropdown')" type="button">
           {{ ['americas' => 'Americas', 'europe' => 'Europe', 'asia' => 'Asia'][session('server', 'americas')] }}
          <svg viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="topbar-menu">
          <a href="{{ route('server.switch', 'americas') }}" class="{{ session('server', 'americas') === 'americas' ? 'active' : '' }}">Americas</a>
          <a href="{{ route('server.switch', 'europe') }}" class="{{ session('server') === 'europe' ? 'active' : '' }}">Europe</a>
          <a href="{{ route('server.switch', 'asia') }}" class="{{ session('server') === 'asia' ? 'active' : '' }}">Asia</a>
        </div>
      </div>

      {{-- Language Selector (tampilan dulu, logic nyusul) --}}
      <div class="topbar-dropdown" id="lang-dropdown">
        <button class="topbar-btn" onclick="toggleTopbarDropdown('lang-dropdown')" type="button">
          {{ strtoupper(app()->getLocale()) }}
          <svg viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="topbar-menu">
          <a href="{{ route('lang.switch', 'id') }}" class="{{ app()->getLocale() === 'id' ? 'active' : '' }}">🇮🇩 Indonesia</a>
          <a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">🇬🇧 English</a>
          <a href="{{ route('lang.switch', 'pt_BR') }}" class="{{ app()->getLocale() === 'pt_BR' ? 'active' : '' }}">🇧🇷 Português (BR)</a>
          <a href="{{ route('lang.switch', 'ru') }}" class="{{ app()->getLocale() === 'ru' ? 'active' : '' }}">🇷🇺 Русский</a>
          <a href="{{ route('lang.switch', 'de') }}" class="{{ app()->getLocale() === 'de' ? 'active' : '' }}">🇩🇪 Deutsch</a>
          <a href="{{ route('lang.switch', 'pl') }}" class="{{ app()->getLocale() === 'pl' ? 'active' : '' }}">🇵🇱 Polski</a>
          <a href="{{ route('lang.switch', 'zh') }}" class="{{ app()->getLocale() === 'zh' ? 'active' : '' }}">🇨🇳 中文</a>
        </div>
      </div>

    </div>
  </div>
</header>

<main>
  <div class="wrap">
    @yield('content')
  </div>
</main>

<footer>
  <div class="wrap">
    <div class="footer-links">
      <a href="https://t.me/HarunGamingTools" target="_blank">{{ __("nav.footer_channel") }}</a>
      <a href="https://t.me/HGTCommunity" target="_blank">{{ __("nav.footer_community") }}</a>
      <a href="https://saweria.co/Mamangharun" target="_blank">Saweria</a>
      <a href="https://trakteer.id/sahabat%20sambungng" target="_blank">Trakteer</a>
    </div>
    <div class="footer-sponsor" style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);">
      <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:6px;">
        💼 Tertarik jadi sponsor atau kerja sama? Hubungi kami di <a href="https://t.me/HarunGamingTools" target="_blank" style="color:var(--gold);text-decoration:underline;">Telegram</a>
      </p>
    </div>
    <p class="footer-credit">{{ __("nav.footer_credit") }}</p>
  </div>
</footer>

<button type="button" id="nav-toggle-btn" class="nav-toggle-btn" aria-label="Sembunyikan/tampilkan navigasi" aria-controls="bottom-nav">
    <svg viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
</button>

<nav class="bottom-nav" id="bottom-nav" aria-label="Navigasi utama">
    <a href="/" class="bottom-nav-item {{ request()->is('/') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9"/></svg>
        <span>{{ __("nav.home") }}</span>
    </a>

    <a href="/reels" class="bottom-nav-item {{ request()->is('reels*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M10 8.5v7l5.5-3.5-5.5-3.5Z"/></svg>
        <span>{{ __("reels") }}</span>
    </a>

    <a href="/social" class="bottom-nav-item {{ request()->routeIs('social.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17" cy="9" r="2.5"/><path d="M15.5 14.3c2.9.4 5.5 2.5 5.5 5.7"/></svg>
        <span>{{ __("nav.social") }}</span>
    </a>

    @auth
        <a href="/notifikasi" class="bottom-nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
    @else
        <a href="/login" class="bottom-nav-item">
    @endauth
        <svg viewBox="0 0 24 24"><path d="M6 9a6 6 0 0 1 12 0c0 4 1.5 5.5 2 6.5H4c.5-1 2-2.5 2-6.5Z"/><path d="M9.5 18.5a2.5 2.5 0 0 0 5 0"/></svg>
        <span>{{ __("nav.notifications") }}</span>
    </a>

    @auth
        <a href="/profile" class="bottom-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    @else
        <a href="/login" class="bottom-nav-item">
    @endauth
        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.9 3.1-7 7-7s7 3.1 7 7"/></svg>
        <span>{{ __("nav.profile") }}</span>
    </a>
</nav>

<script>
  function toggleTopbarDropdown(id) {
    const target = document.getElementById(id);
    document.querySelectorAll('.topbar-dropdown.open').forEach(el => {
      if (el !== target) el.classList.remove('open');
    });
    target.classList.toggle('open');
  }
  document.addEventListener('click', function (e) {
    document.querySelectorAll('.topbar-dropdown.open').forEach(el => {
      if (!el.contains(e.target)) el.classList.remove('open');
    });
  });

  // ===== Bottom Nav Toggle (mobile) =====
  (function () {
    const bottomNav = document.getElementById('bottom-nav');
    const toggleBtn = document.getElementById('nav-toggle-btn');
    if (!bottomNav || !toggleBtn) return;

    const STORAGE_KEY = 'bottomNavCollapsed';
    const setState = (collapsed) => {
      bottomNav.classList.toggle('is-hidden', collapsed);
      toggleBtn.classList.toggle('is-collapsed', collapsed);
      document.body.classList.toggle('nav-collapsed', collapsed);
    };

    let collapsed = localStorage.getItem(STORAGE_KEY) === '1';
    setState(collapsed);

    toggleBtn.addEventListener('click', function () {
      collapsed = !collapsed;
      setState(collapsed);
      localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
    });
  })();
</script>
</body>
</html>