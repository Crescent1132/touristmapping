
<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$error = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 12 || strlen($password) > 72) {
        $error = 'Password must be between 12 and 72 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $check = $pdo->prepare(
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'This email is already registered.';
        } else {
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $sql = "INSERT INTO users (name, email, password, role)
                    VALUES (?, ?, ?, 'visitor')";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $name,
                $email,
                $hashedPassword
            ]);

            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | Tourist Mapping PH</title>

    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__.'/assets/css/style.css') ?>">

    <style>
        .register-box {
            max-width: 450px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 15px #00000015;
        }

        .register-box label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        .register-box input {
            width: 100%;
            padding: 12px;
            margin-top: 7px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .register-box button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #064e8a;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .error-message {
            color: #b42318;
            background: #fee4e2;
            padding: 10px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

<header>
    <h2>Tourist Mapping PH</h2>

    <nav>
        <a href="index.php">Home</a>
        <a href="map.php">Explore Map</a>
        <a href="login.php">Login</a>
    </nav>
</header>

<main class="container">

    <div class="register-box">
        <h1>Create Account</h1>
        <p>Register to use Tourist Mapping Philippines.</p>

        <?php if ($error !== ''): ?>
            <p class="error-message">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="register.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="name">Full Name</label>
            <input
                type="text"
                id="name"
                name="name"
                maxlength="100"
                value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                maxlength="254"
                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="12"
                maxlength="72"
                required
            >

            <label for="confirm_password">Confirm Password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="12"
                maxlength="72"
                required
            >

            <button type="submit">Register</button>

        </form>

        <p>
            Already have an account?
            <a href="login.php">Login here</a>.
        </p>
    </div>

</main>

<footer>
    <p>&copy; <?= date('Y') ?> Tourist Mapping Philippines</p>
</footer>

</body>
</html>
