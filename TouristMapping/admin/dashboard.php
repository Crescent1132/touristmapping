<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_role('admin');

$stmt = $pdo->query("SELECT COUNT(*) FROM destinations");
$totalDestinations = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM destinations WHERE status = 'published'");
$publishedDestinations = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM destinations WHERE status = 'draft'");
$draftDestinations = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM categories");
$totalCategories = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Tourist Mapping PH</title>
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
        nav.fut-nav a{font-family:Montserrat,sans-serif;font-weight:600;text-decoration:none;color:#cbd5e1;letter-spacing:.08em;font-size:12px}
        nav.fut-nav a:hover{color:#fff}
        .main{padding:40px 60px;max-width:1400px;margin:0 auto}
        .kicker{font-family:Montserrat,sans-serif;letter-spacing:.5em;text-transform:uppercase;color:var(--neon-blue);font-size:11px}
        h1{font-family:Poppins,sans-serif;font-weight:800;text-transform:uppercase;margin:10px 0;background:linear-gradient(90deg,#fff,var(--neon-blue),var(--neon-purple));-webkit-background-clip:text;color:transparent}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px;margin-top:26px}
        .card{background:linear-gradient(180deg, rgba(10,10,26,.95), rgba(0,0,0,.98));border:1px solid rgba(168,85,247,.35);border-radius:16px;box-shadow:0 40px 80px rgba(168,85,247,.18);padding:24px}
        .card-label{color:#94a3b8;font-family:Montserrat,sans-serif;letter-spacing:.08em;text-transform:uppercase;font-size:11px}
        .card-num{font-family:Poppins,sans-serif;font-weight:800;font-size:48px;margin-top:8px;background:linear-gradient(90deg,#fff,var(--neon-blue));-webkit-background-clip:text;color:transparent}
        .actions{margin-top:30px;display:flex;flex-wrap:wrap;gap:12px}
        .btn-neon{font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.1em;text-transform:uppercase;text-decoration:none;padding:12px 26px;border-radius:999px;background:linear-gradient(90deg,var(--neon-blue),var(--neon-purple),var(--neon-pink));color:#fff}
        .btn-ghost{font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.1em;text-transform:uppercase;text-decoration:none;padding:12px 26px;border-radius:999px;border:1px solid rgba(148,163,184,.6);color:#e5e7eb}
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
            <button class="btn-ghost" style="background:transparent">LOGOUT</button>
        </form>
    </nav>
</header>
<main class="main">
    <div class="kicker">COMMAND CENTER</div>
    <h1>ADMIN DASHBOARD</h1>
    <p style="color:#94a3b8">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></p>
    <div class="grid">
        <div class="card"><div class="card-label">TOTAL DESTINATIONS</div><div class="card-num"><?= $totalDestinations ?></div></div>
        <div class="card"><div class="card-label">PUBLISHED</div><div class="card-num"><?= $publishedDestinations ?></div></div>
        <div class="card"><div class="card-label">DRAFT</div><div class="card-num"><?= $draftDestinations ?></div></div>
        <div class="card"><div class="card-label">CATEGORIES</div><div class="card-num"><?= $totalCategories ?></div></div>
    </div>
    <div class="actions">
        <a class="btn-neon" href="destinations.php"><i class="bi bi-map"></i> MANAGE DESTINATIONS</a>
        <a class="btn-ghost" href="add_destination.php"><i class="bi bi-plus-circle"></i> ADD DESTINATION</a>
        <a class="btn-ghost" href="categories.php"><i class="bi bi-tags"></i> MANAGE CATEGORIES</a>
    </div>
</main>
<footer>&copy; <?= date('Y') ?> TOURIST MAPPING PH. ALL RIGHTS RESERVED.</footer>
</body>
</html>
