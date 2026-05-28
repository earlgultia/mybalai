<?php
require_once '_admin_common.php';

$message = '';
if (isset($_GET['toggle'])) {
    $userId = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("
        SELECT u.is_active
        FROM users u
        JOIN user_role_assignments ura ON ura.user_id = u.user_id AND ura.is_active = 1
        JOIN roles r ON r.role_id = ura.role_id
        WHERE u.user_id = ? AND r.role_name = 'resident'
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $resident = $stmt->fetch();
    if ($resident) {
        updateSubset('users', [
            'is_active' => $resident['is_active'] ? 0 : 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'user_id', $userId);
        logActivity($_SESSION['user_id'], 'Updated resident status', 'users', $userId);
        redirect('residents.php?msg=status');
    }
}

// Handle resident deletion (allow barangay captain or users with permission)
if (isset($_GET['delete'])) {
    $userId = (int)$_GET['delete'];
    if (hasRole('barangay_captain') || hasPermission('delete_residents')) {
        updateSubset('users', [
            'is_active' => 0,
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], 'user_id', $userId);
        // deactivate any role assignments
        $stmt = $pdo->prepare("UPDATE user_role_assignments SET is_active = 0 WHERE user_id = ?");
        $stmt->execute([$userId]);
        logActivity($_SESSION['user_id'], 'Deleted resident', 'users', $userId);
        redirect('residents.php?msg=deleted');
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'status') {
    $message = 'Resident status updated.';
}

$stmt = $pdo->query("
    SELECT u.*, rp.*,
           (SELECT COUNT(*) FROM document_requests WHERE user_id = u.user_id) AS total_requests,
           (SELECT COUNT(*) FROM complaints WHERE complainant_id = u.user_id) AS total_complaints,
           (SELECT COUNT(*) FROM appointments WHERE user_id = u.user_id) AS total_appointments
    FROM users u
    LEFT JOIN resident_profiles rp ON u.user_id = rp.user_id
    JOIN user_role_assignments ura ON ura.user_id = u.user_id AND ura.is_active = 1
    JOIN roles r ON r.role_id = ura.role_id
    WHERE r.role_name = 'resident' AND (u.deleted_at IS NULL OR u.deleted_at = '0000-00-00 00:00:00')
    ORDER BY u.created_at DESC
");
$residents = $stmt->fetchAll();

adminHeader('Residents', 'residents');
?>
<?php if ($message): ?>
<div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4"><?php echo e($message); ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="stat-card bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Total Residents</p>
        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo count($residents); ?></p>
    </div>
    <div class="stat-card bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Active Accounts</p>
        <p class="text-3xl font-bold text-green-600 mt-2"><?php echo count(array_filter($residents, fn($resident) => !empty($resident['is_active']))); ?></p>
    </div>
    <div class="stat-card bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Inactive Accounts</p>
        <p class="text-3xl font-bold text-red-600 mt-2"><?php echo count(array_filter($residents, fn($resident) => empty($resident['is_active']))); ?></p>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b flex flex-wrap gap-3 justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Resident Directory</h3>
            <p class="text-sm text-gray-500">Review profiles, requests, complaints, and appointment activity.</p>
        </div>
        <input id="residentSearch" type="search" placeholder="Search residents" class="border rounded-lg px-3 py-2 w-full sm:w-72">
    </div>
    <div class="overflow-x-auto hidden md:block">
        <table class="w-full" id="residentTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resident</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($residents as $resident): ?>
                <tr>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                                <?php echo e(strtoupper(substr($resident['first_name'] ?? 'R', 0, 1) . substr($resident['last_name'] ?? '', 0, 1))); ?>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900"><?php echo e(($resident['first_name'] ?? '') . ' ' . ($resident['last_name'] ?? '')); ?></div>
                                <div class="text-sm text-gray-500">ID #<?php echo (int)$resident['user_id']; ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <div><?php echo e($resident['email'] ?? 'N/A'); ?></div>
                        <div class="text-gray-500"><?php echo e($resident['phone_number'] ?? 'N/A'); ?></div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <?php echo e(trim(($resident['house_number'] ?? '') . ' ' . ($resident['street_address'] ?? '')) ?: 'N/A'); ?>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <div>Documents: <?php echo (int)($resident['total_requests'] ?? 0); ?></div>
                        <div>Complaints: <?php echo (int)($resident['total_complaints'] ?? 0); ?></div>
                        <div>Appointments: <?php echo (int)($resident['total_appointments'] ?? 0); ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full <?php echo !empty($resident['is_active']) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo !empty($resident['is_active']) ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="residents.php?toggle=<?php echo (int)$resident['user_id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm font-semibold mr-3">
                            <?php echo !empty($resident['is_active']) ? 'Deactivate' : 'Activate'; ?>
                        </a>
                        <?php if (hasRole('barangay_captain') || hasPermission('delete_residents')): ?>
                        <a href="#" onclick="if(confirm('Delete this resident account? This cannot be undone.')){ window.location='residents.php?delete=<?php echo (int)$resident['user_id']; ?>'; } return false;" class="text-red-600 hover:text-red-800 text-sm font-semibold">
                            Delete
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($residents)): ?>
                <tr><td colspan="6" class="text-center py-8 text-gray-500">No residents found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile stacked cards -->
    <div class="md:hidden p-4 space-y-3" id="residentCards">
        <?php if (empty($residents)): ?>
            <div class="text-center py-8 text-gray-500">No residents found.</div>
        <?php endif; ?>
        <?php foreach ($residents as $resident): ?>
        <div class="resident-card bg-white rounded-lg shadow p-3">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                        <?php echo e(strtoupper(substr($resident['first_name'] ?? 'R', 0, 1) . substr($resident['last_name'] ?? '', 0, 1))); ?>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900"><?php echo e(($resident['first_name'] ?? '') . ' ' . ($resident['last_name'] ?? '')); ?></div>
                        <div class="text-xs text-gray-500">ID #<?php echo (int)$resident['user_id']; ?></div>
                    </div>
                </div>
                <div class="text-right">
                    <span class="px-2 py-1 text-xs rounded-full <?php echo !empty($resident['is_active']) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo !empty($resident['is_active']) ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
            </div>
            <div class="mt-3 text-sm text-gray-700">
                <div><?php echo e($resident['email'] ?? 'N/A'); ?> • <?php echo e($resident['phone_number'] ?? 'N/A'); ?></div>
                <div class="text-gray-600 mt-1"><?php echo e(trim(($resident['house_number'] ?? '') . ' ' . ($resident['street_address'] ?? '')) ?: 'N/A'); ?></div>
                <div class="text-sm text-gray-600 mt-2">Documents: <?php echo (int)($resident['total_requests'] ?? 0); ?> • Complaints: <?php echo (int)($resident['total_complaints'] ?? 0); ?> • Appointments: <?php echo (int)($resident['total_appointments'] ?? 0); ?></div>
            </div>
            <div class="mt-3">
                        <div class="grid grid-cols-2 gap-2">
                            <a href="residents.php?toggle=<?php echo (int)$resident['user_id']; ?>" class="block text-center px-3 py-2 rounded-lg border <?php echo !empty($resident['is_active']) ? 'border-red-600 text-red-600' : 'border-green-600 text-green-600'; ?>">
                                <?php echo !empty($resident['is_active']) ? 'Deactivate' : 'Activate'; ?>
                            </a>
                            <?php if (hasRole('barangay_captain') || hasPermission('delete_residents')): ?>
                            <a href="#" onclick="if(confirm('Delete this resident account? This cannot be undone.')){ window.location='residents.php?delete=<?php echo (int)$resident['user_id']; ?>'; } return false;" class="block text-center px-3 py-2 rounded-lg border border-red-600 text-red-600">
                                Delete
                            </a>
                            <?php endif; ?>
                        </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    (function () {
        const residentSearch = document.getElementById('residentSearch');
        const filter = () => {
            const q = (residentSearch?.value || '').trim().toLowerCase();
            const rows = document.querySelectorAll('#residentTable tbody tr');
            const cards = document.querySelectorAll('.resident-card');
            rows.forEach(row => {
                row.style.display = q === '' || row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
            cards.forEach(card => {
                card.style.display = q === '' || card.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        };
        residentSearch?.addEventListener('input', filter);
    })();
</script>
<?php adminFooter(); ?>
