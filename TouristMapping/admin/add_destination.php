<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_role('admin');
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Destination | Tourist Mapping PH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@500;600;700&family=Poppins:wght@600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root{--neon-purple:#a855f7;--neon-pink:#ec4899;--neon-blue:#38bdf8;}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:radial-gradient(1200px 800px at 80% -10%, rgba(168,85,247,.18), transparent), linear-gradient(180deg,#000000 0%, #050510 100%);color:#e5e7eb}
        header.fut{position:sticky;top:0;z-index:50;display:flex;align-items:center;justify-content:space-between;padding:12px 40px;background:rgba(0,0,0,.94);backdrop-filter:blur(12px);border-bottom:1px solid rgba(168,85,247,.4)}
        .logo{display:flex;align-items:center;gap:10px;font-family:Space Grotesk,Montserrat,sans-serif;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
        .logo-dot{width:10px;height:10px;border-radius:50%;background:radial-gradient(circle,#fff,var(--neon-purple));box-shadow:0 0 30px var(--neon-purple)}
        nav.fut-nav{display:flex;gap:22px;align-items:center;flex-wrap:wrap}
        nav.fut-nav a{font-family:Montserrat,sans-serif;font-weight:600;text-decoration:none;color:#cbd5e1;letter-spacing:.08em;font-size:12px;position:relative}
        nav.fut-nav a::after{content:"";position:absolute;left:0;bottom:-6px;width:100%;height:1px;background:linear-gradient(90deg,var(--neon-blue),var(--neon-purple));transform:scaleX(0);transition:transform .3s}
        nav.fut-nav a:hover{color:#fff}
        nav.fut-nav a:hover::after{transform:scaleX(1)}
        .nav-btn{font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.08em;text-transform:uppercase;text-decoration:none;padding:9px 18px;border-radius:999px;border:1px solid rgba(148,163,184,.6);color:#e5e7eb;background:transparent;cursor:pointer}
        .main{padding:40px 60px;max-width:1100px;margin:0 auto}
        .kicker{font-family:Montserrat,sans-serif;letter-spacing:.5em;text-transform:uppercase;color:var(--neon-blue);font-size:11px}
        h1{font-family:Poppins,sans-serif;font-weight:800;text-transform:uppercase;margin:10px 0;background:linear-gradient(90deg,#fff,var(--neon-blue),var(--neon-purple));-webkit-background-clip:text;color:transparent}
        .card{background:linear-gradient(180deg, rgba(10,10,26,.95), rgba(0,0,0,.98));border:1px solid rgba(168,85,247,.35);border-radius:16px;box-shadow:0 40px 80px rgba(168,85,247,.18);padding:40px}
        .form-label{font-family:Montserrat,sans-serif;letter-spacing:.08em;text-transform:uppercase;font-size:11px;color:#cbd5e1}
        .form-control,.form-select{width:100%;margin-top:8px;padding:12px 14px;border-radius:12px;border:1px solid rgba(148,163,184,.5);background:rgba(0,0,0,.7);color:#e5e7eb;font-family:Inter,sans-serif}
        .form-control:focus,.form-select:focus{outline:none;border-color:rgba(168,85,247,.9);box-shadow:0 0 0 4px rgba(168,85,247,.18)}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px}
        .btn-neon{font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.1em;text-transform:uppercase;text-decoration:none;padding:12px 28px;border-radius:999px;border:none;background:linear-gradient(90deg,var(--neon-blue),var(--neon-purple),var(--neon-pink));color:#fff;box-shadow:0 0 50px rgba(168,85,247,.28);cursor:pointer}
        .btn-ghost{font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.1em;text-transform:uppercase;text-decoration:none;padding:12px 28px;border-radius:999px;border:1px solid rgba(148,163,184,.6);color:#e5e7eb;background:transparent}
        footer{padding:30px;text-align:center;color:#64748b}
    </style>
</head>
<body>
<header class="fut">
    <div class="logo"><span class="logo-dot"></span><span>TOURIST MAPPING PH</span></div>
    <nav class="fut-nav">
        <a href="dashboard.php">DASHBOARD</a>
        <a href="destinations.php">DESTINATIONS</a>
        <a href="categories.php">CATEGORIES</a>
        <a href="../index.php">VIEW SITE</a>
        <form action="../logout.php" method="POST" class="m-0">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button class="nav-btn">LOGOUT</button>
        </form>
    </nav>
</header>
<main class="main">
    <div class="kicker">CREATE</div>
    <h1>ADD NEW DESTINATION</h1>
    <div class="card mt-4">
        <form method="POST" enctype="multipart/form-data">
            <div class="grid">
                <div><label class="form-label">Destination Name</label><input class="form-control" name="name" required></div>
                <div><label class="form-label">Slug</label><input class="form-control" name="slug" required></div>
                <div><label class="form-label">Category</label><select class="form-select" name="category_id" required><option value="">Select</option><?php foreach($categories as $c):?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach;?></select></div>
                <div><label class="form-label">Region</label><input class="form-control" name="region"></div>
                <div><label class="form-label">Province</label><input class="form-control" name="province"></div>
                <div><label class="form-label">Municipality/City</label><input class="form-control" name="municipality"></div>
                <div><label class="form-label">Latitude</label><input class="form-control" name="latitude"></div>
                <div><label class="form-label">Longitude</label><input class="form-control" name="longitude"></div>
                <div><label class="form-label">Status</label><select class="form-select" name="status"><option value="published">Published</option><option value="draft">Draft</option></select></div>
                <div><label class="form-label">Image</label><input class="form-control" type="file" name="image" accept="image/*"></div>
            </div>
            <div style="margin-top:24px">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="6"></textarea>
            </div>
            <div style="display:flex;gap:14px;flex-wrap:wrap;margin-top:30px">
                <button class="btn-neon" type="submit">SAVE DESTINATION</button>
                <a class="btn-ghost" href="destinations.php">CANCEL</a>
            </div>
        </form>
    </div>
</main>
<footer>&copy; <?= date('Y') ?> TOURIST MAPPING PH. ALL RIGHTS RESERVED.</footer>
</body>
</html>
