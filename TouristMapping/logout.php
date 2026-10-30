
<?php
require_once __DIR__ . '/config/auth.php';

// Allow logout only through POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Check the security token.
verify_csrf();

// Check the user's role before destroying the session.
$role = $_SESSION['role'] ?? 'visitor';

// Remove all session data.
$_SESSION = [];

// Remove the session cookie.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session.
session_destroy();

// Redirect based on the previous role.
if ($role === 'admin') {
    header('Location: admin/login.php');
} else {
    header('Location: login.php');
}

exit;
?>
