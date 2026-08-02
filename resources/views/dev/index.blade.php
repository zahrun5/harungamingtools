@extends('layouts.app')
@section('title', 'Dev Dashboard - HarunGamingTools')
@section('content')
<div style="max-width:480px;margin:0 auto;">
    <div style="margin-bottom:20px;">
        <div style="color:var(--gold);font-family:'Fraunces',serif;font-size:1.15rem;font-weight:700;">
            🛠️ Dev Dashboard
        </div>
        <div style="color:var(--text-muted);font-size:.8rem;margin-top:2px;">
            Semua tools admin dalam satu tempat.
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:10px;">

        <a href="/dev/crafting-stations" style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;text-decoration:none;transition:border-color .2s;"
           onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='var(--border)'">
            <div>
                <div style="color:var(--text);font-size:.9rem;font-weight:600;">⚒️ Crafting Stations</div>
                <div style="color:var(--text-muted);font-size:.75rem;margin-top:2px;">Kelola kategori per crafting station</div>
            </div>
            <span style="color:var(--text-muted);">→</span>
        </a>

        <a href="{{ route('dev.reels.index') }}" style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;text-decoration:none;transition:border-color .2s;"
           onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='var(--border)'">
            <div>
                <div style="color:var(--text);font-size:.9rem;font-weight:600;">🎬 Kelola Reels</div>
                <div style="color:var(--text-muted);font-size:.75rem;margin-top:2px;">Tambah, import, aktifkan/nonaktifkan reel</div>
            </div>
            <span style="color:var(--text-muted);">→</span>
        </a>

        <a href="{{ route('dev.verifications') }}" style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;text-decoration:none;transition:border-color .2s;"
           onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='var(--border)'">
            <div>
                <div style="color:var(--text);font-size:.9rem;font-weight:600;">✅ Verifikasi Albion</div>
                <div style="color:var(--text-muted);font-size:.75rem;margin-top:2px;">Approve/reject pengajuan verifikasi karakter</div>
            </div>
            <span style="color:var(--text-muted);">→</span>
        </a>

        <a href="{{ route('dev.recipe-items') }}" style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;text-decoration:none;transition:border-color .2s;"
           onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='var(--border)'">
            <div>
                <div style="color:var(--text);font-size:.9rem;font-weight:600;">📦 Recipe Items</div>
                <div style="color:var(--text-muted);font-size:.75rem;margin-top:2px;">Lihat & simpan item dari resep crafting</div>
            </div>
            <span style="color:var(--text-muted);">→</span>
        </a>

        <a href="{{ route('dev.saved-items') }}" style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;text-decoration:none;transition:border-color .2s;"
           onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='var(--border)'">
            <div>
                <div style="color:var(--text);font-size:.9rem;font-weight:600;">💾 Saved Items</div>
                <div style="color:var(--text-muted);font-size:.75rem;margin-top:2px;">Item yang sudah tersimpan di database</div>
            </div>
            <span style="color:var(--text-muted);">→</span>
        </a>

        <a href="{{ route('dev.test') }}" style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;text-decoration:none;transition:border-color .2s;"
           onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='var(--border)'">
            <div>
                <div style="color:var(--text);font-size:.9rem;font-weight:600;">🧪 Test Page</div>
                <div style="color:var(--text-muted);font-size:.75rem;margin-top:2px;">Halaman percobaan/debug</div>
            </div>
            <span style="color:var(--text-muted);">→</span>
        </a>

    </div>

    <div style="margin-top:20px;">
        <a href="/profile" style="color:var(--text-muted);font-size:.82rem;text-decoration:underline;">← Kembali ke profil</a>
    </div>
</div>
@endsection
