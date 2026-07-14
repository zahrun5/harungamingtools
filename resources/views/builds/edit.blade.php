@extends('layouts.app')
@section('title', 'Edit Build - HarunGamingTools')
@section('content')
<div style="max-width:480px;margin:0 auto;">

    <h1 style="font-family:'Fraunces',serif;color:var(--gold);font-size:1.3rem;margin-bottom:20px;">
        Edit Build: {{ $build->name }}
    </h1>

    <form method="POST" action="{{ route('builds.update') }}">
        @csrf
        @method('PUT')

        @include('builds._form', ['build' => $build])

        <div style="display:flex;gap:10px;margin-top:24px;">
            <a href="{{ route('profile.show') }}"
               style="flex:1;text-align:center;padding:12px;border-radius:10px;border:1px solid var(--border);color:var(--text-muted);font-size:.9rem;font-weight:600;text-decoration:none;">
                Batal
            </a>
            <button type="submit"
                    style="flex:2;text-align:center;padding:12px;border-radius:10px;border:1px solid var(--gold);background:var(--gold);color:#1a1a1a;font-size:.9rem;font-weight:700;cursor:pointer;">
                Update Build
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('builds.destroy') }}"
          onsubmit="return confirm('Yakin mau hapus build ini? Gak bisa dibalikin.');"
          style="margin-top:12px;">
        @csrf
        @method('DELETE')
        <button type="submit"
                style="width:100%;text-align:center;padding:12px;border-radius:10px;border:1px solid #e5484d;background:none;color:#e5484d;font-size:.85rem;font-weight:600;cursor:pointer;">
            🗑️ Hapus Build
        </button>
    </form>

</div>
@endsection