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
<?php if ($message): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-6"><?php echo e($message); ?></div><?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow p-6">
            <div class="text-center">
                <i class="fas fa-user-circle profile-avatar text-6xl md:text-7xl text-gray-300 mb-3"></i>
            <h2 class="text-xl font-semibold text-gray-800"><?php echo e(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></h2>
            <p class="text-sm text-gray-500"><?php echo e($user['email'] ?? ''); ?></p>
        </div>
        <div class="mt-6 space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Account Type</span><span class="font-medium">Resident</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Status</span><span class="font-medium"><?php echo !empty($user['is_active']) ? 'Active' : 'Inactive'; ?></span></div>
            <div class="flex justify-between"><span class="text-gray-500">Member Since</span><span class="font-medium"><?php echo !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A'; ?></span></div>
        </div>
    </div>

    <form method="POST" class="lg:col-span-2 bg-white rounded-xl shadow p-6 space-y-5">
        <h2 class="text-xl font-semibold text-gray-800">Profile Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                <input type="text" name="first_name" required value="<?php echo e($user['first_name'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                <input type="text" name="last_name" required value="<?php echo e($user['last_name'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required value="<?php echo e($user['email'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input type="text" name="phone_number" value="<?php echo e($user['phone_number'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Zip Code</label>
                <input type="text" name="zip_code" value="<?php echo e($profile['zip_code'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                <input id="streetAddressInput" list="addressSuggestions" type="text" name="street_address" value="<?php echo e($profile['street_address'] ?? ''); ?>" placeholder="Type street, barangay, or city" class="w-full border rounded-lg px-3 py-2">
                <datalist id="addressSuggestions"></datalist>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                <input type="text" name="barangay" value="<?php echo e($profile['barangay'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <input type="text" name="city" value="<?php echo e($profile['city'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                <input type="text" name="province" value="<?php echo e($profile['province'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact</label>
                <input type="text" name="emergency_contact_name" value="<?php echo e($profile['emergency_contact_name'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Number</label>
                <input type="text" name="emergency_contact_number" value="<?php echo e($profile['emergency_contact_number'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
        </div>
        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 w-full">Save Profile</button>
    </form>
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
