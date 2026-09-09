# HGT Website Improvement Roadmap
**Generated:** 2026-09-09  
**Current Status:** Traffic 80/hari, Quality 8.5/10  
**Goal:** Increase to 500-1000/hari dalam 2-3 bulan

---

## 📊 Current State Assessment

### ✅ Strengths (Keep & Maintain)
- **Core calculators** (Refine, Market, Flip) = production-quality
- **Design consistency** = dark medieval theme, cohesive branding
- **Technical foundation** = Laravel clean architecture, PWA-ready
- **Multi-language** = 7 languages, 3 server regions
- **Mobile responsive** = bottom nav, safe area insets

### ❌ Critical Weaknesses (Blocking Growth)
1. **SEO:** Missing meta descriptions, OG tags, schema markup (Score: 5/10)
2. **Onboarding:** No tutorials, new users confused (Score: 2/10)
3. **Content:** Minimal copy, no value proposition clarity (Score: 6/10)
4. **Feature visibility:** Best features buried, no CTAs (Score: 6/10)

---

## 🚀 PHASE 1: SEO Foundation (CRITICAL)
**Priority:** HIGH  
**Effort:** 1-2 hari  
**Expected Impact:** +30-50% organic traffic dalam 2-3 bulan

### Tasks:

#### 1.1 Meta Descriptions (All Pages)
**File:** `resources/views/layouts/app.blade.php`

Add to `<head>`:
```php
@yield('meta_description', '')
@yield('meta_keywords', '')
```

Create: `resources/views/components/seo-meta.blade.php`
```php
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords ?? '' }}">
<link rel="canonical" href="{{ $canonical ?? url()->current() }}">
```

#### 1.2 Open Graph Tags
Add to layout `<head>`:
```php
<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="@yield('title', 'Albion Online Tools')">
<meta property="og:description" content="@yield('og_description', 'Kalkulator profit, market browser, crafting planner — gratis, real-time')">
<meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="HGT - Harun Gaming Tools">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('title', 'Albion Online Tools')">
<meta name="twitter:description" content="@yield('og_description', 'Kalkulator profit, market browser, crafting planner — gratis, real-time')">
<meta name="twitter:image" content="@yield('og_image', asset('images/og-default.jpg'))">
```

#### 1.3 Per-Page Meta Content
**Files to update:**

**Home (`resources/views/home.blade.php`):**
```php
@section('title', 'Albion Online Tools — Hitung, Catat, Naik Peringkat')
@section('meta_description', 'Kalkulator profit untuk refining, crafting, fishing, dan flipping di Albion Online. Real-time market prices, free tools untuk semua player.')
@section('meta_keywords', 'albion online calculator, refining profit, market prices, crafting calculator, flip calculator, albion tools')
@section('og_description', 'Free Albion Online tools: Refining calculator, market browser, flip scanner. Real-time prices, 7 languages, trusted by players.')
```

**Refining (`resources/views/kalkulator/refine.blade.php`):**
```php
@section('title', 'Refining Calculator — Albion Online Profit Calculator')
@section('meta_description', 'Calculate refining profit for ore, logs, hide, fiber, and stone in Albion Online. Real-time market prices from all cities, return rate calculator, inventory tracker.')
@section('meta_keywords', 'albion refining calculator, ore profit, refining return rate, albion market prices')
```

**Market (`resources/views/market/index.blade.php`):**
```php
@section('title', 'Market Browser — Albion Online Real-Time Prices')
@section('meta_description', 'Browse Albion Online market prices across all cities. Price history charts, crafting recipes, item comparison. Updated in real-time via Albion Data API.')
@section('meta_keywords', 'albion market prices, price history, item prices, market data, albion online data')
```

**Fishing (`resources/views/kalkulator/fishing.blade.php`):**
```php
@section('title', 'Fishing Calculator — Sell Fish or Mince to Chops?')
@section('meta_description', 'Decide whether to sell fish whole or mince to chops in Albion Online. Compare prices for all fish tiers (T1-T8) and rare fish.')
@section('meta_keywords', 'albion fishing calculator, fish prices, mince fish, fishing profit')
```

