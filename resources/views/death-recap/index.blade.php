@extends('layouts.app')

@section('content')
<div style="max-width:700px;margin:0 auto;padding:20px;">
    <h1 style="color:#fff;font-size:22px;margin-bottom:16px;">Rekap Kematian</h1>

    <div style="display:flex;gap:8px;margin-bottom:20px;">
        <input
            type="text"
            id="characterInput"
            placeholder="Masukkan nama karakter..."
            style="flex:1;padding:10px 14px;border-radius:8px;border:1px solid #333;background:#1a1d24;color:#fff;font-size:15px;"
        >
        <button
            id="searchBtn"
            style="padding:10px 20px;border-radius:8px;border:none;background:#4f7cff;color:#fff;font-weight:600;cursor:pointer;"
        >Cari</button>
    </div>

    <div id="statusMsg" style="color:#999;font-size:14px;margin-bottom:12px;"></div>

    <div id="playerHeader" style="display:none;color:#fff;font-size:18px;font-weight:700;margin-bottom:12px;"></div>

    <div id="eventList" style="display:flex;flex-direction:column;gap:8px;"></div>

    <button
        id="loadMoreBtn"
        style="display:none;margin-top:16px;width:100%;padding:10px;border-radius:8px;border:1px solid #333;background:#1a1d24;color:#4f7cff;font-weight:600;cursor:pointer;"
    >Cari Lebih Lama</button>
</div>

<script>
const searchBtn = document.getElementById('searchBtn');
const characterInput = document.getElementById('characterInput');
const statusMsg = document.getElementById('statusMsg');
const playerHeader = document.getElementById('playerHeader');
const eventList = document.getElementById('eventList');
const loadMoreBtn = document.getElementById('loadMoreBtn');

let currentPlayer = null;
let currentOffset = 0;

function itemIconUrl(type) {
    if (!type) return null;
    return `https://render.albiononline.com/v1/item/${type}.png?size=48`;
}

function timeAgo(isoString) {
    const diffMs = Date.now() - new Date(isoString).getTime();
    const mins = Math.floor(diffMs / 60000);
    if (mins < 60) return `${mins} menit lalu`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours} jam lalu`;
    const days = Math.floor(hours / 24);
    return `${days} hari lalu`;
}

function renderRow(ev) {
    const isKill = ev.type === 'kill';
    const badgeColor = isKill ? '#2ecc71' : '#e74c3c';
    const badgeText = isKill ? 'KILL' : 'DEATH';
    const weaponIcon = itemIconUrl(ev.self_weapon_type);

    const row = document.createElement('div');
    row.style.cssText = `
        display:flex;align-items:center;gap:12px;padding:12px;border-radius:8px;
        background:#1a1d24;border-left:4px solid ${badgeColor};cursor:pointer;
    `;
    row.onclick = () => window.location.href = `/death-recap/event/${ev.event_id}`;

    row.innerHTML = `
        <img src="${weaponIcon}" width="40" height="40" style="border-radius:6px;background:#000;flex-shrink:0;" onerror="this.style.display='none'">
        <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:6px;">
                <span style="color:${badgeColor};font-weight:700;font-size:12px;">${badgeText}</span>
                <span style="color:#666;font-size:12px;">${timeAgo(ev.timestamp)}</span>
            </div>
            <div style="color:#fff;font-size:14px;margin-top:2px;">
                ${ev.self_name} <span style="color:#666;">vs</span> ${ev.opponent_name}
            </div>
            <div style="color:#888;font-size:12px;margin-top:2px;">
                ${ev.opponent_guild ?? '-'} &middot; IP ${ev.opponent_ip} &middot; ${ev.fight_type}
            </div>
        </div>
        <div style="text-align:right;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:4px;color:#f1c40f;font-size:13px;font-weight:600;">
                <img src="/images/icons/fame-icon.png" width="16" height="16">
                ${Number(ev.fame).toLocaleString('id-ID')}
            </div>
        </div>
    `;
    return row;
}

async function doSearch() {
    const name = characterInput.value.trim();
    if (!name) return;

    statusMsg.textContent = 'Mencari...';
    playerHeader.style.display = 'none';
    eventList.innerHTML = '';
    loadMoreBtn.style.display = 'none';

    try {
        const res = await fetch('/api/death-recap/search', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name }),
        });

        if (!res.ok) {
            const err = await res.json();
            statusMsg.textContent = err.message || 'Terjadi kesalahan.';
            return;
        }

        const data = await res.json();
        currentPlayer = data.player;
        currentOffset = data.next_offset;

        statusMsg.textContent = '';
        playerHeader.textContent = data.player.name;
        playerHeader.style.display = 'block';

        if (data.events.length === 0) {
            statusMsg.textContent = 'Belum ada riwayat kill/death.';
            return;
        }

        data.events.forEach(ev => eventList.appendChild(renderRow(ev)));
        loadMoreBtn.style.display = 'block';
    } catch (e) {
        statusMsg.textContent = 'Gagal menghubungi server, coba lagi.';
    }
}

async function doLoadMore() {
    if (!currentPlayer) return;

    loadMoreBtn.textContent = 'Memuat...';
    loadMoreBtn.disabled = true;

    try {
        const res = await fetch('/api/death-recap/load-more', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                character_id: currentPlayer.id,
                character_name: currentPlayer.name,
                offset: currentOffset,
            }),
        });

        const data = await res.json();
        currentOffset = data.next_offset;

        data.events.forEach(ev => eventList.appendChild(renderRow(ev)));

        loadMoreBtn.disabled = false;
        loadMoreBtn.textContent = 'Cari Lebih Lama';
        loadMoreBtn.style.display = data.has_more ? 'block' : 'none';

        if (data.events.length === 0) {
            loadMoreBtn.style.display = 'none';
        }
    } catch (e) {
        loadMoreBtn.textContent = 'Cari Lebih Lama';
        loadMoreBtn.disabled = false;
    }
}

searchBtn.addEventListener('click', doSearch);
characterInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') doSearch();
});
loadMoreBtn.addEventListener('click', doLoadMore);
</script>
<x-comments page="death-recap" />

@endsection