<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $sql = "SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Incorrect email or password.';
        }
    }
}
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Tourist Mapping PH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@500;600;700&family=Poppins:wght@600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root{--neon-purple:#a855f7;--neon-pink:#ec4899;--neon-blue:#38bdf8;}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:radial-gradient(1200px 800px at 80% -10%, rgba(168,85,247,.25), transparent), radial-gradient(1000px 700px at 10% 10%, rgba(56,189,248,.18), transparent), linear-gradient(180deg,#000000 0%, #050510 100%);color:#e5e7eb;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .card{width:100%;max-width:460px;background:linear-gradient(180deg, rgba(10,10,26,.95), rgba(0,0,0,.98));border:1px solid rgba(168,85,247,.4);border-radius:18px;box-shadow:0 60px 120px rgba(168,85,247,.25);overflow:hidden}
        .card-head{padding:34px 36px 10px;text-align:center}
        .logo{display:flex;align-items:center;justify-content:center;gap:10px;font-family:Space Grotesk,Montserrat,sans-serif;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
        .logo-dot{width:10px;height:10px;border-radius:50%;background:radial-gradient(circle,#fff,var(--neon-purple));box-shadow:0 0 30px var(--neon-purple)}
        h1{font-family:Poppins,sans-serif;font-weight:800;text-transform:uppercase;margin:14px 0 6px;background:linear-gradient(90deg,#fff,var(--neon-blue),var(--neon-purple));-webkit-background-clip:text;color:transparent}
        .sub{color:#94a3b8;margin:0}
        .card-body{padding:26px 36px 36px}
        .form-label{font-family:Montserrat,sans-serif;letter-spacing:.08em;text-transform:uppercase;font-size:12px;color:#cbd5e1}
        .form-control{width:100%;margin-top:8px;padding:12px 14px;border-radius:12px;border:1px solid rgba(148,163,184,.5);background:rgba(0,0,0,.7);color:#e5e7eb;font-family:Inter,sans-serif}
        .form-control:focus{outline:none;border-color:rgba(168,85,247,.9);box-shadow:0 0 0 4px rgba(168,85,247,.18)}
        .btn-neon{width:100%;margin-top:18px;padding:14px;border-radius:999px;border:none;font-family:Montserrat,sans-serif;font-weight:600;letter-spacing:.12em;text-transform:uppercase;background:linear-gradient(90deg,var(--neon-blue),var(--neon-purple),var(--neon-pink));color:#fff;box-shadow:0 0 60px rgba(168,85,247,.32)}
        .alert{padding:12px 16px;border-radius:12px;background:rgba(244,63,94,.18);border:1px solid rgba(244,63,94,.6);color:#fecaca}
        .link{display:block;text-align:center;margin-top:18px;color:#94a3b8;text-decoration:none}
    </style>
</head>
<body>
<div class="card">
    <div class="card-head">
        <div class="logo"><span class="logo-dot"></span><span>TOURIST MAPPING PH</span></div>
        <h1>LOGIN</h1>
        <p class="sub">Access your account</p>
    </div>
    <div class="card-body">
        <?php if($error!==''):?><div class="alert"><?= e($error) ?></div><?php endif;?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input class="form-control" type="email" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input class="form-control" type="password" name="password" required>
            </div>
            <button class="btn-neon">SIGN IN</button>
        </form>
        <a class="link" href="register.php">Don't have an account? Register</a>
        <a class="link" href="index.php">← Return to Homepage</a>
    </div>
</div>
</body>
</html>
