@extends('layouts.app')

@section('title', 'Reels — Albion Online Tools')

@section('meta_description', 'Watch Albion Online gaming videos and shorts. Tips, tricks, PvP highlights, gathering guides. Vertical video feed optimized for mobile.')

@section('meta_keywords', 'albion online videos, albion shorts, gaming videos, albion tips, pvp highlights, albion reels')

@section('og_description', 'Albion Online Reels: Short gaming videos, tips & tricks, PvP content. TikTok-style vertical feed for mobile gamers.')

@section('content')
<div class="reels-feed" id="reels-feed">
    <div class="reel-player-overlay" id="reel-player-overlay"></div>
    <button type="button" id="reel-mute-toggle" class="reel-mute-toggle">🔇 Tap video buat unmute</button>

    <button type="button" id="reel-nav-prev" class="reel-nav-btn reel-nav-prev" aria-label="Reel sebelumnya">
        <svg viewBox="0 0 24 24"><path d="M6 15l6-6 6 6"/></svg>
    </button>
    <button type="button" id="reel-nav-next" class="reel-nav-btn reel-nav-next" aria-label="Reel berikutnya">
        <svg viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
    </button>

    @forelse ($reels as $reel)
        <div class="reel-slide" data-youtube-id="{{ $reel->youtube_id }}" data-reel-id="{{ $reel->id }}" data-is-sponsored="{{ $reel->is_sponsored ? 'true' : 'false' }}">
            <div class="reel-player">
                <img src="{{ $reel->thumbnail_url }}" alt="{{ $reel->title }}" class="reel-thumb">
                <div class="reel-play-hint">▶</div>
            </div>

            <div class="reel-overlay">
                @if($reel->is_sponsored)
                <div class="reel-sponsored-badge">
                    <svg viewBox="0 0 24 24" width="14" height="14"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                    Sponsored
                    @if($reel->sponsor_name)
                        <span class="sponsor-name">· {{ $reel->sponsor_name }}</span>
                    @endif
                </div>
                @endif

                <div class="reel-info-actions">
                    <div class="reel-info">
                        <div class="reel-title">{{ $reel->title ?? 'Tanpa judul' }}</div>
                        <div class="reel-channel">{{ $reel->channel_name ?? '' }}</div>
                    </div>

                    <div class="reel-actions">
                        {{-- Like & Comment DITUNDA (trafik masih sepi) — jangan dihapus,
                             tinggal un-comment blok ini + blok senada di buildSlideElement()
                             (bagian JS infinite scroll di bawah) kalau mau diaktifkan lagi.
                        @auth
                            <button type="button" class="reel-action-btn" data-action="like" data-reel-id="{{ $reel->id }}">
                                ❤️<span>Suka</span>
                            </button>
                            <button type="button" class="reel-action-btn" data-action="comment" data-reel-id="{{ $reel->id }}">
                                💬<span>Komen</span>
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="reel-action-btn">
                                ❤️<span>Suka</span>
                            </a>
                            <a href="{{ route('login') }}" class="reel-action-btn">
                                💬<span>Komen</span>
                            </a>
                        @endauth
                        --}}
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="reel-empty">
            <p>Belum ada reel tersedia.</p>
        </div>
    @endforelse
</div>

