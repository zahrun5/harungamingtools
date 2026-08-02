<?php

namespace App\Http\Controllers;

use App\Models\Reel;
use App\Models\YoutubeChannel;
use App\Services\YoutubeOembedService;
use App\Services\YoutubeDataApiService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReelDeveloperController extends Controller
{
    public function __construct()
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
    }

    public function index(): View
    {
        $reels = Reel::with('addedBy')->latest()->paginate(20);

        $pendingReels = Reel::where('review_status', 'pending_review')->latest()->get();
        $pendingChannels = YoutubeChannel::where('status', 'pending')->get();

        // Kelompokkan reel pending berdasarkan channel-nya (biar bisa ditampilkan
        // menyatu di view: 1 channel + thumbnail video-video pending-nya).
        $pendingChannelIds = $pendingChannels->pluck('id');
        $reelsByChannel = $pendingReels->whereIn('youtube_channel_id', $pendingChannelIds)
            ->groupBy('youtube_channel_id');
        $orphanReels = $pendingReels->whereNotIn('youtube_channel_id', $pendingChannelIds)->values();

        $channels = Reel::selectRaw('channel_name, count(*) as total, max(created_at) as last_imported_at')
            ->groupBy('channel_name')
            ->orderByDesc('total')
            ->get();

        return view('dev.reels.index', compact(
            'reels', 'channels', 'pendingReels', 'pendingChannels', 'reelsByChannel', 'orphanReels'
        ));
    }

    public function store(Request $request, YoutubeOembedService $service): RedirectResponse
    {
        $validated = $request->validate([
            'youtube_url' => ['required', 'url', 'max:255'],
        ]);

        $resolved = $service->resolve($validated['youtube_url']);

        if (!$resolved) {
            return back()
                ->withInput()
                ->withErrors(['youtube_url' => 'Link tidak valid atau video tidak ditemukan. Pastikan link YouTube-nya benar dan videonya publik.']);
        }

        $exists = Reel::where('youtube_id', $resolved['youtube_id'])->exists();
        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['youtube_url' => 'Video ini sudah pernah ditambahkan.']);
        }

        Reel::create([
            'youtube_url'   => $validated['youtube_url'],
            'youtube_id'    => $resolved['youtube_id'],
            'title'         => $resolved['title'],
            'channel_name'  => $resolved['channel_name'],
            'thumbnail_url' => $resolved['thumbnail_url'],
            'added_by'      => auth()->id(),
        ]);

        return back()->with('success', 'Reel berhasil ditambahkan: ' . ($resolved['title'] ?? $resolved['youtube_id']));
    }

    public function importFromChannel(Request $request, YoutubeDataApiService $service): RedirectResponse
    {
        $validated = $request->validate([
            'channel_input' => ['required', 'string', 'max:255'],
            'max' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        try {
            $shorts = $service->getChannelShorts(
                $validated['channel_input'],
                $validated['max'] ?? 10,
            );
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['channel_input' => $e->getMessage()]);
        }

        if (empty($shorts)) {
            return back()
                ->withInput()
                ->with('warning', 'Tidak ada video Shorts (≤3 menit) yang ditemukan dari channel ini.');
        }

        $added = 0;
        $skipped = 0;

        foreach ($shorts as $video) {
            $exists = Reel::where('youtube_id', $video['youtube_id'])->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Reel::create([
                'youtube_url'   => "https://www.youtube.com/watch?v={$video['youtube_id']}",
                'youtube_id'    => $video['youtube_id'],
                'title'         => $video['title'],
                'channel_name'  => $video['channel_name'],
                'thumbnail_url' => $video['thumbnail_url'],
                'added_by'      => auth()->id(),
            ]);
            $added++;
        }

        $message = "{$added} reel baru ditambahkan.";
        if ($skipped > 0) {
            $message .= " {$skipped} video dilewati (sudah ada sebelumnya).";
        }

        return back()->with('success', $message);
    }

    public function toggleActive(Reel $reel): RedirectResponse
    {
        $reel->update(['is_active' => !$reel->is_active]);

        return back()->with('success', $reel->is_active ? 'Reel diaktifkan.' : 'Reel disembunyikan.');
    }

    public function approveReel(Reel $reel): RedirectResponse
    {
        $reel->update(['review_status' => 'auto_approved', 'is_active' => true]);

        return back()->with('success', 'Reel disetujui.');
    }

    public function approveChannel(YoutubeChannel $channel): RedirectResponse
    {
        $channel->update(['status' => 'active']);

        // Auto-approve reel yang udah nunggu dari channel ini
        Reel::where('youtube_channel_id', $channel->id)
            ->where('review_status', 'pending_review')
            ->update(['review_status' => 'auto_approved', 'is_active' => true]);

        return back()->with('success', "Channel {$channel->channel_title} disetujui.");
    }

    public function rejectChannel(YoutubeChannel $channel): RedirectResponse
    {
        $channel->update(['status' => 'rejected']);

        Reel::where('youtube_channel_id', $channel->id)
            ->where('review_status', 'pending_review')
            ->update(['review_status' => 'rejected']);

        return back()->with('success', "Channel {$channel->channel_title} ditolak.");
    }

    public function destroy(Reel $reel): RedirectResponse
    {
        $reel->delete();

        return back()->with('success', 'Reel dihapus.');
    }
}
