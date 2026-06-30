@extends('layouts.app')

@section('title', 'Dashboard - HarunGamingTools')

@section('content')
<div style="max-width:600px;margin:30px auto;">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;">
        <img src="{{ auth()->user()->photo_url }}" style="width:56px;height:56px;border-radius:50%;border:2px solid var(--gold);">
        <div>
            <h1 style="font-family:'Fraunces',serif;color:var(--gold);font-size:1.2rem;">{{ auth()->user()->name }}</h1>
            <span style="font-size:.78rem;color:var(--text-muted);">
                @if(auth()->user()->role === 'admin')
                    🛡️ Admin
                @else
                    Member
                @endif
            </span>
        </div>
    </div>

    <div style="background:var(--bg-card,#221C15);border:1px solid var(--border);border-radius:10px;padding:18px;margin-bottom:20px;">
        <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:4px;">Total EXP Keaktifan</div>
        <div style="font-family:'Fraunces',serif;font-size:1.6rem;color:var(--gold);font-weight:700;">{{ auth()->user()->calculatorUsages()->count() }}</div>
    </div>

    <a href="/" style="color:var(--text-muted);font-size:.85rem;">← Kembali ke Beranda</a>
</div>
@endsection