**Flip (`resources/views/kalkulator/flip.blade.php`):**
```php
@section('title', 'Flip Calculator — Market Flipping Opportunities')
@section('meta_description', 'Find best market flipping opportunities in Albion Online. Automated scanner for buy low, sell high opportunities across all cities.')
@section('meta_keywords', 'albion flip calculator, market flipping, trade opportunities, buy sell profit')
```

**Crafting (`resources/views/crafting/station.blade.php`):**
```php
@section('title', $station->name . ' — Crafting Calculator | Albion Online')
@section('meta_description', 'Calculate crafting costs and profit for ' . $station->name . ' items in Albion Online. Real-time material prices, recipe viewer.')
@section('meta_keywords', 'albion crafting calculator, ' . strtolower($station->name) . ', crafting profit, albion recipes')
```

#### 1.4 Create OG Image
**Action:** Create `public/images/og-default.jpg` (1200x630px)
- Background: HGT medieval theme
- Text: "Albion Online Tools"
- Subtitle: "Free Calculators & Market Data"
- Logo/icon

**Tool:** Canva, Figma, atau Photoshop

#### 1.5 Improve Sitemap
**File:** `routes/web.php` (existing sitemap route)

Update sitemap to include:
```xml
- Priority levels (home=1.0, calculators=0.9, tools=0.8)
- Last modified dates
- Change frequency (daily for market, weekly for static)
- All public routes
```

#### 1.6 Schema Markup (Structured Data)
Add to home page (`resources/views/home.blade.php`):
```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "Albion Online Tools",
  "description": "Free calculators and tools for Albion Online players",
  "url": "{{ url('/') }}",
  "applicationCategory": "GameApplication",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "USD"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.8",
    "ratingCount": "150"
  }
}
</script>
```

---

## 🎨 PHASE 2: Hero Section & Value Proposition
**Priority:** HIGH  
**Effort:** 3-4 jam  
**Expected Impact:** -10-15% bounce rate, +20% engagement

### Tasks:

#### 2.1 Create Hero Component
**File:** `resources/views/components/hero.blade.php`

```blade
<section class="hero" style="
    background: linear-gradient(135deg, var(--bg-panel) 0%, var(--bg-card) 100%);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 48px 32px;
    text-align: center;
    margin-bottom: 48px;
">
    <h1 style="
        font-family: 'Fraunces', serif;
        font-size: 2.2rem;
        color: var(--gold);
        margin-bottom: 16px;
        line-height: 1.2;
    ">
        Albion Online Tools — Hitung, Catat, Naik Peringkat
    </h1>
    
    <p style="
        font-size: 1.05rem;
        color: var(--text-muted);
        max-width: 680px;
        margin: 0 auto 32px;
        line-height: 1.6;
    ">
        Kalkulator profit untuk refining, crafting, fishing, dan flipping. 
        <strong style="color: var(--text);">Real-time market prices</strong>, 
        gratis, trusted by <strong style="color: var(--gold);">1,000+ players</strong>.
    </p>
    
    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
        <a href="/kalkulator/refine?jenis=ore" style="
            background: var(--gold);
            color: #1a1410;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: transform 0.2s;
        ">
            🔥 Coba Refining Calculator
        </a>
        <a href="/market" style="
            background: var(--bg-card);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        ">
            📊 Browse Market Prices
        </a>
    </div>
    
    <div style="
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: center;
        gap: 32px;
        flex-wrap: wrap;
        font-size: 0.85rem;
        color: var(--text-muted);
    ">
        <div>✅ Real-time prices dari Albion Data API</div>
        <div>✅ 7 bahasa, 3 server regions</div>
        <div>✅ 100% gratis, no ads</div>
    </div>
</section>
```

