<?php
require_once 'config/database.php';

if (isLoggedIn()) {
    if ($_SESSION['user_type'] == 'resident') {
        redirect('resident/dashboard.php');
    } else {
        redirect('admin/dashboard.php');
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("
        SELECT u.*,
            CASE
                WHEN EXISTS (
                    SELECT 1
                    FROM user_role_assignments ura
                    JOIN roles r ON r.role_id = ura.role_id
                    WHERE ura.user_id = u.user_id
                        AND ura.is_active = 1
                        AND r.role_name = 'resident'
                ) THEN 'resident'
                ELSE 'admin'
            END AS user_type
        FROM users u
        WHERE (u.email = ? OR u.username = ?) AND u.is_active = 1
    ");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        refreshUserSessionRoles($user['user_id']);
        
        // Update last login
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE user_id = ?");
        $updateStmt->execute([$user['user_id']]);
        
        // Log activity
        logActivity($user['user_id'], 'User logged in', 'auth', $user['user_id']);
        
        if ($user['user_type'] == 'resident') {
            redirect('resident/dashboard.php');
        } else {
            redirect('admin/dashboard.php');
        }
    } else {
        $error = 'Invalid email/username or password';
        // Log failed attempt
        if ($user) {
            $stmt = $pdo->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MyBalai">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Login - MyBalai Smart Barangay Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json?v=20260727">
    <link rel="apple-touch-icon" href="assets/icons/appicon.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
        body {
            overflow-x: hidden;
            min-height: 100vh;
        }
        .login-bg {
            background: radial-gradient(circle at top left, rgba(129, 140, 248, 0.32), transparent 24%),
                        linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .login-shell {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.75rem;
            width: min(1120px, 100%);
            margin: 0 auto;
            padding: 1.5rem 1rem;
        }

        .login-sidebar {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 620px;
            padding: 3rem;
            border-radius: 36px;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(79, 70, 229, 0.96));
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.22);
            color: #f8fafc;
        }


        .login-sidebar .brand-block {
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .login-sidebar .brand-block span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border-radius: 20px;
            background: rgba(255,255,255,0.12);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.08);
        }

        .login-sidebar h1 {
            font-size: clamp(2.2rem, 3.4vw, 3.6rem);
            line-height: 1.03;
            letter-spacing: -0.04em;
            margin-bottom: 1rem;
        }

        .login-sidebar p {
            max-width: 520px;
            line-height: 1.9;
            color: rgba(248, 250, 252, 0.88);
            margin-bottom: 2rem;
        }

        .login-feature-list {
            display: grid;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .login-feature {
            display: flex;
            align-items: flex-start;
            gap: 0.9rem;
            padding: 1rem 1.1rem;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.09);
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .login-feature i {
            font-size: 1.15rem;
            color: #a5b4fc;
            margin-top: 0.25rem;
        }

        .login-feature span {
            display: block;
            font-size: 0.95rem;
            line-height: 1.6;
            color: rgba(248, 250, 252, 0.92);
        }

        .login-sidebar .help-note {
            font-size: 0.95rem;
            color: rgba(248, 250, 252, 0.76);
            line-height: 1.7;
        }

        .login-panel {
            background: rgba(255,255,255,0.96);
            border-radius: 32px;
            box-shadow: 0 24px 80px rgba(15,23,42,0.14);
            overflow: hidden;
        }

        .login-panel .panel-header {
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            padding: 2rem 2.2rem;
            text-align: center;
        }

        .login-panel .panel-header h2 {
            font-size: clamp(1.7rem, 2.2vw, 2.4rem);
            color: #1e293b;
            margin-bottom: 0.75rem;
        }

        .login-panel .panel-header p {
            color: #475569;
            line-height: 1.8;
        }

        .login-panel .panel-body {
            padding: 2rem 2.2rem 2.4rem;
        }

        .login-panel .form-label { color: #334155; }

        .login-panel .form-input {
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .login-panel .form-input:focus { box-shadow: 0 0 0 4px rgba(99,102,241,0.14); }

        .login-panel .submit-btn { transition: transform .2s ease, box-shadow .2s ease; }

        .login-panel .submit-btn:hover { transform: translateY(-1px); }

        .login-panel .back-link { color: #475569; }
        .login-panel .back-link:hover { color: #4338ca; }

        @media (max-width: 1023px) {
            .login-shell { grid-template-columns: 1fr; gap: 1.25rem; padding: 1.25rem 0.75rem; }
            .login-sidebar { display: none; }
            .login-panel { border-radius: 28px; }
            .login-panel .panel-header { padding: 1.8rem 1.6rem; }
            .login-panel .panel-body { padding: 1.8rem 1.6rem 2rem; }
        }

        @media (max-width: 420px) {
            .login-shell { padding: 1rem 0.5rem; }
            .login-panel .panel-header h2 { font-size: 1.6rem; }
            .login-panel .panel-header p { font-size: 0.95rem; }
            .login-panel .panel-body { padding: 1.6rem 1.25rem 1.8rem; }
            .login-panel .submit-btn { padding-top: 0.9rem; padding-bottom: 0.9rem; }
            .login-panel .form-input { padding-top: 0.75rem; padding-bottom: 0.75rem; }
        }
    </style>
</head>
<body class="login-bg min-h-screen">
    <div class="login-shell">
        <aside class="login-sidebar">
            <div>
                <a href="index.php" class="brand-block">
                    <span><i class="fas fa-home"></i></span>
                    <div>
                        <div class="text-xl font-semibold">MyBalai</div>
                        <div class="text-sm text-indigo-100/85">Smart Barangay Services Portal</div>
                    </div>
                </a>

                <div>
                    <h1>Secure citizen services, all in one place.</h1>
                    <p>Sign in and manage document requests, appointments, complaints, and resident records from your community dashboard.</p>
                </div>

                <div class="login-feature-list">
                    <div class="login-feature">
                        <i class="fas fa-shield-alt"></i>
                        <span>Protected access with online resident verification.</span>
                    </div>
                    <div class="login-feature">
                        <i class="fas fa-bolt"></i>
                        <span>Fast approval workflows for documents and appointments.</span>
                    </div>
                    <div class="login-feature">
                        <i class="fas fa-users"></i>
                        <span>Resident-first portal built for easy barangay access.</span>
                    </div>
                </div>
            </div>

            <div class="help-note">Need help? Contact your barangay office if your login is not recognized or you need account support.</div>
        </aside>

        <section class="login-panel">
            <div class="panel-header">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-3xl bg-indigo-100 text-indigo-700 shadow-inner shadow-indigo-200/40">
                    <i class="fas fa-sign-in-alt text-2xl"></i>
                </div>
                <h2>Login to your account</h2>
                <p>Access your digital barangay services with your email or username.</p>
            </div>

            <div class="panel-body">
                <?php if ($error): ?>
                    <div class="mb-6 rounded-2xl border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-5">
                    <div>
                        <label class="form-label mb-2 block text-sm font-semibold">Email or Username</label>
                        <input type="text" name="email" required
                            class="form-input w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none"
                            placeholder="Enter your email or username">
                    </div>

                    <div>
                        <label class="form-label mb-2 block text-sm font-semibold">Password</label>
                        <div class="relative">
                            <input id="passwordInput" type="password" name="password" required
                                class="form-input w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none"
                                placeholder="Enter your password">
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-3 flex items-center text-slate-500 hover:text-slate-700 focus:outline-none" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn w-full rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-500/15 hover:shadow-indigo-600/20">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-slate-600">
                    <span>Barangay resident?</span>
                    <a href="register.php" class="ml-1 font-semibold text-indigo-600 hover:text-indigo-800">Create your account</a>
                </div>

                <div class="mt-8 text-center">
                    <a href="index.php" class="back-link inline-flex items-center gap-2 text-sm font-semibold transition hover:text-indigo-800">
                        <i class="fas fa-arrow-left"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </section>
    </div>
        <script>
            (function(){
                var passwordInput = document.getElementById('passwordInput');
                var togglePasswordButton = document.getElementById('togglePassword');
                var togglePasswordIcon = null;
                if (togglePasswordButton && typeof togglePasswordButton.querySelector === 'function') {
                    togglePasswordIcon = togglePasswordButton.querySelector('i');
                }

                if (togglePasswordButton && passwordInput) {
                    togglePasswordButton.addEventListener('click', function () {
                        try {
                            var isPassword = passwordInput.type === 'password';
                            passwordInput.type = isPassword ? 'text' : 'password';
                            if (togglePasswordIcon) {
                                togglePasswordIcon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
                            }
                        } catch (e) {
                            // fail silently on unexpected errors
                            console.warn('Password toggle failed', e);
                        }
                    });
                }
            })();
        </script>
    </div>
    <script src="assets/js/pwa.js?v=20260727"></script>
</body>
</html>
