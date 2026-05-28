<?php
session_start();
require_once 'config/database.php';

function destroySessionAndRedirect() {
    if (isset($_SESSION['user_id'])) {
        logActivity($_SESSION['user_id'], 'User logged out', 'auth', $_SESSION['user_id']);
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
    header('Location: login.php');
    exit();
}

if (isset($_GET['confirm']) && $_GET['confirm'] === '1') {
    destroySessionAndRedirect();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - MyBalai</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #0f766e 100%);
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            title: 'Are You Sure You Want to Logout?',
            text: 'Please choose Yes or No.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
            allowOutsideClick: false,
            allowEscapeKey: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.replace('logout.php?confirm=1');
                return;
            }

            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.replace('index.php');
            }
        });
    });
</script>
</body>
</html>