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
            <h2 style="font-size:0.95rem;font-weight:600;color:var(--gold);margin-bottom:4px;">
                ⏳ Channel & reel menunggu review ({{ $pendingChannels->total() }} channel, {{ $pendingReelsCount }} reel)
            </h2>
            <p style="font-size:0.76rem;color:var(--text-muted);margin-bottom:14px;">
                Menampilkan {{ $pendingChannels->count() }} dari {{ $pendingChannels->total() }} channel (halaman {{ $pendingChannels->currentPage() }}/{{ $pendingChannels->lastPage() }})
            </p>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.82rem;color:var(--text-muted);margin-bottom:14px;">
                <input type="checkbox" id="select-all-channels" style="width:16px;height:16px;flex-shrink:0;accent-color:var(--gold);">
                Pilih semua channel di halaman ini
            </label>
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach ($pendingChannels as $channel)
                    @php $channelReels = $reelsByChannel->get($channel->id, collect()); @endphp
                    <div style="background:var(--bg-panel);border:1px solid var(--border);border-radius:10px;padding:14px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
                            <label style="display:flex;align-items:center;gap:10px;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:pointer;">
                                <input
                                    type="checkbox"
                                    value="channel:{{ $channel->id }}"
                                    class="bulk-select bulk-select-channel"
                                    style="width:18px;height:18px;flex-shrink:0;accent-color:var(--gold);"
                                >
                                <span>
                                    <strong>{{ $channel->channel_title }}</strong>
                                    <span style="color:var(--text-muted);font-size:0.78rem;">({{ $channelReels->count() }} video)</span>
                                </span>
                            </label>
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
            <div style="margin-top:16px;">
                {{ $pendingChannels->onEachSide(1)->links() }}
            </div>
        </div>
    @endif

    {{-- Reel pending yang channel-nya tidak terdeteksi/tidak ada dalam daftar
         channel pending (misalnya ditambahkan manual lewat form di atas) --}}
    @if ($orphanReels->isNotEmpty())
        <div style="background:var(--bg-card);border:1px solid #6b5f2f;border-radius:12px;padding:20px;margin-bottom:32px;">
            <h2 style="font-size:0.95rem;font-weight:600;color:var(--gold);margin-bottom:4px;">
                🔍 Reel lain menunggu review ({{ $orphanReels->total() }})
            </h2>
            <p style="font-size:0.76rem;color:var(--text-muted);margin-bottom:14px;">
                Menampilkan {{ $orphanReels->count() }} dari {{ $orphanReels->total() }} reel (halaman {{ $orphanReels->currentPage() }}/{{ $orphanReels->lastPage() }})
            </p>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.82rem;color:var(--text-muted);margin-bottom:14px;">
                <input type="checkbox" id="select-all-reels" style="width:16px;height:16px;flex-shrink:0;accent-color:var(--gold);">
                Pilih semua reel di halaman ini
            </label>
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach ($orphanReels as $reel)
                    <div style="display:flex;gap:14px;background:var(--bg-panel);border:1px solid var(--border);border-radius:10px;padding:12px;align-items:center;">
                        <input
                            type="checkbox"
                            value="reel:{{ $reel->id }}"
                            class="bulk-select bulk-select-reel"
                            style="width:18px;height:18px;flex-shrink:0;accent-color:var(--gold);"
                        >
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
            <div style="margin-top:16px;">
                {{ $orphanReels->onEachSide(1)->links() }}
            </div>
        </div>
    @endif

    {{-- List reels --}}
    <div style="display:flex;flex-direction:column;gap:12px;">
        @forelse ($reels as $reel)
            <div style="display:flex;gap:14px;background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:12px;align-items:center;{{ !$reel->is_active ? 'opacity:0.5;' : '' }}">
                <img src="{{ $reel->thumbnail_url ?? asset('images/icons/icon-192.png') }}" alt="" style="width:96px;height:54px;object-fit:cover;border-radius:6px;flex-shrink:0;">

                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                        <div style="font-weight:600;font-size:0.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $reel->title ?? '(tanpa judul)' }}
                        </div>
                        @if($reel->is_sponsored)
                            <span style="background:rgba(217,166,83,0.15);border:1px solid rgba(217,166,83,0.4);color:var(--gold);padding:2px 8px;border-radius:10px;font-size:0.68rem;font-weight:600;flex-shrink:0;">
                                Sponsored
                            </span>
                        @endif
                    </div>
                    <div style="font-size:0.78rem;color:var(--text-muted);">
                        {{ $reel->channel_name ?? '—' }} · ditambahkan oleh {{ $reel->addedBy->name ?? 'system' }}
                        @if($reel->is_sponsored && $reel->sponsor_name)
                            · {{ $reel->sponsor_name }}
                        @endif
                        @if($reel->is_sponsored && $reel->impressions > 0)
                            · {{ number_format($reel->impressions) }} views
                        @endif
                    </div>
                </div>

                <div style="display:flex;gap:6px;flex-shrink:0;flex-wrap:wrap;">
                    <form method="POST" action="{{ route('dev.reels.toggle', $reel) }}">
                        @csrf
                        <button type="submit" style="background:var(--bg-panel);border:1px solid var(--border);color:var(--text);padding:6px 12px;border-radius:6px;font-size:0.78rem;cursor:pointer;">
                            {{ $reel->is_active ? 'Sembunyikan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <button type="button" onclick="toggleSponsoredModal({{ $reel->id }}, {{ $reel->is_sponsored ? 'true' : 'false' }}, '{{ addslashes($reel->sponsor_name ?? '') }}', '{{ addslashes($reel->sponsor_url ?? '') }}')" style="background:var(--bg-panel);border:1px solid var(--gold-dim);color:var(--gold);padding:6px 12px;border-radius:6px;font-size:0.78rem;cursor:pointer;">
                        {{ $reel->is_sponsored ? '★ Edit Sponsor' : '☆ Jadikan Sponsor' }}
                    </button>
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

{{-- Form tersembunyi untuk bulk approve/reject. Checkbox di atas TIDAK ada di
     dalam form ini (biar tidak nested dengan form Setujui/Tolak per-item yang
     sudah ada) — sebelum submit, JS menyalin item yang dicentang ke sini
     sebagai hidden input, lalu form ini di-submit dengan cara biasa (bukan
     AJAX/fetch), jadi tetap 1x request = 1x transaksi ke SQLite walau isinya
     puluhan/ratusan item. --}}
<form method="POST" action="{{ route('dev.reels.bulk-action') }}" id="bulk-form" style="display:none;">
    @csrf
    @method('PATCH')
    <input type="hidden" name="bulk_action" id="bulk-action-input" value="">
    <div id="bulk-hidden-inputs"></div>
</form>

{{-- Floating bar: muncul otomatis begitu ada checkbox yang dicentang --}}
<div id="bulk-bar" style="
    position:fixed;
    left:50%;
    bottom:20px;
    transform:translate(-50%, 120%);
    z-index:50;
    display:flex;
    align-items:center;
    gap:14px;
    background:#1a1410;
    border:1px solid var(--gold, #c9a869);
    box-shadow:0 8px 24px rgba(0,0,0,0.5);
    border-radius:999px;
    padding:10px 12px 10px 20px;
    transition:transform 0.22s ease;
    max-width:calc(100vw - 32px);
">
    <span id="bulk-count" style="color:var(--gold, #c9a869);font-size:0.85rem;font-weight:600;white-space:nowrap;">
        0 dipilih
    </span>
    <div style="display:flex;gap:8px;">
        <button type="button" id="bulk-approve-btn" style="background:#1f3d2a;border:1px solid #2f6b45;color:#a8e6b8;padding:9px 18px;border-radius:999px;font-size:0.82rem;font-weight:600;cursor:pointer;white-space:nowrap;">
            ✓ Setujui Terpilih
        </button>
        <button type="button" id="bulk-reject-btn" style="background:#3d1f1f;border:1px solid #6b2f2f;color:#e88;padding:9px 18px;border-radius:999px;font-size:0.82rem;font-weight:600;cursor:pointer;white-space:nowrap;">
            ✕ Tolak Terpilih
        </button>
    </div>
    <button type="button" id="bulk-clear-btn" title="Batalkan pilihan" style="background:transparent;border:none;color:var(--text-muted, #999);font-size:1.1rem;cursor:pointer;padding:4px 6px;line-height:1;">
        ×
    </button>
</div>

<script>
(function () {
    var checkboxes   = document.querySelectorAll('.bulk-select');
    var bar          = document.getElementById('bulk-bar');
    var countLabel   = document.getElementById('bulk-count');
    var form         = document.getElementById('bulk-form');
    var actionInput  = document.getElementById('bulk-action-input');
    var hiddenHolder = document.getElementById('bulk-hidden-inputs');

    function getSelected() {
        return Array.prototype.filter.call(checkboxes, function (cb) { return cb.checked; });
    }

    function updateBar() {
        var selected = getSelected();
        countLabel.textContent = selected.length + ' dipilih';
        bar.style.transform = selected.length > 0
            ? 'translate(-50%, 0)'
            : 'translate(-50%, 120%)';
    }

    function submitBulk(action) {
        var selected = getSelected();
        if (selected.length === 0) return;

        var confirmMsg = action === 'reject'
            ? 'Tolak ' + selected.length + ' item terpilih? Aksi ini tidak bisa dibatalkan.'
            : 'Setujui ' + selected.length + ' item terpilih?';
        if (!confirm(confirmMsg)) return;

        hiddenHolder.innerHTML = '';
        selected.forEach(function (cb) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected[]';
            input.value = cb.value;
            hiddenHolder.appendChild(input);
        });
        actionInput.value = action;
        form.submit();
    }

    checkboxes.forEach(function (cb) {
        cb.addEventListener('change', updateBar);
    });

    // "Pilih semua di halaman ini" — di-scope per section (channel / reel),
    // jadi centang-semua-channel gak ikut nyentang reel orphan atau sebaliknya.
    // Batasnya emang per HALAMAN, bukan per total data, karena checkbox yang
    // ada di DOM cuma yang lagi ditampilkan di halaman aktif (5 channel /
    // 20 reel), sisanya ada di halaman lain dan otomatis gak ke-affect.
    function bindSelectAll(masterId, groupClass) {
        var master = document.getElementById(masterId);
        if (!master) return;

        var group = document.querySelectorAll('.' + groupClass);

        master.addEventListener('change', function () {
            group.forEach(function (cb) { cb.checked = master.checked; });
            updateBar();
        });

        // Kalau user uncheck salah satu item satuan, master ikut ke-uncheck.
        // Kalau semua item ke-check manual satu-satu, master ikut ke-check.
        group.forEach(function (cb) {
            cb.addEventListener('change', function () {
                var allChecked = Array.prototype.every.call(group, function (item) {
                    return item.checked;
                });
                master.checked = allChecked;
            });
        });
    }

    bindSelectAll('select-all-channels', 'bulk-select-channel');
    bindSelectAll('select-all-reels', 'bulk-select-reel');

    document.getElementById('bulk-approve-btn').addEventListener('click', function () {
        submitBulk('approve');
    });
    document.getElementById('bulk-reject-btn').addEventListener('click', function () {
        submitBulk('reject');
    });
    document.getElementById('bulk-clear-btn').addEventListener('click', function () {
        checkboxes.forEach(function (cb) { cb.checked = false; });
        updateBar();
    });
})();

// ── Sponsored Modal ──
function toggleSponsoredModal(reelId, isSponsored, sponsorName, sponsorUrl) {
    var modal = document.getElementById('sponsored-modal');
    if (!modal) {
        // Create modal if it doesn't exist
        modal = document.createElement('div');
        modal.id = 'sponsored-modal';
        modal.innerHTML = `
            <div style="position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:100;display:flex;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target === this) toggleSponsoredModal()">
                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:24px;max-width:480px;width:100%;" onclick="event.stopPropagation()">
                    <h3 style="font-family:'Fraunces',serif;color:var(--gold);font-size:1.2rem;margin-bottom:16px;">Kelola Sponsored Content</h3>
                    <form id="sponsored-form" method="POST" style="display:flex;flex-direction:column;gap:16px;">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" id="sponsored-reel-id" name="reel_id" value="">
                        
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                            <input type="checkbox" id="sponsored-checkbox" name="is_sponsored" value="1" style="width:18px;height:18px;cursor:pointer;">
                            <span style="font-size:0.88rem;color:var(--text);">Jadikan sebagai konten sponsored</span>
                        </label>
                        
                        <div id="sponsored-fields" style="display:none;">
                            <div style="margin-bottom:14px;">
                                <label style="display:block;font-size:0.85rem;color:var(--text-muted);margin-bottom:6px;">Nama Sponsor (opsional)</label>
                                <input type="text" id="sponsor-name-input" name="sponsor_name" placeholder="Contoh: Gaming Gear Store" style="width:100%;background:var(--bg-panel);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:8px;font-size:0.88rem;">
                            </div>
                            <div>
                                <label style="display:block;font-size:0.85rem;color:var(--text-muted);margin-bottom:6px;">URL Sponsor (opsional)</label>
                                <input type="url" id="sponsor-url-input" name="sponsor_url" placeholder="https://example.com" style="width:100%;background:var(--bg-panel);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:8px;font-size:0.88rem;">
                            </div>
                        </div>
                        
                        <div style="display:flex;gap:10px;justify-content:flex-end;">
                            <button type="button" onclick="toggleSponsoredModal()" style="background:var(--bg-panel);border:1px solid var(--border);color:var(--text);padding:10px 20px;border-radius:8px;font-size:0.88rem;cursor:pointer;">Batal</button>
                            <button type="submit" style="background:var(--gold);color:#1a1410;border:none;padding:10px 20px;border-radius:8px;font-weight:600;font-size:0.88rem;cursor:pointer;">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Handle checkbox toggle
        document.getElementById('sponsored-checkbox').addEventListener('change', function() {
            document.getElementById('sponsored-fields').style.display = this.checked ? 'block' : 'none';
        });
    }
    
    if (typeof reelId !== 'undefined') {
        // Open modal with data
        modal.style.display = 'block';
        document.getElementById('sponsored-form').action = '/dev/reels/' + reelId + '/sponsored';
        document.getElementById('sponsored-reel-id').value = reelId;
        document.getElementById('sponsored-checkbox').checked = isSponsored;
        document.getElementById('sponsor-name-input').value = sponsorName || '';
        document.getElementById('sponsor-url-input').value = sponsorUrl || '';
        document.getElementById('sponsored-fields').style.display = isSponsored ? 'block' : 'none';
    } else {
        // Close modal
        modal.style.display = 'none';
    }
}

</script>
@endsection
