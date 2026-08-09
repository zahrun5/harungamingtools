<?php

namespace App\Http\Controllers;

use App\Models\Reel;
use App\Models\YoutubeChannel;
use App\Services\YoutubeOembedService;
use App\Services\YoutubeDataApiService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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

        // Total pending (dipakai buat header count, gak kepengaruh pagination).
        $pendingReelsCount = Reel::where('review_status', 'pending_review')->count();

        // ID SEMUA channel pending (bukan cuma yang tampil di halaman ini) —
        // dipakai buat nentuin reel mana yang "orphan" (channel-nya gak
        // terdeteksi/gak ada di daftar pending), jadi orphan detection tetap
        // benar walau channel-nya lagi ada di halaman 2, 3, dst.
        $allPendingChannelIds = YoutubeChannel::where('status', 'pending')->pluck('id');

        // Channel pending, 5 per halaman. Page-name khusus (channels_page)
        // biar gak bentrok sama pagination reel di bawahnya.
        $pendingChannels = YoutubeChannel::where('status', 'pending')
            ->latest()
            ->paginate(5, ['*'], 'channels_page');

        // Reel pending milik channel yang tampil di halaman channel SAAT INI
        // aja (bukan semua channel pending) — biar konsisten sama channel
        // yang lagi ditampilkan.
        $currentPageChannelIds = $pendingChannels->pluck('id');
        $reelsByChannel = Reel::where('review_status', 'pending_review')
            ->whereIn('youtube_channel_id', $currentPageChannelIds)
            ->latest()
            ->get()
            ->groupBy('youtube_channel_id');

        // Reel pending yang channel-nya gak ada di daftar pending sama sekali
        // (orphan), 20 per halaman, page-name sendiri (reels_page).
        $orphanReels = Reel::where('review_status', 'pending_review')
            ->whereNotIn('youtube_channel_id', $allPendingChannelIds)
            ->latest()
            ->paginate(20, ['*'], 'reels_page');

        $channels = Reel::selectRaw('channel_name, count(*) as total, max(created_at) as last_imported_at')
            ->groupBy('channel_name')
            ->orderByDesc('total')
            ->get();

        return view('dev.reels.index', compact(
            'reels', 'channels', 'pendingReelsCount', 'pendingChannels', 'reelsByChannel', 'orphanReels'
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

    public function bulkAction(Request $request): RedirectResponse
    {
        $action   = $request->input('bulk_action');
        $selected = $request->input('selected', []);

        if (!in_array($action, ['approve', 'reject'], true) || empty($selected)) {
            return back()->withErrors(['bulk' => 'Tidak ada item yang dipilih atau aksi tidak valid.']);
        }

        // Pisahkan value checkbox "channel:5" / "reel:12" jadi 2 kelompok ID.
        $channelIds = [];
        $reelIds    = [];

        foreach ($selected as $item) {
            [$type, $id] = explode(':', $item, 2) + [null, null];

            if ($type === 'channel' && is_numeric($id)) {
                $channelIds[] = (int) $id;
            } elseif ($type === 'reel' && is_numeric($id)) {
                $reelIds[] = (int) $id;
            }
        }

        // Semua update digabung dalam 1 transaksi -> 1x write ke SQLite,
        // walau jumlah channel/reel yang diproses banyak.
        DB::transaction(function () use ($action, $channelIds, $reelIds) {
            if (!empty($channelIds)) {
                YoutubeChannel::whereIn('id', $channelIds)
                    ->update(['status' => $action === 'approve' ? 'active' : 'rejected']);

                Reel::whereIn('youtube_channel_id', $channelIds)
                    ->where('review_status', 'pending_review')
                    ->update($action === 'approve'
                        ? ['review_status' => 'auto_approved', 'is_active' => true]
                        : ['review_status' => 'rejected']);
            }

            if (!empty($reelIds)) {
                if ($action === 'approve') {
                    Reel::whereIn('id', $reelIds)
                        ->update(['review_status' => 'auto_approved', 'is_active' => true]);
                } else {
                    Reel::whereIn('id', $reelIds)->delete();
                }
            }
        });

        $count = count($channelIds) + count($reelIds);
        $verb  = $action === 'approve' ? 'disetujui' : 'ditolak';

        return back()->with('success', "{$count} item berhasil {$verb}.");
    }

    public function destroy(Reel $reel): RedirectResponse
    {
        $reel->delete();

        return back()->with('success', 'Reel dihapus.');
    }
}
