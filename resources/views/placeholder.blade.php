@extends('layouts.app')

@section('title', $title . ' - HarunGamingTools')

@section('content')
    <div style="min-height:40vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:48px 24px;">
        <h1 style="font-family:'Fraunces',serif;color:var(--gold);margin-bottom:12px;">{{ $title }}</h1>
        <div style="font-size:3rem;margin-bottom:14px;">🚧</div>
        <p style="color:var(--text-muted);max-width:360px;">Fitur ini sedang kami bangun, nantikan update selanjutnya!</p>
    </div>
@endsection
