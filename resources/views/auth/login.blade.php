@extends('layouts.app')

@section('title', 'Login - HarunGamingTools')

@section('content')
<div style="max-width:400px;margin:60px auto;text-align:center;">
    <div style="font-size:2.2rem;margin-bottom:10px;">⚔️</div>
    <h1 style="font-family:'Fraunces',serif;color:var(--gold);font-size:1.4rem;margin-bottom:8px;">Masuk ke HarunGamingTools</h1>
    <p style="color:var(--text-muted);font-size:.88rem;margin-bottom:30px;">Login pakai Telegram buat masuk leaderboard & simpan riwayat aktivitas kamu.</p>

    <div style="background:var(--bg-card,#221C15);border:1px solid var(--border);border-radius:12px;padding:30px 20px;display:flex;justify-content:center;">
        <script async src="https://telegram.org/js/telegram-widget.js?22"
            data-telegram-login="HarunGamingToolsBot"
            data-size="large"
            data-auth-url="https://harungamingtools.fun/auth/telegram/callback"
            data-request-access="write">
        </script>
    </div>

</div>

    {{-- Divider --}}
    <div style="display:flex;align-items:center;gap:10px;margin:20px 0;">
        <div style="flex:1;height:1px;background:var(--border);"></div>
        <span style="color:var(--text-muted);font-size:.8rem;">atau</span>
        <div style="flex:1;height:1px;background:var(--border);"></div>
    </div>

    {{-- Tombol Google --}}
    <a href="{{ route('auth.google') }}" style="display:flex;align-items:center;justify-content:center;gap:10px;background:#fff;color:#333;font-size:.95rem;font-weight:600;padding:12px 20px;border-radius:10px;text-decoration:none;border:1px solid #ddd;">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="22" height="22" alt="Google">
        Masuk dengan Google
    </a>

    <p style="color:var(--text-muted);font-size:.78rem;margin-top:20px;">


</div>
@endsection
