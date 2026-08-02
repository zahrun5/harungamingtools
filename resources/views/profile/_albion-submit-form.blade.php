<form method="POST" action="/profile/albion/submit">
    @csrf
    <div style="margin-bottom:10px;">
        <select name="albion_server"
                style="width:100%;background:var(--bg-panel);border:1px solid {{ $errors->has('albion_server') ? '#e63946' : 'var(--border)' }};color:var(--text);padding:10px 14px;border-radius:8px;font-size:.9rem;outline:none;">
            <option value="">Pilih Server</option>
            <option value="asia" {{ old('albion_server') === 'asia' ? 'selected' : '' }}>Asia</option>
            <option value="europe" {{ old('albion_server') === 'europe' ? 'selected' : '' }}>Europe</option>
            <option value="americas" {{ old('albion_server') === 'americas' ? 'selected' : '' }}>Americas</option>
        </select>
        @error('albion_server')
            <p style="color:#e63946;font-size:.78rem;margin-top:4px;">{{ $message }}</p>
        @enderror
    </div>
    <div style="display:flex;gap:8px;">
        <input type="text" name="albion_ign" placeholder="Nama karakter in-game"
               value="{{ old('albion_ign') }}"
               style="flex:1;background:var(--bg-panel);border:1px solid {{ $errors->has('albion_ign') ? '#e63946' : 'var(--border)' }};color:var(--text);padding:10px 14px;border-radius:8px;font-size:.9rem;outline:none;">
        <button type="submit"
                style="padding:10px 16px;background:var(--gold);color:var(--bg);font-weight:700;font-size:.85rem;border:none;border-radius:8px;cursor:pointer;white-space:nowrap;">
            Ajukan
        </button>
    </div>
    @error('albion_ign')
        <p style="color:#e63946;font-size:.78rem;margin-top:6px;">{{ $message }}</p>
    @enderror
    <p style="color:var(--text-muted);font-size:.72rem;margin-top:8px;">
        Setelah diajukan, admin akan mengirim kode verifikasi lewat mail in-game ke karakter kamu.
    </p>
</form>
