@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 text-stone-200">

    @php
        // Warna & inisial "crest" dibuat deterministic dari nama, jadi konsisten
        // tiap render tanpa perlu data gambar dari API Albion (yang memang tidak ada).
        $rankMedal = function ($rank) {
            return match (true) {
                $rank === 1 => ['ring' => 'from-amber-300 via-yellow-400 to-amber-600', 'text' => 'text-amber-950', 'border' => 'border-amber-500/60'],
                $rank === 2 => ['ring' => 'from-stone-200 via-stone-300 to-stone-400', 'text' => 'text-stone-900', 'border' => 'border-stone-400/40'],
                $rank === 3 => ['ring' => 'from-orange-400 via-orange-500 to-orange-800', 'text' => 'text-orange-950', 'border' => 'border-orange-600/40'],
                default => ['ring' => 'from-stone-800 to-stone-800', 'text' => 'text-stone-400', 'border' => 'border-stone-800'],
            };
        };
        $crestColor = function ($name) {
            $hash = crc32((string) $name);
            $hue = $hash % 360;
            return "hsl({$hue}deg 48% 30%)";
        };
        $crestInitials = function ($name) {
            $name = trim((string) $name);
            if ($name === '') return '?';
            $parts = preg_split('/[\s\-_]+/', $name);
            $letters = '';
            foreach (array_slice($parts, 0, 2) as $p) {
                $letters .= mb_strtoupper(mb_substr($p, 0, 1));
            }
            return $letters !== '' ? $letters : mb_strtoupper(mb_substr($name, 0, 2));
        };
        $top3 = collect($rows)->take(3);
    @endphp

    {{-- Header --}}
    <div class="mb-5">
        <p class="text-[11px] uppercase tracking-[0.2em] text-amber-500/70 font-semibold mb-1">
            {{ ucfirst($server) }} &middot; {{ $period === 'daily' ? 'Harian' : 'Mingguan' }}
        </p>
        <h1 class="text-2xl font-extrabold text-amber-400 tracking-tight">Leaderboard</h1>
        <div class="h-px mt-3 bg-gradient-to-r from-amber-600/60 via-stone-700 to-transparent"></div>
    </div>

    {{-- Podium top 3 --}}
    @if ($top3->count() > 0)
        <div class="grid grid-cols-3 gap-2 mb-6 items-end">
            @foreach ($top3 as $p)
                @php
                    $name = $mode === 'guild' ? $p->guild_name : $p->player_name;
                    $medal = $rankMedal($p->rank);
                    $order = $p->rank === 1 ? 'order-2' : ($p->rank === 2 ? 'order-1' : 'order-3');
                    $lift = $p->rank === 1 ? '-mt-3' : '';
                @endphp
                <div class="{{ $order }} {{ $lift }} rounded-lg border {{ $medal['border'] }} bg-stone-900/60 p-3 text-center">
                    <div class="mx-auto mb-2 w-9 h-9 rounded-full bg-gradient-to-br {{ $medal['ring'] }} flex items-center justify-center text-xs font-bold {{ $medal['text'] }} shadow-inner">
                        {{ $p->rank }}
                    </div>
                    <div class="w-8 h-8 mx-auto rounded-md mb-1.5 flex items-center justify-center text-[10px] font-bold text-white/90"
                         style="background: {{ $crestColor($name) }}">
                        {{ $crestInitials($name) }}
                    </div>
                    <div class="text-xs font-semibold text-stone-200 truncate">{{ $name }}</div>
                    <div class="text-[10px] text-emerald-400 mt-0.5 font-medium">{{ number_format($p->kill_fame) }}</div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Selector server --}}
    <div class="flex gap-2 mb-3">
        @foreach (['americas' => 'Americas', 'europe' => 'Europe', 'asia' => 'Asia'] as $key => $label)
            <a href="{{ request()->fullUrlWithQuery(['server' => $key]) }}"
               class="px-3 py-1.5 rounded-full border text-sm transition-colors {{ $server === $key ? 'bg-amber-700 border-amber-500 text-white shadow-sm shadow-amber-900/40' : 'border-stone-700 text-stone-400 hover:border-stone-600' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Toggle Guild / Player --}}
    <div class="flex gap-1 mb-3 bg-stone-900/60 border border-stone-800 rounded-lg p-1">
        <a href="{{ request()->fullUrlWithQuery(['mode' => 'guild']) }}"
           class="flex-1 text-center py-1.5 rounded-md text-sm font-medium transition-colors {{ $mode === 'guild' ? 'bg-amber-700 text-white shadow-sm' : 'text-stone-400 hover:text-stone-200' }}">
            🛡️ Guild
        </a>
        <a href="{{ request()->fullUrlWithQuery(['mode' => 'player']) }}"
           class="flex-1 text-center py-1.5 rounded-md text-sm font-medium transition-colors {{ $mode === 'player' ? 'bg-amber-700 text-white shadow-sm' : 'text-stone-400 hover:text-stone-200' }}">
            ⚔️ Player
        </a>
    </div>

    {{-- Filter periode --}}
    <div class="flex gap-3 mb-4 text-sm">
        <a href="{{ request()->fullUrlWithQuery(['period' => 'daily']) }}"
           class="{{ $period === 'daily' ? 'text-amber-400 font-semibold' : 'text-stone-500 hover:text-stone-400' }}">Harian</a>
        <span class="text-stone-700">|</span>
        <a href="{{ request()->fullUrlWithQuery(['period' => 'weekly']) }}"
           class="{{ $period === 'weekly' ? 'text-amber-400 font-semibold' : 'text-stone-500 hover:text-stone-400' }}">Mingguan</a>
    </div>

    {{-- Search --}}
    <form method="GET" class="mb-4 flex gap-2">
        <input type="hidden" name="mode" value="{{ $mode }}">
        <input type="hidden" name="period" value="{{ $period }}">
        <input type="hidden" name="server" value="{{ $server }}">
        <div class="relative flex-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-600 text-sm">🔍</span>
            <input type="text" name="q" value="{{ $search }}"
                   placeholder="Cari {{ $mode === 'guild' ? 'guild' : 'player' }}..."
                   class="w-full bg-stone-900 border border-stone-700 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:border-amber-600/60 focus:ring-1 focus:ring-amber-600/40">
        </div>
        <button type="submit" class="px-4 py-2 bg-amber-700 hover:bg-amber-600 transition-colors rounded-lg text-sm font-medium">Cari</button>
    </form>

    {{-- Hasil search (kalau ada) --}}
    @if ($search !== '')
        @if ($searchResult)
            @php
                $srName = $mode === 'guild' ? $searchResult['guild_name'] : $searchResult['player_name'];
            @endphp
            <div class="mb-4 p-3 rounded-lg border flex items-center gap-3 {{ $searchResult['in_top_rank'] ? 'border-amber-600/60 bg-stone-800' : 'border-stone-700 bg-stone-900' }}">
                <div class="w-10 h-10 shrink-0 rounded-md flex items-center justify-center text-xs font-bold text-white/90"
                     style="background: {{ $crestColor($srName) }}">
                    {{ $crestInitials($srName) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center gap-2">
                        <div class="truncate">
                            <span class="font-semibold text-amber-300">
                                #{{ $searchResult['rank'] === 999 ? '999+' : $searchResult['rank'] }}
                            </span>
                            — {{ $srName }}
                            @if ($mode === 'player' && ($searchResult['guild_name'] ?? null))
                                <span class="text-stone-500 text-sm">[{{ $searchResult['guild_name'] }}]</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-sm text-stone-400 mt-1 flex gap-3">
                        <span class="text-emerald-400">⚔️ {{ number_format($searchResult['kill_fame']) }}</span>
                        <span class="text-rose-400">💀 {{ number_format($searchResult['death_fame']) }}</span>
                    </div>
                    @if (! $searchResult['in_top_rank'])
                        <div class="text-xs text-stone-500 mt-1">Di luar top rank periode ini</div>
                    @endif
                </div>
            </div>
        @else
            <div class="mb-4 text-sm text-stone-500">Tidak ditemukan.</div>
        @endif
    @endif

    {{-- Tabel ranking --}}
    <div class="rounded-lg border border-stone-800 overflow-x-auto">
        <table class="w-full text-sm min-w-[560px]">
            <thead class="bg-stone-900 text-stone-400">
                <tr>
                    <th class="px-3 py-2 text-left whitespace-nowrap">Rank</th>
                    <th class="px-3 py-2 text-left">{{ $mode === 'guild' ? 'Guild' : 'Player' }}</th>
                    <th class="px-3 py-2 text-right whitespace-nowrap">⚔️ Kill Fame</th>
                    <th class="px-3 py-2 text-right whitespace-nowrap">💀 Death Fame</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    @php
                        $isOwn = $mode === 'guild'
                            ? ($userGuildName && $row->guild_name === $userGuildName)
                            : ($userIgn && $row->player_name === $userIgn);
                        $rowName = $mode === 'guild' ? $row->guild_name : $row->player_name;
                        $medal = $rankMedal($row->rank);
                        $isTop3 = $row->rank <= 3;
                    @endphp
                    <tr class="border-t border-stone-800 {{ $isOwn ? 'bg-amber-900/20 border-l-2 border-l-amber-500' : 'hover:bg-stone-900/40' }}">
                        <td class="px-3 py-2 whitespace-nowrap">
                            @if ($isTop3)
                                <span class="inline-flex w-6 h-6 rounded-full bg-gradient-to-br {{ $medal['ring'] }} {{ $medal['text'] }} items-center justify-center text-xs font-bold">
                                    {{ $row->rank }}
                                </span>
                            @else
                                <span class="text-stone-400">{{ $row->rank }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 shrink-0 rounded-md flex items-center justify-center text-[9px] font-bold text-white/90"
                                     style="background: {{ $crestColor($rowName) }}">
                                    {{ $crestInitials($rowName) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="whitespace-nowrap truncate">{{ $rowName }}</div>
                                    @if ($mode === 'player' && $row->guild_name)
                                        <div class="text-stone-500 text-xs whitespace-nowrap truncate">[{{ $row->guild_name }}]</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2 text-right whitespace-nowrap text-emerald-400/90">{{ number_format($row->kill_fame) }}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap text-rose-400/90">{{ number_format($row->death_fame) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-4 text-center text-stone-500">
                            Belum ada data untuk periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
