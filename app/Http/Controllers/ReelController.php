<?php

namespace App\Http\Controllers;

use App\Models\Reel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReelController extends Controller
{
    public function index(): View
    {
        $reels = $this->getReelsWithSponsored(30);

        return view('reels.index', compact('reels'));
    }

    public function more(Request $request): JsonResponse
    {
        $excludeIds = collect($request->input('exclude', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->all();

        $reels = $this->getReelsWithSponsored(15, $excludeIds);

        // Kalau semua reel udah pernah ditampilin (kehabisan konten baru), izinkan
        // pengulangan daripada feed jadi mentok/mati.
        if ($reels->isEmpty() && !empty($excludeIds)) {
            $reels = $this->getReelsWithSponsored(15, []);
        }

        return response()->json([
            'reels' => $reels,
            'has_more' => $reels->isNotEmpty(),
        ]);
    }

    /**
     * Ambil reels dengan sponsored injection setiap N video.
     * Default: 1 sponsored setiap 25 organic reels.
     */
    private function getReelsWithSponsored(int $limit, array $excludeIds = []): \Illuminate\Support\Collection
    {
        $sponsoredFrequency = config('services.analytics.sponsored_frequency', 25);
        
        // Hitung berapa sponsored yang perlu diinjek
        $sponsoredCount = (int) floor($limit / $sponsoredFrequency);
        $organicCount = $limit - $sponsoredCount;

        // Ambil organic reels
        $organicReels = Reel::organic()
            ->when(!empty($excludeIds), fn ($query) => $query->whereNotIn('id', $excludeIds))
            ->inRandomOrder()
            ->limit($organicCount)
            ->get(['id', 'youtube_id', 'title', 'channel_name', 'thumbnail_url', 'is_sponsored', 'sponsor_name', 'sponsor_url']);

        // Ambil sponsored reels (kalau ada)
        $sponsoredReels = collect();
        if ($sponsoredCount > 0) {
            $sponsoredReels = Reel::sponsored()
                ->when(!empty($excludeIds), fn ($query) => $query->whereNotIn('id', $excludeIds))
                ->inRandomOrder()
                ->limit($sponsoredCount)
                ->get(['id', 'youtube_id', 'title', 'channel_name', 'thumbnail_url', 'is_sponsored', 'sponsor_name', 'sponsor_url']);
        }

        // Inject sponsored di posisi yang tepat (setiap 25 organic)
        $result = collect();
        $organicIndex = 0;
        $sponsoredIndex = 0;

        for ($i = 0; $i < $limit; $i++) {
            // Setiap kelipatan frequency, masukkan sponsored (kalau masih ada)
            if (($i + 1) % $sponsoredFrequency === 0 && $sponsoredIndex < $sponsoredReels->count()) {
                $result->push($sponsoredReels[$sponsoredIndex]);
                $sponsoredIndex++;
            } elseif ($organicIndex < $organicReels->count()) {
                $result->push($organicReels[$organicIndex]);
                $organicIndex++;
            }
        }

        return $result;
    }
}
