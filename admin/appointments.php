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

<?php
$totalAppointments = count($appointments);
$pendingCount = count(array_filter($appointments, fn($appointment) => ($appointment['status'] ?? '') === 'pending'));
$confirmedCount = count(array_filter($appointments, fn($appointment) => ($appointment['status'] ?? '') === 'confirmed'));
$completedCount = count(array_filter($appointments, fn($appointment) => ($appointment['status'] ?? '') === 'completed'));
$cancelledCount = count(array_filter($appointments, fn($appointment) => ($appointment['status'] ?? '') === 'cancelled'));
?>
<div class="space-y-6">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 md:p-6">
        <div class="md:flex md:items-center md:justify-between gap-6">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Manage Appointments</h2>
                <p class="mt-2 text-sm text-slate-600 max-w-2xl">View resident requests, update status, and keep appointment records organized with separate desktop and mobile dashboards.</p>
            </div>
            <div class="hidden md:grid grid-cols-4 gap-4 w-full">
                <div class="rounded-3xl bg-slate-50 border border-slate-200 p-4">
                    <div class="text-sm text-slate-500">Total</div>
                    <div class="mt-3 text-2xl font-semibold text-slate-900"><?php echo $totalAppointments; ?></div>
                </div>
                <div class="rounded-3xl bg-sky-50 border border-sky-200 p-4">
                    <div class="text-sm text-slate-500">Pending</div>
                    <div class="mt-3 text-2xl font-semibold text-slate-900"><?php echo $pendingCount; ?></div>
                </div>
                <div class="rounded-3xl bg-emerald-50 border border-emerald-200 p-4">
                    <div class="text-sm text-slate-500">Confirmed</div>
                    <div class="mt-3 text-2xl font-semibold text-slate-900"><?php echo $confirmedCount; ?></div>
                </div>
                <div class="rounded-3xl bg-violet-50 border border-violet-200 p-4">
                    <div class="text-sm text-slate-500">Completed</div>
                    <div class="mt-3 text-2xl font-semibold text-slate-900"><?php echo $completedCount; ?></div>
                </div>
            </div>
        </div>
        <div class="mt-5 md:hidden grid grid-cols-2 gap-3">
            <div class="rounded-3xl bg-white border border-slate-200 p-4">
                <div class="text-xs uppercase tracking-wide text-slate-500">Total</div>
                <div class="mt-2 text-xl font-semibold text-slate-900"><?php echo $totalAppointments; ?></div>
            </div>
            <div class="rounded-3xl bg-sky-50 border border-sky-200 p-4">
                <div class="text-xs uppercase tracking-wide text-slate-500">Pending</div>
                <div class="mt-2 text-xl font-semibold text-slate-900"><?php echo $pendingCount; ?></div>
            </div>
            <div class="rounded-3xl bg-emerald-50 border border-emerald-200 p-4">
                <div class="text-xs uppercase tracking-wide text-slate-500">Confirmed</div>
                <div class="mt-2 text-xl font-semibold text-slate-900"><?php echo $confirmedCount; ?></div>
            </div>
            <div class="rounded-3xl bg-violet-50 border border-violet-200 p-4">
                <div class="text-xs uppercase tracking-wide text-slate-500">Completed</div>
                <div class="mt-2 text-xl font-semibold text-slate-900"><?php echo $completedCount; ?></div>
            </div>
        </div>
    </div>

    <div class="hidden md:grid md:grid-cols-[1.9fr_0.9fr] gap-6">
        <section class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5">
                <h3 class="text-lg font-semibold text-slate-900">Appointment queue</h3>
                <p class="mt-1 text-sm text-slate-500">Use the table below for desktop updates and quick status changes.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-6 py-4 text-left">Resident</th>
                            <th class="px-6 py-4 text-left">Type</th>
                            <th class="px-6 py-4 text-left">Schedule</th>
                            <th class="px-6 py-4 text-left">Purpose</th>
                            <th class="px-6 py-4 text-left">Status</th>
                            <th class="px-6 py-4 text-left">Update</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <?php foreach ($appointments as $appointment): ?>
                        <tr class="hover:bg-slate-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900"><?php echo e($appointment['first_name'] . ' ' . $appointment['last_name']); ?></div>
                                <div class="text-sm text-slate-500"><?php echo e($appointment['email']); ?></div>
                            </td>
                            <td class="px-6 py-4 text-slate-700"><?php echo e(labelize($appointment['appointment_type'] ?? 'Appointment')); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-700">
                                <?php echo !empty($appointment['preferred_date']) ? date('M d, Y', strtotime($appointment['preferred_date'])) : 'N/A'; ?>
                                <?php echo !empty($appointment['preferred_time']) ? ' at ' . date('g:i A', strtotime($appointment['preferred_time'])) : ''; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 max-w-xs"><?php echo e($appointment['purpose'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium <?php echo statusBadge($appointment['status'] ?? 'pending'); ?>"><?php echo e(labelize($appointment['status'] ?? 'pending')); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <form method="POST" class="space-y-3">
                                    <input type="hidden" name="appointment_id" value="<?php echo (int)$appointment['appointment_id']; ?>">
                                    <div>
                                        <select name="status" class="min-w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-200">
                                            <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $status): ?>
                                            <option value="<?php echo $status; ?>" <?php echo ($appointment['status'] ?? '') == $status ? 'selected' : ''; ?>><?php echo labelize($status); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <input type="text" name="admin_notes" placeholder="Admin notes" class="min-w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-200" value="<?php echo e($appointment['admin_notes'] ?? ''); ?>">
                                    </div>
                                    <button class="w-full bg-blue-600 text-white rounded-lg px-3 py-2 text-sm font-medium hover:bg-blue-700">Save</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($appointments)): ?>
                        <tr><td colspan="6" class="text-center py-10 text-slate-500">No appointments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <aside class="space-y-6">
            <div class="sticky top-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Status summary</h3>
                <div class="mt-5 space-y-4 text-sm text-slate-600">
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 p-4">
                        <span>Total appointments</span>
                        <span class="font-semibold text-slate-900"><?php echo $totalAppointments; ?></span>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-sky-50 p-4">
                        <span>Pending</span>
                        <span class="font-semibold text-slate-900"><?php echo $pendingCount; ?></span>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-emerald-50 p-4">
                        <span>Confirmed</span>
                        <span class="font-semibold text-slate-900"><?php echo $confirmedCount; ?></span>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-violet-50 p-4">
                        <span>Completed</span>
                        <span class="font-semibold text-slate-900"><?php echo $completedCount; ?></span>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-rose-50 p-4">
                        <span>Cancelled</span>
                        <span class="font-semibold text-slate-900"><?php echo $cancelledCount; ?></span>
                    </div>
                </div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                <h4 class="text-sm font-semibold text-slate-900">Desktop quick tips</h4>
                <p class="mt-3 text-sm text-slate-600">Use the table to scan appointments quickly, update status inline, and preserve notes for each request.</p>
            </div>
        </aside>
    </div>

    <div class="md:hidden space-y-4">
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-4">
                <div class="text-xs uppercase tracking-wide text-slate-500">Total</div>
                <div class="mt-2 text-2xl font-semibold text-slate-900"><?php echo $totalAppointments; ?></div>
            </div>
            <div class="rounded-3xl border border-sky-200 bg-sky-50 p-4">
                <div class="text-xs uppercase tracking-wide text-slate-500">Pending</div>
                <div class="mt-2 text-2xl font-semibold text-slate-900"><?php echo $pendingCount; ?></div>
            </div>
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4">
                <div class="text-xs uppercase tracking-wide text-slate-500">Confirmed</div>
                <div class="mt-2 text-2xl font-semibold text-slate-900"><?php echo $confirmedCount; ?></div>
            </div>
            <div class="rounded-3xl border border-violet-200 bg-violet-50 p-4">
                <div class="text-xs uppercase tracking-wide text-slate-500">Completed</div>
                <div class="mt-2 text-2xl font-semibold text-slate-900"><?php echo $completedCount; ?></div>
            </div>
        </div>

        <?php if (empty($appointments)): ?>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 text-center text-slate-500">No appointments found.</div>
        <?php endif; ?>

        <?php foreach ($appointments as $appointment): ?>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-lg font-semibold text-slate-900"><?php echo e($appointment['first_name'] . ' ' . $appointment['last_name']); ?></div>
                    <div class="mt-1 text-sm text-slate-500"><?php echo e($appointment['email']); ?></div>
                    <div class="mt-3 text-sm text-slate-700"><span class="font-medium">Type:</span> <?php echo e(labelize($appointment['appointment_type'] ?? 'Appointment')); ?></div>
                    <div class="mt-2 text-sm text-slate-700"><span class="font-medium">Schedule:</span>
                        <?php echo !empty($appointment['preferred_date']) ? date('M d, Y', strtotime($appointment['preferred_date'])) : 'N/A'; ?>
                        <?php echo !empty($appointment['preferred_time']) ? ' at ' . date('g:i A', strtotime($appointment['preferred_time'])) : ''; ?>
                    </div>
                </div>
                <div class="shrink-0">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo statusBadge($appointment['status'] ?? 'pending'); ?>"><?php echo e(labelize($appointment['status'] ?? 'pending')); ?></span>
                </div>
            </div>
            <div class="mt-4 text-sm text-slate-700"><span class="font-medium">Purpose:</span> <?php echo e($appointment['purpose'] ?? 'N/A'); ?></div>
            <form method="POST" class="mt-5 space-y-3">
                <input type="hidden" name="appointment_id" value="<?php echo (int)$appointment['appointment_id']; ?>">
                <div>
                    <select name="status" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-200">
                        <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $status): ?>
                        <option value="<?php echo $status; ?>" <?php echo ($appointment['status'] ?? '') == $status ? 'selected' : ''; ?>><?php echo labelize($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <input type="text" name="admin_notes" placeholder="Admin notes" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-200" value="<?php echo e($appointment['admin_notes'] ?? ''); ?>">
                </div>
                <button class="w-full bg-blue-600 text-white rounded-xl px-4 py-3 text-sm font-semibold hover:bg-blue-700">Save</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php adminFooter(); ?>
