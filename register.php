<?php
require_once 'config/database.php';

if (isLoggedIn()) {
    if ($_SESSION['user_type'] == 'resident') {
        redirect('resident/dashboard.php');
    }
    redirect('admin/dashboard.php');
}

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $username = sanitize($_POST['username'] ?? '');
    $phone = sanitize($_POST['phone_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $zipCode = sanitize($_POST['zip_code'] ?? '');
    $streetAddress = sanitize($_POST['street_address'] ?? '');

    if (!$firstName || !$lastName || !$email || !$username || !$password || !$confirmPassword) {
        $error = 'Please complete all required fields.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00') AND (email = ? OR username = ?)");
        $stmt->execute([$email, $username]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Email or username is already registered.';
        } else {
            $roleId = getRoleId('resident');
            if (!$roleId) {
                $error = 'Resident role was not found.';
            } else {
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("
                        INSERT INTO users (primary_role_id, username, email, password_hash, first_name, last_name, phone_number, is_active, is_verified, email_verified)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, 0)
                    ");
                    $stmt->execute([
                        $roleId,
                        $username,
                        $email,
                        password_hash($password, PASSWORD_DEFAULT),
                        $firstName,
                        $lastName,
                        $phone ?: null,
                    ]);
                    $userId = (int)$pdo->lastInsertId();

                    $stmt = $pdo->prepare("INSERT INTO user_role_assignments (user_id, role_id, is_active) VALUES (?, ?, 1)");
                    $stmt->execute([$userId, $roleId]);

                    $stmt = $pdo->prepare("
                        INSERT INTO resident_profiles (user_id, street_address, barangay, zip_code)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$userId, $streetAddress ?: null, 'LATROBE', $zipCode ?: null]);

                    logActivity($userId, 'Resident account registered', 'users', $userId);
                    $pdo->commit();
                    $message = 'Your resident account has been created. You can now log in.';
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $error = 'Registration could not be completed. Please try again.';
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
    <title>Resident Registration - MyBalai</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * { font-family: 'Poppins', sans-serif; }
        body { overflow-x: hidden; min-height: 100vh; }
    </style>
    <style>
        .login-bg {
            background: radial-gradient(circle at top left, rgba(99, 102, 241, 0.28), transparent 25%),
                        linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .page-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            min-height: 100vh;
            padding: 2.5rem 1.25rem;
            gap: 1.5rem;
            align-items: center;
        }

        .page-header {
            max-width: 860px;
            margin: 0 auto;
            text-align: center;
            color: #ffffff;
        }

        .brand-link {
            display: inline-flex;
            align-items: center;
            gap: 0.9rem;
            color: inherit;
            text-decoration: none;
        }

        .brand-logo {
            width: 3rem;
            height: 3rem;
            display: grid;
            place-items: center;
            border-radius: 1rem;
            background: rgba(255,255,255,0.14);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1);
        }

        .page-title {
            font-size: clamp(2.2rem, 3.6vw, 3.4rem);
            font-weight: 800;
            margin: 1rem 0 0.5rem;
            letter-spacing: -0.05em;
            color: #facc15;
        }

        .page-subtitle {
            max-width: 720px;
            margin: 0 auto;
            line-height: 1.85;
            color: rgba(255,255,255,0.86);
            font-size: 1rem;
        }

        .register-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 480px;
            gap: 2rem;
            max-width: 1180px;
            width: 100%;
            margin: 0 auto;
        }

        .register-aside {
            padding: 3rem 2.5rem;
            border-radius: 32px;
            background: rgba(15, 23, 42, 0.95);
            color: #f8fafc;
            box-shadow: 0 28px 90px rgba(15, 23, 42, 0.18);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 640px;
        }

        .aside-brand-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
        }

        .aside-brand-text {
            color: rgba(248, 250, 252, 0.78);
            font-size: 0.95rem;
        }

        .aside-title {
            font-size: clamp(2.3rem, 3vw, 3rem);
            font-weight: 800;
            line-height: 1.03;
            margin-bottom: 1rem;
        }

        .aside-copy {
            color: rgba(248, 250, 252, 0.86);
            line-height: 1.85;
            max-width: 33rem;
            margin-bottom: 2rem;
        }

        .feature-list {
            display: grid;
            gap: 1rem;
        }

        .feature-item {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 1rem;
            border-radius: 22px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 1.05rem 1.2rem;
        }

        .feature-item i {
            color: #a5b4fc;
            font-size: 1.2rem;
            margin-top: 0.2rem;
        }

        .feature-item span {
            color: rgba(248, 250, 252, 0.92);
            line-height: 1.7;
            font-size: 0.96rem;
        }

        .aside-note {
            color: rgba(248, 250, 252, 0.72);
            line-height: 1.8;
            font-size: 0.96rem;
            margin-top: 2.5rem;
        }

        .register-panel {
            display: grid;
            grid-template-rows: auto 1fr;
            border-radius: 32px;
            overflow: hidden;
            background: rgba(255,255,255,0.98);
            box-shadow: 0 35px 110px rgba(15, 23, 42, 0.18);
        }

        .panel-header {
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            padding: 2.2rem 2.4rem;
            text-align: center;
        }

        .panel-header h1 {
            color: #0f172a;
            font-size: clamp(1.9rem, 2.4vw, 2.4rem);
            margin-bottom: 0.8rem;
            font-weight: 800;
        }

        .panel-header p {
            color: #475569;
            line-height: 1.85;
            max-width: 540px;
            margin: 0 auto;
            font-size: 1rem;
        }

        .panel-body {
            padding: 2.4rem 2.6rem 2.8rem;
        }

        .form-label { color: #334155; font-weight: 600; }
        .form-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 18px;
            padding: 1rem 1.05rem;
            color: #0f172a;
            background: #ffffff;
            box-shadow: inset 0 0 0 1px rgba(148,163,184,0.08);
        }

        .form-input:focus {
            outline: none;
            border-color: #4338ca;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.14);
        }

        .btn-primary {
            width: 100%;
            border-radius: 18px;
            padding: 1rem 1.1rem;
            background: linear-gradient(135deg, #4338ca, #7c3aed);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 18px 44px rgba(67, 56, 202, 0.18);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 50px rgba(67, 56, 202, 0.24);
        }

        .helper-line {
            margin-top: 1.4rem;
            text-align: center;
            color: #64748b;
            font-size: 0.95rem;
        }

        .helper-line a {
            color: #4338ca;
            font-weight: 600;
            text-decoration: none;
        }

        .helper-line a:hover { color: #312e81; }

        .form-grid { display: grid; gap: 1rem; }
        .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-cols-1 { grid-template-columns: 1fr; }

        @media (max-width: 1024px) {
            .register-shell { grid-template-columns: 1fr; max-width: 900px; }
            .register-aside { min-height: auto; padding: 2.6rem 2rem; }
            .panel-header { padding: 2rem 2rem; }
            .panel-body { padding: 2rem 2rem 2.4rem; }
        }

        @media (max-width: 768px) {
            .page-shell { padding: 1.4rem 0.85rem; }
            .page-header { text-align: left; }
            .page-title { font-size: 2.1rem; }
            .register-aside { display: none; }
            .register-panel { border-radius: 28px; }
            .panel-header { padding: 1.8rem 1.6rem; }
            .panel-body { padding: 1.8rem 1.6rem 2.2rem; }
            .form-grid { gap: 0.95rem; }
            .btn-primary { padding: 0.95rem 1rem; }
        }

        @media (max-width: 420px) {
            .page-shell { padding: 1rem 0.6rem; }
            .page-title { font-size: 1.9rem; }
            .panel-header { padding: 1.4rem 1rem; }
            .panel-body { padding: 1.4rem 1rem 1.8rem; }
            .form-input { padding: 0.9rem 0.95rem; }
        }
    </style>
</head>
<body class="login-bg min-h-screen">
    <div class="page-shell">
        <header class="page-header">
            <a href="index.php" class="brand-link">
                <span class="brand-logo"><i class="fas fa-home text-white"></i></span>
                <div>
                    <div class="aside-brand-title">MyBalai</div>
                    <div class="aside-brand-text">Smart Barangay Services Portal</div>
                </div>
            </a>
            <h2 class="page-title">Create your resident account</h2>
            <p class="page-subtitle">Register as a barangay resident to access digital document requests, appointments, complaint filing, and community services.</p>
        </header>

        <main class="register-shell">
            <aside class="register-aside">
                <div>
                    <div class="aside-brand">
                        <span class="brand-logo"><i class="fas fa-shield-alt text-white"></i></span>
                        <div>
                            <div class="aside-brand-title">Resident-first portal</div>
                            <div class="aside-brand-text">A safe and simple way to create a resident account and access barangay programs from your phone or desktop.</div>
                        </div>
                    </div>

                    <div>
                        <h3 class="aside-title">Fast access to barangay services</h3>
                        <p class="aside-copy">Complete your registration and immediately start requesting documents, booking appointments, and tracking barangay announcements.</p>
                    </div>

                    <div class="feature-list">
                        <div class="feature-item">
                            <i class="fas fa-file-alt"></i>
                            <span>Quick online registration without visiting the barangay hall.</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-calendar-check"></i>
                            <span>Manage appointments, complaints, and resident requests in one portal.</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-users"></i>
                            <span>Designed for residents with clear guidance and mobile-friendly controls.</span>
                        </div>
                    </div>
                </div>
                <div class="aside-note">Need help while registering? Contact your barangay office if you encounter issues signing up or need assistance validating your account.</div>
            </aside>

            <section class="register-panel">
                <div class="panel-header">
                    <h1>Resident Registration</h1>
                    <p>For barangay residents only. Staff accounts are created by system administrators.</p>
                </div>

                <div class="panel-body">
                    <?php if ($message): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-6">
                            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-6">
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" autocomplete="off" class="space-y-5">
                        <div class="form-grid grid-cols-2">
                            <div>
                                <label class="form-label mb-2 block">First Name</label>
                                <input type="text" name="first_name" required class="form-input" placeholder="First name">
                            </div>
                            <div>
                                <label class="form-label mb-2 block">Last Name</label>
                                <input type="text" name="last_name" required class="form-input" placeholder="Last name">
                            </div>
                            <div>
                                <label class="form-label mb-2 block">Email</label>
                                <input type="email" name="email" required class="form-input" placeholder="Email address">
                            </div>
                            <div>
                                <label class="form-label mb-2 block">Username</label>
                                <input type="text" name="username" required class="form-input" placeholder="Choose a username">
                            </div>
                            <div>
                                <label class="form-label mb-2 block">Phone Number</label>
                                <input type="text" name="phone_number" class="form-input" placeholder="Optional">
                            </div>
                            <div>
                                <label class="form-label mb-2 block">Zip Code</label>
                                <input type="text" name="zip_code" class="form-input" placeholder="Optional">
                            </div>
                        </div>

                        <div>
                            <label class="form-label mb-2 block">Street Address</label>
                            <input id="streetAddressInput" list="addressSuggestions" type="text" name="street_address" placeholder="Type street, barangay, or city" class="form-input">
                            <datalist id="addressSuggestions"></datalist>
                        </div>

                        <div class="form-grid grid-cols-2">
                            <div>
                                <label class="form-label mb-2 block">Password</label>
                                <div class="relative">
                                    <input id="passwordInput" type="password" name="password" required minlength="8" autocomplete="new-password" class="form-input pr-12" placeholder="Create a password">
                                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-3 flex items-center text-slate-500 hover:text-slate-700 focus:outline-none" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="form-label mb-2 block">Confirm Password</label>
                                <div class="relative">
                                    <input id="confirmPasswordInput" type="password" name="confirm_password" required minlength="8" autocomplete="new-password" class="form-input pr-12" placeholder="Confirm password">
                                    <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-3 flex items-center text-slate-500 hover:text-slate-700 focus:outline-none" aria-label="Toggle confirm password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary">
                            <i class="fas fa-user-plus mr-2"></i> Create Resident Account
                        </button>
                    </form>

                    <div class="helper-line">
                        Already registered? <a href="login.php">Login here</a>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script>
        (function(){
            var passwordInput = document.getElementById('passwordInput');
            var togglePassword = document.getElementById('togglePassword');
            var confirmPasswordInput = document.getElementById('confirmPasswordInput');
            var toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

            if (togglePassword && passwordInput) {
                var icon = (typeof togglePassword.querySelector === 'function') ? togglePassword.querySelector('i') : null;
                togglePassword.addEventListener('click', function(){
                    try {
                        var isHidden = passwordInput.type === 'password';
                        passwordInput.type = isHidden ? 'text' : 'password';
                        if (icon) icon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
                    } catch (e) { console.warn('Password toggle failed', e); }
                });
            }

            if (toggleConfirmPassword && confirmPasswordInput) {
                var icon2 = (typeof toggleConfirmPassword.querySelector === 'function') ? toggleConfirmPassword.querySelector('i') : null;
                toggleConfirmPassword.addEventListener('click', function(){
                    try {
                        var isHidden = confirmPasswordInput.type === 'password';
                        confirmPasswordInput.type = isHidden ? 'text' : 'password';
                        if (icon2) icon2.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
                    } catch (e) { console.warn('Confirm toggle failed', e); }
                });
            }

            // Street address suggestions (barangay, city, province) using PSGC API endpoints
            var streetInput = document.getElementById('streetAddressInput');
            var list = document.getElementById('addressSuggestions');
            if (!streetInput || !list) return;

            var endpoints = [
                'https://psgc.vercel.app/api/barangay?q=',
                'https://psgc.vercel.app/api/municipality?q=',
                'https://psgc.vercel.app/api/city?q=',
                'https://psgc.vercel.app/api/province?q=',
                'https://psgc.vercel.app/api/search?q='
            ];

            function extractSuggestions(data) {
                var out = [];
                if (!data) return out;
                if (Array.isArray(data)) {
                    data.forEach(function(item){
                        var barangay = item.barangay || item.barangay_name || item.name || item.barangay_name_en;
                        var city = item.city_municipality || item.city || item.municipality_name || item.city_municipality_name || item.municipality;
                        var province = item.province || item.province_name || item.province_name_en;
                        var parts = [];
                        if (barangay) parts.push(barangay);
                        if (city) parts.push(city);
                        if (province) parts.push(province);
                        if (parts.length) out.push(parts.join(', '));
                    });
                } else if (typeof data === 'object') {
                    var item = data;
                    var barangay = item.barangay || item.barangay_name || item.name;
                    var city = item.city_municipality || item.city || item.municipality_name;
                    var province = item.province || item.province_name;
                    var parts = [];
                    if (barangay) parts.push(barangay);
                    if (city) parts.push(city);
                    if (province) parts.push(province);
                    if (parts.length) out.push(parts.join(', '));
                }
                return out;
            }

            function debounce(fn, wait) {
                wait = wait || 300;
                var t;
                return function() {
                    var args = arguments;
                    clearTimeout(t);
                    t = setTimeout(function(){ fn.apply(null, args); }, wait);
                };
            }

            var doLookup = debounce(function(q){
                if (!q || q.length < 2) { list.innerHTML = ''; return; }
                try {
                    var fetches = endpoints.map(function(ep){
                        return fetch(ep + encodeURIComponent(q)).then(function(r){ return r.ok ? r.json() : null; }).catch(function(){ return null; });
                    });
                    Promise.all(fetches).then(function(results){
                        var suggestions = {};
                        results.forEach(function(res){
                            var items = extractSuggestions(res);
                            items.forEach(function(s){ if (s && s.length) suggestions[s] = true; });
                        });
                        list.innerHTML = '';
                        Object.keys(suggestions).slice(0,20).forEach(function(val){
                            var opt = document.createElement('option');
                            opt.value = val;
                            list.appendChild(opt);
                        });
                    }).catch(function(e){ console.warn('Address lookup Promise failed', e); });
                } catch (e) { console.warn('Address lookup failed', e); }
            }, 300);

            streetInput.addEventListener('input', function(e){
                doLookup((e && e.target && e.target.value) ? e.target.value.trim() : '');
            });
        })();
    </script>
</body>
</html>
