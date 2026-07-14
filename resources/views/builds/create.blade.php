@extends('layouts.app')
@section('title', 'Buat Build - HarunGamingTools')
@section('content')
<div style="max-width:480px;margin:0 auto;">

    <h1 style="font-family:'Fraunces',serif;color:var(--gold);font-size:1.3rem;margin-bottom:20px;">
        Buat Build Baru
    </h1>

    <form method="POST" action="{{ route('builds.store') }}">
        @csrf

        @include('builds._form')

        <div style="display:flex;gap:10px;margin-top:24px;">
            <a href="{{ route('profile.show') }}"
               style="flex:1;text-align:center;padding:12px;border-radius:10px;border:1px solid var(--border);color:var(--text-muted);font-size:.9rem;font-weight:600;text-decoration:none;">
                Batal
            </a>
            <button type="submit"
                    style="flex:2;text-align:center;padding:12px;border-radius:10px;border:1px solid var(--gold);background:var(--gold);color:#1a1a1a;font-size:.9rem;font-weight:700;cursor:pointer;">
                Simpan Build
            </button>
        </div>
    </form>

</div>
@endsection