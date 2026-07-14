<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kelola Crafting Station — Dev</title>
<style>
  * { box-sizing: border-box; }
  body { font-family: -apple-system, Segoe UI, Roboto, sans-serif; background:#1a1a1a; color:#e5e5e5; margin:0; padding:20px; }
  h1 { font-size:20px; margin:0 0 16px; }
  h2 { font-size:15px; margin:0 0 10px; color:#c9a24b; }
  .layout { display:flex; gap:20px; flex-wrap:wrap; }
  .panel { background:#242424; border:1px solid #3a3a3a; border-radius:8px; padding:16px; }
  .col-list { flex:1 1 320px; min-width:280px; max-height:80vh; overflow-y:auto; }
  .col-editor { flex:2 1 480px; min-width:320px; }
  .station-item { border:1px solid #3a3a3a; border-radius:6px; padding:10px 12px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center; gap:8px; }
  .station-item .meta { font-size:12px; color:#999; }
  .station-item .name { font-weight:600; }
  .btn { background:#3a3a3a; color:#eee; border:none; border-radius:5px; padding:6px 10px; font-size:12px; cursor:pointer; }
  .btn:hover { background:#4a4a4a; }
  .btn-primary { background:#c9a24b; color:#1a1a1a; font-weight:600; }
  .btn-primary:hover { background:#d9b45c; }
  .btn-danger { background:#7a2e2e; }
  .btn-danger:hover { background:#932f2f; }
  .btn-sm { padding:4px 8px; font-size:11px; }
  input[type=text] { width:100%; background:#1a1a1a; border:1px solid #3a3a3a; color:#eee; border-radius:5px; padding:8px 10px; font-size:13px; }
  label.field { display:block; margin-bottom:12px; font-size:12px; color:#aaa; }
  label.field span { display:block; margin-bottom:4px; }
  .search-results { border:1px solid #3a3a3a; border-radius:6px; margin-top:6px; max-height:180px; overflow-y:auto; display:none; }
  .search-results div { padding:6px 10px; font-size:12px; cursor:pointer; border-bottom:1px solid #2e2e2e; }
  .search-results div:hover { background:#333; }
  .search-results .path { color:#888; font-size:11px; }
  .tree { border:1px solid #3a3a3a; border-radius:6px; padding:10px; max-height:340px; overflow-y:auto; font-size:13px; }
  .tree details { margin-left:14px; }
  .tree > details { margin-left:0; }
  .tree summary { cursor:pointer; padding:3px 0; }
  .tree label { display:flex; align-items:center; gap:6px; padding:3px 0 3px 14px; cursor:pointer; }
  .tree input[type=checkbox] { accent-color:#c9a24b; }
  .selected-count { font-size:12px; color:#c9a24b; margin:8px 0; }
  .toast { position:fixed; top:16px; right:16px; background:#2e7d32; color:#fff; padding:10px 16px; border-radius:6px; font-size:13px; display:none; z-index:50; }
  .toast.error { background:#7a2e2e; }
  .row { display:flex; gap:10px; }
  .row > * { flex:1; }
  a.preview-link { color:#7db8ff; font-size:12px; text-decoration:none; }
  a.preview-link:hover { text-decoration:underline; }
</style>
</head>
<body>

<h1>🛠️ Kelola Crafting Station</h1>

<div class="layout">
  <div class="panel col-list">
    <h2>Daftar Station</h2>
    <button class="btn btn-primary btn-sm" style="margin-bottom:10px;width:100%;" onclick="resetEditor()">+ Station Baru</button>
    <div id="stationList">
      @foreach($stations as $st)
        <div class="station-item" data-id="{{ $st->id }}" data-slug="{{ $st->slug }}" data-name="{{ $st->name }}">
          <div>
            <div class="name">{{ $st->name }}</div>
            <div class="meta">{{ $st->slug }} · {{ $st->categories_count }} kategori</div>
            <a class="preview-link" href="/crafting/{{ $st->slug }}" target="_blank">Preview halaman →</a>
          </div>
          <div style="display:flex; flex-direction:column; gap:4px;">
            <button class="btn btn-sm" onclick="editStation({{ $st->id }}, '{{ $st->slug }}', '{{ addslashes($st->name) }}')">Edit</button>
            <button class="btn btn-sm btn-danger" onclick="deleteStation({{ $st->id }}, '{{ addslashes($st->name) }}')">Hapus</button>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <div class="panel col-editor">
    <h2 id="editorTitle">Station Baru</h2>

    <label class="field">
      <span>Nama Station</span>
      <input type="text" id="stationName" placeholder="Contoh: Hunter's Lodge" oninput="autoSlug()">
    </label>
    <label class="field">
      <span>Slug (dipakai di URL /crafting/&lt;slug&gt;)</span>
      <input type="text" id="stationSlug" placeholder="hunters-lodge">
    </label>

    <h2>Cari Kategori</h2>
    <input type="text" id="searchBox" placeholder="Ketik nama kategori... (min 2 huruf)" oninput="onSearch()">
    <div class="search-results" id="searchResults"></div>

    <h2 style="margin-top:16px;">Browse Kategori (centang yang dipakai)</h2>
    <div class="tree" id="tree">Memuat...</div>
    <div class="selected-count" id="selectedCount">0 kategori dipilih</div>

    <div class="row" style="margin-top:10px;">
      <button class="btn btn-primary" onclick="saveStation()">💾 Simpan Station</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
let editingStationId = null;
let checkedIds = new Set();
let treeData = null;

function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast' + (isError ? ' error' : '');
  t.style.display = 'block';
  setTimeout(() => t.style.display = 'none', 2500);
}

function autoSlug() {
  if (editingStationId) return; // jangan auto-ubah slug pas mode edit
  const name = document.getElementById('stationName').value;
  document.getElementById('stationSlug').value = name
    .toLowerCase()
    .replace(/'/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '');
}

function resetEditor() {
  editingStationId = null;
  checkedIds = new Set();
  document.getElementById('editorTitle').textContent = 'Station Baru';
  document.getElementById('stationName').value = '';
  document.getElementById('stationSlug').value = '';
  document.getElementById('searchBox').value = '';
  renderTree();
  updateSelectedCount();
}

async function editStation(id, slug, name) {
  editingStationId = id;
  document.getElementById('editorTitle').textContent = 'Edit: ' + name;
  document.getElementById('stationName').value = name;
  document.getElementById('stationSlug').value = slug;

  const res = await fetch(`/dev/crafting-stations/${id}/categories`);
  const ids = await res.json();
  checkedIds = new Set(ids);
  renderTree();
  updateSelectedCount();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function deleteStation(id, name) {
  if (!confirm(`Hapus station "${name}"? Kategori market TIDAK ikut kehapus, cuma link-nya.`)) return;
  const res = await fetch(`/dev/crafting-stations/${id}`, { method: 'DELETE' });
  if (res.ok) {
    showToast('Station dihapus');
    setTimeout(() => location.reload(), 600);
  } else {
    showToast('Gagal menghapus', true);
  }
}

// ================= TREE =================
let nodeById = {};

function indexTree(nodes) {
  nodes.forEach(n => {
    nodeById[n.id] = n;
    if (n.children && n.children.length) indexTree(n.children);
  });
}

function getDescendantIds(node) {
  let ids = [];
  (node.children || []).forEach(c => {
    ids.push(c.id);
    ids = ids.concat(getDescendantIds(c));
  });
  return ids;
}

async function loadTree() {
  const res = await fetch('/dev/crafting-stations/categories-tree');
  treeData = await res.json();
  nodeById = {};
  indexTree(treeData);
  renderTree();
}

function renderTree() {
  const container = document.getElementById('tree');
  container.innerHTML = '';
  treeData.forEach(node => container.appendChild(buildNode(node)));
}

function buildNode(node) {
  const hasChildren = node.children && node.children.length > 0;

  if (!hasChildren) {
    const label = document.createElement('label');
    label.innerHTML = `<input type="checkbox" value="${node.id}" ${checkedIds.has(node.id) ? 'checked' : ''}> ${node.name}`;
    label.querySelector('input').addEventListener('change', (e) => toggleCheck(node.id, e.target.checked));
    return label;
  }

  const details = document.createElement('details');
  const summary = document.createElement('summary');
  summary.innerHTML = `<label style="display:inline-flex;" onclick="event.stopPropagation()"><input type="checkbox" value="${node.id}" ${checkedIds.has(node.id) ? 'checked' : ''}> <b>${node.name}</b></label>`;
  summary.querySelector('input').addEventListener('change', (e) => toggleCheck(node.id, e.target.checked));
  details.appendChild(summary);

  node.children.forEach(child => details.appendChild(buildNode(child)));
  return details;
}

function toggleCheck(id, checked) {
  if (checked) checkedIds.add(id); else checkedIds.delete(id);

  // Cascade: kalau node ini punya anak, ikut centang/uncheck semua descendant-nya
  // (soalnya item nempel di leaf, bukan di kategori pembungkus kayak "Bow").
  const node = nodeById[id];
  if (node && node.children && node.children.length) {
    getDescendantIds(node).forEach(descId => {
      if (checked) checkedIds.add(descId); else checkedIds.delete(descId);
      const cb = document.querySelector(`.tree input[type=checkbox][value="${descId}"]`);
      if (cb) cb.checked = checked;
    });
  }

  updateSelectedCount();
}

function updateSelectedCount() {
  document.getElementById('selectedCount').textContent = checkedIds.size + ' kategori dipilih';
}

// ================= SEARCH =================
let searchTimer;
function onSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(doSearch, 300);
}

async function doSearch() {
  const q = document.getElementById('searchBox').value.trim();
  const box = document.getElementById('searchResults');
  if (q.length < 2) { box.style.display = 'none'; return; }

  const res = await fetch('/dev/crafting-stations/categories-search?q=' + encodeURIComponent(q));
  const results = await res.json();

  if (!results.length) {
    box.innerHTML = '<div style="color:#888;">Tidak ada hasil</div>';
    box.style.display = 'block';
    return;
  }

  box.innerHTML = results.map(r => `
    <div onclick="quickAdd(${r.id})">
      ${r.name} ${checkedIds.has(r.id) ? '✅' : ''}
      <div class="path">${r.path}</div>
    </div>
  `).join('');
  box.style.display = 'block';
}

function quickAdd(id) {
  checkedIds.add(id);

  const node = nodeById[id];
  if (node && node.children && node.children.length) {
    getDescendantIds(node).forEach(descId => checkedIds.add(descId));
  }

  updateSelectedCount();
  renderTree(); // supaya checkbox di tree ikut ke-centang & expand kalau perlu
  doSearch(); // refresh centang di hasil pencarian
}

// ================= SAVE =================
async function saveStation() {
  const name = document.getElementById('stationName').value.trim();
  const slug = document.getElementById('stationSlug').value.trim();

  if (!name || !slug) { showToast('Nama & slug wajib diisi', true); return; }
  if (!checkedIds.size) { showToast('Pilih minimal 1 kategori', true); return; }

  const body = { name, slug };
  if (editingStationId) body.id = editingStationId;

  const res = await fetch('/dev/crafting-stations', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    body: JSON.stringify(body),
  });

  if (!res.ok) { showToast('Gagal menyimpan station', true); return; }
  const station = await res.json();

  const syncRes = await fetch(`/dev/crafting-stations/${station.id}/categories`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    body: JSON.stringify({ category_ids: Array.from(checkedIds) }),
  });

  if (syncRes.ok) {
    showToast('Tersimpan! Preview: /crafting/' + station.slug);
    setTimeout(() => location.reload(), 900);
  } else {
    showToast('Station tersimpan tapi kategori gagal disinkron', true);
  }
}

loadTree();
</script>

</body>
</html>
