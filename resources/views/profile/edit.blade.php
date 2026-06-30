@extends('layouts.app')
@section('title', 'Edit Profil - HarunGamingTools')
@section('content')
<div style="max-width:480px;margin:0 auto;">

    <h1 style="font-family:'Fraunces',serif;color:var(--gold);font-size:1.3rem;margin-bottom:24px;">✏️ Edit Profil</h1>

    <form method="POST" action="/profile/update">
        @csrf

		{{-- Nama Custom --}}
		<div style="margin-bottom:24px;">
		    <label style="display:block;font-size:.85rem;color:var(--text-muted);margin-bottom:8px;">Nama Tampilan</label>
		    <div style="display:flex;gap:8px;">
		        <input type="text" name="custom_name" id="custom_name"
		               value="{{ old('custom_name', $user->custom_name) }}"
		               placeholder="{{ $user->name }}"
		               style="flex:1;background:var(--bg-panel);border:1px solid {{ $errors->has('custom_name') ? '#e63946' : 'var(--border)' }};color:var(--text);padding:10px 14px;border-radius:8px;font-size:.95rem;outline:none;"
		               maxlength="20"
		               oninput="document.getElementById('char-count').textContent=this.value.length">
		        <button type="button" onclick="randomName()"
		                style="padding:10px 14px;background:var(--bg-panel);border:1px solid var(--border);color:var(--text-muted);border-radius:8px;cursor:pointer;font-size:.85rem;white-space:nowrap;">
		            🎲 Random
		        </button>
		    </div>
		    <div style="display:flex;justify-content:space-between;margin-top:6px;">
		        <p style="color:{{ $errors->has('custom_name') ? '#e63946' : 'var(--text-muted)' }};font-size:.78rem;">
		            @error('custom_name')
		                {{ $message }}
		            @else
		                Kosongkan untuk pakai nama asli. Max 20 karakter.
		            @enderror
		        </p>
		        <p style="color:var(--text-muted);font-size:.78rem;"><span id="char-count">{{ strlen(old('custom_name', $user->custom_name ?? '')) }}</span>/20</p>
		    </div>
		</div>

        {{-- Pilih Style Avatar --}}
        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:.85rem;color:var(--text-muted);margin-bottom:8px;">Style Avatar</label>
            <select name="avatar_style" id="avatar_style"
                    style="width:100%;background:var(--bg-panel);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:8px;font-size:.95rem;outline:none;">
                @foreach(['pixel-art','adventurer','avataaars','bottts','fun-emoji','lorelei','notionists','open-peeps','personas','rings','shapes'] as $style)
                    <option value="{{ $style }}" {{ ($user->avatar_style ?? 'pixel-art') === $style ? 'selected' : '' }}>
                        {{ $style }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Pilih Avatar --}}
        <div style="margin-bottom:24px;">
            <label style="display:block;font-size:.85rem;color:var(--text-muted);margin-bottom:8px;">Pilih Avatar</label>
            <input type="hidden" name="avatar_seed" id="avatar_seed" value="{{ old('avatar_seed', $user->avatar_seed) }}">

            <div id="avatar-grid" style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;">
                {{-- Diisi oleh JS --}}
            </div>
        </div>

        {{-- Preview --}}
        <div style="text-align:center;margin-bottom:24px;">
            <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:10px;">Preview</p>
            <img id="avatar-preview" src="{{ $user->display_avatar }}" alt="preview"
                 style="width:80px;height:80px;border-radius:50%;border:3px solid var(--gold);background:var(--bg-panel);">
        </div>

        <button type="submit"
                style="width:100%;padding:12px;background:var(--gold);color:var(--bg);font-weight:700;font-size:.95rem;border:none;border-radius:10px;cursor:pointer;">
            Simpan Profil
        </button>
        <a href="/profile" style="display:block;text-align:center;margin-top:12px;color:var(--text-muted);font-size:.85rem;">Batal</a>
    </form>
</div>

<script>
const seeds = ['Harun','Albion','Knight','Mage','Archer','Rogue','Healer','Tank','Bard','Druid',
                'Wolf','Bear','Eagle','Fox','Lion','Shadow','Storm','Blaze','Frost','Void'];

const styleEl = document.getElementById('avatar_style');
const seedEl  = document.getElementById('avatar_seed');
const grid    = document.getElementById('avatar-grid');
const preview = document.getElementById('avatar-preview');
const prefixes = ['Shadow','Iron','Storm','Blaze','Frost','Silver','Black','Wild','Dark','Swift','Stone','Blood'];
const suffixes = ['Knight','Archer','Mage','Blade','Wolf','Reaper','Scout','Warden','Hunter','Drake','Raven','Bear'];

function randomName() {
    const p = prefixes[Math.floor(Math.random() * prefixes.length)];
    const s = suffixes[Math.floor(Math.random() * suffixes.length)];
    const name = p + s;
    const input = document.getElementById('custom_name');
    input.value = name;
    document.getElementById('char-count').textContent = name.length;
}

function buildGrid() {
    const style = styleEl.value;
    grid.innerHTML = '';
    seeds.forEach(seed => {
        const url = `https://api.dicebear.com/9.x/${style}/svg?seed=${seed}`;
        const img = document.createElement('img');
        img.src = url;
        img.style.cssText = 'width:100%;aspect-ratio:1;border-radius:50%;border:2px solid var(--border);cursor:pointer;background:var(--bg-panel);transition:border-color .2s;';
        if (seed === seedEl.value) {
            img.style.borderColor = 'var(--gold)';
        }
        img.addEventListener('click', () => {
            document.querySelectorAll('#avatar-grid img').forEach(i => i.style.borderColor = 'var(--border)');
            img.style.borderColor = 'var(--gold)';
            seedEl.value = seed;
            preview.src = url;
        });
        grid.appendChild(img);
    });
}

styleEl.addEventListener('change', buildGrid);
buildGrid();
</script>
@endsection
