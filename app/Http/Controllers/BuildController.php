<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuildController extends Controller
{
    // daftar equipment_slot yang valid, buat whitelist parameter ?slot= di searchItems()
    private const VALID_SLOTS = [
        'MainHand', 'OffHand', 'Head', 'Armor', 'Shoes',
        'Cape', 'Bag', 'Mount', 'Potion', 'Food',
    ];

    // form bikin build baru — cuma bisa diakses kalau user belum punya build
    public function create()
    {
        if (Auth::user()->build) {
            return redirect()->route('builds.edit');
        }

        return view('builds.create');
    }

    public function store(Request $request)
    {
        if (Auth::user()->build) {
            return redirect()->route('builds.edit');
        }

        $validated = $this->validateBuild($request);

        Auth::user()->build()->create($validated);

        return redirect()->route('profile.show')
            ->with('success', 'Build berhasil dibuat!');
    }

    // form edit — selalu build milik user yang login, gak perlu {build} di URL
    public function edit()
    {
        $build = Auth::user()->build;
        abort_unless($build, 404);

        return view('builds.edit', compact('build'));
    }

    public function update(Request $request)
    {
        $build = Auth::user()->build;
        abort_unless($build, 404);

        $validated = $this->validateBuild($request);

        $build->update($validated);

        return redirect()->route('profile.show')
            ->with('success', 'Build berhasil diupdate!');
    }

    public function destroy()
    {
        $build = Auth::user()->build;
        abort_unless($build, 404);

        $build->delete();

        return redirect()->route('profile.show')
            ->with('success', 'Build berhasil dihapus.');
    }

    /**
     * Endpoint AJAX buat update SATU slot aja, dipanggil dari popup di profile/show.blade.php
     * (builds/_paperdoll.blade.php) — biar user bisa ganti item langsung dari halaman profil
     * tanpa perlu buka halaman edit build.
     *
     * PATCH /builds/slot  body: { field: 'head_id', item_id: 123|null }
     */
    public function updateSlot(Request $request)
    {
        $build = Auth::user()->build;
        abort_unless($build, 404);

        $validated = $request->validate([
            'field' => 'required|string|in:main_hand_id,off_hand_id,head_id,armor_id,shoes_id,cape_id,bag_id,mount_id,potion_id,food_id',
            'item_id' => 'nullable|integer|exists:items,id',
        ]);

        $build->update([$validated['field'] => $validated['item_id']]);

        return response()->json(['success' => true]);
    }

    /**
     * Endpoint AJAX buat popup pemilihan item di builds/_paperdoll.blade.php.
     * Ganti dari mode search-by-name jadi mode filter Tier & Enchant (grid icon),
     * biar user yang gak apal nama item Albion tetep gampang milih.
     * Query dibatasi per-slot + limit 60 baris, jadi ringan meski tabel items ribuan baris.
     *
     * GET /builds/items/search?slot=Head&tier=8&enchant=2
     */
    public function searchItems(Request $request)
    {
        $slot = $request->query('slot');
        $tier = $request->query('tier');
        $enchant = $request->query('enchant');

        if (!in_array($slot, self::VALID_SLOTS, true)) {
            return response()->json([], 400);
        }

        $query = Item::query()
            ->join('categories', 'categories.id', '=', 'items.category_id')
            ->where('categories.equipment_slot', $slot)
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

        return $query
            ->orderBy('items.tier')
            ->orderBy('items.enc')
            ->orderBy('items.name')
            ->limit(60)
            ->get();
    }

    private function validateBuild(Request $request): array
    {
        return $request->validate([
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
        ]);
    }
}