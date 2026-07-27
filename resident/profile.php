<?php
require_once '_resident_common.php';

$userId = $_SESSION['user_id'];
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    updateSubset('users', [
        'first_name' => sanitize($_POST['first_name']),
        'last_name' => sanitize($_POST['last_name']),
        'email' => sanitize($_POST['email']),
        'phone_number' => sanitize($_POST['phone_number']),
        'updated_at' => date('Y-m-d H:i:s'),
    ], 'user_id', $userId);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM resident_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $profileData = [
        'user_id' => $userId,
        'zip_code' => sanitize($_POST['zip_code']),
        'street_address' => sanitize($_POST['street_address']),
        'barangay' => sanitize($_POST['barangay']),
        'city' => sanitize($_POST['city']),
        'province' => sanitize($_POST['province']),
        'emergency_contact_name' => sanitize($_POST['emergency_contact_name']),
        'emergency_contact_number' => sanitize($_POST['emergency_contact_number']),
        'updated_at' => date('Y-m-d H:i:s'),
    ];
    if ($stmt->fetchColumn()) {
        updateSubset('resident_profiles', $profileData, 'user_id', $userId);
    } else {
        $profileData['created_at'] = date('Y-m-d H:i:s');
        insertSubset('resident_profiles', $profileData);
    }

    $_SESSION['user_name'] = sanitize($_POST['first_name']) . ' ' . sanitize($_POST['last_name']);
    $_SESSION['user_email'] = sanitize($_POST['email']);
    logActivity($userId, 'Updated profile', 'users', $userId);
    $message = 'Profile updated successfully.';
}

