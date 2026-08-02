@extends('layouts.app')

@section('title', 'Dev — Kelola Reels')

@section('content')
<div style="max-width:760px;margin:0 auto;">
    <h1 style="font-family:'Fraunces',serif;color:var(--gold);font-size:1.4rem;margin-bottom:20px;">
        🎬 Kelola Reels
    </h1>

    @if (session('success'))
        <div style="background:#1f3d2a;border:1px solid #2f6b45;color:#a8e6b8;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:0.88rem;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Form tambah reel --}}
    <form method="POST" action="{{ route('dev.reels.store') }}" style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:32px;">
        @csrf
        <label style="display:block;font-size:0.85rem;color:var(--text-muted);margin-bottom:8px;">
            Paste link YouTube (judul, channel, thumbnail otomatis diambil)
        </label>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <input
                type="url"
                name="youtube_url"
                value="{{ old('youtube_url') }}"
                placeholder="https://youtube.com/watch?v=..."
                required
                style="flex:1;min-width:220px;background:var(--bg-panel);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:8px;font-size:0.88rem;"
            >
            <button type="submit" style="background:var(--gold);color:#1a1410;border:none;padding:10px 22px;border-radius:8px;font-weight:600;font-size:0.88rem;cursor:pointer;">
                Tambah
            </button>
        </div>
        @error('youtube_url')
            <p style="color:#e88;font-size:0.8rem;margin-top:8px;">{{ $message }}</p>
        @enderror
    </form>

    {{-- Daftar channel yang sudah pernah diimport --}}
    @if ($channels->isNotEmpty())
        <details style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:16px 20px;margin-bottom:20px;">
            <summary style="cursor:pointer;font-size:0.88rem;font-weight:600;color:var(--gold);">
                📋 Channel yang sudah pernah diimport ({{ $channels->count() }} channel, {{ $channels->sum('total') }} reel)
            </summary>
            <div style="margin-top:14px;display:flex;flex-direction:column;gap:6px;max-height:280px;overflow-y:auto;">
                @foreach ($channels as $channel)
                    <div style="display:flex;justify-content:space-between;gap:10px;font-size:0.82rem;padding:6px 0;border-bottom:1px solid var(--border);">
                        <span style="color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $channel->channel_name ?? '(tanpa nama)' }}
                        </span>
                        <span style="color:var(--text-muted);flex-shrink:0;">
                            {{ $channel->total }} reel · {{ \Illuminate\Support\Carbon::parse($channel->last_imported_at)->diffForHumans() }}
                        </span>
                    </div>
                @endforeach
            </div>
        </details>
    @endif

    {{-- Form import massal dari channel --}}
    <form method="POST" action="{{ route('dev.reels.import') }}" style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:32px;">
        @csrf
        <label style="display:block;font-size:0.85rem;color:var(--text-muted);margin-bottom:8px;">
            Import dari channel (ambil otomatis video Shorts ≤3 menit terbaru)
        </label>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <input
                type="text"
                name="channel_input"
                value="{{ old('channel_input') }}"
                placeholder="@namachannel, link youtube.com/@nama, atau channel ID"
                required
                style="flex:1;min-width:220px;background:var(--bg-panel);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:8px;font-size:0.88rem;"
            >
            <input
                type="number"
                name="max"
                value="{{ old('max', 10) }}"
                min="1"
                max="500"
                title="Jumlah maksimal video yang diimport"
                style="width:80px;background:var(--bg-panel);border:1px solid var(--border);color:var(--text);padding:10px 12px;border-radius:8px;font-size:0.88rem;"
            >
            <button type="submit" style="background:var(--gold);color:#1a1410;border:none;padding:10px 22px;border-radius:8px;font-weight:600;font-size:0.88rem;cursor:pointer;">
                Import
            </button>
        </div>
        <p style="font-size:0.75rem;color:var(--text-muted);margin-top:8px;margin-bottom:0;">
            Video yang sudah pernah ditambahkan otomatis dilewati.
        </p>
        @error('channel_input')
            <p style="color:#e88;font-size:0.8rem;margin-top:8px;">{{ $message }}</p>
        @enderror
    </form>

    @if (session('warning'))
        <div style="background:#3d3520;border:1px solid #6b5f2f;color:#e8d8a8;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:0.88rem;">
            {{ session('warning') }}
        </div>
    @endif

    {{-- Channel + reel menunggu review, digabung jadi satu: 1 channel = 1 card,
         video-video pending-nya ditampilkan sebagai thumbnail di bawahnya.
         Setujui/Tolak channel otomatis approve/reject semua video pending-nya. --}}
    @if ($pendingChannels->isNotEmpty())
        <div style="background:var(--bg-card);border:1px solid #6b5f2f;border-radius:12px;padding:20px;margin-bottom:20px;">
            <h2 style="font-size:0.95rem;font-weight:600;color:var(--gold);margin-bottom:14px;">
                ⏳ Channel & reel menunggu review ({{ $pendingChannels->count() }} channel, {{ $pendingReels->count() }} reel)
            </h2>
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach ($pendingChannels as $channel)
                    @php $channelReels = $reelsByChannel->get($channel->id, collect()); @endphp
                    <div style="background:var(--bg-panel);border:1px solid var(--border);border-radius:10px;padding:14px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
                            <span style="color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <strong>{{ $channel->channel_title }}</strong>
                                <span style="color:var(--text-muted);font-size:0.78rem;">({{ $channelReels->count() }} video)</span>
                            </span>
                            <div style="display:flex;gap:6px;flex-shrink:0;">
                                <form method="POST" action="{{ route('dev.reels.channels.approve', $channel) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" style="background:#1f3d2a;border:1px solid #2f6b45;color:#a8e6b8;padding:6px 12px;border-radius:6px;font-size:0.78rem;cursor:pointer;">
                                        Setujui Semua
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('dev.reels.channels.reject', $channel) }}" onsubmit="return confirm('Tolak channel ini? Semua video pending dari channel ini juga akan ditolak.');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" style="background:#3d1f1f;border:1px solid #6b2f2f;color:#e88;padding:6px 12px;border-radius:6px;font-size:0.78rem;cursor:pointer;">
                                        Tolak Semua
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if ($channelReels->isNotEmpty())
                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;">
                                @foreach ($channelReels as $reel)
                                    <img
                                        src="{{ $reel->thumbnail_url ?? asset('images/icons/icon-192.png') }}"
                                        alt="{{ $reel->title }}"
                                        title="{{ $reel->title }}"
                                        style="width:80px;height:45px;object-fit:cover;border-radius:6px;"
                                    >
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Reel pending yang channel-nya tidak terdeteksi/tidak ada dalam daftar
         channel pending (misalnya ditambahkan manual lewat form di atas) --}}
    @if ($orphanReels->isNotEmpty())
        <div style="background:var(--bg-card);border:1px solid #6b5f2f;border-radius:12px;padding:20px;margin-bottom:32px;">
            <h2 style="font-size:0.95rem;font-weight:600;color:var(--gold);margin-bottom:14px;">
                🔍 Reel lain menunggu review ({{ $orphanReels->count() }})
            </h2>
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach ($orphanReels as $reel)
                    <div style="display:flex;gap:14px;background:var(--bg-panel);border:1px solid var(--border);border-radius:10px;padding:12px;align-items:center;">
                        <img src="{{ $reel->thumbnail_url ?? asset('images/icons/icon-192.png') }}" alt="" style="width:96px;height:54px;object-fit:cover;border-radius:6px;flex-shrink:0;">

                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:600;font-size:0.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $reel->title ?? '(tanpa judul)' }}
                            </div>
                            <div style="font-size:0.78rem;color:var(--text-muted);">
                                {{ $reel->channel_name ?? '—' }}
                            </div>
                        </div>

                        <div style="display:flex;gap:6px;flex-shrink:0;">
                            <form method="POST" action="{{ route('dev.reels.approve', $reel) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" style="background:#1f3d2a;border:1px solid #2f6b45;color:#a8e6b8;padding:6px 12px;border-radius:6px;font-size:0.78rem;cursor:pointer;">
                                    Setujui
                                </button>
                            </form>
                            <form method="POST" action="{{ route('dev.reels.destroy', $reel) }}" onsubmit="return confirm('Hapus reel ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:#3d1f1f;border:1px solid #6b2f2f;color:#e88;padding:6px 12px;border-radius:6px;font-size:0.78rem;cursor:pointer;">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- List reels --}}
    <div style="display:flex;flex-direction:column;gap:12px;">
        @forelse ($reels as $reel)
            <div style="display:flex;gap:14px;background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:12px;align-items:center;{{ !$reel->is_active ? 'opacity:0.5;' : '' }}">
                <img src="{{ $reel->thumbnail_url ?? asset('images/icons/icon-192.png') }}" alt="" style="width:96px;height:54px;object-fit:cover;border-radius:6px;flex-shrink:0;">

                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:0.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $reel->title ?? '(tanpa judul)' }}
                    </div>
                    <div style="font-size:0.78rem;color:var(--text-muted);">
                        {{ $reel->channel_name ?? '—' }} · ditambahkan oleh {{ $reel->addedBy->name ?? 'system' }}
                    </div>
                </div>

                <div style="display:flex;gap:6px;flex-shrink:0;">
                    <form method="POST" action="{{ route('dev.reels.toggle', $reel) }}">
                        @csrf
                        <button type="submit" style="background:var(--bg-panel);border:1px solid var(--border);color:var(--text);padding:6px 12px;border-radius:6px;font-size:0.78rem;cursor:pointer;">
                            {{ $reel->is_active ? 'Sembunyikan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('dev.reels.destroy', $reel) }}" onsubmit="return confirm('Hapus reel ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:#3d1f1f;border:1px solid #6b2f2f;color:#e88;padding:6px 12px;border-radius:6px;font-size:0.78rem;cursor:pointer;">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p style="color:var(--text-muted);font-size:0.88rem;">Belum ada reel yang ditambahkan.</p>
        @endforelse
    </div>

    <div style="margin-top:20px;">
        {{ $reels->links() }}
    </div>
</div>
@endsection
