<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'HarunGamingTools — Hitung, Catat, Naik Peringkat')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,900&family=Sora:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#14110F; --bg-panel:#1C1712; --bg-card:#221C15;
    --gold:#D9A653; --gold-dim:#A9803F; --teal:#5FB3A8;
    --text:#EFE7D8; --text-muted:#9C9181; --border:#332B21;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:'Sora',sans-serif;line-height:1.5;}
  a{color:inherit;text-decoration:none;}
  .wrap{max-width:1180px;margin:0 auto;padding:0 24px;}

  header{position:sticky;top:0;z-index:50;background:rgba(20,17,15,0.92);backdrop-filter:blur(8px);border-bottom:1px solid var(--border);}
  .nav-row{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;max-width:1180px;margin:0 auto;}
  .logo{display:flex;align-items:center;gap:10px;font-family:'Fraunces',serif;font-weight:700;font-size:1.25rem;}
  .logo-mark{width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,var(--gold),var(--gold-dim));display:flex;align-items:center;justify-content:center;font-family:'JetBrains Mono',monospace;font-weight:700;color:var(--bg);font-size:0.85rem;}

  .btn-login{border:1px solid var(--border);color:var(--text);padding:8px 18px;border-radius:6px;font-size:0.88rem;font-weight:500;transition:border-color 0.2s,color 0.2s;}
  .btn-login:hover{border-color:var(--gold);color:var(--gold);}

  .profile{display:flex;align-items:center;gap:8px;background:var(--bg-panel);border:1px solid var(--border);padding:6px 12px 6px 6px;border-radius:30px;}
  .profile img{width:26px;height:26px;border-radius:50%;}
  .profile span{font-size:0.85rem;font-weight:600;}
  .profile button{background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:0.85rem;}
  .profile button:hover{color:var(--gold);}

  main{padding:48px 0;}

  .float-support{position:fixed;right:18px;bottom:18px;z-index:60;display:flex;flex-direction:column;gap:10px;align-items:flex-end;}
.float-btn{display:flex;align-items:center;gap:8px;font-size:0.8rem;font-weight:600;padding:10px 16px;border-radius:30px;background:var(--bg-panel);border:1px solid var(--border);color:var(--text-muted);box-shadow:0 4px 14px rgba(0,0,0,0.4);transition:border-color 0.2s,color 0.2s,transform 0.15s;}
.float-btn:hover{transform:translateY(-2px);border-color:var(--gold);color:var(--gold);}
.float-saweria:hover{border-color:var(--teal);color:var(--teal);}
  @media (max-width:480px){.float-btn span{display:none;}.float-btn{padding:11px 13px;}}

  footer{border-top:1px solid var(--border);padding:32px 0;margin-top:48px;}
  .footer-links{display:flex;gap:20px;flex-wrap:wrap;font-size:0.85rem;color:var(--text-muted);}
  .footer-links a:hover{color:var(--gold);}
  .footer-credit{margin-top:16px;font-size:0.8rem;color:var(--text-muted);}
</style>
</head>
<body>
<header>
  <div class="nav-row">
    <a href="/" class="logo"><span class="logo-mark">HG</span> HarunGamingTools</a>
    <div>
      @auth
        <div style="display:flex;align-items:center;gap:8px;">
          <a href="/profile" style="display:flex;align-items:center;background:var(--bg-panel);border:1px solid var(--border);padding:6px;border-radius:30px;">
            <img src="{{ auth()->user()->display_avatar }}" alt="foto" style="width:32px;height:32px;border-radius:50%;">
          </a>
          <form method="POST" action="/logout" style="display:inline;">
            @csrf
            <button type="submit" style="background:none;border:1px solid var(--border);color:var(--text-muted);padding:6px 14px;border-radius:20px;cursor:pointer;font-size:.85rem;">Logout</button>
          </form>
        </div>
      @else
        <a href="/login" class="btn-login">Masuk</a>
      @endauth
    </div>
  </div>
</header>

<main>
  <div class="wrap">
    @yield('content')
  </div>
</main>

<footer>
  <div class="wrap">
    <div class="footer-links">
      <a href="https://t.me/HarunGamingTools" target="_blank">Channel</a>
      <a href="https://t.me/HGTCommunity" target="_blank">Grup Komunitas</a>
      <a href="https://saweria.co/Mamangharun" target="_blank">Saweria</a>
      <a href="https://trakteer.id/sahabat%20sambungng" target="_blank">Trakteer</a>
    </div>
    <p class="footer-credit">Dibuat oleh Harun · HarunGamingTools © 2026</p>
  </div>
</footer>

<div class="float-support" aria-label="Dukung HarunGamingTools">
    <a href="https://saweria.co/Mamangharun" target="_blank" class="float-btn float-saweria">Saweria</a>
    <a href="https://trakteer.id/sahabat%20sambungng" target="_blank" class="float-btn">Trakteer</a>
    <a href="https://t.me/HarunGamingTools" target="_blank" class="float-btn">Channel</a>
    <a href="https://t.me/HGTCommunity" target="_blank" class="float-btn">Grup</a>
</div>

</body>
</html>