$user = getUserData($userId);
$stmt = $pdo->prepare("SELECT * FROM resident_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch() ?: [];

residentHeader('My Profile', 'profile');
?>
<?php if ($message): ?>
    <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-sm mb-6">
        <?php echo e($message); ?>
    </div>
<?php endif; ?>

<div class="space-y-6">
    <section class="overflow-hidden rounded-[32px] bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 p-6 text-white shadow-[0_30px_80px_rgba(15,23,42,0.24)]">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-sm uppercase tracking-[0.3em] text-slate-300">Resident profile</p>
                <h1 class="mt-3 text-3xl font-semibold sm:text-4xl">Your personal details</h1>
                <p class="mt-4 text-sm leading-6 text-slate-200">Keep your contact information up to date so barangay staff can reach you when needed.</p>
            </div>
            <div class="grid w-full gap-3 sm:grid-cols-2 lg:w-auto">
                <div class="rounded-3xl bg-white/10 p-5 text-slate-100 ring-1 ring-white/10">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Registered</p>
                    <p class="mt-3 text-3xl font-semibold"><?php echo !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A'; ?></p>
                </div>
                <div class="rounded-3xl bg-white/10 p-5 text-slate-100 ring-1 ring-white/10">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Status</p>
                    <p class="mt-3 text-3xl font-semibold"><?php echo !empty($user['is_active']) ? 'Active' : 'Inactive'; ?></p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[minmax(320px,420px)_minmax(0,1fr)]">
        <aside class="space-y-6 lg:sticky lg:top-24">
            <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-center">
                    <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-slate-100 text-slate-400 shadow-sm">
                        <i class="fas fa-user-circle text-5xl"></i>
                    </div>
                    <h2 class="mt-4 text-xl font-semibold text-slate-900"><?php echo e(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?></h2>
                    <p class="mt-2 text-sm text-slate-500"><?php echo e($user['email'] ?? ''); ?></p>
                </div>

                <div class="mt-7 space-y-3 text-sm text-slate-600">
                    <div class="flex items-center justify-between rounded-3xl bg-slate-50 px-4 py-3">
                        <span class="text-slate-500">Account type</span>
                        <span class="font-semibold text-slate-900">Resident</span>
                    </div>
                    <div class="flex items-center justify-between rounded-3xl bg-slate-50 px-4 py-3">
                        <span class="text-slate-500">Member since</span>
                        <span class="font-semibold text-slate-900"><?php echo !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A'; ?></span>
                    </div>
                    <div class="flex items-center justify-between rounded-3xl bg-slate-50 px-4 py-3">
                        <span class="text-slate-500">Current status</span>
                        <span class="font-semibold text-slate-900"><?php echo !empty($user['is_active']) ? 'Active' : 'Inactive'; ?></span>
                    </div>
                </div>
            </section>

            <section class="rounded-[32px] border border-slate-200 bg-slate-950/95 p-6 text-slate-100 shadow-2xl shadow-slate-900/20">
                <p class="text-sm uppercase tracking-[0.28em] text-slate-400">Keep it fresh</p>
                <h3 class="mt-3 text-2xl font-semibold text-white">Update your details</h3>
                <p class="mt-3 text-sm leading-6 text-slate-300">Make sure your phone and emergency contact information are current for faster barangay response.</p>
            </section>
        </aside>

        <section class="space-y-6">
            <div class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Edit Profile</h2>
                        <p class="text-sm text-slate-500">Your information is only visible to barangay staff.</p>
                    </div>
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Profile details</span>
                </div>

                <form method="POST" class="mt-6 grid gap-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">First Name</label>
                            <input type="text" name="first_name" required value="<?php echo e($user['first_name'] ?? ''); ?>" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Last Name</label>
                            <input type="text" name="last_name" required value="<?php echo e($user['last_name'] ?? ''); ?>" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                            <input type="email" name="email" required value="<?php echo e($user['email'] ?? ''); ?>" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Phone Number</label>
                            <input type="text" name="phone_number" value="<?php echo e($user['phone_number'] ?? ''); ?>" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Zip Code</label>
                            <input type="text" name="zip_code" value="<?php echo e($profile['zip_code'] ?? ''); ?>" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Street Address</label>
                            <input id="streetAddressInput" list="addressSuggestions" type="text" name="street_address" value="<?php echo e($profile['street_address'] ?? ''); ?>" placeholder="Type street, barangay, or city" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                            <datalist id="addressSuggestions"></datalist>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Barangay</label>
                            <input type="text" name="barangay" value="<?php echo e($profile['barangay'] ?? ''); ?>" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">City</label>
                            <input type="text" name="city" value="<?php echo e($profile['city'] ?? ''); ?>" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Province</label>
                            <input type="text" name="province" value="<?php echo e($profile['province'] ?? ''); ?>" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Emergency Contact</label>
                            <input type="text" name="emergency_contact_name" value="<?php echo e($profile['emergency_contact_name'] ?? ''); ?>" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Emergency Contact Number</label>
                        <input type="text" name="emergency_contact_number" value="<?php echo e($profile['emergency_contact_number'] ?? ''); ?>" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                    </div>

                    <button class="inline-flex w-full items-center justify-center rounded-3xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-700">Save Profile</button>
                </form>
            </div>
        </section>
    </div>
</div>

<script>
    (function(){
        const streetInput = document.getElementById('streetAddressInput');
        const list = document.getElementById('addressSuggestions');
        if (!streetInput || !list) return;

        const endpoints = [
            'https://psgc.vercel.app/api/barangay?q=',
            'https://psgc.vercel.app/api/municipality?q=',
            'https://psgc.vercel.app/api/city?q=',
            'https://psgc.vercel.app/api/province?q=',
            'https://psgc.vercel.app/api/search?q='
        ];

        function extractSuggestions(data) {
            const out = [];
            if (!data) return out;
            if (Array.isArray(data)) {
                data.forEach(item => {
                    const barangay = item.barangay || item.barangay_name || item.name || item.barangay_name_en;
                    const city = item.city_municipality || item.city || item.municipality_name || item.city_municipality_name || item.municipality;
                    const province = item.province || item.province_name || item.province_name_en;
                    const parts = [];
                    if (barangay) parts.push(barangay);
                    if (city) parts.push(city);
                    if (province) parts.push(province);
                    if (parts.length) out.push(parts.join(', '));
                });
            } else if (typeof data === 'object') {
                const item = data;
                const barangay = item.barangay || item.barangay_name || item.name;
                const city = item.city_municipality || item.city || item.municipality_name;
                const province = item.province || item.province_name;
                const parts = [];
                if (barangay) parts.push(barangay);
                if (city) parts.push(city);
                if (province) parts.push(province);
                if (parts.length) out.push(parts.join(', '));
            }
            return out;
        }

        function debounce(fn, wait = 300){
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), wait);
            };
        }

        const doLookup = debounce(async (q) => {
            if (!q || q.length < 2) { list.innerHTML = ''; return; }
            try {
                const fetches = endpoints.map(ep => fetch(ep + encodeURIComponent(q)).then(r => r.ok ? r.json() : null).catch(() => null));
                const results = await Promise.all(fetches);
                const suggestions = new Set();
                results.forEach(res => {
                    const items = extractSuggestions(res);
                    items.forEach(s => {
                        if (s && s.length) suggestions.add(s);
                    });
                });
                list.innerHTML = '';
                Array.from(suggestions).slice(0, 20).forEach(val => {
                    const opt = document.createElement('option');
                    opt.value = val;
                    list.appendChild(opt);
                });
            } catch (e) {
                console.warn('Address lookup failed', e);
            }
        }, 300);

        streetInput.addEventListener('input', (e) => {
            doLookup(e.target.value.trim());
        });
    })();
</script>

<?php residentFooter(); ?>
