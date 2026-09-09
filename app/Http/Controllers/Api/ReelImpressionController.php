<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReelImpressionController extends Controller
{
    /**
     * Track impression untuk sponsored reel.
     * Dipanggil dari frontend saat user mulai nonton sponsored video.
     */
    public function track(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reel_id' => ['required', 'integer', 'exists:reels,id'],
        ]);

        $reel = Reel::find($validated['reel_id']);

        // Hanya track kalau memang sponsored
        if ($reel && $reel->is_sponsored) {
            $reel->increment('impressions');
            
            return response()->json([
                'success' => true,
                'impressions' => $reel->impressions,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Reel not sponsored or not found',
        ], 400);
    }
}
