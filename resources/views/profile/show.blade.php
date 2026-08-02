@extends('layouts.app')
@section('title', 'Profil - Albion Online Tools')
@section('content')
<div style="max-width:480px;margin:0 auto;">
    @if(session('success'))
        <div style="background:#1a3a2a;border:1px solid #2d6a4f;color:#52b788;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:.88rem;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#3a1a1a;border:1px solid #6a2d2d;color:#e74c3c;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:.88rem;">
            {{ session('error') }}
        </div>
    @endif

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
                <div style="color:var(--text-muted);font-size:.82rem;margin-top:2px;">
                    {{ $user->telegram_username ? '@'.$user->telegram_username : ($user->email ?? '-') }}
                </div>
            @else
                <div style="color:var(--gold);font-family:'Fraunces',serif;font-size:1.05rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $user->display_name }}
                </div>
                @if($user->custom_name)
                    <div style="color:var(--text-muted);font-size:.78rem;">{{ $user->name }}</div>
                @endif
                <div style="color:var(--text-muted);font-size:.82rem;margin-top:2px;">
                    {{ $user->telegram_username ? '@'.$user->telegram_username : ($user->email ?? '-') }}
                </div>
            @endif
        </div>
    </div>

    {{-- KARAKTER ALBION: verifikasi & identitas --}}
    <div style="margin-top:12px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:16px;">
        @if($user->verification_status === 'verified')
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="position:relative;width:40px;height:40px;flex-shrink:0;">
                    @if($user->albion_avatar_code && file_exists(public_path('images/albion-avatars/'.$user->albion_avatar_code.'.png')))
                        <img src="{{ asset('images/albion-avatars/'.$user->albion_avatar_code.'.png') }}" alt="avatar Albion"
                             style="width:100%;height:100%;border-radius:50%;background:var(--bg-panel);">
                    @endif
                    @if($user->albion_avatar_ring && file_exists(public_path('images/albion-rings/'.$user->albion_avatar_ring.'.png')))
                        <img src="{{ asset('images/albion-rings/'.$user->albion_avatar_ring.'.png') }}" alt="ring"
                             style="position:absolute;top:-4px;left:-4px;width:48px;height:48px;">
                    @endif
                </div>
                <div style="min-width:0;">
                    <div style="color:var(--text);font-size:.9rem;font-weight:700;">
                        {{ $user->albion_guild_name ?: 'Tanpa Guild' }}
                    </div>
                    @if($user->albion_alliance_name)
                        <div style="color:var(--text-muted);font-size:.78rem;margin-top:1px;">{{ $user->albion_alliance_name }}</div>
                    @endif
                </div>
            </div>
        @elseif($user->verification_status === 'pending')
            <div style="color:var(--text);font-size:.85rem;font-weight:600;margin-bottom:4px;">⏳ Menunggu Verifikasi</div>
            <p style="color:var(--text-muted);font-size:.78rem;margin-bottom:12px;">
                Karakter <strong>{{ $user->albion_ign }}</strong> sudah diajukan. Cek mail in-game kamu
                untuk kode verifikasi, lalu masukkan di bawah ini.
            </p>
            <form method="POST" action="/profile/albion/confirm">
                @csrf
                <div style="display:flex;gap:8px;">
                    <input type="text" name="verification_code" placeholder="HGT-XXXXXX"
                           value="{{ old('verification_code') }}"
                           style="flex:1;background:var(--bg-panel);border:1px solid {{ $errors->has('verification_code') ? '#e63946' : 'var(--border)' }};color:var(--text);padding:10px 14px;border-radius:8px;font-size:.9rem;outline:none;text-transform:uppercase;">
                    <button type="submit"
                            style="padding:10px 16px;background:var(--gold);color:var(--bg);font-weight:700;font-size:.85rem;border:none;border-radius:8px;cursor:pointer;white-space:nowrap;">
                        Konfirmasi
                    </button>
                </div>
                @error('verification_code')
                    <p style="color:#e63946;font-size:.78rem;margin-top:6px;">{{ $message }}</p>
                @enderror
            </form>
        @elseif($user->verification_status === 'rejected')
            <div style="color:#e63946;font-size:.85rem;font-weight:600;margin-bottom:8px;">✗ Verifikasi Ditolak</div>
            <p style="color:var(--text-muted);font-size:.78rem;margin-bottom:12px;">
                Pengajuan sebelumnya ditolak. Silakan ajukan ulang.
            </p>
            @include('profile._albion-submit-form')
        @else
            <div style="color:var(--text);font-size:.85rem;font-weight:600;margin-bottom:10px;">🎮 Hubungkan Karakter Albion</div>
            @include('profile._albion-submit-form')
        @endif
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
            <div style="display:flex;justify-content:flex-end;margin-bottom:10px;">
                <a href="{{ route('builds.index') }}" style="color:var(--text-muted);font-size:.78rem;text-decoration:underline;">Kelola build →</a>
            </div>
            @include('builds._paperdoll', ['build' => $user->activeBuild, 'editable' => true])
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

    {{-- STATS BAR --}}
    <div style="margin-top:16px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px;display:flex;">
        <div style="flex:1;text-align:center;padding:14px 8px;border-right:1px solid var(--border);">
            <div style="color:var(--gold);font-size:1.1rem;font-weight:700;">{{ number_format($user->points ?? 0) }}</div>
            <div style="color:var(--text-muted);font-size:.72rem;margin-top:2px;">Poin</div>
        </div>
        <div style="flex:1;text-align:center;padding:14px 8px;border-right:1px solid var(--border);">
            <div style="color:var(--gold);font-size:1.1rem;font-weight:700;">{{ $user->calculatorUsages()->count() ?? 0 }}</div>
            <div style="color:var(--text-muted);font-size:.72rem;margin-top:2px;">Kalkulator Dipakai</div>
        </div>
        <div style="flex:1;text-align:center;padding:14px 8px;">
            <div style="color:var(--gold);font-size:1.1rem;font-weight:700;">{{ $user->created_at->format('M Y') }}</div>
            <div style="color:var(--text-muted);font-size:.72rem;margin-top:2px;">Bergabung</div>
        </div>
    </div>

    {{-- EDIT PROFIL & ADMIN --}}
    <div style="margin-top:16px;display:flex;gap:10px;">
        <a href="/profile/edit" style="flex:1;text-align:center;padding:12px;border-radius:10px;border:1px solid var(--border);color:var(--text);font-size:.9rem;font-weight:600;transition:border-color .2s;text-decoration:none;"
           onmouseover="this.style.borderColor='var(--gold)';this.style.color='var(--gold)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
            ✏️ Edit Profil
        </a>
        @if(auth()->user()?->role === 'admin')
            <a href="{{ route('dev.index') }}" style="flex:1;text-align:center;padding:12px;border-radius:10px;border:1px solid var(--border);color:var(--text);font-size:.9rem;font-weight:600;transition:border-color .2s;text-decoration:none;"
               onmouseover="this.style.borderColor='var(--gold)';this.style.color='var(--gold)'"
               onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
                🛠️ Admin
            </a>
        @endif
    </div>

    {{-- LOGOUT --}}
    <form method="POST" action="/logout" style="margin-top:10px;">
        @csrf
        <button type="submit" style="width:100%;text-align:center;padding:12px;border-radius:10px;border:1px solid var(--border);background:none;color:var(--text-muted);font-size:.9rem;font-weight:600;cursor:pointer;transition:border-color .2s,color .2s;"
                onmouseover="this.style.borderColor='#c0392b';this.style.color='#e74c3c'"
                onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)'">
            🚪 Logout
        </button>
    </form>

</div>
@endsection