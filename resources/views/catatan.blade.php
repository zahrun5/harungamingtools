@extends('layouts.app')

@section('title', 'Catatan - HarunGamingTools')

@section('content')
<div style="text-align:center;padding:80px 20px;">
    <div style="font-size:3rem;margin-bottom:16px;">📝</div>
    <h1 style="font-family:'Fraunces',serif;color:var(--gold);font-size:1.4rem;margin-bottom:10px;">Catatan</h1>
    <p style="color:var(--text-muted);font-size:.9rem;">Fitur sedang dikembangkan. Sabar ya! 🚧</p>
    <a href="/" style="display:inline-block;margin-top:24px;color:var(--text-muted);font-size:.85rem;">← Kembali ke Beranda</a>
</div>

<x-comments page="catatan" />
@endsection
