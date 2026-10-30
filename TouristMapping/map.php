<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

// Require login before showing the map.
if (empty($_SESSION['user_id'])) {
    header('Location: index.php?login_required=1');
    exit;
}

// Get published tourist destinations with category.
$sql = "SELECT
            d.name,
            d.slug,
            d.municipality,
            d.province,
            d.description,
            d.latitude,
            d.longitude,
            d.image,
            c.name AS category_name
        FROM destinations d
        LEFT JOIN categories c ON d.category_id = c.id
        WHERE d.status = 'published'
        ORDER BY d.name ASC";

$stmt = $pdo->query($sql);
$destinations = $stmt->fetchAll();

// Get categories for filter
$catStmt = $pdo->query("SELECT DISTINCT c.name FROM categories c INNER JOIN destinations d ON d.category_id = c.id WHERE d.status = 'published' ORDER BY c.name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

// Convert PHP data to JSON for JavaScript.
$destinationsJson = json_encode(
    $destinations,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
);

if ($destinationsJson === false) {
    $destinationsJson = '[]';
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$isLoggedIn = !empty($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tourist Map | Tourist Mapping PH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@500;600;700&family=Poppins:wght@600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__.'/assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        :root{--bg:#000000;--bg2:#050510;--navy:#0a0a1a;--neon-purple:#a855f7;--neon-pink:#ec4899;--neon-blue:#38bdf8;}
        body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:radial-gradient(1200px 800px at 80% -10%, rgba(168,85,247,.18), transparent), linear-gradient(180deg,#000000 0%, #050510 100%);color:#e5e7eb}
        header.fut{position:fixed;top:0;left:0;right:0;z-index:60;display:flex;align-items:center;justify-content:space-between;padding:12px 40px;background:linear-gradient(90deg, rgba(0,0,0,.95), rgba(10,10,26,.85));backdrop-filter:blur(12px);border-bottom:1px solid rgba(168,85,247,.35)}
        .logo{display:flex;align-items:center;gap:10px;font-family:Space Grotesk,Montserrat,sans-serif;font-weight:700;letter-spacing:.12em;text-transform:uppercase;text-decoration:none;color:#fff}
        .logo-dot{width:10px;height:10px;border-radius:50%;background:radial-gradient(circle,#fff,var(--neon-purple));box-shadow:0 0 25px var(--neon-purple)}
        nav.fut-nav{display:flex;gap:22px;align-items:center}
        nav.fut-nav a{font-family:Montserrat,sans-serif;font-weight:600;text-decoration:none;color:#cbd5e1;letter-spacing:.08em;font-size:13px}
        nav.fut-nav a:hover{color:#fff}
        .map-page-wrap{padding-top:90px}
        .map-hero-fut{position:relative;text-align:center;padding:60px 20px}
        .map-hero-fut h1{font-family:Poppins,sans-serif;font-weight:800;font-size:clamp(36px,6vw,64px);text-transform:uppercase;margin:0;background:linear-gradient(90deg,#fff,var(--neon-blue),var(--neon-purple));-webkit-background-clip:text;color:transparent}
        .map-hero-fut p{color:#cbd5e1;margin:10px auto 0;max-width:680px}
        .map-shell{width:95%;max-width:1500px;margin:30px auto 60px;background:linear-gradient(180deg, rgba(10,10,26,.9), rgba(0,0,0,.95));border-radius:18px;border:1px solid rgba(168,85,247,.35);box-shadow:0 60px 120px rgba(168,85,247,.18);overflow:hidden}
        .map-topbar{display:flex;flex-wrap:wrap;gap:14px;align-items:center;padding:16px 24px;background:linear-gradient(90deg,rgba(56,189,248,.25),rgba(168,85,247,.25));}
        .map-topbar .form-control,.map-topbar .form-select{background:rgba(0,0,0,.7);color:#e5e7eb;border:1px solid rgba(148,163,184,.5);border-radius:999px;padding:10px 16px}
        .map-grid{display:grid;grid-template-columns:minmax(0,1fr) 380px}
        #map{height:680px}
        .panel{border-left:1px solid rgba(148,163,184,.4);display:flex;flex-direction:column;max-height:680px;background:#0f1026}
        .panel-head{padding:18px 22px;border-bottom:1px solid rgba(148,163,184,.4);background:#15172b;color:#fff}
        .panel-list{overflow-y:auto;flex:1}
        .panel-item{padding:14px 20px;border-bottom:1px solid rgba(148,163,184,.18);cursor:pointer;color:#e5e7eb;user-select:none}
        .panel-item:hover{background:#1f2240;cursor:pointer}
        .panel-item .fw-semibold{color:#fff}
        .panel-item .small{color:#cbd5e1}
        .popup-img{width:100%;height:90px;object-fit:cover;border-radius:10px;margin-bottom:8px}
        .btn-fut{font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.08em;text-transform:uppercase;text-decoration:none;padding:10px 18px;border-radius:999px;border:1px solid rgba(226,232,240,.5);color:#e5e7eb;background:transparent}
        footer.fut{padding:30px;text-align:center;color:#64748b;border-top:1px solid rgba(148,163,184,.25)}
        @media(max-width:992px){.map-grid{grid-template-columns:1fr}.panel{max-height:none;border-left:none;border-top:1px solid rgba(148,163,184,.3)}#map{height:520px}}
    </style>
</head>
<body>
<header class="fut">
    <a class="logo" href="index.php"><span class="logo-dot"></span><span>TOURIST MAPPING PH</span></a>
    <nav class="fut-nav">
        <a href="index.php">HOME</a>
        <a href="index.php#destinations">DESTINATIONS</a>
        <a href="index.php#about">ABOUT US</a>
        <a href="index.php#contact">CONTACT US</a>
        <a href="map.php">TOURIST MAP</a>
    </nav>
</header>
<main class="map-page-wrap">
    <section class="map-hero-fut">
        <h1>INTERACTIVE TOURIST MAP</h1>
        <p>Navigate the Philippines through a futuristic, neon-enhanced mapping experience.</p>
    </section>
    <div class="map-shell">
        <div class="map-topbar">
            <div class="fw-semibold"><i class="bi bi-broadcast"></i> LIVE DESTINATION NETWORK</div>
            <div class="ms-auto d-flex flex-wrap gap-3 align-items-center">
                <select id="categoryFilter" class="form-select form-select-sm" style="width:auto;min-width:200px;">
                    <option value="all">All Categories</option>
                    <?php foreach($categories as $cat):?><option value="<?= e($cat) ?>"><?= e($cat) ?></option><?php endforeach;?>
                </select>
                <input id="destSearch" type="search" class="form-control form-control-sm" placeholder="Search destinations, province, city..." style="width:260px;">
                <button class="btn-fut" id="resetView"><i class="bi bi-arrow-counterclockwise"></i> RESET VIEW</button>
                <button class="btn-fut" id="showAll"><i class="bi bi-globe"></i> SHOW ALL</button>
                <button class="btn-fut" id="fullscreenBtn"><i class="bi bi-arrows-fullscreen"></i> FULLSCREEN</button>
            </div>
        </div>
        <div class="map-grid">
            <div id="map"></div>
            <aside class="panel">
                <div class="panel-head d-flex justify-content-between align-items-center">
                    <div><div class="fw-semibold">DESTINATIONS</div><small>Published locations</small></div>
                    <span class="badge bg-info text-dark"><?= count($destinations) ?></span>
                </div>
                <div class="panel-list" id="destList"></div>
            </aside>
        </div>
    </div>
</main>
<footer class="fut">
    <div>&copy; <?= date("Y") ?> TOURIST MAPPING PH. ALL RIGHTS RESERVED.</div>
</footer>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.touristDestinations = <?= $destinationsJson ?>;
    const focusSlug = new URLSearchParams(window.location.search).get('focus');
    window.addEventListener('load', function(){
        if(focusSlug && window.touristDestinations){
            setTimeout(function(){
                const m = window.touristDestinations.find(function(d){ return d.slug===focusSlug; });
                if(m && m.latitude && m.longitude){
                    const lat = parseFloat(m.latitude), lng = parseFloat(m.longitude);
                    if(!isNaN(lat)&&!isNaN(lng)){
                        if(typeof map !== 'undefined'){
                            map.setView([lat,lng], 14);
                        }
                    }
                }
            }, 800);
        }
    });
</script>
<script src="assets/js/map.js?v=<?= time() ?>"></script>
</body>
</html>
