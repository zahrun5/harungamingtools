# 🛠️ Crafting Calculator - Advance Mode Plan

**Status:** Planning Phase  
**Priority:** High  
**Estimated Time:** 6 hours (3 sessions × 2 hours)  
**Complexity:** ⭐⭐⭐⭐⭐ (Very High)  
**Impact:** ⭐⭐⭐⭐☆ (High)

---

## 📋 Current State

### Simple Mode (Existing ✅):
**User Flow:**
1. User pilih item/equipment yang mau di-craft
2. Klik item → muncul pop-up resources yang dibutuhkan
3. User masukkan resources + jumlah ke inventory
4. Klik tombol crafting
5. Calculate profit

**Good for:** Casual users, one-off calculations

---

### Advance Mode (To Build ⏳):
**User Flow:**
1. User pilih **Crafting Station** (Mage Tower, Hunter's Lodge, dll)
2. System tampilkan **ALL resources** yang relevan untuk station tersebut
3. User klik resources dari list → masuk inventory (harga + qty)
4. System **auto-detect** item/equipment apa yang bisa di-craft
5. Muncul **tombol bergambar item** yang memenuhi syarat
6. User klik tombol → craft → profit calculation

**Good for:** Power users, batch crafting, resource optimization

**Similar to:** Refining Calculator workflow

---

## 🎯 Requirements

### Functional Requirements:

#### 1. Station Selection
- Dropdown/filter untuk pilih crafting station
- Stations available:
  - Mage's Tower
  - Hunter's Lodge
  - Warrior's Forge
  - Toolmaker
  - Saddler
  - Alchemist's Lab
  - Cook
  - (dan lainnya sesuai game)

#### 2. Resource List Display
**Challenge:** Beda station = beda resources

**Mage's Tower contoh:**
- Metal Bars (T2-T8, enchant 0-4)
- Leather (T2-T8, enchant 0-4)
- Cloth (T2-T8, enchant 0-4)
- **Artifacts:** Cursed Skull, Occult Tome, etc.
- **Runes:** For enchanting
- **Souls:** Guardian/Siphoned Energy

**Hunter's Lodge contoh:**
- Wood Planks (T2-T8, enchant 0-4)
- Leather (T2-T8, enchant 0-4)
- Cloth (T2-T8, enchant 0-4)
- **Artifacts:** Ghoul Heart, Werewolf Pelt, etc.
- **Runes:** For enchanting
- **Souls:** Guardian/Siphoned Energy

**Warrior's Forge contoh:**
- Metal Bars (T2-T8, enchant 0-4)
- Leather (T2-T8, enchant 0-4)
- **Artifacts:** Knight's Crest, Royal Sigil, etc.
- **Runes:** For enchanting
- **Souls:** Guardian/Siphoned Energy

#### 3. Inventory System (Similar to Refining)
- Grid layout (5 columns)
- Add item: harga + quantity
- Edit/delete items
- Visual icons
- Total modal calculation

#### 4. Recipe Matching (Auto-detect)
**Algorithm:**
```
FOR each recipe in selected_station:
  IF all_required_materials_in_inventory:
    IF quantities_sufficient:
      SHOW craft_button(item)
```

**Example:**
- Inventory has: T4 Metal Bar (200), T4 Leather (100), T4 Cloth (50)
- Recipe T4 Soldier Helmet needs: Metal (16), Leather (8)
- ✅ Show "T4 Soldier Helmet" button (can craft 12x max)

#### 5. Craft Buttons (Dynamic)
- Grid of craftable items
- Show item icon + name
- Show max quantity craftable
- Click → calculate exact craft amount
- Profit/loss calculation

#### 6. Material Types to Handle
- **Base materials:** Metal, Leather, Wood, Cloth, Stone
- **Artifacts:** Faction-specific (20+ types)
- **Runes:** T4-T8 runes for enchanting
- **Souls:** Guardian Essence, Siphoned Energy
- **Hearts:** Avalonian Hearts (T4-T8)
- **Crystal:** Faded/Corrupted for royal gear
- **Focus:** Optional for resource return

---

## 🗂️ Data Structure

### Station Configuration:
```php
$stationResources = [
    'mage-tower' => [
        'base' => ['metal', 'leather', 'cloth'],
        'artifacts' => ['T4_CURSEDSKULL', 'T4_CURSEDSTAFF_AVALON', ...],
        'runes' => ['T4_RUNE', 'T5_RUNE', ...],
        'souls' => ['T4_SOUL', 'T5_SOUL', ...]
    ],
    'hunters-lodge' => [
        'base' => ['wood', 'leather', 'cloth'],
        'artifacts' => ['T4_GHOULHEART', 'T4_WEREWOLFPELT', ...],
        'runes' => ['T4_RUNE', 'T5_RUNE', ...],
        'souls' => ['T4_SOUL', 'T5_SOUL', ...]
    ],
    // ... other stations
];
```

### Recipe Structure:
```php
$recipes = [
    'T4_HEAD_PLATE_SET1' => [ // Soldier Helmet
        'station' => 'warriors-forge',
        'requirements' => [
            'T4_METALBAR' => 16,
            'T4_LEATHER' => 8
        ],
        'output' => 'T4_HEAD_PLATE_SET1',
        'quantity' => 1
    ],
    'T4_HEAD_PLATE_SET1@1' => [ // Soldier Helmet .1
        'station' => 'warriors-forge',
        'requirements' => [
            'T4_METALBAR' => 16,
            'T4_LEATHER' => 8,
            'T4_RUNE' => 1
        ],
        'output' => 'T4_HEAD_PLATE_SET1@1',
        'quantity' => 1
    ],
    // ... hundreds of recipes
];
```

---

## 🏗️ Implementation Plan

### Session 1: Foundation (2 hours)

#### Task 1.1: Explore Existing Code (30 min)
- Read `resources/views/kalkulator/crafting.blade.php`
- Understand current simple mode structure
- Identify reusable components
- Check existing API/data sources

#### Task 1.2: Setup Station Filter (30 min)
- Add station dropdown (similar to refining material filter)
- CSS styling matching calculator theme
- JavaScript toggle logic
- localStorage save station preference

**Files to modify:**
- `resources/views/kalkulator/crafting.blade.php`
- Inline JavaScript

#### Task 1.3: Design Data Structure (30 min)
- Define station resource mappings
- Plan recipe data format
- Decide: Database vs JSON file vs API?
- Create migration if needed

**Files to create:**
- `database/migrations/YYYY_MM_DD_crafting_recipes.php` (if DB)
- OR `storage/app/recipes.json` (if JSON)

#### Task 1.4: Build Resource List per Station (30 min)
- Query/filter resources by station
- Display in item list format
- Icons + names + prices (from API)
- Scrollable list

**Expected Output Session 1:**
- ✅ Station selection working
- ✅ Resource list showing per station
- ✅ Data structure defined
- ⏳ Inventory + matching (next session)

---

### Session 2: Inventory + Display (2 hours)

#### Task 2.1: Build Inventory System (45 min)
- Grid layout (5 columns, similar to refining)
- Add item modal (price + quantity)
- Edit/delete functionality
- Visual display with icons
- localStorage persistence

**Reuse from refining:**
- Slot structure
- Modal popup
- Grid CSS

#### Task 2.2: Inventory State Management (30 min)
- JavaScript object to track inventory
- Add/update/remove functions
- Calculate total modal (cost)
- Sync with UI

#### Task 2.3: Recipe Data Loading (30 min)
- Load all recipes for selected station
- Parse recipe requirements
- Cache in memory for performance

#### Task 2.4: Basic UI Polish (15 min)
- Responsive layout
- Loading states
- Empty states
- Error handling

**Expected Output Session 2:**
- ✅ Inventory system working
- ✅ Can add/edit/delete items
- ✅ Recipe data loaded
- ⏳ Auto-detect + craft buttons (next session)

---

### Session 3: Recipe Matching + Craft (2 hours)

#### Task 3.1: Recipe Matching Algorithm (45 min)
```javascript
function findCraftableItems(inventory, recipes) {
    let craftable = [];
    
    for (let recipe of recipes) {
        let canCraft = true;
        let maxQuantity = Infinity;
        
        for (let [material, required] of Object.entries(recipe.requirements)) {
            let inInventory = inventory[material] || 0;
            
            if (inInventory < required) {
                canCraft = false;
                break;
            }
            
            maxQuantity = Math.min(maxQuantity, Math.floor(inInventory / required));
        }
        
        if (canCraft) {
            craftable.push({
                item: recipe.output,
                maxQty: maxQuantity
            });
        }
    }
    
    return craftable;
}
```

#### Task 3.2: Craft Buttons Display (30 min)
- Grid of craftable items
- Item icon + name
- "Max: X" label
- Click handler

**Similar to refining:** "Refine buttons" section

#### Task 3.3: Craft Action + Calculation (30 min)
- User clicks craft button
- Modal: "Craft how many?"
- Calculate:
  - Materials used
  - Materials remaining
  - Result value (from market prices)
  - Tax deduction
  - Profit/loss
- Display results

#### Task 3.4: Final Polish + Testing (15 min)
- Test all flows
- Fix bugs
- Responsive check
- Clear cache

**Expected Output Session 3:**
- ✅ Auto-detect working
- ✅ Craft buttons showing
- ✅ Profit calculation working
- ✅ **FEATURE COMPLETE!**

---

## 🧩 Technical Challenges

### Challenge 1: Recipe Data Source
**Problem:** Hundreds of recipes, need accurate data

**Solutions:**
1. **Parse from items.xml** (game files)
   - Pro: Accurate, comprehensive
   - Con: Complex parsing
   
2. **Manual JSON file**
   - Pro: Simple, controlled
   - Con: Tedious, error-prone
   
3. **Use existing API/database**
   - Pro: If already exists, easy
   - Con: Need to check if available

**Recommended:** Check if recipe data already exists in system, else parse items.xml

---

### Challenge 2: Artifact Mapping
**Problem:** Each station has different artifacts

**Solution:**
```php
$stationArtifacts = [
    'mage-tower' => [
        'T4_CURSED' => ['CURSEDSKULL', 'CURSEDSTAFF_AVALON'],
        'T4_MORGANA' => ['RITUAL_MORGANA_AVALON'],
        // ...
    ],
    'hunters-lodge' => [
        'T4_KEEPER' => ['GHOULHEART', 'WOLF_KEEPER_AVALON'],
        'T4_NATURE' => ['TREEHEART_KEEPER_AVALON'],
        // ...
    ]
];
```

---

### Challenge 3: Enchantment Variations
**Problem:** Each item has 5 variants (.0, .1, .2, .3, .4)

**Solution:**
- Base recipe shows .0 (no enchant)
- User can toggle enchant level
- Rune requirements adjust automatically
- OR: Show all 5 as separate buttons (simpler but cluttered)

**Recommended:** Start with showing all 5 separately (simpler MVP)

---

### Challenge 4: Performance
**Problem:** Hundreds of recipes to check on each inventory change

**Solution:**
- Debounce recipe matching (wait 500ms after inventory change)
- Cache craftable items
- Only recalculate on inventory change
- Lazy load recipes (only for selected station)

---

## 🎨 UI/UX Design

### Layout (Desktop):
```
┌─────────────────────────────────────────────────┐
│  [Station Dropdown ▼]  [Search...]     [?]      │
├──────────────────────┬──────────────────────────┤
│  RESOURCE LIST       │  INVENTORY (Grid 5x?)    │
│  ┌────────────────┐  │  ┌──┬──┬──┬──┬──┐       │
│  │ T4 Metal Bar   │  │  │  │  │  │  │  │       │
│  │ 💰 1,250       │  │  └──┴──┴──┴──┴──┘       │
│  └────────────────┘  │                          │
│  ┌────────────────┐  │  CRAFTABLE ITEMS         │
│  │ T4 Leather     │  │  ┌────┬────┬────┐       │
│  │ 💰 850         │  │  │ 🛡️ │ ⚔️ │ 👕 │       │
│  └────────────────┘  │  │Max │Max │Max │       │
│  ...                 │  │ 12 │  8 │ 15 │       │
│                      │  └────┴────┴────┘       │
│                      │                          │
│                      │  PROFIT CALCULATION      │
│                      │  Modal: 12,500           │
│                      │  Result: 18,750          │
│                      │  Profit: +6,250 ✅       │
└──────────────────────┴──────────────────────────┘
```

### Layout (Mobile):
```
┌──────────────────────┐
│ [Station ▼] [Search] │
├──────────────────────┤
│ 📦 INVENTORY (tap)   │
│ ┌──┬──┬──┬──┬──┐    │
│ │  │  │  │  │  │    │
│ └──┴──┴──┴──┴──┘    │
├──────────────────────┤
│ RESOURCE LIST        │
│ ┌────────────────┐   │
│ │ T4 Metal Bar   │   │
│ └────────────────┘   │
├──────────────────────┤
│ CRAFTABLE ITEMS      │
│ ┌────┬────┬────┐    │
│ │ 🛡️ │ ⚔️ │ 👕 │    │
│ └────┴────┴────┘    │
└──────────────────────┘
```

---

## 📊 Success Metrics

### User Engagement:
- Time spent on crafting calculator: +30%
- Crafting calculations per session: +50%
- Return visits for crafting: +25%

### Feature Usage:
- % users trying advance mode: >40%
- Average items in inventory: 8-12
- Average craft calculations: 3-5 per session

### Business Impact:
- Overall calculator usage: +20%
- User retention: +15%
- Power user satisfaction: High

---

## 🚨 Risks & Mitigation

### Risk 1: Recipe Data Incomplete
**Impact:** Wrong calculations, user frustration  
**Mitigation:** 
- Start with popular items only (MVP)
- Add "Report error" button
- Community contribution system

### Risk 2: Performance Issues
**Impact:** Slow UI, bad UX  
**Mitigation:**
- Optimize algorithm (debounce, cache)
- Lazy load
- Consider Web Worker for heavy computation

### Risk 3: Complexity Overwhelms Users
**Impact:** Users prefer simple mode  
**Mitigation:**
- Clear mode toggle (Simple/Advance)
- Tutorial for advance mode
- Save user preference

### Risk 4: Maintenance Burden
**Impact:** Game updates break calculator  
**Mitigation:**
- Modular recipe data (easy to update)
- Automated tests
- Version tracking

---

## ✅ Definition of Done

**Advance mode is complete when:**
- [x] User can select crafting station
- [x] Resource list shows correctly per station
- [x] User can add resources to inventory
- [x] System auto-detects craftable items
- [x] Craft buttons appear for valid items
- [x] Clicking craft shows profit calculation
- [x] Responsive on mobile
- [x] localStorage saves state
- [x] No major bugs
- [x] Basic tutorial added (optional)

---

## 📚 Reference Materials

### Similar Implementations:
- Refining Calculator (`/kalkulator/refine`) - Similar workflow
- Market Browser (`/market`) - Item list + filters
- Existing Crafting Simple - Foundation code

### Data Sources:
- Items.xml (game files) - Recipe data
- Albion Data Project API - Market prices
- Item localizations - Names in 7 languages

### Design Reference:
- Calculator parchment theme
- Medieval color scheme (gold, brown)
- Grid layouts (5 columns)
- Modal popups

---

## 🎯 Next Steps When Starting

**Session 1 Start:**
1. Read this plan thoroughly
2. Explore existing crafting simple code
3. Setup station filter UI
4. Begin data structure design

**Ask yourself:**
- Do we have recipe data already?
- Can we reuse refining calculator code?
- What's the minimum viable version?

**Remember:**
- MVP first, polish later
- Test frequently
- Commit after each task
- Document decisions

---

**Status:** Ready to implement  
**Start Date:** TBD (when ready)  
**Estimated Completion:** 3 sessions from start  
**Last Updated:** 2026-09-09 17:33 UTC
