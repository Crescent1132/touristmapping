<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tourist Mapping PH | Futuristic Discovery</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@500;600;700&family=Poppins:wght@600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__."\/assets\/css\/style.css") ?>">
    <style>
        :root{
            --bg:#000000; --bg2:#050510; --navy:#0a0a1a; --neon-purple:#a855f7; --neon-pink:#ec4899; --neon-blue:#38bdf8; --neon-orange:#fb923c; --neon-red:#f43f5e;
        }
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:radial-gradient(1200px 800px at 80% -10%, rgba(168,85,247,.25), transparent), radial-gradient(1000px 700px at 10% 10%, rgba(56,189,248,.18), transparent), linear-gradient(180deg,#000000 0%, #050510 40%, #000000 100%);color:#e5e7eb;line-height:1.7;overflow-x:hidden}
        header.fut{position:fixed;top:0;left:0;right:0;z-index:50;display:flex;align-items:center;justify-content:space-between;padding:14px 40px;background:linear-gradient(90deg, rgba(0,0,0,.9), rgba(10,10,26,.8));backdrop-filter:blur(10px);border-bottom:1px solid rgba(168,85,247,.35)}
        .logo{display:flex;align-items:center;gap:10px;font-family:Space Grotesk,Montserrat,sans-serif;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
        .logo-dot{width:10px;height:10px;border-radius:50%;background:radial-gradient(circle,#fff, var(--neon-purple));box-shadow:0 0 25px var(--neon-purple)}
        nav.fut-nav{display:flex;gap:26px;align-items:center}
        nav.fut-nav a{font-family:Montserrat,sans-serif;font-weight:600;text-decoration:none;color:#cbd5e1;letter-spacing:.08em;font-size:13px;position:relative}
        nav.fut-nav a::after{content:"";position:absolute;left:0;bottom:-6px;width:100%;height:1px;background:linear-gradient(90deg,var(--neon-blue),var(--neon-purple),var(--neon-pink));transform:scaleX(0);transform-origin:left;transition:transform .3s}
        nav.fut-nav a:hover{color:#fff}
        nav.fut-nav a:hover::after{transform:scaleX(1)}
        .hero-fut{position:relative;min-height:100vh;display:flex;align-items:center;padding:0 40px}
        .hero-grid{position:absolute;inset:0;background-image:radial-gradient(rgba(168,85,247,.12) 1px, transparent 1px);background-size:40px 40px;opacity:.25;mix-blend-mode:screen}
        .hero-waves{position:absolute;left:0;right:0;bottom:-1px;height:280px;background:radial-gradient(120% 100% at 50% 0%, transparent 60%, rgba(168,85,247,.25) 100%), url("data:image/svg+xml,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 1440 320\"%3E%3Cpath fill=\"rgba(56,189,248,0.18)\" d=\"M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,149.3C672,139,768,149,864,170.7C960,192,1056,224,1152,218.7C1248,213,1344,171,1392,149.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z\"/%3E%3C/svg%3E") bottom/cover no-repeat;filter:drop-shadow(0 -40px 80px rgba(168,85,247,.25))}
        .hero-content{position:relative;z-index:2;max-width:900px}
        .kicker{font-family:Montserrat,sans-serif;letter-spacing:.5em;text-transform:uppercase;color:var(--neon-blue);font-size:12px}
        .hero-title{font-family:Poppins,sans-serif;font-weight:800;font-size:clamp(48px,10vw,120px);line-height:.95;margin:18px 0;background:linear-gradient(90deg,#fff 0%, var(--neon-blue) 40%, var(--neon-purple) 70%, var(--neon-pink) 100%);-webkit-background-clip:text;color:transparent;text-transform:uppercase}
        .hero-sub{max-width:620px;font-size:clamp(16px,2vw,20px);color:#cbd5e1}
        .cta-row{display:flex;flex-wrap:wrap;gap:16px;margin-top:32px}
        .btn-neon{font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.12em;text-transform:uppercase;text-decoration:none;padding:14px 34px;border-radius:999px;border:1px solid transparent;background:linear-gradient(90deg,var(--neon-blue),var(--neon-purple),var(--neon-pink));color:#fff;box-shadow:0 0 60px rgba(168,85,247,.35)}
        .btn-ghost{font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.12em;text-transform:uppercase;text-decoration:none;padding:14px 34px;border-radius:999px;border:1px solid rgba(148,163,184,.6);color:#e5e7eb;background:transparent}
        .sphere{position:absolute;border-radius:50%;filter:blur(0);mix-blend-mode:screen;animation:float 12s ease-in-out infinite}
        .s1{width:220px;height:220px;right:12%;top:18%;background:radial-gradient(circle, rgba(236,72,153,.45), transparent 70%);box-shadow:0 0 120px rgba(236,72,153,.25)}
        .s2{width:160px;height:160px;right:28%;bottom:28%;background:radial-gradient(circle, rgba(56,189,248,.35), transparent 70%);box-shadow:0 0 90px rgba(56,189,248,.25);animation-delay:-4s}
        .s3{width:120px;height:120px;right:8%;bottom:18%;background:radial-gradient(circle, rgba(168,85,247,.35), transparent 70%);box-shadow:0 0 70px rgba(168,85,247,.25);animation-delay:-7s}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-18px)}}
        section{position:relative;padding:100px 40px}
        .section-title{font-family:Poppins,sans-serif;font-weight:700;font-size:clamp(28px,5vw,56px);text-transform:uppercase;margin:0}
        .section-sub{color:#94a3b8;max-width:720px}
        footer.fut{padding:40px;border-top:1px solid rgba(148,163,184,.25);text-align:center;color:#64748b}
        @media(max-width:900px){header.fut{padding:12px 20px} nav.fut-nav{gap:16px;flex-wrap:wrap} .hero-fut{padding:0 20px}}
    </style>
</head>
<body>
<header class="fut">
    <div class="logo"><span class="logo-dot"></span><span>TOURIST MAPPING PH</span></div>
    <nav class="fut-nav">
        <a href="index.php">HOME</a>
        <a href="#about">ABOUT US</a>
        <a href="#destinations">DESTINATIONS</a>
        <a href="map.php">TOURIST MAP</a>
        <a href="#contact">CONTACT US</a>
        <?php if($isAdmin):?><a href="admin/dashboard.php">ADMIN</a><?php elseif($isLoggedIn):?><a href="logout.php">LOGOUT</a><?php else:?><a href="login.php">LOGIN</a><?php endif;?>
    </nav>
</header>
<main>
    <section class="hero-fut">
        <div class="hero-grid"></div>
        <div class="hero-waves"></div>
        <div class="sphere s1"></div>
        <div class="sphere s2"></div>
        <div class="sphere s3"></div>
        <div class="hero-content">
            <div class="kicker">DISCOVER THE PHILIPPINES</div>
            <h1 class="hero-title">EXPLORE THE WORLD</h1>
            <p class="hero-sub">Discover beautiful destinations, explore hidden gems, and experience unforgettable adventures across the Philippines with our futuristic interactive mapping platform.</p>
            <div class="cta-row">
                <a class="btn-neon" href="map.php">EXPLORE MAP</a>
                <a class="btn-ghost" href="#destinations">DISCOVER MORE</a>
            </div>
        </div>
    </section>
    <section id="destinations">
        <div class="container" style="margin:0 auto">
            <div class="kicker">CURATED EXPERIENCES</div>
            <h2 class="section-title">TOURIST DESTINATIONS</h2>
            <p class="section-sub">Handpicked destinations with immersive details and interactive exploration.</p>
        </div>
    </section>
    <section id="about">
        <div class="container" style="margin:0 auto">
            <div class="kicker">OUR VISION</div>
            <h2 class="section-title">ABOUT US</h2>
            <p class="section-sub">A modern tourist mapping platform built to help travelers discover, navigate, and experience the best of the Philippines.</p>
        </div>
    </section>
    <section id="contact">
        <div class="container" style="margin:0 auto">
            <div class="kicker">GET IN TOUCH</div>
            <h2 class="section-title">CONTACT US</h2>
            <p class="section-sub">Have questions or suggestions? Reach out to our team.</p>
        </div>
    </section>
</main>
<footer class="fut">
    <div>&copy; <?= date("Y") ?> TOURIST MAPPING PH. ALL RIGHTS RESERVED.</div>
</footer>
</body>
</html>