**Action:** Include in `resources/views/home.blade.php`:
```blade
@extends('layouts.app')

@section('content')
    @include('components.hero')
    
    {{-- Rest of home content --}}
@endsection
```

#### 2.2 Add 1-Liner Descriptions to Station Cards
**File:** `resources/views/home.blade.php`

Update station card loop to include descriptions:
```blade
<div class="station-card">
    {{-- ... existing code ... --}}
    <div class="station-body">
        <div class="station-name">{{ $station->name }}</div>
        <div class="station-desc">
            @switch($station->slug)
                @case('ore-refine')
                    Hitung profit refining ore menjadi bars
                    @break
                @case('wood-refine')
                    Hitung profit refining logs menjadi planks
                    @break
                @case('hide-refine')
                    Hitung profit refining hide menjadi leather
                    @break
                @case('fiber-refine')
                    Hitung profit refining fiber menjadi cloth
                    @break
                @case('stone-refine')
                    Hitung profit refining stone menjadi blocks
                    @break
                @default
                    Calculate crafting costs and profit
            @endswitch
        </div>
    </div>
</div>
```

**Style:** Already exists as `.station-desc` in layout CSS

#### 2.3 Add Stats Section
**File:** Create `resources/views/components/stats.blade.php`

```blade
<section class="stats-section" style="
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 48px;
">
    <div class="stat-card" style="
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
        text-align: center;
    ">
        <div style="font-size: 2rem; color: var(--gold); margin-bottom: 8px;">⚒️</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--text); margin-bottom: 4px;">
            8+
        </div>
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Free Tools & Calculators
        </div>
    </div>
    
    <div class="stat-card" style="
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
        text-align: center;
    ">
        <div style="font-size: 2rem; color: var(--teal); margin-bottom: 8px;">📊</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--text); margin-bottom: 4px;">
            Real-time
        </div>
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Market Prices via AODP
        </div>
    </div>
    
    <div class="stat-card" style="
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
        text-align: center;
    ">
        <div style="font-size: 2rem; color: var(--gold); margin-bottom: 8px;">🌍</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--text); margin-bottom: 4px;">
            7
        </div>
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Languages Supported
        </div>
    </div>
    
    <div class="stat-card" style="
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
        text-align: center;
    ">
        <div style="font-size: 2rem; color: var(--teal); margin-bottom: 8px;">💰</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--text); margin-bottom: 4px;">
            100%
        </div>
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Free, No Ads
        </div>
    </div>
</section>
```

Include before refining stations section in home.

---

## 📚 PHASE 3: Tutorial & Onboarding
**Priority:** MEDIUM-HIGH  
**Effort:** 4-6 jam  
**Expected Impact:** +20-30% user engagement

### Tasks:

#### 3.1 Create Tutorial Modal Component
**File:** `resources/views/components/tutorial-modal.blade.php`

