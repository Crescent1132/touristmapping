
<?php

require_once __DIR__ . '/session.php';

// Check if the user is logged in.
function require_login()
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /TouristMapping/login.php');
        exit;
    }
}

// Check if the user has the required role.
function require_role($role)
{
    require_login();

    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Access denied. You do not have permission.');
    }
}

// Create a security token for forms.
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Check the security token when a form is submitted.
function verify_csrf()
{
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        http_response_code(403);
        exit('Invalid request. Please go back and try again.');
    }
}
