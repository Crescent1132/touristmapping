<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$slug = trim($_GET['slug'] ?? '');
if ($slug === '' || strlen($slug) > 220) {
    http_response_code(404);
    exit('Tourist destination not found.');
}

$sql = "SELECT
            d.id,
            d.name,
            d.slug,
            d.region,
            d.province,
            d.municipality,
            d.address,
            d.description,
            d.latitude,
            d.longitude,
            d.image,
            d.entrance_fee,
            d.opening_hours,
            d.contact_info,
            c.name AS category_name
        FROM destinations d
        LEFT JOIN categories c ON d.category_id = c.id
        WHERE d.slug = ? AND d.status = 'published'
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$slug]);
$destination = $stmt->fetch();

if (!$destination) {
    http_response_code(404);
    exit('Tourist destination not found or not published.');
}

$latitude = isset($destination['latitude']) && $destination['latitude'] !== '' ? (float)$destination['latitude'] : null;
$longitude = isset($destination['longitude']) && $destination['longitude'] !== '' ? (float)$destination['longitude'] : null;
$googleMapsUrl = ($latitude !== null && $longitude !== null)
    ? 'https://www.google.com/maps?q=' . rawurlencode($latitude . ',' . $longitude)
    : 'https://www.google.com/maps/search/' . rawurlencode($destination['name'] . ' ' . $destination['municipality'] . ' ' . $destination['province']);

$isLoggedIn = !empty($_SESSION['user_id']);
$isAdmin = $isLoggedIn && ($_SESSION['role'] ?? '') === 'admin';

// reviews table
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS destination_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        destination_id INT NOT NULL,
        user_id INT NOT NULL,
        rating TINYINT NOT NULL,
        review TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (destination_id),
        INDEX (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Exception $e) {}

$reviewError = '';
$reviewSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!$isLoggedIn) {
        $reviewError = 'Please log in to submit a review.';
    } else {
        $rating = (int)($_POST['rating'] ?? 0);
        $reviewText = trim($_POST['review'] ?? '');
        if ($rating < 1 || $rating > 5) {
            $reviewError = 'Please select a rating from 1 to 5 stars.';
        } else {
            try {
                $chk = $pdo->prepare("SELECT id FROM destination_reviews WHERE destination_id = ? AND user_id = ?");
                $chk->execute([$destination['id'], $_SESSION['user_id']]);
                if ($chk->fetch()) {
                    $reviewError = 'You have already submitted a review for this destination.';
                } else {
                    $ins = $pdo->prepare("INSERT INTO destination_reviews (destination_id, user_id, rating, review) VALUES (?, ?, ?, ?)");
                    $ins->execute([$destination['id'], $_SESSION['user_id'], $rating, $reviewText]);
                    $reviewSuccess = 'Thank you for your review!';
                }
            } catch (Exception $e) {
                $reviewError = 'Something went wrong while submitting your review.';
            }
        }
    }
}

$reviews = [];
$avgRating = 0;
$totalReviews = 0;
try {
    $rstmt = $pdo->prepare("SELECT r.rating, r.review, r.created_at, u.name as user_name FROM destination_reviews r LEFT JOIN users u ON u.id = r.user_id WHERE r.destination_id = ? ORDER BY r.created_at DESC");
    $rstmt->execute([$destination['id']]);
    $reviews = $rstmt->fetchAll();
    $totalReviews = count($reviews);
    if ($totalReviews > 0) {
        $sum = 0;
        foreach ($reviews as $rv) { $sum += (int)$rv['rating']; }
        $avgRating = round($sum / $totalReviews, 1);
    }
} catch (Exception $e) {}

// nearby
$nearby = [];
try {
    $nst = $pdo->prepare("SELECT id, name, slug, municipality, province, description, image FROM destinations WHERE status='published' AND id != ? AND province = ? ORDER BY RAND() LIMIT 4");
    $nst->execute([$destination['id'], $destination['province']]);
    $nearby = $nst->fetchAll();
} catch (Exception $e) {}

