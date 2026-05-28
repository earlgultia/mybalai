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
    <title>Login - MyBalai Smart Barangay Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
        .login-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        /* Mobile responsive tweaks */
        @media (max-width: 768px) {
            .max-w-6xl { max-width: 100%; padding-left: 0.75rem; padding-right: 0.75rem; }
            .max-w-lg { max-width: 420px; margin: 0 auto; }
            .login-card { border-radius: 18px; overflow: hidden; }
            .login-card .bg-gradient-to-r { padding: 18px; }
            .login-card .px-5 { padding-left: 1rem; padding-right: 1rem; }
            .login-card .px-8 { padding-left: 1rem; padding-right: 1rem; }
            .login-card input { font-size: 0.95rem; padding-top: 0.75rem; padding-bottom: 0.75rem; }
            .login-card .rounded-xl { border-radius: 14px; }
            .login-card .shadow-[0_24px_80px_rgba(15,23,42,0.28)] { box-shadow: 0 12px 30px rgba(15,23,42,0.18); }
            .login-card .mx-auto.mb-3 { margin-bottom: 8px; }
            .login-card img, .login-card i { max-width: 100%; }
            #togglePassword { right: 10px; }
            .login-card form { gap: 0.75rem; }
        }

        @media (max-width: 420px) {
            .login-card .bg-gradient-to-r h2 { font-size: 1.25rem; }
            .login-card .bg-gradient-to-r p { font-size: 0.85rem; }
            .login-card input { font-size: 0.9rem; }
            .login-card .mx-auto { padding-left: 6px; padding-right: 6px; }
            .login-card { margin: 0 6px; }
        }
    </style>
</head>
<body class="login-bg min-h-screen">
    <div class="mx-auto flex min-h-screen w-full max-w-6xl flex-col items-center justify-center px-4 py-6 sm:px-6 sm:py-10">
        <div class="w-full max-w-lg">
            <div class="mb-6 flex justify-center sm:mb-8">
                <a href="index.php" class="inline-flex max-w-full items-center gap-4 text-white">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-sm">
                        <i class="fas fa-home text-2xl"></i>
                    </span>
                    <span class="text-left">
                        <span class="block text-2xl font-bold leading-none">MyBalai</span>
                        <span class="mt-1 block text-sm font-medium text-indigo-100">Smart Barangay Services Portal</span>
                    </span>
                </a>
            </div>

            <div class="login-card overflow-hidden rounded-[28px] shadow-[0_24px_80px_rgba(15,23,42,0.28)]">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-5 text-center text-white sm:px-8 sm:py-6">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15">
                        <i class="fas fa-sign-in-alt text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold sm:text-[1.7rem]">Welcome Back</h2>
                    <p class="mt-1 text-sm text-indigo-100 sm:text-base">Login to your account</p>
                </div>

                <div class="px-5 py-6 sm:px-8 sm:py-8">
                    <?php if ($error): ?>
                        <div class="mb-6 rounded-xl border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-5">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                <i class="fas fa-envelope mr-2 text-indigo-600"></i>Email or Username
                            </label>
                            <input type="text" name="email" required
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                placeholder="Enter your email or username">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                <i class="fas fa-lock mr-2 text-indigo-600"></i>Password
                            </label>
                            <div class="relative">
                                <input id="passwordInput" type="password" name="password" required
                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 pr-12 text-sm text-slate-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                    placeholder="Enter your password">
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-3 flex items-center text-slate-500 transition hover:text-slate-800 focus:outline-none" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3 text-sm font-bold text-white shadow-lg transition duration-200 hover:from-indigo-700 hover:to-purple-700">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </button>
                    </form>

                    <div class="mt-6 text-center text-sm text-slate-700">
                        <span>Barangay resident?</span>
                        <a href="register.php" class="ml-1 font-semibold text-indigo-600 hover:text-indigo-800">Create your account</a>
                    </div>
                </div>
            </div>

            <div class="mt-6 text-center">
                <a href="index.php" class="inline-flex items-center gap-2 text-sm font-semibold text-white transition hover:text-indigo-100">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Home</span>
                </a>
            </div>
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
</body>
</html>
