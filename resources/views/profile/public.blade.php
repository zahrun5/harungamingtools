@extends('layouts.app')
@section('title', ($user->display_name ?? $user->name) . ' - Albion Online Tools')
@section('content')
<div style="max-width:480px;margin:0 auto;">

    {{-- HEADER: avatar + nama (pakai nama karakter in-game kalau sudah verified) --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <img src="{{ $user->display_avatar }}" alt="avatar"
             style="width:52px;height:52px;border-radius:50%;border:2px solid var(--gold);background:var(--bg-panel);flex-shrink:0;">
        <div style="min-width:0;">
            @if($user->verification_status === 'verified')
                <div style="display:flex;align-items:center;gap:6px;">
                    <span style="color:var(--gold);font-family:'Fraunces',serif;font-size:1.05rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $user->albion_ign }}
                    </span>
                    <span style="color:#52b788;font-size:.85rem;flex-shrink:0;" title="Terverifikasi">✓</span>
                </div>
            @else
                <div style="color:var(--gold);font-family:'Fraunces',serif;font-size:1.05rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $user->display_name }}
                </div>
            @endif
        </div>
    </div>

    {{-- INFO GUILD (cuma tampil kalau sudah terverifikasi) --}}
    @if($user->verification_status === 'verified' && $user->albion_guild_name)
        <div style="margin-top:12px;text-align:center;">
            <div style="display:inline-flex;align-items:center;gap:6px;background:var(--bg-panel);border:1px solid var(--border);border-radius:20px;padding:6px 14px;position:relative;">
                @if($user->albion_avatar_ring && file_exists(public_path('images/albion-rings/'.$user->albion_avatar_ring.'.png')))
                    <img src="{{ asset('images/albion-rings/'.$user->albion_avatar_ring.'.png') }}" alt="ring" style="width:20px;height:20px;">
                @endif
                <span style="color:var(--text-muted);font-size:.78rem;">{{ $user->albion_guild_name }}</span>
            </div>
        </div>
    @endif

    {{-- STATS BAR --}}
    <div style="margin-top:16px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px;display:flex;">
        <div style="flex:1;text-align:center;padding:14px 8px;border-right:1px solid var(--border);">
            <div style="color:var(--gold);font-size:1.1rem;font-weight:700;">{{ number_format($user->points ?? 0) }}</div>
            <div style="color:var(--text-muted);font-size:.72rem;margin-top:2px;">Poin</div>
        </div>
        <div style="flex:1;text-align:center;padding:14px 8px;">
            <div style="color:var(--gold);font-size:1.1rem;font-weight:700;">{{ $user->created_at->format('M Y') }}</div>
            <div style="color:var(--text-muted);font-size:.72rem;margin-top:2px;">Bergabung</div>
        </div>
    </div>

    {{-- TAB: Build / Statistik --}}
    <div style="margin-top:16px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:16px;">
        <div style="display:flex;gap:6px;background:var(--bg-panel);border-radius:10px;padding:4px;margin-bottom:14px;">
            <button type="button" id="tab-btn-build" onclick="hgtSwitchProfileTab('build')"
                    style="flex:1;padding:8px;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;background:var(--gold);color:var(--bg);">
                🛡️ Build
            </button>
            <button type="button" id="tab-btn-stats" onclick="hgtSwitchProfileTab('stats')"
                    style="flex:1;padding:8px;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;background:none;color:var(--text-muted);">
                📊 Statistik
            </button>
        </div>

        <div id="tab-panel-build">
            @if($isOwnProfile)
                <div style="display:flex;justify-content:flex-end;margin-bottom:10px;">
                    <a href="{{ route('builds.index') }}" style="color:var(--text-muted);font-size:.78rem;text-decoration:underline;">Kelola build →</a>
                </div>
            @endif
            @include('builds._paperdoll', ['build' => $user->activeBuild, 'editable' => $isOwnProfile])
        </div>

        <div id="tab-panel-stats" style="display:none;">
            @if($user->verification_status === 'verified')
                <div style="display:flex;gap:16px;margin-bottom:12px;">
                    <div style="flex:1;">
                        <div style="color:var(--gold);font-size:.95rem;font-weight:700;">{{ number_format($user->kill_fame ?? 0) }}</div>
                        <div style="color:var(--text-muted);font-size:.7rem;">Kill Fame</div>
                    </div>
                    <div style="flex:1;">
                        <div style="color:var(--gold);font-size:.95rem;font-weight:700;">{{ number_format($user->death_fame ?? 0) }}</div>
                        <div style="color:var(--text-muted);font-size:.7rem;">Death Fame</div>
                    </div>
                </div>
                @include('profile._albion-stats-breakdown')
            @else
                <p style="color:var(--text-muted);font-size:.82rem;">Statistik cuma tersedia buat karakter yang sudah terverifikasi.</p>
            @endif
        </div>
    </div>

    <script>
        function hgtSwitchProfileTab(tab) {
            const buildPanel = document.getElementById('tab-panel-build');
            const statsPanel = document.getElementById('tab-panel-stats');
            const buildBtn = document.getElementById('tab-btn-build');
            const statsBtn = document.getElementById('tab-btn-stats');
            const active = { background: 'var(--gold)', color: 'var(--bg)' };
            const inactive = { background: 'none', color: 'var(--text-muted)' };

            if (tab === 'build') {
                buildPanel.style.display = '';
                statsPanel.style.display = 'none';
                Object.assign(buildBtn.style, active);
                Object.assign(statsBtn.style, inactive);
            } else {
                buildPanel.style.display = 'none';
                statsPanel.style.display = '';
                Object.assign(statsBtn.style, active);
                Object.assign(buildBtn.style, inactive);
            }
        }
    </script>

    {{-- EDIT PROFIL — cuma muncul kalau ini profil milik sendiri --}}
    @if($isOwnProfile)
        <div style="margin-top:16px;display:flex;gap:10px;">
            <a href="/profile/edit" style="flex:1;text-align:center;padding:12px;border-radius:10px;border:1px solid var(--border);color:var(--text);font-size:.9rem;font-weight:600;transition:border-color .2s;text-decoration:none;"
               onmouseover="this.style.borderColor='var(--gold)';this.style.color='var(--gold)'"
               onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
                ✏️ Edit Profil
            </a>
        </div>
    @endif

</div>
@endsection
