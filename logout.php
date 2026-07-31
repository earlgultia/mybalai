<?php
require_once __DIR__ . '/config/database.php';

if (isset($_SESSION['user_id'])) {
    try {
        logActivity($_SESSION['user_id'], 'User logged out', 'auth', $_SESSION['user_id']);
    } catch (Throwable $e) {
        // Audit logging must not prevent the session from being cleared.
    }
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();
header('Location: index.php');
exit();