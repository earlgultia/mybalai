<?php
require_once '_admin_common.php';

$message = '';
$isTreasurer = hasRole('barangay_treasurer');
// Ensure `system_settings` table exists; if not, attempt to create it so settings can be saved.
try {
    $cols = tableColumns('system_settings');
    if (empty($cols)) {
        $createSql = "CREATE TABLE IF NOT EXISTS `system_settings` (
            `setting_id` int(11) NOT NULL AUTO_INCREMENT,
            `setting_key` varchar(100) NOT NULL,
            `setting_value` text DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`setting_id`),
            UNIQUE KEY `unique_setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $pdo->exec($createSql);
        // refresh columns cache by calling tableColumns again
        $cols = tableColumns('system_settings');
    }
} catch (Exception $e) {
    // creation failed — we'll handle missing columns during save
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $settings = [
        'barangay_name' => sanitize($_POST['barangay_name'] ?? ''),
        'barangay_address' => sanitize($_POST['barangay_address'] ?? ''),
        'contact_email' => sanitize($_POST['contact_email'] ?? ''),
        'contact_phone' => sanitize($_POST['contact_phone'] ?? ''),
        'monthly_fee' => sanitize($_POST['monthly_fee'] ?? ''),
    ];
    if ($isTreasurer) {
        $settings['treasurer_availability'] = sanitize($_POST['treasurer_availability'] ?? 'in_office');
        $settings['treasurer_gcash_phone'] = sanitize($_POST['treasurer_gcash_phone'] ?? '');
    }

    $columns = tableColumns('system_settings');
    if (in_array('setting_key', $columns) && in_array('setting_value', $columns)) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = ?");
        foreach ($settings as $key => $value) {
            $stmtCheck->execute([$key]);
            if ($stmtCheck->fetchColumn()) {
                updateSubset('system_settings', ['setting_value' => $value, 'updated_at' => date('Y-m-d H:i:s')], 'setting_key', $key);
            } else {
                insertSubset('system_settings', ['setting_key' => $key, 'setting_value' => $value, 'created_at' => date('Y-m-d H:i:s')]);
            }
        }
        $message = 'Settings saved.';
    } else {
        $message = 'Unable to save settings (missing table columns).';
    }
}

$values = [
    'barangay_name' => 'Barangay Alejawan Lutao, Duero, Bohol',
    'barangay_address' => 'Barangay Alejawan Lutao, Duero, Bohol',
    'contact_email' => '',
    'contact_phone' => '09944462851',
    'monthly_fee' => '',
    'treasurer_availability' => 'in_office',
    'treasurer_gcash_phone' => '',
];
try {
    $columns = tableColumns('system_settings');
    if (in_array('setting_key', $columns) && in_array('setting_value', $columns)) {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
        foreach ($stmt->fetchAll() as $row) {
            $values[$row['setting_key']] = $row['setting_value'];
        }
    }
} catch (Exception $e) {
}

adminHeader('System Settings', 'settings');
?>
<?php if ($message): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4"><?php echo e($message); ?></div><?php endif; ?>

<div class="space-y-4">
    <div class="md:hidden rounded-[28px] bg-slate-950 text-white p-5 shadow-lg border border-slate-800">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-slate-400">System Settings</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">Barangay configuration</h2>
                <p class="mt-2 text-sm text-slate-300">Update your barangay's contact details, fee info, and system contact settings.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-200">Admin</span>
        </div>
        <div class="mt-5 grid gap-3 sm:grid-cols-2">
            <div class="rounded-3xl bg-slate-900 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Barangay</p>
                <p class="mt-3 text-sm font-semibold text-white"><?php echo e($values['barangay_name'] ?: 'Not set'); ?></p>
            </div>
            <div class="rounded-3xl bg-slate-900 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Contact email</p>
                <p class="mt-3 text-sm font-semibold text-white"><?php echo e($values['contact_email'] ?: 'Not set'); ?></p>
            </div>
            <div class="rounded-3xl bg-slate-900 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Contact phone</p>
                <p class="mt-3 text-sm font-semibold text-white"><?php echo e($values['contact_phone'] ?: 'Not set'); ?></p>
            </div>
            <div class="rounded-3xl bg-slate-900 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Monthly fee</p>
                <p class="mt-3 text-sm font-semibold text-white"><?php echo e($values['monthly_fee'] !== '' ? '₱' . e($values['monthly_fee']) : 'Not set'); ?></p>
            </div>
        </div>
    </div>

    <div class="hidden md:grid md:grid-cols-3 gap-6">
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500 uppercase tracking-[0.24em]">Barangay</p>
            <p class="mt-4 text-2xl font-semibold text-slate-900"><?php echo e($values['barangay_name'] ?: 'Not set'); ?></p>
        </div>
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500 uppercase tracking-[0.24em]">Contact</p>
            <p class="mt-4 text-lg font-semibold text-slate-900"><?php echo e($values['contact_phone'] ?: 'No phone'); ?></p>
            <p class="mt-1 text-sm text-slate-500"><?php echo e($values['contact_email'] ?: 'No email'); ?></p>
        </div>
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500 uppercase tracking-[0.24em]">Monthly fee</p>
            <p class="mt-4 text-3xl font-semibold text-slate-900"><?php echo e($values['monthly_fee'] !== '' ? '₱' . e($values['monthly_fee']) : '—'); ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <form method="POST" class="lg:col-span-2 bg-white rounded-lg shadow p-6 space-y-4">
        <h3 class="text-lg font-semibold">Barangay Settings</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Barangay Name</label>
                <input type="text" name="barangay_name" value="<?php echo e($values['barangay_name']); ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Fee</label>
                <input type="number" step="0.01" name="monthly_fee" value="<?php echo e($values['monthly_fee']); ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                <input type="email" name="contact_email" value="<?php echo e($values['contact_email']); ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                <input type="text" name="contact_phone" value="<?php echo e($values['contact_phone']); ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <?php if ($isTreasurer): ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Treasurer Availability</label>
                <select name="treasurer_availability" class="w-full border rounded-lg px-3 py-2">
                    <option value="in_office" <?php echo ($values['treasurer_availability'] ?? 'in_office') === 'in_office' ? 'selected' : ''; ?>>In office / available for cash</option>
                    <option value="away" <?php echo ($values['treasurer_availability'] ?? 'in_office') === 'away' ? 'selected' : ''; ?>>Away / not available</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Treasurer GCash Phone Number</label>
                <input type="text" name="treasurer_gcash_phone" value="<?php echo e($values['treasurer_gcash_phone']); ?>" placeholder="e.g. 09XXXXXXXXX" class="w-full border rounded-lg px-3 py-2">
            </div>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea name="barangay_address" rows="3" class="w-full border rounded-lg px-3 py-2"><?php echo e($values['barangay_address']); ?></textarea>
        </div>
        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Save Settings</button>
    </form>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">System</h3>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Signed in as</span><span class="font-medium"><?php echo e($_SESSION['user_name']); ?></span></div>
            <div class="flex justify-between"><span class="text-gray-500">Role</span><span class="font-medium"><?php echo e(ucfirst($_SESSION['user_type'])); ?></span></div>
            <div class="flex justify-between"><span class="text-gray-500">Date</span><span class="font-medium"><?php echo date('M d, Y'); ?></span></div>
        </div>
    </div>
</div>
<?php adminFooter(); ?>
