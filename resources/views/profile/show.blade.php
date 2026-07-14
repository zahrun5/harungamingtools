@extends('layouts.app')
@section('title', 'Profil - HarunGamingTools')
@section('content')
<div style="max-width:480px;margin:0 auto;">
    @if(session('success'))
        <div style="background:#1a3a2a;border:1px solid #2d6a4f;color:#52b788;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:.88rem;">
            {{ session('success') }}
        </div>
    @endif

    {{-- HEADER: avatar + nama --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <img src="{{ $user->display_avatar }}" alt="avatar"
             style="width:52px;height:52px;border-radius:50%;border:2px solid var(--gold);background:var(--bg-panel);flex-shrink:0;">
        <div style="min-width:0;">
            <div style="color:var(--gold);font-family:'Fraunces',serif;font-size:1.05rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $user->display_name }}
            </div>
            @if($user->custom_name)
                <div style="color:var(--text-muted);font-size:.78rem;">{{ $user->name }}</div>
            @endif
            <div style="color:var(--text-muted);font-size:.82rem;margin-top:2px;">
                {{ $user->telegram_username ? '@'.$user->telegram_username : ($user->email ?? '-') }}
            </div>
        </div>
    </div>

    {{-- EQUIPMENT — bisa diklik langsung buat ganti item, tersimpan otomatis --}}
    @include('builds._paperdoll', ['build' => $build])

    {{-- INFO KARAKTER ALBION --}}
    <div style="margin-top:12px;text-align:center;">
        @if($user->albion_character_name ?? false)
            <div style="margin-top:10px;display:inline-flex;align-items:center;gap:6px;background:var(--bg-panel);border:1px solid var(--border);border-radius:20px;padding:6px 14px;">
                <span style="color:var(--text);font-size:.82rem;font-weight:600;">{{ $user->albion_character_name }}</span>
                @if($user->albion_guild_name ?? false)
                    <span style="color:var(--text-muted);font-size:.78rem;">· {{ $user->albion_guild_name }}</span>
                @endif
                @if($user->is_character_verified ?? false)
                    <span style="color:#52b788;font-size:.78rem;" title="Terverifikasi">✓</span>
                @endif
            </div>
        @endif
    </div>

    {{-- STATS BAR --}}
    <div style="margin-top:16px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px;display:flex;">
        <div style="flex:1;text-align:center;padding:14px 8px;border-right:1px solid var(--border);">
            <div style="color:var(--gold);font-size:1.1rem;font-weight:700;">{{ number_format($user->points ?? 0) }}</div>
            <div style="color:var(--text-muted);font-size:.72rem;margin-top:2px;">Poin</div>
        </div>
        <div style="flex:1;text-align:center;padding:14px 8px;border-right:1px solid var(--border);">
            <div style="color:var(--gold);font-size:1.1rem;font-weight:700;">{{ $user->calculatorUsages()->count() ?? 0 }}</div>
            <div style="color:var(--text-muted);font-size:.72rem;margin-top:2px;">Kalkulator Dipakai</div>
        </div>
        <div style="flex:1;text-align:center;padding:14px 8px;">
            <div style="color:var(--gold);font-size:1.1rem;font-weight:700;">{{ $user->created_at->format('M Y') }}</div>
            <div style="color:var(--text-muted);font-size:.72rem;margin-top:2px;">Bergabung</div>
        </div>
    </div>

    {{-- EDIT PROFIL --}}
    <div style="margin-top:16px;display:flex;gap:10px;">
        <a href="/profile/edit" style="flex:1;text-align:center;padding:12px;border-radius:10px;border:1px solid var(--border);color:var(--text);font-size:.9rem;font-weight:600;transition:border-color .2s;text-decoration:none;"
           onmouseover="this.style.borderColor='var(--gold)';this.style.color='var(--gold)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
            ✏️ Edit Profil
        </a>
        @if(!$build)
            <a href="{{ route('builds.create') }}" style="flex:1;text-align:center;padding:12px;border-radius:10px;border:1px solid var(--gold);color:var(--gold);font-size:.9rem;font-weight:600;text-decoration:none;">
                ➕ Build Baru
            </a>
        @endif
    </div>

</div>
@endsection