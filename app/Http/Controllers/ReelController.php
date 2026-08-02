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
        $reels = Reel::where('is_active', true)->inRandomOrder()->limit(30)->get();

        return view('reels.index', compact('reels'));
    }

    public function more(Request $request): JsonResponse
    {
        $excludeIds = collect($request->input('exclude', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->all();

        $reels = Reel::where('is_active', true)
            ->when(!empty($excludeIds), fn ($query) => $query->whereNotIn('id', $excludeIds))
            ->inRandomOrder()
            ->limit(15)
            ->get(['id', 'youtube_id', 'title', 'channel_name', 'thumbnail_url']);

        // Kalau semua reel udah pernah ditampilin (kehabisan konten baru), izinkan
        // pengulangan daripada feed jadi mentok/mati.
        if ($reels->isEmpty() && !empty($excludeIds)) {
            $reels = Reel::where('is_active', true)
                ->inRandomOrder()
                ->limit(15)
                ->get(['id', 'youtube_id', 'title', 'channel_name', 'thumbnail_url']);
        }

        return response()->json([
            'reels' => $reels,
            'has_more' => $reels->isNotEmpty(),
        ]);
    }
}
