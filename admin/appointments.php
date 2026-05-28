<?php
require_once '_admin_common.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['appointment_id'])) {
    updateSubset('appointments', [
        'status' => sanitize($_POST['status']),
        'admin_notes' => sanitize($_POST['admin_notes'] ?? ''),
        'confirmed_by' => $_SESSION['user_id'],
        'updated_at' => date('Y-m-d H:i:s'),
    ], 'appointment_id', (int)$_POST['appointment_id']);
    logActivity($_SESSION['user_id'], 'Updated appointment', 'appointments', (int)$_POST['appointment_id']);
    $message = 'Appointment updated successfully.';
}

$stmt = $pdo->query("
    SELECT a.*, u.first_name, u.last_name, u.email
    FROM appointments a
    JOIN users u ON a.user_id = u.user_id
    ORDER BY a.preferred_date DESC, a.preferred_time DESC
");
$appointments = $stmt->fetchAll();

adminHeader('Appointments', 'appointments');
?>
<?php if ($message): ?>
<div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4"><?php echo e($message); ?></div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b"><h3 class="text-lg font-semibold text-gray-800">Manage Appointments</h3></div>
    <div class="overflow-x-auto hidden md:block">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resident</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Schedule</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Update</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($appointments as $appointment): ?>
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-medium"><?php echo e($appointment['first_name'] . ' ' . $appointment['last_name']); ?></div>
                        <div class="text-sm text-gray-500"><?php echo e($appointment['email']); ?></div>
                    </td>
                    <td class="px-6 py-4"><?php echo e(labelize($appointment['appointment_type'] ?? 'Appointment')); ?></td>
                    <td class="px-6 py-4 text-sm">
                        <?php echo !empty($appointment['preferred_date']) ? date('M d, Y', strtotime($appointment['preferred_date'])) : 'N/A'; ?>
                        <?php echo !empty($appointment['preferred_time']) ? ' at ' . date('g:i A', strtotime($appointment['preferred_time'])) : ''; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs"><?php echo e($appointment['purpose'] ?? 'N/A'); ?></td>
                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full <?php echo statusBadge($appointment['status'] ?? 'pending'); ?>"><?php echo e(labelize($appointment['status'] ?? 'pending')); ?></span></td>
                    <td class="px-6 py-4">
                        <form method="POST" class="space-y-2">
                            <input type="hidden" name="appointment_id" value="<?php echo (int)$appointment['appointment_id']; ?>">
                            <select name="status" class="border rounded px-2 py-1 text-sm w-full">
                                <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo ($appointment['status'] ?? '') == $status ? 'selected' : ''; ?>><?php echo labelize($status); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="admin_notes" placeholder="Notes" class="border rounded px-2 py-1 text-sm w-full" value="<?php echo e($appointment['admin_notes'] ?? ''); ?>">
                            <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($appointments)): ?>
                <tr><td colspan="6" class="text-center py-8 text-gray-500">No appointments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile stacked cards -->
    <div class="md:hidden p-4 space-y-3">
        <?php if (empty($appointments)): ?>
            <div class="text-center py-8 text-gray-500">No appointments found.</div>
        <?php endif; ?>
        <?php foreach ($appointments as $appointment): ?>
        <div class="bg-white rounded-lg shadow p-3">
            <div class="flex items-start justify-between">
                <div class="pr-3">
                    <div class="font-medium text-gray-900"><?php echo e($appointment['first_name'] . ' ' . $appointment['last_name']); ?></div>
                    <div class="text-sm text-gray-500 mb-2"><?php echo e($appointment['email']); ?></div>
                    <div class="text-sm text-gray-700"><strong>Type:</strong> <?php echo e(labelize($appointment['appointment_type'] ?? 'Appointment')); ?></div>
                    <div class="text-sm text-gray-700 mt-1"><strong>Schedule:</strong>
                        <?php echo !empty($appointment['preferred_date']) ? date('M d, Y', strtotime($appointment['preferred_date'])) : 'N/A'; ?>
                        <?php echo !empty($appointment['preferred_time']) ? ' at ' . date('g:i A', strtotime($appointment['preferred_time'])) : ''; ?>
                    </div>
                    <div class="text-sm text-gray-700 mt-1"><strong>Purpose:</strong> <?php echo e($appointment['purpose'] ?? 'N/A'); ?></div>
                </div>
                <div class="text-right">
                    <div class="mb-2"><span class="px-2 py-1 text-xs rounded-full <?php echo statusBadge($appointment['status'] ?? 'pending'); ?>"><?php echo e(labelize($appointment['status'] ?? 'pending')); ?></span></div>
                </div>
            </div>
            <form method="POST" class="mt-3 space-y-2">
                <input type="hidden" name="appointment_id" value="<?php echo (int)$appointment['appointment_id']; ?>">
                <div>
                    <select name="status" class="w-full border rounded px-2 py-2 text-sm">
                        <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $status): ?>
                        <option value="<?php echo $status; ?>" <?php echo ($appointment['status'] ?? '') == $status ? 'selected' : ''; ?>><?php echo labelize($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <input type="text" name="admin_notes" placeholder="Notes" class="w-full border rounded px-2 py-2 text-sm" value="<?php echo e($appointment['admin_notes'] ?? ''); ?>">
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