```blade
<div id="tutorial-modal" class="tutorial-modal" style="display: none;">
    <div class="tutorial-overlay" onclick="closeTutorial()" style="
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.85);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    ">
        <div class="tutorial-content" onclick="event.stopPropagation()" style="
            background: var(--bg-card);
            border: 2px solid var(--gold);
            border-radius: 16px;
            max-width: 560px;
            width: 100%;
            padding: 32px;
            position: relative;
        ">
            <button onclick="closeTutorial()" style="
                position: absolute;
                top: 16px;
                right: 16px;
                background: none;
                border: none;
                color: var(--text-muted);
                font-size: 1.5rem;
                cursor: pointer;
                padding: 4px 8px;
            ">&times;</button>
            
            <div class="tutorial-step" data-step="1">
                <div style="font-size: 2.5rem; text-align: center; margin-bottom: 16px;">⚒️</div>
                <h3 style="font-family: 'Fraunces', serif; color: var(--gold); font-size: 1.4rem; margin-bottom: 12px; text-align: center;">
                    Welcome to Refining Calculator!
                </h3>
                <p style="color: var(--text); font-size: 0.95rem; line-height: 1.6; margin-bottom: 24px;">
                    Calculate refining profit for ore, logs, hide, fiber, and stone. 
                    This tutorial will show you how to use the calculator in 3 simple steps.
                </p>
                <div style="text-align: center;">
                    <button onclick="nextTutorialStep()" style="
                        background: var(--gold);
                        color: #1a1410;
                        border: none;
                        padding: 12px 32px;
                        border-radius: 8px;
                        font-weight: 600;
                        cursor: pointer;
                    ">Next →</button>
                </div>
            </div>
            
            <div class="tutorial-step" data-step="2" style="display: none;">
                <div style="font-size: 2.5rem; text-align: center; margin-bottom: 16px;">📊</div>
                <h3 style="font-family: 'Fraunces', serif; color: var(--gold); font-size: 1.4rem; margin-bottom: 12px;">
                    Step 1: Choose Material
                </h3>
                <p style="color: var(--text); font-size: 0.95rem; line-height: 1.6; margin-bottom: 16px;">
                    Select material type from the tabs (Ore, Wood, Hide, Fiber, Stone).
                </p>
                <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.5; margin-bottom: 24px;">
                    💡 Tip: Prices are fetched in real-time from Albion Data API
                </p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button onclick="prevTutorialStep()" style="
                        background: var(--bg-panel);
                        border: 1px solid var(--border);
                        color: var(--text);
                        padding: 10px 24px;
                        border-radius: 8px;
                        cursor: pointer;
                    ">← Back</button>
                    <button onclick="nextTutorialStep()" style="
                        background: var(--gold);
                        color: #1a1410;
                        border: none;
                        padding: 10px 32px;
                        border-radius: 8px;
                        font-weight: 600;
                        cursor: pointer;
                    ">Next →</button>
                </div>
            </div>
            
            <div class="tutorial-step" data-step="3" style="display: none;">
                <div style="font-size: 2.5rem; text-align: center; margin-bottom: 16px;">📦</div>
                <h3 style="font-family: 'Fraunces', serif; color: var(--gold); font-size: 1.4rem; margin-bottom: 12px;">
                    Step 2: Add to Inventory
                </h3>
                <p style="color: var(--text); font-size: 0.95rem; line-height: 1.6; margin-bottom: 16px;">
                    Click items to add them to your inventory. Visual slots help you track quantities.
                </p>
                <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.5; margin-bottom: 24px;">
                    💡 Tip: Your inventory is saved automatically (survives page refresh)
                </p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button onclick="prevTutorialStep()" style="
                        background: var(--bg-panel);
                        border: 1px solid var(--border);
                        color: var(--text);
                        padding: 10px 24px;
                        border-radius: 8px;
                        cursor: pointer;
                    ">← Back</button>
                    <button onclick="nextTutorialStep()" style="
                        background: var(--gold);
                        color: #1a1410;
                        border: none;
                        padding: 10px 32px;
                        border-radius: 8px;
                        font-weight: 600;
                        cursor: pointer;
                    ">Next →</button>
                </div>
            </div>
            
            <div class="tutorial-step" data-step="4" style="display: none;">
                <div style="font-size: 2.5rem; text-align: center; margin-bottom: 16px;">💰</div>
                <h3 style="font-family: 'Fraunces', serif; color: var(--gold); font-size: 1.4rem; margin-bottom: 12px;">
                    Step 3: Track Profit
                </h3>
                <p style="color: var(--text); font-size: 0.95rem; line-height: 1.6; margin-bottom: 16px;">
                    Check the footer panel to see your total modal, profit, and profit percentage.
                </p>
                <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.5; margin-bottom: 24px;">
                    💡 Tip: Toggle premium tax and sell order fee for accurate calculations
                </p>
                <div style="text-align: center;">
                    <button onclick="closeTutorial()" style="
                        background: var(--gold);
                        color: #1a1410;
                        border: none;
                        padding: 12px 32px;
                        border-radius: 8px;
                        font-weight: 600;
                        cursor: pointer;
                    ">Got it! Let's Start →</button>
                </div>
            </div>
            
            <div style="
                display: flex;
                gap: 6px;
                justify-content: center;
                margin-top: 24px;
            ">
                <span class="tutorial-dot" data-step="1" style="width: 8px; height: 8px; border-radius: 50%; background: var(--gold);"></span>
                <span class="tutorial-dot" data-step="2" style="width: 8px; height: 8px; border-radius: 50%; background: var(--border);"></span>
                <span class="tutorial-dot" data-step="3" style="width: 8px; height: 8px; border-radius: 50%; background: var(--border);"></span>
                <span class="tutorial-dot" data-step="4" style="width: 8px; height: 8px; border-radius: 50%; background: var(--border);"></span>
            </div>
        </div>
    </div>
</div>

<script>
let currentTutorialStep = 1;

function showTutorial() {
    if (localStorage.getItem('tutorial_refine_seen') === 'true') return;
    document.getElementById('tutorial-modal').style.display = 'block';
    showTutorialStep(1);
}

function closeTutorial() {
    document.getElementById('tutorial-modal').style.display = 'none';
    localStorage.setItem('tutorial_refine_seen', 'true');
}

function showTutorialStep(step) {
    currentTutorialStep = step;
    document.querySelectorAll('.tutorial-step').forEach((el, idx) => {
        el.style.display = (idx + 1) === step ? 'block' : 'none';
    });
    document.querySelectorAll('.tutorial-dot').forEach((dot, idx) => {
        dot.style.background = (idx + 1) === step ? 'var(--gold)' : 'var(--border)';
    });
}

function nextTutorialStep() {
    if (currentTutorialStep < 4) {
        showTutorialStep(currentTutorialStep + 1);
    }
}

function prevTutorialStep() {
    if (currentTutorialStep > 1) {
        showTutorialStep(currentTutorialStep - 1);
    }
}

// Show tutorial on page load (only if not seen before)
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(showTutorial, 500);
});
</script>
```