<style>
    /* Halaman reels keluar dari layout normal (main padding, wrap max-width)
       supaya bisa full-screen di antara header & bottom-nav. */
    body.reels-page main{padding:0;}
    body.reels-page .wrap{padding:0;max-width:none;}
    body.reels-page{padding-bottom:0;} /* bottom-nav tetap fixed, kita hitung manual di JS */

    .reels-feed{
        position:fixed;
        left:0;right:0;
        /* top & bottom di-set oleh JS sesuai tinggi asli header & bottom-nav */
        overflow-y:scroll;
        scroll-snap-type:y mandatory;
        scroll-behavior:smooth;
        background:#000;
    }

    /*
     * PENTING soal urutan tumpuk (z-index):
     * - #reel-player-overlay (video, SATU instance, position:fixed, gak pernah pindah DOM) → z-index:1
     * - .reel-slide (thumbnail + caption + tombol, per-reel, ikut normal scroll flow) → z-index:2
     *
     * Slide sengaja ditaruh DI ATAS video. Karena overlay video posisinya fixed persis
     * menutupi area slide yang lagi keliatan, video otomatis "muncul" begitu thumbnail
     * punya slide itu di-transparankan (lihat .reel-slide.is-active .reel-player).
     * Caption & tombol like/komen milik slide TETAP di atas video karena mereka bagian
     * dari slide yang sama, bukan dari overlay video. Ini juga yang bikin tap-to-toggle
     * play/pause bisa jalan normal: klik/tap mendarat di elemen slide biasa (bukan iframe
     * cross-origin, bukan elemen yang di-pointer-events:none-in), jadi tidak pernah "ketelan".
     */
    .reel-slide{
        position:relative;
        z-index:2;
        height:100%;
        scroll-snap-align:start;
        scroll-snap-stop: always;
        display:flex;
        align-items:center;
        justify-content:center;
        overflow:hidden;
        cursor:pointer;
        /* SENGAJA tanpa background di sini. Kalau dikasih background solid di level
           .reel-slide, dia bakal nutupin video (z-index:1) terus walau .reel-player
           di bawah ini udah ditransparankan — background-nya harus nempel di
           .reel-player aja, biar ikut hilang bareng pas slide jadi aktif. */
    }
    .reel-player{
        position:relative;
        width:100%;
        height:100%;
        background:#000; /* nutupin video SEBELUM slide aktif; ikut transparan pas .is-active */
        transition:opacity .15s ease;
    }
    /* Begitu slide ini jadi video yang aktif diputar, thumbnail-nya kita transparankan
       supaya video yang ada di belakangnya (z-index:1) keliatan. Video-nya sendiri TIDAK
       pindah DOM sama sekali, cuma "nongol" karena penutupnya jadi tembus pandang. */
    .reel-slide.is-active .reel-player{
        opacity:0;
    }
    .reel-thumb{
        width:100%;
        height:100%;
        object-fit:cover;
        filter:brightness(0.75);
        -webkit-touch-callout:none; /* matiin context-menu "Salin/Download gambar" pas long-press */
        -webkit-user-select:none;
        user-select:none;
        pointer-events:none; /* biar tap tetap "milik" .reel-slide, bukan ketangkep <img> */
    }
    .reel-play-hint{
        position:absolute;
        top:50%;left:50%;
        transform:translate(-50%,-50%);
        font-size:2.4rem;
        color:rgba(255,255,255,0.85);
        pointer-events:none;
        text-shadow:0 2px 12px rgba(0,0,0,0.5);
    }

    .reel-mute-toggle{
        position:fixed;
        top:12px;left:50%;
        transform:translateX(-50%);
        z-index:20;
        background:rgba(0,0,0,0.6);
        color:#fff;
        border:1px solid rgba(255,255,255,0.25);
        padding:7px 14px;
        border-radius:20px;
        font-size:0.78rem;
        cursor:pointer;
        backdrop-filter:blur(4px);
        transition:opacity .3s;
    }
    .reel-mute-toggle.is-unmuted{opacity:0;pointer-events:none;}

    /* Tombol navigasi atas/bawah — cuma muncul di desktop (>=960px). Di mobile
       swipe/scroll udah cukup & tombol ini cuma bakal numpuk-numpuk gak perlu. */
    .reel-nav-btn{
        display:none;
        position:fixed;
        right:24px;
        z-index:20;
        width:44px;height:44px;
        border-radius:50%;
        background:rgba(0,0,0,0.55);
        border:1px solid rgba(255,255,255,0.25);
        color:#fff;
        align-items:center;justify-content:center;
        cursor:pointer;
        backdrop-filter:blur(4px);
        transition:opacity .2s,background .2s;
    }
    .reel-nav-btn:hover{background:rgba(0,0,0,0.8);}
    .reel-nav-btn:disabled{opacity:0.3;cursor:default;pointer-events:none;}
    .reel-nav-btn svg{width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round;}
    .reel-nav-prev{top:calc(50% - 56px);}
    .reel-nav-next{top:calc(50% + 12px);}

    @media (min-width:960px){
        .reel-nav-btn{display:flex;}
    }

    /* Player tunggal yang "melayang" tetap di posisi yang sama, nggak pernah
       dipindah-pindah ke DOM slide lain (biar nggak reload/reset ke video pertama). */
    .reel-player-overlay{
        position:fixed;
        left:0;right:0;
        z-index:1;
        pointer-events:none; /* semua tap ditangani lapisan slide di atasnya (z-index:2) */
    }
    .reel-player-overlay iframe{
        width:100%;height:100%;border:0;
        pointer-events:none;
    }

    .reel-pause-flash{
        position:absolute;
        top:50%;left:50%;
        transform:translate(-50%,-50%) scale(1);
        font-size:3.2rem;
        color:#fff;
        text-shadow:0 2px 16px rgba(0,0,0,0.6);
        opacity:0;
        pointer-events:none;
        z-index:3;
    }
    .reel-pause-flash.show{
        animation:reelFlash .5s ease;
    }
    @keyframes reelFlash{
        0%{opacity:0;transform:translate(-50%,-50%) scale(0.7);}
        30%{opacity:1;transform:translate(-50%,-50%) scale(1);}
        100%{opacity:0;transform:translate(-50%,-50%) scale(1);}
    }

    .reel-overlay{
        position:absolute;
        left:0;right:0;bottom:0;
        z-index:2;
        display:flex;
        flex-direction:column;
        gap:10px;
        padding:18px 16px;
        background:linear-gradient(to top, rgba(0,0,0,0.75), transparent);
        pointer-events:none; /* biar tap di area caption tetap toggle play/pause */
    }
    .reel-sponsored-badge{
        display:inline-flex;
        align-items:center;
        gap:5px;
        background:rgba(217,166,83,0.15);
        border:1px solid rgba(217,166,83,0.4);
        color:var(--gold);
        padding:5px 10px;
        border-radius:16px;
        font-size:0.72rem;
        font-weight:600;
        align-self:flex-start;
        backdrop-filter:blur(4px);
    }
    .reel-sponsored-badge svg{flex-shrink:0;}
    .reel-sponsored-badge .sponsor-name{
        color:rgba(255,255,255,0.85);
        font-weight:500;
    }
    .reel-info-actions{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:14px;
    }
    .reel-info{color:#fff;pointer-events:none;max-width:75%;}
    .reel-title{font-weight:600;font-size:0.95rem;margin-bottom:3px;}
    .reel-channel{font-size:0.78rem;color:rgba(255,255,255,0.75);}
    .reel-actions{display:flex;flex-direction:column;gap:14px;pointer-events:auto;}
    .reel-action-btn{
        display:flex;flex-direction:column;align-items:center;gap:3px;
        background:none;border:none;color:#fff;font-size:1.3rem;cursor:pointer;
    }
    .reel-action-btn span{font-size:0.65rem;font-weight:500;}
    .reel-action-btn.is-liked{color:#ff375f;}
    .reel-empty{
        height:100%;
        display:flex;align-items:center;justify-content:center;
        color:var(--text-muted);
    }
</style>

<script>
window.reelsAuthenticated = @json(auth()->check());
window.reelsLoginUrl = @json(route('login'));
window.reelsMoreUrl = @json(route('reels.more'));
window.reelsTrackImpressionUrl = @json(route('reels.track-impression'));
window.reelsCsrfToken = @json(csrf_token());

document.addEventListener('DOMContentLoaded', function () {
    const feed = document.getElementById('reels-feed');
    if (!feed) return;

    document.body.classList.add('reels-page');

    const overlay = document.getElementById('reel-player-overlay');
    const navPrevBtn = document.getElementById('reel-nav-prev');
    const navNextBtn = document.getElementById('reel-nav-next');
    let slides = Array.from(feed.querySelectorAll('.reel-slide'));
    if (slides.length === 0) return;

    // Track yang udah di-impression (biar ga double-track)
    const trackedImpressions = new Set();

    // ── 1. Hitung tinggi header & bottom-nav ASLI, sinkronkan ke feed & overlay ──
    function layoutFeed() {
        const header = document.querySelector('header');
        const bottomNav = document.querySelector('.bottom-nav');
        // >=960px, .bottom-nav berubah jadi sidebar kiri (lihat media query di app.blade.php),
        // jadi dimensinya harus dibaca sebagai LEBAR, bukan TINGGI. Breakpoint ini harus
        // selalu sama dengan @media (min-width:960px) di layout utama.
        const isSidebar = window.matchMedia('(min-width: 960px)').matches;

        const headerH = header ? header.offsetHeight : 0;
        const navH = (!isSidebar && bottomNav) ? bottomNav.offsetHeight : 0;
        const navW = (isSidebar && bottomNav) ? bottomNav.offsetWidth : 0;
        const heightCss = `calc(100vh - ${headerH}px - ${navH}px)`;

        feed.style.top = headerH + 'px';
        feed.style.bottom = navH + 'px';
        feed.style.left = navW + 'px';
        feed.style.height = heightCss;

        overlay.style.top = headerH + 'px';
        overlay.style.bottom = navH + 'px';
        overlay.style.left = navW + 'px';
        overlay.style.height = heightCss;
    }

    layoutFeed();
    window.addEventListener('resize', layoutFeed);
    window.addEventListener('load', layoutFeed);

    // ── 2. Preferensi mute persisten ──
    const MUTE_KEY = 'reelsMuted';
    let isMuted = localStorage.getItem(MUTE_KEY) !== 'false'; // default: muted

    const muteToggle = document.getElementById('reel-mute-toggle');
    function setMuted(next) {
        isMuted = next;
        localStorage.setItem(MUTE_KEY, String(isMuted));
        muteToggle.classList.toggle('is-unmuted', !isMuted);
        muteToggle.textContent = isMuted ? '🔇 Tap video buat unmute' : '🔊';
        if (player && typeof player.mute === 'function') {
            isMuted ? player.mute() : player.unMute();
        }
    }
    muteToggle.classList.toggle('is-unmuted', !isMuted);
    muteToggle.textContent = isMuted ? '🔇 Tap video buat unmute' : '🔊';

    muteToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        setMuted(!isMuted);
    });

    // ── 3. Load YouTube IFrame Player API resmi (sekali aja) ──
    let apiReady = false;
    let apiReadyCallback = null;
    window.onYouTubeIframeAPIReady = function () {
        apiReady = true;
        if (apiReadyCallback) { apiReadyCallback(); apiReadyCallback = null; }
    };
    const apiScript = document.createElement('script');
    apiScript.src = 'https://www.youtube.com/iframe_api';
    document.head.appendChild(apiScript);

    // ── 4. SATU player, nggak pernah dipindah DOM-nya. Ganti slide = ganti
    //      video di player yang sama via loadVideoById(). Loop manual via onStateChange. ──
    let player = null;
    let currentIndex = -1;
    let desiredVideoId = null; // video terakhir yang diminta user (kalau scroll cepat sebelum player siap)

    function markActiveSlide(index) {
        slides.forEach((slide, i) => slide.classList.toggle('is-active', i === index));
        updateNavButtons(index);
    }

    // Prev disabled kalau udah di slide pertama. Next disabled kalau udah di slide
    // terakhir yang KE-LOAD — begitu batch baru nyampe (loadMoreReels), tombol next
    // otomatis balik aktif karena slides.length nambah.
    function updateNavButtons(index) {
        navPrevBtn.disabled = index <= 0;
        navNextBtn.disabled = index >= slides.length - 1;
    }

    navPrevBtn.addEventListener('click', () => {
        feed.scrollBy({ top: -feed.clientHeight, behavior: 'smooth' });
    });
    navNextBtn.addEventListener('click', () => {
        feed.scrollBy({ top: feed.clientHeight, behavior: 'smooth' });
    });

    function activateSlide(index) {
        if (index < 0 || index >= slides.length || index === currentIndex) return;
        currentIndex = index;
        desiredVideoId = slides[index].dataset.youtubeId;
        markActiveSlide(index);

        // Track impression untuk sponsored video
        const slide = slides[index];
        const reelId = slide.dataset.reelId;
        const isSponsored = slide.dataset.isSponsored === 'true';
        
        if (isSponsored && !trackedImpressions.has(reelId)) {
            trackedImpressions.add(reelId);
            fetch(window.reelsTrackImpressionUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.reelsCsrfToken,
                },
                body: JSON.stringify({ reel_id: parseInt(reelId) }),
            }).catch(() => {}); // Silent fail, ga ganggu UX
        }

        // Udah mepet ke slide terakhir yang ke-load → ambil batch baru sebelum user kehabisan.
        if (index >= slides.length - 10) {
            loadMoreReels();
        }

        if (player) {
            player.loadVideoById(desiredVideoId);
            isMuted ? player.mute() : player.unMute();
            return;
        }

        if (apiReady) {
            createPlayer();
        } else {
            apiReadyCallback = createPlayer;
        }
    }

    function createPlayer() {
        if (player) return; // jaga-jaga jangan sampai kebuat dua kali
        const playerDiv = document.createElement('div');
        overlay.appendChild(playerDiv);

        player = new YT.Player(playerDiv, {
            videoId: desiredVideoId,
            playerVars: {
                autoplay: 1,
                mute: 1, // wajib mulai muted biar browser izinin autoplay pertama
                playsinline: 1,
                controls: 0,
                rel: 0,
            },
            events: {
                onReady: (e) => {
                    if (!isMuted) e.target.unMute();
                    e.target.playVideo();
                },
                onStateChange: (e) => {
                    // Loop manual: video ini gak dianggap "playlist", jadi kita putar
                    // ulang sendiri dari awal tiap kali statusnya ENDED.
                    if (e.data === YT.PlayerState.ENDED) {
                        e.target.seekTo(0);
                        e.target.playVideo();
                    }
                },
            },
        });
    }

    // ── 5. Deteksi slide aktif via scroll (debounced) ──
    function getActiveIndex() {
        const slideH = feed.clientHeight;
        return Math.round(feed.scrollTop / slideH);
    }

    let scrollTimer = null;
    feed.addEventListener('scroll', () => {
        // Safety net: cek langsung dari posisi scroll fisik, gak gantung ke perhitungan
        // index/currentIndex. Ini jaring pengaman kalau overscroll/momentum-scroll bikin
        // getActiveIndex() "lompat" ke luar batas dan activateSlide() jadi return duluan
        // tanpa sempat manggil loadMoreReels().
        if (feed.scrollTop + feed.clientHeight >= feed.scrollHeight - feed.clientHeight * 2) {
            loadMoreReels();
        }

        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(() => activateSlide(getActiveIndex()), 120);
    }, { passive: true });

    // Mulai dari slide pertama
    activateSlide(0);

    // ── 6. Tap di slide (bukan di tombol suka/komen) → unmute pertama kali,
    //      abis itu toggle play/pause. Dipasang di tiap .reel-slide, bukan di
    //      iframe (yang cross-origin & pointer-events:none), jadi tap selalu
    //      "kena" elemen normal dan gak pernah ketelan. Dipisah jadi fungsi
    //      biar bisa dipasang juga ke slide baru hasil infinite scroll. ──
    function bindSlideEvents(slide) {
        slide.addEventListener('click', (e) => {
            if (e.target.closest('.reel-action-btn')) return; // biar tombol suka/komen tetap jalan sendiri
            if (!player || slide !== slides[currentIndex]) return;

            if (isMuted) {
                setMuted(false);
                return; // tap pertama cuma buat unmute, belum toggle play/pause
            }

            const state = player.getPlayerState();
            if (state === YT.PlayerState.PLAYING) {
                player.pauseVideo();
                flashPauseIcon(slide, '⏸');
            } else {
                player.playVideo();
                flashPauseIcon(slide, '▶');
            }
        });
    }

    slides.forEach(bindSlideEvents);

    function flashPauseIcon(slide, symbol) {
        let flash = slide.querySelector('.reel-pause-flash');
        if (!flash) {
            flash = document.createElement('div');
            flash.className = 'reel-pause-flash';
            slide.appendChild(flash);
        }
        flash.textContent = symbol;
        flash.classList.remove('show');
        void flash.offsetWidth; // restart animasi kalau di-tap berkali-kali cepat
        flash.classList.add('show');
    }

    // ── 6b. Infinite scroll: ambil batch reel baru pas user mepet ke ujung
    //       daftar yang sudah di-load, append ke feed tanpa reload halaman. ──
    let isFetchingMore = false;
    let hasMoreReels = true;
    const loadedReelIds = new Set(slides.map((s) => s.dataset.reelId));

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function buildSlideElement(reel) {
        const slide = document.createElement('div');
        slide.className = 'reel-slide';
        slide.dataset.youtubeId = reel.youtube_id;
        slide.dataset.reelId = reel.id;
        slide.dataset.isSponsored = reel.is_sponsored ? 'true' : 'false';

        // Like & Comment DITUNDA (trafik masih sepi) — samain sama blok Blade di atas.
        // Tinggal un-comment baris di bawah + hapus `''` kalau mau diaktifkan lagi.
        // const actionsHtml = window.reelsAuthenticated
        //     ? `<button type="button" class="reel-action-btn" data-action="like" data-reel-id="${reel.id}">❤️<span>Suka</span></button>
        //        <button type="button" class="reel-action-btn" data-action="comment" data-reel-id="${reel.id}">💬<span>Komen</span></button>`
        //     : `<a href="${window.reelsLoginUrl}" class="reel-action-btn">❤️<span>Suka</span></a>
        //        <a href="${window.reelsLoginUrl}" class="reel-action-btn">💬<span>Komen</span></a>`;
        const actionsHtml = '';

        const sponsoredBadge = reel.is_sponsored 
            ? `<div class="reel-sponsored-badge">
                <svg viewBox="0 0 24 24" width="14" height="14"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                Sponsored
                ${reel.sponsor_name ? `<span class="sponsor-name">· ${escapeHtml(reel.sponsor_name)}</span>` : ''}
               </div>`
            : '';

        slide.innerHTML = `
            <div class="reel-player">
                <img src="${escapeHtml(reel.thumbnail_url)}" alt="${escapeHtml(reel.title)}" class="reel-thumb">
                <div class="reel-play-hint">▶</div>
            </div>
            <div class="reel-overlay">
                ${sponsoredBadge}
                <div class="reel-info-actions">
                    <div class="reel-info">
                        <div class="reel-title">${escapeHtml(reel.title || 'Tanpa judul')}</div>
                        <div class="reel-channel">${escapeHtml(reel.channel_name || '')}</div>
                    </div>
                    <div class="reel-actions">${actionsHtml}</div>
                </div>
            </div>
        `;

        return slide;
    }

    function loadMoreReels() {
            if (isFetchingMore || !hasMoreReels) return;
            isFetchingMore = true;
    
            fetch(window.reelsMoreUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ exclude: Array.from(loadedReelIds) }),
            })
                .then((res) => {
                    if (!res.ok) throw new Error(`HTTP ${res.status} dari ${window.reelsMoreUrl}`);
                    return res.json();
                })
                .then((data) => {
                    hasMoreReels = Boolean(data.has_more);
                    (data.reels || []).forEach((reel) => {
                        const id = String(reel.id);
                        if (loadedReelIds.has(id)) return; // safety, kalau backend kebetulan ngirim dobel
                        loadedReelIds.add(id);
    
                        const slideEl = buildSlideElement(reel);
                        feed.appendChild(slideEl);
                        slides.push(slideEl);
                        bindSlideEvents(slideEl);
                    });
                    updateNavButtons(currentIndex);
                })
                .catch((err) => {
                    // Tetap gak ganggu UX (user masih bisa lanjut nonton yang udah ke-load,
                    // nanti dicoba lagi otomatis pas scroll trigger berikutnya), tapi log
                    // dulu biar ketauan kalau memang route/network-nya yang gagal.
                    console.error('loadMoreReels gagal:', err);
                })
                .finally(() => {
                    isFetchingMore = false;
                });
        }
    // ── 7. Tombol like/komen — placeholder, backend AJAX-nya dibuat di Tahap 4 ──
    feed.addEventListener('click', (e) => {
        const likeBtn = e.target.closest('[data-action="like"]');
        if (likeBtn) {
            likeBtn.classList.toggle('is-liked');
            console.log('like reel', likeBtn.dataset.reelId);
            return;
        }
        const commentBtn = e.target.closest('[data-action="comment"]');
        if (commentBtn) {
            console.log('comment reel', commentBtn.dataset.reelId);
        }
    });
});
</script>
@endsection
