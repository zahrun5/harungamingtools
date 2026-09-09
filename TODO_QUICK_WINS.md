# 🚀 Quick Wins Checklist
**Focus on these HIGH-ROI tasks first**  
**Total time: 4-6 hours**  
**Expected impact: +30-50% organic traffic dalam 2-3 bulan**

---

## ✅ Week 1: SEO Foundation (CRITICAL)

### Day 1-2: Meta Tags (2 hours)
- [ ] Edit `resources/views/layouts/app.blade.php`
  - [ ] Add `@yield('meta_description', '')` to `<head>`
  - [ ] Add `@yield('meta_keywords', '')` to `<head>`
  - [ ] Add Open Graph tags (og:title, og:description, og:image, og:url)
  - [ ] Add Twitter Card tags

- [ ] Create `public/images/og-default.jpg` (1200x630px)
  - Use Canva: https://canva.com
  - Template: Social Media → Facebook Post
  - Text: "Albion Online Tools - Free Calculators"

- [ ] Update these blade files with meta sections:
  - [ ] `resources/views/home.blade.php`
  - [ ] `resources/views/kalkulator/refine.blade.php`
  - [ ] `resources/views/market/index.blade.php`
  - [ ] `resources/views/kalkulator/fishing.blade.php`
  - [ ] `resources/views/kalkulator/flip.blade.php`
  - [ ] `resources/views/crafting/station.blade.php`

**Copy-paste ready meta tags:**
```blade
@section('title', 'Your Page Title Here')
@section('meta_description', 'Your 150-160 character description here')
@section('meta_keywords', 'keyword1, keyword2, keyword3')
@section('og_description', 'Social media description here')
```

### Day 3: Schema & Sitemap (1 hour)
- [ ] Add schema markup to `resources/views/home.blade.php`
  - Copy from IMPROVEMENT_ROADMAP.md section 1.6

- [ ] Improve sitemap route in `routes/web.php`
  - Add priorities, change frequencies, last modified

- [ ] Create `public/robots.txt`

### Day 4: Verify & Test
- [ ] Test Open Graph tags: https://metatags.io
- [ ] Submit sitemap to Google Search Console
- [ ] Check mobile-friendliness: https://search.google.com/test/mobile-friendly

---

## ✅ Week 2: Hero Section & Value Prop (1.5 hours)

### Day 1: Hero Component
- [ ] Create `resources/views/components/hero.blade.php`
  - Copy from IMPROVEMENT_ROADMAP.md section 2.1

- [ ] Include in `resources/views/home.blade.php`:
  ```blade
  @include('components.hero')
  ```

### Day 2: Stats Section
- [ ] Create `resources/views/components/stats.blade.php`
  - Copy from IMPROVEMENT_ROADMAP.md section 2.3

- [ ] Include in home page after hero

### Day 3: Station Descriptions
- [ ] Edit `resources/views/home.blade.php`
  - Add 1-liner descriptions to station cards
  - Copy logic from IMPROVEMENT_ROADMAP.md section 2.2

---

## ✅ Week 3: Featured Tools & Discovery (1 hour)

### Day 1: Featured Tools Section
- [ ] Add featured tools grid to `resources/views/home.blade.php`
  - Copy from IMPROVEMENT_ROADMAP.md section 4.1
  - Place after hero, before refining stations

### Day 2: Test & Refine
- [ ] Test on mobile
- [ ] Check all links work
- [ ] Verify responsive layout

---

## ✅ Week 4: Tutorial Modal (2 hours)

### Day 1: Create Tutorial Component
- [ ] Create `resources/views/components/tutorial-modal.blade.php`
  - Copy from IMPROVEMENT_ROADMAP.md section 3.1

### Day 2: Add to Refining Calculator
- [ ] Edit `resources/views/kalkulator/refine.blade.php`
  - Include tutorial modal
  - Add floating help button (?)

### Day 3: Test Tutorial Flow
- [ ] Clear localStorage
- [ ] Test tutorial appears on first visit
- [ ] Test "Don't show again" works
- [ ] Test help button reopens tutorial

---

## 📊 After 4 Weeks: Check Metrics

### Google Search Console
- [ ] Impressions trend (should be up)
- [ ] Click-through rate (should improve)
- [ ] Which queries showing up
- [ ] Any errors to fix

### Google Analytics
- [ ] Sessions per day (baseline vs now)
- [ ] Bounce rate (should decrease)
- [ ] Pages per session (should increase)
- [ ] Avg session duration

### Goals for Month 1:
- Traffic: 80/day → 150-200/day ✅
- Bounce rate: ??% → <70%
- Indexed pages in Google: 10+ pages with proper meta

---

## 🎯 Priority If You Only Have 2 Hours

Do these in order:
1. **Meta descriptions** (30 min) - Home, Refine, Market pages
2. **Open Graph tags** (20 min) - Layout file
3. **Create OG image** (20 min) - Canva
4. **Hero section** (30 min) - Home page
5. **Featured tools** (20 min) - Home page

**Skip for now:** Tutorials (nice to have, not critical)

---

## 💡 Tips

- **Work in small batches** - Don't try to do everything in 1 day
- **Test after each change** - Make sure nothing breaks
- **Use git commits** - Commit after each completed task
- **Check mobile** - 70% of users are on mobile
- **Don't overthink** - Ship it, iterate later

---

## 📝 Quick References

### Meta Description Formula:
`[Action verb] [benefit] for [target audience]. [Feature 1], [feature 2], [trust signal].`

Example:
"Calculate refining profit for Albion Online. Real-time prices, return rate calculator, trusted by 1000+ players."

### Good OG Image:
- Size: 1200x630px
- Dark background (your medieval theme)
- Big readable text
- Logo/branding
- No tiny details

### Schema Markup:
- Type: WebApplication
- Include: name, description, offers (free)
- Optional: aggregateRating (if you have reviews)

---

## ✅ Done? Next Steps

After completing all quick wins:
1. Wait 2-4 weeks
2. Check Google Search Console for improvements
3. If traffic increasing → Continue with IMPROVEMENT_ROADMAP.md Phase 3-7
4. If traffic stagnant → Focus on content marketing (Reddit, Discord, YouTube)

---

**Remember:** SEO takes time. Don't expect results overnight. But these changes WILL compound over 2-3 months.

**Questions?** Check IMPROVEMENT_ROADMAP.md for detailed instructions.
