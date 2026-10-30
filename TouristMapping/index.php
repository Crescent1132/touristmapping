<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
$stmt = $pdo->query("SELECT id, name, slug, region, province, municipality, description, image, latitude, longitude FROM destinations WHERE status = 'published' ORDER BY name ASC");
$destinations = $stmt->fetchAll();
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$isLoggedIn = !empty($_SESSION['user_id']);
$isAdmin = $isLoggedIn && ($_SESSION['role'] ?? '') === 'admin';
?>
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
    <style>
        :root{--bg:#000000;--navy:#0a0a1a;--neon-purple:#a855f7;--neon-pink:#ec4899;--neon-blue:#38bdf8;}
        *{box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:radial-gradient(1200px 800px at 80% -10%, rgba(168,85,247,.25), transparent), radial-gradient(1000px 700px at 10% 10%, rgba(56,189,248,.18), transparent), linear-gradient(180deg,#000000 0%, #050510 40%, #000000 100%);color:#e5e7eb;line-height:1.7;overflow-x:hidden}
        header.fut-header{position:fixed;top:0;left:0;right:0;z-index:9999;display:flex;align-items:center;justify-content:space-between;padding:14px 60px;background:rgba(0,0,0,.92);backdrop-filter:blur(14px);border-bottom:1px solid rgba(168,85,247,.4);box-shadow:0 10px 60px rgba(168,85,247,.15)}
        .logo-wrap{display:flex;align-items:center;gap:12px;text-decoration:none}
        .logo-dot{width:10px;height:10px;border-radius:50%;background:radial-gradient(circle,#fff,var(--neon-purple));box-shadow:0 0 30px var(--neon-purple)}
        .logo-text{font-family:Space Grotesk,Montserrat,sans-serif;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#fff;font-size:14px}
        nav.fut-nav{display:flex;align-items:center;gap:34px}
        nav.fut-nav a{font-family:Montserrat,sans-serif;font-weight:600;text-decoration:none;color:#cbd5e1;letter-spacing:.14em;font-size:12px;position:relative;text-transform:uppercase}
        nav.fut-nav a::after{content:"";position:absolute;left:0;bottom:-8px;width:100%;height:1px;background:linear-gradient(90deg,var(--neon-blue),var(--neon-purple),var(--neon-pink));transform:scaleX(0);transform-origin:left;transition:transform .35s ease}
        nav.fut-nav a:hover{color:#fff}
        nav.fut-nav a:hover::after{transform:scaleX(1)}
        .nav-actions{display:flex;align-items:center;gap:16px}
        .nav-btn{font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.12em;text-transform:uppercase;text-decoration:none;padding:9px 22px;border-radius:999px;border:1px solid rgba(148,163,184,.6);color:#e5e7eb;font-size:11px;background:transparent}
        .nav-btn-primary{border:none;background:linear-gradient(90deg,var(--neon-blue),var(--neon-purple),var(--neon-pink));color:#fff;box-shadow:0 0 40px rgba(168,85,247,.28)}
        .hero-fut{position:relative;min-height:100svh;display:flex;align-items:center;padding:120px 80px 80px}
        .hero-grid{position:absolute;inset:0;background-image:radial-gradient(rgba(168,85,247,.14) 1px, transparent 1px);background-size:44px 44px;opacity:.24}
        .hero-waves{position:absolute;left:0;right:0;bottom:0;height:320px;background:radial-gradient(120% 100% at 50% 0%, transparent 60%, rgba(168,85,247,.28) 100%), url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='rgba(56,189,248,0.16)' d='M0,160L60,170.7C120,181,240,203,360,197.3C480,192,600,160,720,149.3C840,139,960,149,1080,170.7C1200,192,1320,224,1380,240L1440,256L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z'/%3E%3C/svg%3E") bottom/cover no-repeat}
        .sphere{position:absolute;border-radius:50%;mix-blend-mode:screen;animation:float 12s ease-in-out infinite}
        .s1{width:240px;height:240px;right:14%;top:22%;background:radial-gradient(circle, rgba(236,72,153,.42), transparent 70%);box-shadow:0 0 130px rgba(236,72,153,.24)}
        .s2{width:180px;height:180px;right:30%;bottom:26%;background:radial-gradient(circle, rgba(56,189,248,.34), transparent 70%);animation-delay:-4s}
        .s3{width:140px;height:140px;right:10%;bottom:18%;background:radial-gradient(circle, rgba(168,85,247,.34), transparent 70%);animation-delay:-7s}
        .hero-content{position:relative;z-index:2;max-width:980px}
        .kicker{font-family:Montserrat,sans-serif;letter-spacing:.6em;text-transform:uppercase;color:var(--neon-blue);font-size:11px}
        .hero-title{font-family:Poppins,sans-serif;font-weight:800;font-size:clamp(56px,12vw,150px);line-height:.9;margin:20px 0;text-transform:uppercase;background:linear-gradient(90deg,#fff 0%, var(--neon-blue) 40%, var(--neon-purple) 70%, var(--neon-pink) 100%);-webkit-background-clip:text;color:transparent}
        .hero-sub{max-width:680px;font-size:clamp(16px,2vw,20px);color:#cbd5e1}
        .cta-row{display:flex;gap:18px;flex-wrap:wrap;margin-top:38px}
        .btn-neon{font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.14em;text-transform:uppercase;text-decoration:none;padding:16px 38px;border-radius:999px;background:linear-gradient(90deg,var(--neon-blue),var(--neon-purple),var(--neon-pink));color:#fff;box-shadow:0 0 70px rgba(168,85,247,.38)}
        .btn-ghost{font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.14em;text-transform:uppercase;text-decoration:none;padding:16px 38px;border-radius:999px;border:1px solid rgba(226,232,240,.6);color:#f8fafc;background:transparent}
        section.fut-sec{position:relative;padding:110px 80px}
        .section-title{font-family:Poppins,sans-serif;font-weight:800;font-size:clamp(34px,6vw,72px);text-transform:uppercase;margin:10px 0}
        .section-sub{color:#94a3b8;max-width:760px}
        .carousel-wrap{position:relative;margin-top:50px}
        .carousel-track{display:flex;align-items:center;gap:40px;overflow:hidden;padding:40px 0;scroll-behavior:smooth}
        .carousel-item{position:relative;flex:0 0 320px;height:420px;border-radius:24px;overflow:hidden;border:1px solid rgba(168,85,247,.4);box-shadow:0 40px 80px rgba(168,85,247,.25);transition:all .6s ease;cursor:pointer;animation:floatCard 8s ease-in-out infinite}
        .carousel-item img{width:100%;height:100%;object-fit:cover}
        .carousel-item.active{flex:0 0 380px;height:460px;box-shadow:0 60px 120px rgba(236,72,153,.35), 0 0 80px rgba(168,85,247,.4);border-color:rgba(236,72,153,.9)}
        .carousel-info{position:absolute;left:0;right:0;bottom:0;padding:24px;background:linear-gradient(180deg,transparent,rgba(0,0,0,.95))}
        .carousel-name{font-family:Poppins,sans-serif;font-weight:700;font-size:24px;margin:0}
        .carousel-meta{color:#cbd5e1}
        .carousel-arrow{position:absolute;top:50%;transform:translateY(-50%);width:56px;height:56px;border-radius:50%;border:1px solid rgba(226,232,240,.6);background:rgba(0,0,0,.7);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:10}
        .arrow-left{left:-10px}
        .arrow-right{right:-10px}
        .view-btn{display:inline-block;margin-top:16px;font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.1em;text-transform:uppercase;text-decoration:none;padding:12px 24px;border-radius:999px;background:linear-gradient(90deg,var(--neon-blue),var(--neon-purple),var(--neon-pink));color:#fff}
        .about-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:60px;align-items:center;margin-top:40px}
        .about-text p{color:#cbd5e1;margin:16px 0}
        .stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:30px}
        .stat-card{position:relative;padding:20px 22px;border-radius:14px;border:1px solid rgba(56,189,248,.4);background:radial-gradient(400px 200px at top, rgba(56,189,248,.12), transparent), rgba(5,5,16,.8);text-align:center;box-shadow:0 30px 60px rgba(56,189,248,.12)}
        .stat-num{font-family:Poppins,sans-serif;font-weight:800;font-size:clamp(28px,5vw,44px);background:linear-gradient(90deg,#fff,var(--neon-blue));-webkit-background-clip:text;color:transparent}
        .stat-label{font-family:Montserrat,sans-serif;letter-spacing:.18em;text-transform:uppercase;font-size:10px;color:#94a3b8}
        .about-img{position:relative;border-radius:20px;overflow:hidden;border:1px solid rgba(168,85,247,.5);box-shadow:0 60px 120px rgba(168,85,247,.28)}
        .about-img img{width:100%;height:100%;object-fit:cover;display:block}
        .contact-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:60px;margin-top:40px}
        .contact-card{padding:34px;border-radius:18px;border:1px solid rgba(56,189,248,.35);background:radial-gradient(500px 260px at top, rgba(56,189,248,.12), transparent), linear-gradient(180deg, rgba(10,10,26,.9), rgba(0,0,0,.95));box-shadow:0 50px 100px rgba(56,189,248,.14)}
        .contact-item{display:flex;align-items:flex-start;gap:16px;margin:18px 0}
        .contact-icon{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid rgba(56,189,248,.6);color:var(--neon-blue);box-shadow:0 0 40px rgba(56,189,248,.18)}
        .form-group{margin-bottom:18px}
        .form-label{display:block;font-family:Montserrat,sans-serif;letter-spacing:.12em;text-transform:uppercase;font-size:10px;color:#cbd5e1;margin-bottom:10px}
        .form-input{width:100%;padding:14px 16px;border-radius:12px;border:1px solid rgba(56,189,248,.4);background:rgba(5,5,16,.9);color:#e5e7eb;font-family:Inter,sans-serif}
        .form-input:focus{outline:none;border-color:var(--neon-blue);box-shadow:0 0 0 4px rgba(56,189,248,.14)}
        .btn-send{font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.14em;text-transform:uppercase;padding:14px 34px;border-radius:999px;border:none;background:linear-gradient(90deg,var(--neon-blue),var(--neon-purple));color:#fff;box-shadow:0 0 60px rgba(56,189,248,.26)}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-16px)}}
        @keyframes floatCard{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
        footer.fut-footer{padding:40px 80px;border-top:1px solid rgba(148,163,184,.25);text-align:center;color:#64748b}
        @media(max-width:1024px){header.fut-header{padding:12px 24px}nav.fut-nav{gap:18px;flex-wrap:wrap}.hero-fut{padding:110px 24px 70px}section.fut-sec{padding:80px 24px}.about-grid,.contact-grid{grid-template-columns:1fr;gap:40px}footer.fut-footer{padding:30px 24px}.carousel-item{flex:0 0 260px;height:360px}.carousel-item.active{flex:0 0 300px;height:400px}}
    </style>
</head>
<body>
<header class="fut-header">
    <a class="logo-wrap" href="index.php">
        <span class="logo-dot"></span>
        <span class="logo-text">TOURIST MAPPING PH</span>
    </a>
    <nav class="fut-nav">
        <a href="index.php">HOME</a>
        <a href="#destinations">DESTINATIONS</a>
        <a href="#about">ABOUT US</a>
        <a href="#contact">CONTACT US</a>
        <a href="map.php">TOURIST MAP</a>
    </nav>
    <div class="nav-actions">
        <?php if($isAdmin):?><a class="nav-btn nav-btn-primary" href="admin/dashboard.php">ADMIN DASHBOARD</a><?php elseif($isLoggedIn):?><form action="logout.php" method="POST" class="m-0"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="nav-btn">LOGOUT</button></form><?php else:?><a class="nav-btn" href="login.php">LOGIN</a><a class="nav-btn nav-btn-primary" href="register.php">REGISTER</a><?php endif;?>
    </div>
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
            <h1 class="hero-title">EXPLORE THE PHILIPPINES</h1>
            <p class="hero-sub">Discover beautiful destinations, explore hidden gems, and experience unforgettable adventures across the Philippines through our futuristic interactive mapping platform.</p>
            <div class="cta-row">
                <a class="btn-neon" href="map.php">EXPLORE MAP</a>
                <a class="btn-ghost" href="#destinations">DISCOVER MORE</a>
            </div>
        </div>
    </section>
    <section id="destinations" class="fut-sec">
        <div class="kicker">FEATURED DESTINATIONS</div>
        <h2 class="section-title">TOURIST DESTINATIONS</h2>
        <p class="section-sub">Explore our curated selection of breathtaking Philippine destinations.</p>
        <div class="carousel-wrap">
            <button class="carousel-arrow arrow-left" id="prevBtn"><i class="bi bi-chevron-left"></i></button>
            <div class="carousel-track" id="carouselTrack">
                <?php foreach($destinations as $i => $place): ?>
                    <article class="carousel-item <?= $i === 0 ? 'active' : '' ?>" data-id="<?= $place['id'] ?>" data-slug="<?= e($place['slug']) ?>" data-lat="<?= e($place['latitude']) ?>" data-lng="<?= e($place['longitude']) ?>">
                        <img src="<?= $place['image'] ? e($place['image']) : 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80' ?>" alt="<?= e($place['name']) ?>">
                        <div class="carousel-info">
                            <h3 class="carousel-name"><?= e($place['name']) ?></h3>
                            <div class="carousel-meta"><?= e($place['municipality']) ?>, <?= e($place['province']) ?></div>
                            <p class="carousel-meta mt-2"><?= e(mb_substr(strip_tags($place['description']), 0, 80)) ?>...</p>
                            <a class="view-btn" href="destination.php?slug=<?= urlencode($place['slug']) ?>">VIEW DETAILS</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <button class="carousel-arrow arrow-right" id="nextBtn"><i class="bi bi-chevron-right"></i></button>
        </div>
    </section>
    <section id="about" class="fut-sec">
        <div class="kicker">OUR VISION</div>
        <h2 class="section-title">ABOUT US</h2>
        <p class="section-sub">Empowering travelers to discover, navigate, and experience the best of the Philippines through immersive mapping and curated destination discovery.</p>
        <div class="about-grid">
            <div class="about-text">
                <p>Tourist Mapping PH is a futuristic tourist discovery and interactive mapping platform built to showcase the Philippines' rich natural beauty, vibrant culture, and historic landmarks. Our goal is to make travel planning simpler by bringing destinations, locations, and insights together in one seamless experience.</p>
                <p><strong style="color:#fff">Mission:</strong> To connect travelers with authentic Philippine destinations through an intelligent, modern, and user-friendly mapping experience.</p>
                <p><strong style="color:#fff">Vision:</strong> To be the Philippines' leading digital gateway for discovering hidden gems and iconic destinations with confidence.</p>
                <div class="stats">
                    <div class="stat-card"><div class="stat-num">7,641</div><div class="stat-label">Islands</div></div>
                    <div class="stat-card"><div class="stat-num">17</div><div class="stat-label">Regions</div></div>
                    <div class="stat-card"><div class="stat-num">∞</div><div class="stat-label">Endless Destinations</div></div>
                </div>
            </div>
            <div class="about-img">
                <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=1600&q=80" alt="Beautiful Philippine destination">
            </div>
        </div>
    </section>
    <section id="contact" class="fut-sec">
        <div class="kicker">GET IN TOUCH</div>
        <h2 class="section-title">CONTACT US</h2>
        <p class="section-sub">Have questions, suggestions, or partnerships in mind? We'd love to hear from you.</p>
        <div class="contact-grid">
            <div class="contact-card">
                <h3 style="font-family:Poppins,sans-serif;margin-top:0">Contact Information</h3>
                <div class="contact-item"><div class="contact-icon"><i class="bi bi-envelope"></i></div><div><div style="color:#fff">Email</div><div style="color:#94a3b8">info@touristmappingph.com</div></div></div>
                <div class="contact-item"><div class="contact-icon"><i class="bi bi-telephone"></i></div><div><div style="color:#fff">Phone</div><div style="color:#94a3b8">+63 (02) 1234 5678</div></div></div>
                <div class="contact-item"><div class="contact-icon"><i class="bi bi-geo-alt"></i></div><div><div style="color:#fff">Location</div><div style="color:#94a3b8">Manila, Philippines</div></div></div>
                <div class="contact-item"><div class="contact-icon"><i class="bi bi-share"></i></div><div><div style="color:#fff">Social</div><div style="color:#94a3b8">Follow us for updates</div></div></div>
            </div>
            <div class="contact-card">
                <h3 style="font-family:Poppins,sans-serif;margin-top:0">Send a Message</h3>
                <form>
                    <div class="form-group"><label class="form-label">Full Name</label><input class="form-input" type="text" placeholder="Enter your full name"></div>
                    <div class="form-group"><label class="form-label">Email Address</label><input class="form-input" type="email" placeholder="Enter your email"></div>
                    <div class="form-group"><label class="form-label">Subject</label><input class="form-input" type="text" placeholder="Enter subject"></div>
                    <div class="form-group"><label class="form-label">Message</label><textarea class="form-input" rows="5" placeholder="Write your message..."></textarea></div>
                    <button class="btn-send" type="button">SEND MESSAGE</button>
                </form>
            </div>
        </div>
    </section>
</main>
<footer class="fut-footer">
    <div>&copy; <?= date("Y") ?> TOURIST MAPPING PH. ALL RIGHTS RESERVED.</div>
</footer>
<script>
const track = document.getElementById('carouselTrack');
const items = document.querySelectorAll('.carousel-item');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
let index = 0;
function updateCarousel(){
    items.forEach((item,i)=>{ item.classList.toggle('active', i===index); });
    const offset = items[index].offsetLeft - track.offsetWidth/2 + items[index].offsetWidth/2;
    track.scrollTo({left: offset, behavior:'smooth'});
}
if(prevBtn) prevBtn.addEventListener('click', ()=>{ index = (index-1+items.length)%items.length; updateCarousel(); });
if(nextBtn) nextBtn.addEventListener('click', ()=>{ index = (index+1)%items.length; updateCarousel(); });
items.forEach((item,i)=>{
    item.addEventListener('click', (e)=>{
        if(e.target.classList.contains('view-btn')) return;
        index = i;
        updateCarousel();
        window.location.href = 'map.php?focus=' + item.dataset.slug;
    });
});
window.addEventListener('load', updateCarousel);
</script>
</body>
</html>
