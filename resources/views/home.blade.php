@extends('layouts.app')

@section('title', 'Beranda - HarunGamingTools')

@section('content')
    <h1>Selamat datang di HarunGamingTools 👋</h1>
    <p>Tempat kumpulnya pemain Albion Online — kalkulator, leaderboard, dan komunitas.</p>

  <h2 style="font-family:'Fraunces',serif;color:var(--gold);font-size:1.1rem;margin:30px 0 14px;">🛠️ Kalkulator</h2>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:30px;">

<a href="/kalkulator/refine" style="background:var(--bg-card,#221C15);border:1px solid var(--border);border-radius:10px;padding:18px;display:block;transition:border-color .2s;">
    <div style="font-size:1.6rem;margin-bottom:8px;">⚙️</div>
    <div style="font-weight:600;margin-bottom:4px;">Kalkulator Refine</div>
    <div style="font-size:.8rem;color:var(--text-muted);">Hitung chain-refine logam, kayu, serat, kulit, dan batu.</div>
</a>

    <a href="/kalkulator/fishing" style="background:var(--bg-card,#221C15);border:1px solid var(--border);border-radius:10px;padding:18px;display:block;transition:border-color .2s;">
        <div style="font-size:1.6rem;margin-bottom:8px;">🎣</div>
        <div style="font-weight:600;margin-bottom:4px;">Kalkulator Mancing</div>
        <div style="font-size:.8rem;color:var(--text-muted);">Jual ikan atau dicincang, mana lebih untung?</div>
    </a>
    <a href="/kalkulator/flip" style="background:var(--bg-card,#221C15);border:1px solid var(--border);border-radius:10px;padding:18px;display:block;transition:border-color .2s;">
        <div style="font-size:1.6rem;margin-bottom:8px;">📊</div>
        <div style="font-weight:600;margin-bottom:4px;">Kalkulator Flipping</div>
        <div style="font-size:.8rem;color:var(--text-muted);">Hitung batas harga jual/beli biar gak rugi pajak.</div>
    </a>

<a href="/crafting" style="background:var(--bg-card,#221C15);border:1px solid var(--border);border-radius:10px;padding:18px;display:block;transition:border-color .2s;">
    <div style="font-size:1.6rem;margin-bottom:8px;">🛠️</div>
    <div style="font-weight:600;margin-bottom:4px;">Crafting</div>
    <div style="font-size:.8rem;color:var(--text-muted);">Segera hadir</div>
</a>
<a href="/catatan" style="background:var(--bg-card,#221C15);border:1px solid var(--border);border-radius:10px;padding:18px;display:block;transition:border-color .2s;">
    <div style="font-size:1.6rem;margin-bottom:8px;">📝</div>
    <div style="font-weight:600;margin-bottom:4px;">Catatan</div>
    <div style="font-size:.8rem;color:var(--text-muted);">Segera hadir</div>
</a>

</div>

@livewire('leaderboard')

@endsection
