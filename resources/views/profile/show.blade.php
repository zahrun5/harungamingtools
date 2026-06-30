@extends('layouts.app')
@section('title', 'Profil - HarunGamingTools')
@section('content')
<div style="max-width:480px;margin:0 auto;">

    @if(session('success'))
        <div style="background:#1a3a2a;border:1px solid #2d6a4f;color:#52b788;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:.88rem;">
            {{ session('success') }}
        </div>
    @endif

    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:32px 24px;text-align:center;">
        <img src="{{ $user->display_avatar }}" alt="avatar"
             style="width:96px;height:96px;border-radius:50%;border:3px solid var(--gold);margin-bottom:16px;background:var(--bg-panel);">
        <h1 style="font-family:'Fraunces',serif;color:var(--gold);font-size:1.4rem;">{{ $user->display_name }}</h1>
        @if($user->custom_name)
            <p style="color:var(--text-muted);font-size:.82rem;margin-top:4px;">{{ $user->name }}</p>
        @endif
        <p style="color:var(--text-muted);font-size:.82rem;margin-top:4px;">
            {{ $user->telegram_username ? '@'.$user->telegram_username : ($user->email ?? '-') }}
        </p>
    </div>

    <div style="margin-top:16px;display:flex;gap:10px;">
        <a href="/profile/edit" style="flex:1;text-align:center;padding:12px;border-radius:10px;border:1px solid var(--border);color:var(--text);font-size:.9rem;font-weight:600;transition:border-color .2s;"
           onmouseover="this.style.borderColor='var(--gold)';this.style.color='var(--gold)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
            ✏️ Edit Profil
        </a>
    </div>

</div>
@endsection
