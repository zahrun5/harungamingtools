<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CraftingStation;
use Illuminate\Http\Request;

/**
 * ===========================================================
 * CraftingStationAdminController — halaman dev "Kelola Crafting Station"
 * ===========================================================
 * UI pengganti alur manual (SSH + tinker + artisan command) buat
 * bikin/edit crafting station baru. Semua endpoint di sini admin-only.
 *
 * Kategori yang dipakai SELALU dari Category group='market' (606
 * kategori asli) — TIDAK PERNAH bikin kategori baru, cuma nge-link
 * lewat pivot crafting_station_category. Ini sesuai keputusan dari
 * insiden Mage's Tower sebelumnya: satu sumber kebenaran kategori.
 */
class CraftingStationAdminController extends Controller
{
    private function ensureAdmin(): void
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }
    }

    // Halaman utama: list station + form create/edit
    public function index()
    {
        $this->ensureAdmin();

        $stations = CraftingStation::withCount('categories')->orderBy('name')->get();

        return view('dev.crafting-stations', ['stations' => $stations]);
    }

    // Tree lengkap kategori market (buat browse + checkbox)
    public function categoriesTree()
    {
        $this->ensureAdmin();

        $roots = Category::where('group', 'market')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->with(['children' => function ($q) {
                $q->orderBy('name')->with(['children' => function ($q2) {
                    $q2->orderBy('name');
                }]);
            }])
            ->get()
            ->map(fn($root) => $this->mapNode($root));

        return response()->json($roots);
    }

    private function mapNode($node)
    {
        return [
            'id'       => $node->id,
            'name'     => $node->name,
            'children' => $node->children->map(fn($c) => $this->mapNode($c))->values(),
        ];
    }

    // Search box: cari kategori by nama (case-insensitive), tampilkan path lengkap
    public function searchCategories(Request $request)
    {
        $this->ensureAdmin();

        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $matches = Category::where('group', 'market')
            ->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($q) . '%'])
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'name', 'parent_id']);

        $results = $matches->map(fn($cat) => [
            'id'   => $cat->id,
            'name' => $cat->name,
            'path' => $this->buildPath($cat),
        ]);

        return response()->json($results);
    }

    private function buildPath(Category $cat): string
    {
        $parts = [$cat->name];
        $current = $cat;
        while ($current->parent_id) {
            $current = Category::find($current->parent_id);
            if (!$current) break;
            array_unshift($parts, $current->name);
        }
        return implode(' > ', $parts);
    }

    // Ambil kategori yang sudah ke-attach ke station (buat isi checkbox pas edit)
    public function stationCategories($id)
    {
        $this->ensureAdmin();

        $station = CraftingStation::findOrFail($id);
        $ids = $station->categories()->pluck('categories.id');

        return response()->json($ids);
    }

    // Bikin station baru ATAU update nama station yang sudah ada (by slug)
    public function store(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'slug' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/',
            'name' => 'required|string|max:255',
            'id'   => 'nullable|integer|exists:crafting_stations,id',
        ]);

        if ($request->filled('id')) {
            $station = CraftingStation::findOrFail($request->id);
            $station->update([
                'slug' => $request->slug,
                'name' => $request->name,
            ]);
        } else {
            $station = CraftingStation::firstOrCreate(
                ['slug' => $request->slug],
                ['name' => $request->name]
            );
        }

        return response()->json([
            'id'   => $station->id,
            'slug' => $station->slug,
            'name' => $station->name,
        ]);
    }

    // Sync kategori station — checkbox yang dicentang jadi PERSIS isi pivot
    // (yang di-uncheck otomatis lepas, ini beda dari artisan command lama
    // yang cuma nambah tanpa pernah lepas)
    public function syncCategories(Request $request, $id)
    {
        $this->ensureAdmin();

        $station = CraftingStation::findOrFail($id);

        $ids = $request->input('category_ids', []);
        $validIds = Category::where('group', 'market')->whereIn('id', $ids)->pluck('id');

        $station->categories()->sync($validIds);

        return response()->json([
            'ok'    => true,
            'count' => $validIds->count(),
        ]);
    }

    // Hapus station (pivot ikut kehapus via cascade, kategori market TIDAK kehapus)
    public function destroy($id)
    {
        $this->ensureAdmin();

        $station = CraftingStation::findOrFail($id);
        $station->categories()->detach();
        $station->delete();

        return response()->json(['ok' => true]);
    }
}