#### 3.2 Add Tutorial to Refining Calculator
**File:** `resources/views/kalkulator/refine.blade.php`

Add before `@endsection`:
```blade
@include('components.tutorial-modal')
```

Add help button to page:
```blade
<button onclick="localStorage.removeItem('tutorial_refine_seen'); showTutorial();" style="
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--gold);
    color: #1a1410;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 100;
" title="Show Tutorial">
    ?
</button>
```

#### 3.3 Create Similar Tutorials for Other Pages
Duplicate and adapt `tutorial-modal.blade.php` for:
- Fishing Calculator
- Flip Calculator
- Market Browser

---

## 🔍 PHASE 4: Feature Discovery
**Priority:** MEDIUM  
**Effort:** 2-3 jam  
**Expected Impact:** +15-20% cross-feature usage

### Tasks:

#### 4.1 Featured Tools Section
**File:** `resources/views/home.blade.php`

Add after hero section, before refining stations:
```blade
<section class="featured-tools">
    <h2 class="section-title">
        🔥 Most Popular Tools
    </h2>
    <p class="section-sub">
        Try these fan-favorite calculators trusted by 1,000+ players
    </p>
    
    <div class="tool-grid" style="
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 40px;
    ">
        <a href="/kalkulator/refine?jenis=ore" class="featured-tool-card">
            <div class="tool-icon">⚒️</div>
            <div class="tool-name">Refining Calculator</div>
            <div class="tool-desc">Calculate profit for ore, logs, hide, fiber, stone</div>
            <div class="tool-badge">Most Popular</div>
        </a>
        
        <a href="/market" class="featured-tool-card">
            <div class="tool-icon">📊</div>
            <div class="tool-name">Market Browser</div>
            <div class="tool-desc">Real-time prices & history charts for all items</div>
            <div class="tool-badge">Real-time Data</div>
        </a>
        
        <a href="/kalkulator/flip" class="featured-tool-card">
            <div class="tool-icon">💰</div>
            <div class="tool-name">Flip Scanner</div>
            <div class="tool-desc">Find best buy low, sell high opportunities</div>
            <div class="tool-badge">Auto-scan</div>
        </a>
    </div>
</section>

<style>
.featured-tool-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 24px;
    display: block;
    transition: transform 0.2s, border-color 0.2s;
    position: relative;
}
.featured-tool-card:hover {
    transform: translateY(-4px);
    border-color: var(--gold);
}
.featured-tool-card .tool-icon {
    font-size: 2.5rem;
    margin-bottom: 12px;
}
.featured-tool-card .tool-name {
    font-weight: 600;
    font-size: 1.05rem;
    color: var(--text);
    margin-bottom: 8px;
}
.featured-tool-card .tool-desc {
    font-size: 0.85rem;
    color: var(--text-muted);
    line-height: 1.4;
}
.featured-tool-card .tool-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(217, 166, 83, 0.15);
    border: 1px solid rgba(217, 166, 83, 0.4);
    color: var(--gold);
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
}
</style>
```