// gallery
$gallery = [];
if (!empty($destination['image'])) { $gallery[] = $destination['image']; }
if (stripos($destination['name'], 'Rizal') !== false || stripos($destination['name'], 'Luneta') !== false) {
    $gallery = array_values(array_unique(array_merge($gallery, ['uploads/destinations/Rizal2.jpg','uploads/destinations/Rizal3.jpg','uploads/destinations/Rizal4.jpg'])));
} elseif (stripos($destination['name'], 'Mayon') !== false) {
    $gallery = array_values(array_unique(array_merge($gallery, ['uploads/destinations/Mayon2.jpg','uploads/destinations/Mayon3.jpg','uploads/destinations/Mayon4.jpg'])));
} elseif (stripos($destination['name'], 'Chocolate') !== false) {
    $gallery = array_values(array_unique(array_merge($gallery, ['uploads/destinations/Choco2.jpg','uploads/destinations/Choco3.jpg','uploads/destinations/Choco4.jpg'])));
} else {
    $gallery = array_values(array_unique($gallery));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($destination['name']) ?> | Tourist Mapping PH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@500;600;700&family=Poppins:wght@600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__.'/assets/css/style.css') ?>">
    <style>
        :root{--bg:#000000;--bg2:#050510;--navy:#0a0a1a;--neon-purple:#a855f7;--neon-pink:#ec4899;--neon-blue:#38bdf8;}
        body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:radial-gradient(1200px 800px at 80% -10%, rgba(168,85,247,.18), transparent), linear-gradient(180deg,#000000 0%, #050510 100%);color:#e5e7eb}
        header.fut{position:fixed;top:0;left:0;right:0;z-index:60;display:flex;align-items:center;justify-content:space-between;padding:12px 40px;background:linear-gradient(90deg, rgba(0,0,0,.95), rgba(10,10,26,.85));backdrop-filter:blur(12px);border-bottom:1px solid rgba(168,85,247,.35)}
        .logo{display:flex;align-items:center;gap:10px;font-family:Space Grotesk,Montserrat,sans-serif;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
        .logo-dot{width:10px;height:10px;border-radius:50%;background:radial-gradient(circle,#fff,var(--neon-purple));box-shadow:0 0 25px var(--neon-purple)}
        nav.fut-nav{display:flex;gap:22px;align-items:center}
        nav.fut-nav a{font-family:Montserrat,sans-serif;font-weight:600;text-decoration:none;color:#cbd5e1;letter-spacing:.08em;font-size:13px}
        nav.fut-nav a:hover{color:#fff}
        .dest-page{padding-top:90px}
        .dest-hero{position:relative;padding:60px 20px;text-align:center}
        .dest-hero h1{font-family:Poppins,sans-serif;font-weight:800;font-size:clamp(36px,6vw,64px);text-transform:uppercase;margin:0;background:linear-gradient(90deg,#fff,var(--neon-blue),var(--neon-purple));-webkit-background-clip:text;color:transparent}
        .dest-hero p{color:#cbd5e1}
        .card-fut{background:linear-gradient(180deg, rgba(10,10,26,.9), rgba(0,0,0,.95));border:1px solid rgba(168,85,247,.35);border-radius:16px;box-shadow:0 40px 80px rgba(168,85,247,.18);overflow:hidden}
        .card-header-fut{background:linear-gradient(90deg,rgba(56,189,248,.25),rgba(168,85,247,.25));color:#fff;padding:16px 24px}
        .gallery-img{height:240px;object-fit:cover;border-radius:12px;cursor:pointer}
        .btn-neon{font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.08em;text-transform:uppercase;text-decoration:none;padding:12px 26px;border-radius:999px;background:linear-gradient(90deg,var(--neon-blue),var(--neon-purple),var(--neon-pink));color:#fff}
        footer.fut{padding:30px;text-align:center;color:#64748b;border-top:1px solid rgba(148,163,184,.25)}
    </style>
</head>
<body>
<header class="fut">
    <div class="logo"><span class="logo-dot"></span><span>TOURIST MAPPING PH</span></div>
    <nav class="fut-nav">
        <a href="index.php">HOME</a>
        <a href="index.php#destinations">DESTINATIONS</a>
        <a href="index.php#about">ABOUT US</a>
        <a href="index.php#contact">CONTACT US</a>
        <a href="map.php">TOURIST MAP</a>
        <?php if($isAdmin):?><a href="admin/dashboard.php">ADMIN</a><?php elseif($isLoggedIn):?><form action="logout.php" method="POST" class="d-inline" style="margin:0"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="nav-btn" style="padding:8px 16px;background:transparent;color:#cbd5e1;border:1px solid rgba(148,163,184,.6)">LOGOUT</button></form><?php else:?><a href="login.php">LOGIN</a><?php endif;?>
    </nav>
</header>
<main class="dest-page">
    <section class="dest-hero">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="index.php" class="text-info">Home</a></li>
                    <li class="breadcrumb-item"><a href="map.php" class="text-info">Tourist Map</a></li>
                    <li class="breadcrumb-item active text-light"><?= e($destination['name']) ?></li>
                </ol>
            </nav>
            <h1><?= e($destination['name']) ?></h1>
            <p><i class="bi bi-geo-alt"></i> <?= e($destination['municipality']) ?>, <?= e($destination['province']) ?><?= $destination['region'] ? ' • ' . e($destination['region']) : '' ?></p>
        </div>
    </section>
    <div class="container my-5">
        <div class="card-fut mb-4">
            <div class="card-header-fut"><h4 class="mb-0"><i class="bi bi-images"></i> PHOTO GALLERY</h4></div>
            <div class="p-4">
                <div class="row g-3">
                    <?php foreach($gallery as $idx=>$img):?>
                        <div class="col-md-6"><img src="<?= e($img) ?>" class="w-100 gallery-img" data-bs-toggle="modal" data-bs-target="#galleryModal" onclick="document.getElementById('galleryModalImg').src=this.src"></div>
                    <?php endforeach;?>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card-fut mb-4">
                    <div class="card-header-fut"><h4 class="mb-0"><i class="bi bi-info-circle"></i> ABOUT THIS DESTINATION</h4></div>
                    <div class="p-4">
                        <p><?= nl2br(e($destination['description'])) ?></p>
                    </div>
                </div>
                <div class="card-fut mb-4">
                    <div class="card-header-fut"><h4 class="mb-0"><i class="bi bi-map"></i> LOCATION MAP</h4></div>
                    <div class="p-4">
                        <?php if($latitude!==null&&$longitude!==null):?><iframe class="w-100" style="height:420px;border:0;border-radius:12px" loading="lazy" allowfullscreen src="https://maps.google.com/maps?q=<?= urlencode($latitude.','.$longitude) ?>&z=13&output=embed"></iframe><?php endif;?>
                        <a href="<?= e($googleMapsUrl) ?>" target="_blank" class="btn-neon mt-3 d-inline-block"><i class="bi bi-geo-alt-fill"></i> VIEW ON GOOGLE MAPS</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card-fut mb-4">
                    <div class="card-header-fut"><h5 class="mb-0"><i class="bi bi-clipboard-check"></i> OVERVIEW</h5></div>
                    <div class="p-4">
                        <p class="mb-2"><strong>Category:</strong> <?= e($destination['category_name']??'N/A') ?></p>
                        <p class="mb-2"><strong>Region:</strong> <?= e($destination['region']??'N/A') ?></p>
                        <p class="mb-2"><strong>Province:</strong> <?= e($destination['province']??'N/A') ?></p>
                        <p class="mb-2"><strong>Municipality/City:</strong> <?= e($destination['municipality']??'N/A') ?></p>
                        <p class="mb-0"><strong>Coordinates:</strong> <?= ($latitude!==null&&$longitude!==null)?e($latitude.', '.$longitude):'N/A' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<div class="modal fade" id="galleryModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content bg-dark"><div class="modal-header border-0"><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body text-center"><img id="galleryModalImg" src="" class="img-fluid rounded"></div></div></div></div>
<footer class="fut">
    <div>&copy; <?= date("Y") ?> TOURIST MAPPING PH. ALL RIGHTS RESERVED.</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
