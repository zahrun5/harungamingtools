@extends('layouts.app')
@section('title', 'Verifikasi Albion (Admin) - Albion Online Tools')
@section('content')
<div style="max-width:720px;margin:0 auto;">

    <h1 style="font-family:'Fraunces',serif;color:var(--gold);font-size:1.3rem;margin-bottom:6px;">🎮 Verifikasi Karakter Albion</h1>
    <p style="color:var(--text-muted);font-size:.85rem;margin-bottom:24px;">
        Kirim mail in-game berisi kode ke karakter yang pengajuannya valid. User akan submit kode itu
        sendiri lewat halaman profil mereka setelah menerima mail.
    </p>

    @if(session('success'))
        <div style="background:#1a3a2a;border:1px solid #2d6a4f;color:#52b788;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:.88rem;">
            {{ session('success') }}
        </div>
    @endif

    @forelse($pendingUsers as $u)
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:16px;margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                <div style="min-width:0;">
                    <div style="color:var(--text);font-size:.95rem;font-weight:700;">{{ $u->albion_ign }}</div>
                    <div style="color:var(--text-muted);font-size:.78rem;margin-top:2px;">
                        Server: <span style="color:var(--text);">{{ ucfirst($u->albion_server) }}</span>
                        · Akun HGT: {{ $u->display_name }}
                        @if($u->telegram_username) (@{{ $u->telegram_username }}) @endif
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <div style="font-family:'Courier New',monospace;color:var(--gold);font-size:1rem;font-weight:700;letter-spacing:1px;">
                        {{ $u->verification_code }}
                    </div>
                    <div style="color:var(--text-muted);font-size:.72rem;margin-top:2px;">
                        Expired: {{ $u->verification_code_expires_at?->format('d M H:i') ?? '-' }}
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('dev.verifications.reject', $u) }}" style="margin-top:12px;"
                  onsubmit="return confirm('Tolak pengajuan {{ $u->albion_ign }}?');">
                @csrf
                <button type="submit"
                        style="padding:8px 14px;background:none;border:1px solid var(--border);color:var(--text-muted);border-radius:8px;font-size:.78rem;cursor:pointer;transition:border-color .2s,color .2s;"
                        onmouseover="this.style.borderColor='#c0392b';this.style.color='#e74c3c'"
                        onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)'">
                    ✗ Tolak Pengajuan
                </button>
            </form>
        </div>
    @empty
        <div style="text-align:center;padding:40px 20px;color:var(--text-muted);font-size:.9rem;">
            Tidak ada pengajuan verifikasi yang menunggu.
        </div>
    @endforelse

</div>
@endsection