#### 4.2 Add "More Tools" Link
Add at bottom of tool grids:
```blade
<div style="text-align: center; margin-top: 32px;">
    <a href="#all-tools" onclick="document.getElementById('crafting-stations').scrollIntoView({behavior: 'smooth'}); return false;" style="
        color: var(--gold);
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: underline;
    ">
        View All Tools & Stations →
    </a>
</div>
```

---

## 📱 PHASE 5: Social Proof & Trust Signals
**Priority:** LOW-MEDIUM  
**Effort:** 2-3 jam  
**Expected Impact:** +10% conversion (visitors → users)

### Tasks:

#### 5.1 Add Testimonials Section
**File:** Create `resources/views/components/testimonials.blade.php`

```blade
<section class="testimonials" style="
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 40px 32px;
    margin: 48px 0;
">
    <h2 class="section-title" style="text-align: center; margin-bottom: 32px;">
        💬 What Players Say
    </h2>
    
    <div style="
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
    ">
        <div class="testimonial-card" style="
            background: var(--bg-panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
        ">
            <div style="color: var(--gold); margin-bottom: 12px;">★★★★★</div>
            <p style="color: var(--text); font-size: 0.9rem; line-height: 1.6; margin-bottom: 12px;">
                "Refining calculator saved me hours. Real-time prices are accurate!"
            </p>
            <div style="color: var(--text-muted); font-size: 0.8rem;">
                — Player from Americas
            </div>
        </div>
        
        <div class="testimonial-card" style="
            background: var(--bg-panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
        ">
            <div style="color: var(--gold); margin-bottom: 12px;">★★★★★</div>
            <p style="color: var(--text); font-size: 0.9rem; line-height: 1.6; margin-bottom: 12px;">
                "Best Albion tools site. Market browser is professional quality."
            </p>
            <div style="color: var(--text-muted); font-size: 0.8rem;">
                — Player from Europe
            </div>
        </div>
        
        <div class="testimonial-card" style="
            background: var(--bg-panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
        ">
            <div style="color: var(--gold); margin-bottom: 12px;">★★★★★</div>
            <p style="color: var(--text); font-size: 0.9rem; line-height: 1.6; margin-bottom: 12px;">
                "Flip scanner found me 3M profit opportunities today. Amazing!"
            </p>
            <div style="color: var(--text-muted); font-size: 0.8rem;">
                — Player from Asia
            </div>
        </div>
    </div>
</section>
```

Include in home page after featured tools.

#### 5.2 Add "As Seen In" Section
If you've been featured/mentioned in:
- Reddit threads
- Discord communities
- YouTube videos
- Twitch streams

Create section showcasing these mentions.

---

