<?php
require_once '_admin_common.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complaint_id'])) {
    updateSubset('complaints', [
        'status' => sanitize($_POST['status']),
        'resolution_notes' => sanitize($_POST['resolution_notes'] ?? ''),
        'assigned_to' => $_SESSION['user_id'],
        'resolved_at' => $_POST['status'] == 'resolved' ? date('Y-m-d H:i:s') : null,
        'updated_at' => date('Y-m-d H:i:s'),
    ], 'complaint_id', (int)$_POST['complaint_id']);
    logActivity($_SESSION['user_id'], 'Updated complaint', 'complaints', (int)$_POST['complaint_id']);
    $message = 'Complaint updated successfully.';
}

$stmt = $pdo->query("
    SELECT c.*, u.first_name, u.last_name, u.email
    FROM complaints c
    JOIN users u ON c.complainant_id = u.user_id
    ORDER BY c.created_at DESC
");
$complaints = $stmt->fetchAll();

adminHeader('Complaints / Blotter', 'complaints');
?>
<?php if ($message): ?>
<div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4"><?php echo e($message); ?></div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b"><h3 class="text-lg font-semibold text-gray-800">Manage Complaints</h3></div>
    <div class="overflow-x-auto hidden md:block">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Complainant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Filed</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Update</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($complaints as $complaint): ?>
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-medium"><?php echo e($complaint['first_name'] . ' ' . $complaint['last_name']); ?></div>
                        <div class="text-sm text-gray-500"><?php echo e($complaint['email']); ?></div>
                    </td>
                    <td class="px-6 py-4"><?php echo e(labelize($complaint['complaint_type'] ?? 'Complaint')); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-md">
                        <?php if (!empty($complaint['subject'])): ?><div class="font-medium text-gray-800"><?php echo e($complaint['subject']); ?></div><?php endif; ?>
                        <?php echo e($complaint['description'] ?? 'N/A'); ?>
                    </td>
                    <td class="px-6 py-4 text-sm"><?php echo !empty($complaint['created_at']) ? date('M d, Y', strtotime($complaint['created_at'])) : 'N/A'; ?></td>
                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full <?php echo statusBadge($complaint['status'] ?? 'submitted'); ?>"><?php echo e(labelize($complaint['status'] ?? 'submitted')); ?></span></td>
                    <td class="px-6 py-4">
                        <form method="POST" class="space-y-2">
                            <input type="hidden" name="complaint_id" value="<?php echo (int)$complaint['complaint_id']; ?>">
                            <select name="status" class="border rounded px-2 py-1 text-sm w-full">
                                <?php foreach (['submitted', 'in_progress', 'resolved', 'dismissed'] as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo ($complaint['status'] ?? '') == $status ? 'selected' : ''; ?>><?php echo labelize($status); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="resolution_notes" placeholder="Resolution notes" class="border rounded px-2 py-1 text-sm w-full" value="<?php echo e($complaint['resolution_notes'] ?? ''); ?>">
                            <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($complaints)): ?>
                <tr><td colspan="6" class="text-center py-8 text-gray-500">No complaints found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile stacked cards -->
    <div class="md:hidden p-4 space-y-3">
        <?php if (empty($complaints)): ?>
            <div class="text-center py-8 text-gray-500">No complaints found.</div>
        <?php endif; ?>
        <?php foreach ($complaints as $complaint): ?>
        <div class="bg-white rounded-lg shadow p-3">
            <div class="flex items-start justify-between">
                <div class="pr-3">
                    <div class="font-medium text-gray-900"><?php echo e($complaint['first_name'] . ' ' . $complaint['last_name']); ?></div>
                    <div class="text-sm text-gray-500 mb-2"><?php echo e($complaint['email']); ?></div>
                    <div class="text-sm text-gray-700"><strong>Type:</strong> <?php echo e(labelize($complaint['complaint_type'] ?? 'Complaint')); ?></div>
                    <?php if (!empty($complaint['subject'])): ?><div class="text-sm font-medium text-gray-800 mt-1"><?php echo e($complaint['subject']); ?></div><?php endif; ?>
                    <div class="text-sm text-gray-700 mt-1"><?php echo e($complaint['description'] ?? 'N/A'); ?></div>
                    <div class="text-sm text-gray-500 mt-2">Filed: <?php echo !empty($complaint['created_at']) ? date('M d, Y', strtotime($complaint['created_at'])) : 'N/A'; ?></div>
                </div>
                <div class="text-right">
                    <div class="mb-2"><span class="px-2 py-1 text-xs rounded-full <?php echo statusBadge($complaint['status'] ?? 'submitted'); ?>"><?php echo e(labelize($complaint['status'] ?? 'submitted')); ?></span></div>
                </div>
            </div>
            <form method="POST" class="mt-3 space-y-2">
                <input type="hidden" name="complaint_id" value="<?php echo (int)$complaint['complaint_id']; ?>">
                <div>
                    <select name="status" class="w-full border rounded px-2 py-2 text-sm">
                        <?php foreach (['submitted', 'in_progress', 'resolved', 'dismissed'] as $status): ?>
                        <option value="<?php echo $status; ?>" <?php echo ($complaint['status'] ?? '') == $status ? 'selected' : ''; ?>><?php echo labelize($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <input type="text" name="resolution_notes" placeholder="Resolution notes" class="w-full border rounded px-2 py-2 text-sm" value="<?php echo e($complaint['resolution_notes'] ?? ''); ?>">
                </div>
                <div class="flex">
                    <button class="flex-1 bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php adminFooter(); ?>
