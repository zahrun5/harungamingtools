@extends('layouts.app')
@section('title', 'Build Saya - HarunGamingTools')
@section('content')
<div style="max-width:480px;margin:0 auto;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h1 style="font-family:'Fraunces',serif;color:var(--gold);font-size:1.3rem;margin:0;">
            Build Saya
        </h1>
        <a href="{{ route('builds.create') }}"
           style="padding:8px 14px;border-radius:8px;border:1px solid var(--gold);background:var(--gold);color:#1a1a1a;font-size:.82rem;font-weight:700;text-decoration:none;">
            + Build Baru
        </a>
    </div>

    @if(session('success'))
        <div style="background:rgba(46,160,67,.15);border:1px solid rgba(46,160,67,.4);color:#3fb950;padding:10px 14px;border-radius:8px;font-size:.85rem;margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif

    @forelse($builds as $build)
        <div style="background:var(--bg-card);border:1px solid {{ $build->is_active ? 'var(--gold)' : 'var(--border)' }};border-radius:12px;padding:14px 16px;margin-bottom:12px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                <span style="color:var(--text);font-weight:700;font-size:.95rem;">{{ $build->name }}</span>
                @if($build->is_active)
                    <span style="color:var(--gold);font-size:.68rem;font-weight:700;border:1px solid var(--gold);border-radius:6px;padding:2px 6px;">
                        AKTIF DI PROFIL
                    </span>
                @endif
            </div>

            @if($build->notes)
                <p style="color:var(--text-muted);font-size:.8rem;margin:0 0 10px;">{{ $build->notes }}</p>
            @endif

            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('builds.edit', $build) }}"
                   style="flex:1;text-align:center;padding:8px;border-radius:8px;border:1px solid var(--border);color:var(--text-muted);font-size:.8rem;font-weight:600;text-decoration:none;">
                    Edit
                </a>

                @unless($build->is_active)
                    <form method="POST" action="{{ route('builds.activate', $build) }}" style="flex:1;">
                        @csrf
                        <button type="submit"
                                style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--gold);background:none;color:var(--gold);font-size:.8rem;font-weight:600;cursor:pointer;">
                            Pasang ke Profil
                        </button>
                    </form>
                @endunless

                <form method="POST" action="{{ route('builds.destroy', $build) }}"
                      onsubmit="return confirm('Yakin mau hapus build ini? Gak bisa dibalikin.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            style="padding:8px 12px;border-radius:8px;border:1px solid #e5484d;background:none;color:#e5484d;font-size:.8rem;font-weight:600;cursor:pointer;">
                        🗑️
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div style="text-align:center;color:var(--text-muted);font-size:.85rem;padding:40px 0;">
            Belum punya build.<br>
            <a href="{{ route('builds.create') }}" style="color:var(--gold);">Bikin build pertama →</a>
        </div>
    @endforelse

</div>
@endsection
