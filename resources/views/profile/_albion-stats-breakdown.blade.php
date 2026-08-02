@php
    $stats = $user->lifetime_statistics ?? [];
    $pve = $stats['PvE'] ?? [];
    $gathering = $stats['Gathering'] ?? [];
    $crafting = $stats['Crafting'] ?? [];
@endphp

@if(!empty($stats))
<div style="margin-top:12px;">
    <button type="button" onclick="toggleStatsBreakdown()" id="stats-toggle-btn"
            style="width:100%;padding:10px 14px;background:var(--bg-card);border:1px solid var(--border);color:var(--text-muted);border-radius:10px;font-size:.8rem;font-weight:600;cursor:pointer;display:flex;justify-content:space-between;align-items:center;">
        <span>📊 Statistik Lengkap</span>
        <span id="stats-toggle-icon">▼</span>
    </button>

    <div id="stats-breakdown" style="display:none;margin-top:8px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:16px;">

        {{-- Ringkasan fame per kategori --}}
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:16px;">
            <div>
                <div style="color:var(--gold);font-weight:700;font-size:.92rem;">{{ number_format($pve['Total'] ?? 0) }}</div>
                <div style="color:var(--text-muted);font-size:.7rem;">PvE Fame</div>
            </div>
            <div>
                <div style="color:var(--gold);font-weight:700;font-size:.92rem;">{{ number_format($gathering['All']['Total'] ?? 0) }}</div>
                <div style="color:var(--text-muted);font-size:.7rem;">Gathering Fame</div>
            </div>
            <div>
                <div style="color:var(--gold);font-weight:700;font-size:.92rem;">{{ number_format($crafting['Total'] ?? 0) }}</div>
                <div style="color:var(--text-muted);font-size:.7rem;">Crafting Fame</div>
            </div>
            <div>
                <div style="color:var(--gold);font-weight:700;font-size:.92rem;">{{ number_format($stats['FishingFame'] ?? 0) }}</div>
                <div style="color:var(--text-muted);font-size:.7rem;">Fishing Fame</div>
            </div>
            <div>
                <div style="color:var(--gold);font-weight:700;font-size:.92rem;">{{ number_format($stats['FarmingFame'] ?? 0) }}</div>
                <div style="color:var(--text-muted);font-size:.7rem;">Farming Fame</div>
            </div>
            <div>
                <div style="color:var(--gold);font-weight:700;font-size:.92rem;">{{ number_format($stats['CrystalLeague'] ?? 0) }}</div>
                <div style="color:var(--text-muted);font-size:.7rem;">Crystal League</div>
            </div>
        </div>

        {{-- PvE per region --}}
        @if(!empty($pve))
        <div style="margin-bottom:14px;padding-top:12px;border-top:1px solid var(--border);">
            <div style="color:var(--text);font-size:.8rem;font-weight:700;margin-bottom:8px;">⚔️ PvE per Region</div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                @foreach(['Royal','Outlands','Avalon','Hellgate','CorruptedDungeon','Mists'] as $region)
                    @if(($pve[$region] ?? 0) > 0)
                        <div style="background:var(--bg-panel);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:.75rem;">
                            <span style="color:var(--text-muted);">{{ $region }}:</span>
                            <span style="color:var(--text);font-weight:600;">{{ number_format($pve[$region]) }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Gathering per resource --}}
        @if(!empty($gathering))
        <div style="margin-bottom:14px;padding-top:12px;border-top:1px solid var(--border);">
            <div style="color:var(--text);font-size:.8rem;font-weight:700;margin-bottom:8px;">⛏️ Gathering per Resource</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                @foreach(['Fiber','Hide','Ore','Rock','Wood'] as $resource)
                    @php $r = $gathering[$resource] ?? []; @endphp
                    @if(($r['Total'] ?? 0) > 0)
                        <div style="display:flex;justify-content:space-between;font-size:.75rem;">
                            <span style="color:var(--text-muted);">{{ $resource }}</span>
                            <span style="color:var(--text);font-weight:600;">{{ number_format($r['Total']) }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Crafting per region --}}
        @if(!empty($crafting))
        <div style="padding-top:12px;border-top:1px solid var(--border);">
            <div style="color:var(--text);font-size:.8rem;font-weight:700;margin-bottom:8px;">🔨 Crafting per Region</div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                @foreach(['Royal','Outlands','Avalon'] as $region)
                    @if(($crafting[$region] ?? 0) > 0)
                        <div style="background:var(--bg-panel);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:.75rem;">
                            <span style="color:var(--text-muted);">{{ $region }}:</span>
                            <span style="color:var(--text);font-weight:600;">{{ number_format($crafting[$region]) }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

<script>
function toggleStatsBreakdown() {
    const el = document.getElementById('stats-breakdown');
    const icon = document.getElementById('stats-toggle-icon');
    const isHidden = el.style.display === 'none';
    el.style.display = isHidden ? 'block' : 'none';
    icon.textContent = isHidden ? '▲' : '▼';
}
</script>
@endif