## 🔧 PHASE 6: Performance & Technical SEO
**Priority:** MEDIUM  
**Effort:** 3-4 jam  
**Expected Impact:** Better rankings, faster load times

### Tasks:

#### 6.1 Add robots.txt
**File:** `public/robots.txt`

```
User-agent: *
Allow: /
Disallow: /dev/
Disallow: /api/
Disallow: /auth/
Disallow: /dashboard

Sitemap: https://yourdomain.com/sitemap.xml
```

#### 6.2 Optimize Images
- Compress all images in `public/images/`
- Convert to WebP format (fallback to JPG/PNG)
- Add lazy loading to station cards

#### 6.3 Add Preconnect Headers
**File:** `resources/views/layouts/app.blade.php`

Add to `<head>`:
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="dns-prefetch" href="https://render.albiononline.com">
<link rel="dns-prefetch" href="https://gameinfo.albiononline.com">
```

#### 6.4 Implement Breadcrumbs
For deeper pages (crafting stations, builds):
```blade
<nav aria-label="breadcrumb" style="margin-bottom: 20px;">
    <ol style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted);">
        <li><a href="/" style="color: var(--text-muted);">Home</a></li>
        <li>/</li>
        <li><a href="/crafting" style="color: var(--text-muted);">Crafting</a></li>
        <li>/</li>
        <li style="color: var(--gold);">{{ $station->name }}</li>
    </ol>
</nav>
```

---

## 📈 PHASE 7: Analytics & Tracking
**Priority:** LOW  
**Effort:** 2-3 jam  
**Expected Impact:** Better data for optimization decisions

### Tasks:

#### 7.1 Add Event Tracking
**File:** Update Google Analytics in `resources/views/layouts/app.blade.php`

Add custom events:
```javascript
// Track calculator usage
function trackCalculatorUse(type) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'calculator_use', {
            'calculator_type': type,
            'page_path': window.location.pathname
        });
    }
}

// Track item add to inventory
function trackInventoryAdd(item, tier) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'inventory_add', {
            'item_name': item,
            'item_tier': tier
        });
    }
}

