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
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
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
    </style>
    <style>
        .login-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .login-card { background: rgba(255,255,255,0.98); }

        /* Mobile layout tweaks */
        @media (max-width: 768px) {
            .container { padding-left: 1rem; padding-right: 1rem; }
            .max-w-3xl { max-width: 100%; }
            .login-card { border-radius: 14px; box-shadow: 0 12px 30px rgba(2,6,23,0.12); }
            .p-8 { padding: 1rem; }
            .bg-gradient-to-r { padding: 1rem; }
            .grid.md\:grid-cols-2 { grid-template-columns: 1fr; }
            .grid > div { width: 100%; }
            input.w-full { font-size: 0.95rem; padding-top: 0.75rem; padding-bottom: 0.75rem; }
            .w-full.bg-gradient-to-r { padding: 0.75rem; }
            button[type="submit"] { padding-top: 0.85rem; padding-bottom: 0.85rem; }
            #togglePassword, #toggleConfirmPassword { right: 10px; }
        }

        @media (max-width: 420px) {
            .bg-gradient-to-r h1 { font-size: 1.25rem; }
            .text-2xl { font-size: 1.15rem; }
            .p-8 { padding: 0.75rem; }
            .login-card { margin: 0 8px; }
        }
    </style>
</head>
<body class="login-bg min-h-screen">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-8">
                <a href="index.php" class="inline-flex items-center space-x-2 text-white">
                    <i class="fas fa-home text-3xl"></i>
                    <span class="text-2xl font-bold">MyBalai</span>
                </a>
                <p class="text-white mt-2">Create a resident account</p>
            </div>

            <div class="login-card rounded-2xl shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
                    <h1 class="text-2xl font-bold">Resident Registration</h1>
                    <p class="text-indigo-100 mt-1">For barangay residents only. Staff accounts are created by the system admin.</p>
                </div>
                <div class="p-8">
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">First Name</label>
                                <input type="text" name="first_name" required class="w-full border rounded-lg px-4 py-3">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
                                <input type="text" name="last_name" required class="w-full border rounded-lg px-4 py-3">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                <input type="email" name="email" required class="w-full border rounded-lg px-4 py-3">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                                <input type="text" name="username" required class="w-full border rounded-lg px-4 py-3">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                                <input type="text" name="phone_number" class="w-full border rounded-lg px-4 py-3">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Zip Code</label>
                                <input type="text" name="zip_code" class="w-full border rounded-lg px-4 py-3">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Street Address</label>
                                <input id="streetAddressInput" list="addressSuggestions" type="text" name="street_address" placeholder="Type street, barangay, or city" class="w-full border rounded-lg px-4 py-3">
                                <datalist id="addressSuggestions"></datalist>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                                <div class="relative">
                                    <input id="passwordInput" type="password" name="password" required minlength="8" autocomplete="new-password" value="" class="w-full border rounded-lg px-4 py-3 pr-12">
                                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-3 flex items-center text-slate-500 transition hover:text-slate-800 focus:outline-none" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                                <div class="relative">
                                    <input id="confirmPasswordInput" type="password" name="confirm_password" required minlength="8" autocomplete="new-password" value="" class="w-full border rounded-lg px-4 py-3 pr-12">
                                    <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-3 flex items-center text-slate-500 transition hover:text-slate-800 focus:outline-none" aria-label="Toggle confirm password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-3 px-4 rounded-lg hover:from-indigo-700 hover:to-purple-700">
                            <i class="fas fa-user-plus mr-2"></i>Create Resident Account
                        </button>
                    </form>

                    <div class="mt-6 text-center text-sm">
                        <span class="text-gray-600">Already registered?</span>
                        <a href="login.php" class="font-semibold text-indigo-600 hover:text-indigo-800">Login</a>
                    </div>
                </div>
            </div>
        </div>
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
