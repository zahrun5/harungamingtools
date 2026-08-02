<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BuildController extends Controller
{
    // daftar equipment_slot yang valid, buat whitelist parameter ?slot= di searchItems()
    private const VALID_SLOTS = [
        'MainHand', 'OffHand', 'Head', 'Armor', 'Shoes',
        'Cape', 'Bag', 'Mount', 'Potion', 'Food',
    ];

    // slot => nama kolom, dipakai buat validasi & whitelist field di updateSlot()
    private const FIELD_MAP = [
        'MainHand' => 'main_hand_id', 'OffHand' => 'off_hand_id', 'Head' => 'head_id',
        'Armor' => 'armor_id', 'Shoes' => 'shoes_id', 'Cape' => 'cape_id',
        'Bag' => 'bag_id', 'Mount' => 'mount_id', 'Potion' => 'potion_id', 'Food' => 'food_id',
    ];

    // daftar semua build milik user yang login
    public function index()
    {
        $builds = Auth::user()->builds()->with(Build::SLOT_RELATIONS)->latest()->get();

        return view('builds.index', compact('builds'));
    }

    public function create()
    {
        return view('builds.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateBuild($request);

        // build pertama yang dibuat user otomatis jadi aktif (tampil di profil)
        $isFirstBuild = Auth::user()->builds()->doesntExist();

        $build = Auth::user()->builds()->create($validated + ['is_active' => $isFirstBuild]);

        return redirect()->route('builds.index')
            ->with('success', "Build \"{$build->name}\" berhasil dibuat!");
    }

    public function edit(Build $build)
    {
        $this->authorizeOwner($build);

        return view('builds.edit', compact('build'));
    }

    public function update(Request $request, Build $build)
    {
        $this->authorizeOwner($build);

        $validated = $this->validateBuild($request);
        $build->update($validated);

        return redirect()->route('builds.index')
            ->with('success', 'Build berhasil diupdate!');
    }

    public function destroy(Build $build)
    {
        $this->authorizeOwner($build);

        $wasActive = $build->is_active;
        $build->delete();

        // kalau yang dihapus itu build aktif, otomatis pindahin status aktif
        // ke build lain yang masih tersisa (kalau ada), biar profil gak kosong
        if ($wasActive) {
            Auth::user()->builds()->oldest()->first()?->update(['is_active' => true]);
        }

        return redirect()->route('builds.index')
            ->with('success', 'Build berhasil dihapus.');
    }

    // jadiin $build sebagai satu-satunya build yang tampil di profil publik
    public function activate(Build $build)
    {
        $this->authorizeOwner($build);

        DB::transaction(function () use ($build) {
            Auth::user()->builds()->where('id', '!=', $build->id)->update(['is_active' => false]);
            $build->update(['is_active' => true]);
        });

        return back()->with('success', "Build \"{$build->name}\" sekarang tampil di profil.");
    }

    /**
     * Endpoint AJAX buat update SATU slot aja, dipanggil dari popup di profile/show.blade.php
     * (builds/_paperdoll.blade.php) — biar user bisa ganti item langsung dari halaman profil
     * tanpa perlu buka halaman edit build. Sekarang butuh {build} di URL karena user bisa
     * punya banyak build.
     *
     * PATCH /builds/{build}/slot  body: { field: 'head_id', item_id: 123|null }
     */
    public function updateSlot(Request $request, Build $build)
    {
        $this->authorizeOwner($build);

        $validated = $request->validate([
            'field' => 'required|string|in:' . implode(',', self::FIELD_MAP),
            'item_id' => 'nullable|integer|exists:items,id',
            'quality' => 'nullable|integer|min:1|max:5',
        ]);

        $qualities = $build->qualities ?? [];

        if ($validated['item_id'] === null) {
            // slot dikosongin -> quality tersimpannya juga dibuang, biar gak nyangkut
            unset($qualities[$validated['field']]);
        } else {
            $qualities[$validated['field']] = $validated['quality'] ?? 1;
        }

        $build->update([
            $validated['field'] => $validated['item_id'],
            'qualities' => $qualities,
        ]);

        return response()->json(['success' => true]);
    }

    // Pola token di api_id buat deteksi equipment slot, karena tabel `categories`
    // TERNYATA gak punya kolom equipment_slot sama sekali (asumsi kode lama salah).
    // Pola divalidasi dari sample data asli di DB, bukan tebakan generik:
    // - MainHand: token '2H' (termasuk senjata artefak T4_ARTEFACT_2H_...) atau 'MAIN'
    // - OffHand: token 'OFF' (shield/book/torch)
    // - Cape & Bag: dua bentuk beda — item dasar (T2_CAPE / T2_BAG, PERSIS di akhir
    //   string) dan varian premium (T4_CAPEITEM_... / T4_BACKPACK_...)
    // - Mount: token 'MOUNT' — sengaja BUKAN cuma substring biasa, karena
    //   T1_FACTION_MOUNTAIN_TOKEN_1 mengandung "MOUNTAIN" yang harus DIKECUALIKAN
    //   (pola '_MOUNT_' gak cocok ke '_MOUNTAIN_' karena beda karakter ke-7)
    // - Potion & Food: BELUM ADA item dengan token ini di tabel `items` sama sekali
    //   (dicek langsung, gak nemu satupun) — sengaja dikosongin, picker-nya akan
    //   selalu kosong sampai item Potion/Food di-sync/dipetakan ke tabel items
    private const SLOT_PATTERNS = [
        'MainHand' => ['%\_2H\_%', '%\_MAIN\_%'],
        'OffHand' => ['%\_OFF\_%'],
        'Head' => ['%\_HEAD\_%'],
        'Armor' => ['%\_ARMOR\_%'],
        'Shoes' => ['%\_SHOES\_%'],
        'Cape' => ['%\_CAPE', '%\_CAPEITEM\_%'],
        'Bag' => ['%\_BAG', '%\_BACKPACK\_%'],
        'Mount' => ['%\_MOUNT\_%'],
        'Potion' => [],
        'Food' => [],
    ];

    /**
     * Endpoint AJAX buat popup pemilihan item di builds/_paperdoll.blade.php DAN
     * builds/_form.blade.php. Filter Tier & Enchant (grid icon) — TIDAK dukung
     * search-by-name (?q=), biar konsisten di kedua tempat.
     * Query dibatasi per-slot + limit 60 baris, jadi ringan meski tabel items ribuan baris.
     *
     * GET /builds/items/search?slot=Head&tier=8&enchant=2
     */
    public function searchItems(Request $request)
    {
        $slot = $request->query('slot');
        $tier = $request->query('tier');
        $enchant = $request->query('enchant');
        $quality = $request->query('quality');   // string: 'Normal', 'Good', dst — HANYA buat preview render

        if (!in_array($slot, self::VALID_SLOTS, true)) {
            return response()->json([], 400);
        }

        $patterns = self::SLOT_PATTERNS[$slot];

        // Potion & Food: belum ada datanya di tabel items, balikin kosong tanpa query
        if (empty($patterns)) {
            return response()->json([]);
        }

        $query = Item::query()
            ->where(function ($q) use ($patterns) {
                foreach ($patterns as $pattern) {
                    $q->orWhereRaw("items.api_id LIKE ? ESCAPE '\\'", [$pattern]);
                }
            })
            ->select([
                'items.id',
                'items.name',
                'items.api_id',
                'items.tier',
                'items.quality',
                'items.enc',
            ]);

        if ($tier !== null && $tier !== '') {
            $query->where('items.tier', (int) $tier);
        }

        if ($enchant !== null && $enchant !== '') {
            $query->where('items.enc', (int) $enchant);
        }

        // CATATAN: quality SENGAJA gak dipakai buat filter query lagi. Semua item di
        // tabel `items` datanya quality Normal doang, jadi kalau di-filter beneran
        // hasilnya bakal selalu kosong pas user pilih Good/Outstanding/dst. Quality
        // di sini murni dipakai buat nambah suffix ?quality=N ke URL render supaya
        // preview icon di grid tampil sesuai quality yang lagi dipilih user.
        $qualityInt = Item::QUALITY_MAP[$quality] ?? 1;

        return $query
            ->orderBy('items.tier')
            ->orderBy('items.enc')
            ->orderBy('items.name')
            ->limit(60)
            ->get()
            ->map(function ($item) use ($qualityInt) {
                // Sama kayak MarketController@items: enc > 0 harus nambah suffix
                // @{enc} ke api_id sebelum dibangun jadi URL render, atau icon
                // yang balik dari render.albiononline.com selalu versi @0 (T8.0)
                // walaupun user filter Enchant 4.
                $enc = (int) ($item->enc ?? 0);
                $apiIdWithEnc = $enc > 0 ? "{$item->api_id}@{$enc}" : $item->api_id;

                $qualitySuffix = $qualityInt > 1 ? "?quality={$qualityInt}" : '';

                $item->img_url = "https://render.albiononline.com/v1/item/{$apiIdWithEnc}.png{$qualitySuffix}";
                $item->quality_int = $qualityInt;
                return $item;
            });
    }

    private function authorizeOwner(Build $build): void
    {
        abort_unless($build->user_id === Auth::id(), 403);
    }

    private function validateBuild(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'main_hand_id' => 'nullable|exists:items,id',
            'off_hand_id' => 'nullable|exists:items,id',
            'head_id' => 'nullable|exists:items,id',
            'armor_id' => 'nullable|exists:items,id',
            'shoes_id' => 'nullable|exists:items,id',
            'cape_id' => 'nullable|exists:items,id',
            'bag_id' => 'nullable|exists:items,id',
            'mount_id' => 'nullable|exists:items,id',
            'potion_id' => 'nullable|exists:items,id',
            'food_id' => 'nullable|exists:items,id',
            'quality' => 'nullable|array',
            'quality.*' => 'nullable|integer|min:1|max:5',
        ]);

        // pisahin quality dari input field, cuma simpan buat slot yang beneran ada item-nya
        $qualities = collect($validated['quality'] ?? [])
            ->filter(fn ($q, $field) => !empty($validated[$field] ?? null))
            ->all();

        unset($validated['quality']);
        $validated['qualities'] = $qualities;

        return $validated;
    }
}