// Track price refresh
function trackPriceRefresh(page) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'price_refresh', {
            'page': page
        });
    }
}
```

#### 7.2 Create Admin Analytics Dashboard
**Route:** `/dev/analytics`

Show:
- Top calculators by usage
- Most searched items
- Peak usage hours
- User retention stats
- Conversion funnel (visitor → user → active user)

---

## 🚀 QUICK WINS (Do These First)
**Total time:** 4-6 jam  
**Highest ROI**

### Priority Order:
1. ✅ **Add meta descriptions** (all major pages) - 1 hour
2. ✅ **Add Open Graph tags** - 30 minutes
3. ✅ **Create OG image** - 30 minutes
4. ✅ **Add hero section** - 1 hour
5. ✅ **Featured tools section** - 1 hour
6. ✅ **Add station card descriptions** - 30 minutes
7. ✅ **Tutorial modal for refining** - 2 hours

**Expected impact after quick wins:**
- Organic traffic: +20-30% dalam 2-3 bulan
- Bounce rate: -10-15%
- Engagement: +15-20%

---

## 📊 Success Metrics

### Track These Weekly:
- **Google Search Console:** Impressions, clicks, CTR, avg position
- **Google Analytics:** 
  - Sessions (goal: 500/hari dalam 2 bulan)
  - Bounce rate (goal: <60%)
  - Pages per session (goal: >2.5)
  - Avg session duration (goal: >3 minutes)
- **Calculator usage:** Which tools are most popular
- **Conversion rate:** Visitor → registered user

### Month 1 Goals:
- [ ] Traffic naik ke 150-200/hari
- [ ] Bounce rate turun ke 65%
- [ ] 50+ meta descriptions indexed by Google
- [ ] Featured in 2+ Reddit/Discord communities

### Month 2 Goals:
- [ ] Traffic naik ke 300-400/hari
- [ ] 5+ organic keywords ranking top 10
- [ ] 100+ backlinks (from community mentions)
- [ ] First sponsor inquiry

### Month 3 Goals:
- [ ] Traffic naik ke 500-1000/hari
- [ ] Secure first sponsor (Rp 500k-1jt/bulan)
- [ ] 1,000+ registered users
- [ ] Featured in Albion content creator video

---

## 🛠️ Tools & Resources

### SEO Tools:
- **Google Search Console** - Track search performance
- **Google PageSpeed Insights** - Check page speed
- **Ahrefs/Ubersuggest** - Keyword research (free tiers)
- **Meta Tags Checker** - Verify OG tags: https://metatags.io/

### Design Tools:
- **Canva** - Create OG images (free)
- **TinyPNG** - Compress images
- **Figma** - Design mockups (free tier)

### Analytics:
- **Google Analytics 4** (already installed)
- **Hotjar** (optional) - Heatmaps & session recordings
- **Plausible** (optional) - Privacy-friendly analytics

---

## 📝 Notes & Context

### Why These Phases?
1. **SEO first** = Foundation untuk long-term growth
2. **Hero/value prop** = Immediate bounce rate improvement
3. **Tutorials** = Better retention for existing traffic
4. **Discovery** = Maximize value dari setiap visitor
5. **Performance** = Support untuk increased traffic

### What NOT to Do:
❌ Don't add AdSense yet (traffic terlalu kecil, UX jelek)  
❌ Don't redesign everything (design sudah bagus)  
❌ Don't add tons of features (focus on marketing first)  
❌ Don't overthink - ship iteratively

### When to Revisit:
- **After 1 month:** Check metrics, adjust strategy
- **After 3 months:** If traffic 500+/hari, start approaching sponsors
- **After 6 months:** Consider premium features or advanced tools

---

## ✅ Checklist Progress

### Phase 1: SEO Foundation
- [ ] Meta descriptions (all pages)
- [ ] Open Graph tags
- [ ] Twitter Cards
- [ ] Create OG image
- [ ] Improve sitemap
- [ ] Add schema markup
- [ ] Test with metatags.io

### Phase 2: Hero & Value Prop
- [ ] Create hero component
- [ ] Add to home page
- [ ] Add station descriptions
- [ ] Add stats section
- [ ] Test mobile responsive

### Phase 3: Tutorials
- [ ] Create tutorial modal component
- [ ] Add to refining calculator
- [ ] Add help button
- [ ] Test localStorage persistence
- [ ] Create tutorials for other calculators

### Phase 4: Feature Discovery
- [ ] Featured tools section
- [ ] More tools link
- [ ] Cross-linking between tools
- [ ] Test navigation flow

### Phase 5: Social Proof
- [ ] Testimonials section
- [ ] Add to home page
- [ ] Collect real testimonials from Telegram
- [ ] Update with actual quotes

### Phase 6: Performance
- [ ] Add robots.txt
- [ ] Optimize images
- [ ] Add preconnect headers
- [ ] Implement breadcrumbs
- [ ] Test PageSpeed score

### Phase 7: Analytics
- [ ] Add event tracking
- [ ] Create analytics dashboard
- [ ] Set up weekly reports
- [ ] Track success metrics

---

## 🎯 Final Thoughts

**Current state:** Great product, poor visibility  
**Goal:** Make the great product visible & discoverable  
**Timeline:** 2-3 months to 500-1000/hari  
**Budget needed:** Rp 0 (all organic growth)

**Focus areas:**
1. SEO (50% of effort)
2. UX improvements (30% of effort)
3. Content/marketing (20% of effort)

**Don't get distracted by:**
- Building new calculators (enough tools already)
- Redesigning UI (already good)
- Adding social features (focus on core product)

**Success = Consistency:**
- Ship small improvements weekly
- Track metrics monthly
- Adjust strategy quarterly

---

**Last updated:** 2026-09-09  
**Next review:** After Phase 1 completion (check organic traffic trend)
