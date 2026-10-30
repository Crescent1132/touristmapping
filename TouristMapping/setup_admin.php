
<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$error = '';
$success = '';

// Check whether an admin account already exists.
$stmt = $pdo->query(
    "SELECT COUNT(*) FROM users WHERE role = 'admin'"
);

$adminExists = (int) $stmt->fetchColumn() > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // Check again when the form is submitted.
    $stmt = $pdo->query(
        "SELECT COUNT(*) FROM users WHERE role = 'admin'"
    );

    $adminExists = (int) $stmt->fetchColumn() > 0;

    if ($adminExists) {
        $error = 'An admin account already exists. Remove this setup page.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid name and email address.';
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
                $error = 'This email is already registered. Use another email.';
            } else {
                try {
                    $hashedPassword = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $insert = $pdo->prepare(
                        "INSERT INTO users (name, email, password, role)
                         VALUES (?, ?, ?, 'admin')"
                    );

                    $insert->execute([
                        $name,
                        $email,
                        $hashedPassword
                    ]);

                    $success = 'Admin account created successfully. You can now log in.';
                    $adminExists = true;

                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    $error = 'Could not create the admin account. Check the database settings.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Set Up Admin | Tourist Mapping PH</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .setup-box {
            max-width: 480px;
            margin: 40px auto;
            padding: 28px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 15px #00000015;
        }

        .setup-box label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        .setup-box input {
            width: 100%;
            padding: 12px;
            margin-top: 7px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .setup-box button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #064e8a;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .message {
            padding: 12px;
            border-radius: 6px;
            margin: 15px 0;
        }

        .error {
            background: #fee4e2;
            color: #b42318;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }
    </style>
</head>

<body>

<header>
    <h2>Tourist Mapping PH</h2>
</header>

<main class="container">

    <div class="setup-box">
        <h1>Admin Setup</h1>
        <p>Create the first administrator account for your project.</p>

        <?php if ($error !== ''): ?>
            <p class="message error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <p class="message success">
                <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
            </p>

            <p>
                <a class="button" href="login.php">Go to Login</a>
            </p>

        <?php elseif ($adminExists): ?>

            <p>An administrator already exists. This setup page cannot create another one.</p>
            <p><a href="login.php">Go to Login</a></p>

        <?php else: ?>

            <form method="POST" action="setup_admin.php">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
                >

                <label for="name">Admin Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    maxlength="100"
                    required
                >

                <label for="email">Admin Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    maxlength="254"
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

                <button type="submit">Create Admin Account</button>

            </form>

        <?php endif; ?>
    </div>

</main>

</body>
</html>
