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
    <style>
        :root {
            color-scheme: dark;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a;
        }
        * {
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            min-height: 100%;
            background: radial-gradient(circle at top left, rgba(56,189,248,0.22), transparent 38%),
                        radial-gradient(circle at bottom right, rgba(59,130,246,0.18), transparent 32%),
                        #0f172a;
            color: #e2e8f0;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .logout-shell {
            width: min(100%, 680px);
            background: rgba(15, 23, 42, 0.94);
            border: 1px solid rgba(148, 163, 184, 0.12);
            border-radius: 32px;
            box-shadow: 0 28px 90px rgba(15, 23, 42, 0.35);
            overflow: hidden;
        }
        .logout-grid {
            display: grid;
            gap: 1.5rem;
            padding: 2rem;
        }
        @media (min-width: 768px) {
            .logout-grid {
                grid-template-columns: 220px 1fr;
                align-items: center;
                padding: 2.5rem;
            }
        }
        .logout-visual {
            display: grid;
            place-items: center;
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(59,130,246,0.15), rgba(15,23,42,0.5));
            padding: 2rem;
        }
        .logout-visual .icon {
            width: 96px;
            height: 96px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background: rgba(56,189,248,0.18);
            color: #7dd3fc;
            font-size: 2.4rem;
            border: 1px solid rgba(56,189,248,0.25);
        }
        .logout-visual p {
            margin: 1rem 0 0;
            font-size: 0.95rem;
            color: #94a3b8;
            text-align: center;
            line-height: 1.7;
        }
        .logout-content h1 {
            margin: 0;
            font-size: clamp(2rem, 2.5vw, 2.5rem);
            line-height: 1.05;
            color: #f8fafc;
        }
        .logout-content p {
            margin: 1rem 0 0;
            line-height: 1.7;
            color: #cbd5e1;
        }
        .logout-actions {
            display: grid;
            gap: 1rem;
            margin-top: 1.75rem;
        }
        @media (min-width: 640px) {
            .logout-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 1.25rem;
            border-radius: 18px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }
        .button:hover {
            transform: translateY(-1px);
        }
        .button-primary {
            background: linear-gradient(135deg, #38bdf8, #2563eb);
            color: #0f172a;
            box-shadow: 0 16px 30px rgba(56,189,248,0.18);
        }
        .button-secondary {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(148,163,184,0.18);
            color: #e2e8f0;
        }
        .button-secondary:hover {
            background: rgba(255,255,255,0.12);
        }
        .logout-footer {
            padding: 1.5rem 2rem 2rem;
            background: rgba(15, 23, 42, 0.78);
            border-top: 1px solid rgba(148,163,184,0.08);
            text-align: center;
            color: #94a3b8;
            font-size: 0.92rem;
        }
    </style>
</head>
<body>
    <div class="logout-shell">
        <div class="logout-grid">
            <div class="logout-visual">
                <div class="icon"><i class="fas fa-sign-out-alt"></i></div>
                <p>Signing out keeps your resident account safe. You can log back in anytime to continue using MyBalai.</p>
            </div>
            <div class="logout-content">
                <p class="eyebrow" style="text-transform: uppercase; letter-spacing: 0.18em; font-size: 0.78rem; color: #60a5fa;">Logout confirmation</p>
                <h1>Ready to leave?</h1>
                <p>Confirm logout to end your current session securely. If you want to stay signed in, choose the alternate option below.</p>
                <div class="logout-actions">
                    <a href="logout.php?confirm=1" class="button button-primary">Yes, logout</a>
                    <a href="index.php" class="button button-secondary">No, take me back</a>
                </div>
            </div>
        </div>
        <div class="logout-footer">
            This page is optimized for both desktop and mobile devices. Your session will remain secure until you log out.
        </div>
    </div>
</body>
</html>