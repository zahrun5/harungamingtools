<?php

namespace App\Http\Controllers;

use App\Services\AlbionKillboardService;
use Illuminate\Http\Request;

class DeathRecapController extends Controller
{
    public function __construct(protected AlbionKillboardService $killboard)
    {
    }

    public function index()
    {
        return view('death-recap.index');
    }

    public function search(Request $request)
    {
        $request->validate(['name' => 'required|string|max:64']);

        $player = $this->killboard->searchPlayer($request->name);

        if (! $player) {
            return response()->json(['message' => 'Karakter tidak ditemukan.'], 404);
        }

        $events = $this->killboard->getRecentEvents($player['Id'], $player['Name'], limit: 5);

        return response()->json([
            'player' => [
                'id' => $player['Id'],
                'name' => $player['Name'],
            ],
            'events' => $events,
            'next_offset' => 5,
        ]);
    }

    public function loadMore(Request $request)
    {
        $request->validate([
            'character_id' => 'required|string',
            'character_name' => 'required|string',
            'offset' => 'required|integer|min:0',
        ]);

        $events = $this->killboard->loadMoreEvents(
            $request->character_id,
            $request->character_name,
            $request->offset,
            limit: 5
        );

        return response()->json([
            'events' => $events,
            'next_offset' => $request->offset + 5,
            'has_more' => count($events) === 5,
        ]);
    }

    public function show(int $eventId)
    {
        $event = $this->killboard->getEventDetail($eventId);

        if (! $event) {
            abort(404, 'Event tidak ditemukan.');
        }

        return view('death-recap.show', ['event' => $event]);
    }
}