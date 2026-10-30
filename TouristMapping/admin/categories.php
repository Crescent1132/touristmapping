<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_role('admin');
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $error = 'Category name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            $success = 'Category added successfully.';
        } catch (Exception $ex) {
            $error = 'Category could not be added.';
        }
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories | Tourist Mapping PH</title>
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
        .nav-btn-neon{border:none;background:linear-gradient(90deg,var(--neon-blue),var(--neon-purple),var(--neon-pink));color:#fff;box-shadow:0 0 40px rgba(168,85,247,.25)}
        .main{padding:40px 60px;max-width:1100px;margin:0 auto}
        .kicker{font-family:Montserrat,sans-serif;letter-spacing:.5em;text-transform:uppercase;color:var(--neon-blue);font-size:11px}
        h1{font-family:Poppins,sans-serif;font-weight:800;text-transform:uppercase;margin:10px 0;background:linear-gradient(90deg,#fff,var(--neon-blue),var(--neon-purple));-webkit-background-clip:text;color:transparent}
        .card{background:linear-gradient(180deg, rgba(10,10,26,.95), rgba(0,0,0,.98));border:1px solid rgba(168,85,247,.35);border-radius:16px;box-shadow:0 40px 80px rgba(168,85,247,.18);overflow:hidden}
        table{width:100%;border-collapse:collapse}
        thead{background:linear-gradient(90deg,rgba(56,189,248,.25),rgba(168,85,247,.25))}
        th,td{padding:14px 18px;text-align:left;border-bottom:1px solid rgba(148,163,184,.25)}
        th{font-family:Montserrat,sans-serif;text-transform:uppercase;letter-spacing:.08em;font-size:11px}
        .form-control{padding:12px 14px;border-radius:12px;border:1px solid rgba(148,163,184,.5);background:rgba(0,0,0,.7);color:#e5e7eb;font-family:Inter,sans-serif}
        .form-control:focus{outline:none;border-color:rgba(168,85,247,.9);box-shadow:0 0 0 4px rgba(168,85,247,.18)}
        .btn-neon{font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.08em;text-transform:uppercase;text-decoration:none;padding:12px 24px;border-radius:999px;border:none;background:linear-gradient(90deg,var(--neon-blue),var(--neon-purple),var(--neon-pink));color:#fff;box-shadow:0 0 40px rgba(168,85,247,.28);cursor:pointer}
        .alert{padding:12px 16px;border-radius:12px}
        .alert-success{background:rgba(34,197,94,.18);border:1px solid rgba(34,197,94,.6);color:#86efac}
        .alert-error{background:rgba(244,63,94,.18);border:1px solid rgba(244,63,94,.6);color:#fecaca}
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
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="kicker">CLASSIFICATION</div>
            <h1>MANAGE CATEGORIES</h1>
        </div>
    </div>
    <div class="card mt-4 p-4" style="margin-bottom:28px;width:100%">
        <form method="POST" style="display:flex;flex-wrap:nowrap;gap:16px;align-items:center;width:100%">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <div style="flex:1 1 0%;min-width:0">
                <input class="form-control" type="text" name="name" placeholder="Enter category name" required style="width:100%;box-sizing:border-box">
            </div>
            <button class="btn-neon" type="submit" style="margin-top:0;white-space:nowrap;flex-shrink:0;height:46px"><i class="bi bi-plus-circle"></i> ADD CATEGORY</button>
        </form>
        <?php if($success):?><div class="alert alert-success mt-3"><?= e($success) ?></div><?php endif;?>
        <?php if($error):?><div class="alert alert-error mt-3"><?= e($error) ?></div><?php endif;?>
    </div>
    <div class="card mt-4">
        <table>
            <thead>
                <tr><th>ID</th><th>CATEGORY NAME</th></tr>
            </thead>
            <tbody>
                <?php foreach($categories as $cat):?>
                    <tr><td><?= $cat['id'] ?></td><td><?= e($cat['name']) ?></td></tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
</main>
<footer>&copy; <?= date('Y') ?> TOURIST MAPPING PH. ALL RIGHTS RESERVED.</footer>
</body>
</html>
