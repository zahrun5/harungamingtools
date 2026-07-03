<div>
    <h2 style="font-family:'Fraunces',serif;color:var(--gold);font-size:1.1rem;margin:30px 0 14px;">🏆 Leaderboard</h2>

    @if($topUsers->isEmpty())
        <div style="background:var(--bg-card,#221C15);border:1px dashed var(--border);border-radius:10px;padding:24px;text-align:center;color:var(--text-muted);font-size:.85rem;">
            Belum ada data. Yuk jadi yang pertama pakai kalkulator!
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:8px;">
            @foreach($topUsers as $index => $user)
                @php
                    $rank = $index + 1;
                    $medal = match(true) {
                        $rank === 1 => '🥇',
                        $rank === 2 => '🥈',
                        $rank === 3 => '🥉',
                        default => null,
                    };
                @endphp
                <div style="display:flex;align-items:center;gap:12px;background:var(--bg-card,#221C15);border:1px solid var(--border);border-radius:10px;padding:10px 16px;{{ $rank <= 3 ? 'border-color:var(--gold);' : '' }}">
                    <div style="width:28px;text-align:center;font-size:{{ $medal ? '1.3rem' : '.9rem' }};font-weight:700;color:var(--text-muted);flex-shrink:0;">
                        {{ $medal ?? $rank }}
                    </div>
                    <img src="{{ $user->display_avatar }}" style="width:36px;height:36px;border-radius:50%;flex-shrink:0;border:1px solid var(--border);">
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $user->display_name }}</div>
                    </div>
                    <div style="font-family:'JetBrains Mono',monospace;color:var(--gold);font-weight:700;font-size:.88rem;white-space:nowrap;">
                        {{ $user->points }} EXP
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
