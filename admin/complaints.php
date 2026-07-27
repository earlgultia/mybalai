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

<?php
$totalComplaints = count($complaints);
$submittedCount = count(array_filter($complaints, fn($complaint) => ($complaint['status'] ?? '') === 'submitted'));
$progressCount = count(array_filter($complaints, fn($complaint) => ($complaint['status'] ?? '') === 'in_progress'));
$resolvedCount = count(array_filter($complaints, fn($complaint) => ($complaint['status'] ?? '') === 'resolved'));
$dismissedCount = count(array_filter($complaints, fn($complaint) => ($complaint['status'] ?? '') === 'dismissed'));
?>
<div class="space-y-6">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 md:p-6">
        <div class="md:flex md:items-center md:justify-between gap-6">
            <div class="space-y-4">
                <div class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1 text-sm font-semibold text-sky-700">Resident blotter</div>
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Manage Complaints</h2>
                    <p class="mt-2 text-sm text-slate-600 max-w-2xl">Track resident blotter reports with a dedicated desktop board and mobile-first case cards.</p>
                </div>
            </div>
            <div class="hidden md:grid grid-cols-4 gap-4 w-full md:w-auto">
                <div class="rounded-3xl bg-slate-50 border border-slate-200 p-4">
                    <div class="text-sm text-slate-500">Total</div>
                    <div class="mt-3 text-2xl font-semibold text-slate-900"><?php echo $totalComplaints; ?></div>
                </div>
                <div class="rounded-3xl bg-sky-50 border border-sky-200 p-4">
                    <div class="text-sm text-slate-500">Submitted</div>
                    <div class="mt-3 text-2xl font-semibold text-slate-900"><?php echo $submittedCount; ?></div>
                </div>
                <div class="rounded-3xl bg-amber-50 border border-amber-200 p-4">
                    <div class="text-sm text-slate-500">In Progress</div>
                    <div class="mt-3 text-2xl font-semibold text-slate-900"><?php echo $progressCount; ?></div>
                </div>
                <div class="rounded-3xl bg-emerald-50 border border-emerald-200 p-4">
                    <div class="text-sm text-slate-500">Resolved</div>
                    <div class="mt-3 text-2xl font-semibold text-slate-900"><?php echo $resolvedCount; ?></div>
                </div>
            </div>
        </div>
        <div class="mt-5 md:hidden space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-3xl bg-white border border-slate-200 p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-500">Total</div>
                    <div class="mt-2 text-xl font-semibold text-slate-900"><?php echo $totalComplaints; ?></div>
                </div>
                <div class="rounded-3xl bg-sky-50 border border-sky-200 p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-500">Submitted</div>
                    <div class="mt-2 text-xl font-semibold text-slate-900"><?php echo $submittedCount; ?></div>
                </div>
                <div class="rounded-3xl bg-amber-50 border border-amber-200 p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-500">In Progress</div>
                    <div class="mt-2 text-xl font-semibold text-slate-900"><?php echo $progressCount; ?></div>
                </div>
                <div class="rounded-3xl bg-emerald-50 border border-emerald-200 p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-500">Resolved</div>
                    <div class="mt-2 text-xl font-semibold text-slate-900"><?php echo $resolvedCount; ?></div>
                </div>
            </div>
            <div class="rounded-3xl bg-slate-50 border border-slate-200 p-4">
                <div class="font-semibold text-slate-900">Need more details?</div>
                <p class="mt-2 text-sm text-slate-600">Tap any case card below to update status, add notes, or view filing details quickly.</p>
            </div>
        </div>
    </div>

    <div class="hidden md:grid md:grid-cols-[1.9fr_0.9fr] gap-6">
        <section class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5">
                <h3 class="text-lg font-semibold text-slate-900">Complaint board</h3>
                <p class="mt-1 text-sm text-slate-500">Review active cases and update statuses directly from the desktop table.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-6 py-4 text-left">Complainant</th>
                            <th class="px-6 py-4 text-left">Type</th>
                            <th class="px-6 py-4 text-left">Details</th>
                            <th class="px-6 py-4 text-left">Filed</th>
                            <th class="px-6 py-4 text-left">Status</th>
                            <th class="px-6 py-4 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <?php foreach ($complaints as $complaint): ?>
                        <tr class="hover:bg-slate-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900"><?php echo e($complaint['first_name'] . ' ' . $complaint['last_name']); ?></div>
                                <div class="text-sm text-slate-500"><?php echo e($complaint['email']); ?></div>
                            </td>
                            <td class="px-6 py-4 text-slate-700"><?php echo e(labelize($complaint['complaint_type'] ?? 'Complaint')); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-600 max-w-md">
                                <?php if (!empty($complaint['subject'])): ?><div class="font-medium text-slate-900"><?php echo e($complaint['subject']); ?></div><?php endif; ?>
                                <?php echo e($complaint['description'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700"><?php echo !empty($complaint['created_at']) ? date('M d, Y', strtotime($complaint['created_at'])) : 'N/A'; ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium <?php echo statusBadge($complaint['status'] ?? 'submitted'); ?>"><?php echo e(labelize($complaint['status'] ?? 'submitted')); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <form method="POST" class="space-y-3">
                                    <input type="hidden" name="complaint_id" value="<?php echo (int)$complaint['complaint_id']; ?>">
                                    <div>
                                        <select name="status" class="min-w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-200">
                                            <?php foreach (['submitted', 'in_progress', 'resolved', 'dismissed'] as $status): ?>
                                            <option value="<?php echo $status; ?>" <?php echo ($complaint['status'] ?? '') == $status ? 'selected' : ''; ?>><?php echo labelize($status); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <input type="text" name="resolution_notes" placeholder="Resolution notes" class="min-w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-200" value="<?php echo e($complaint['resolution_notes'] ?? ''); ?>">
                                    </div>
                                    <button class="w-full bg-blue-600 text-white rounded-lg px-3 py-2 text-sm font-medium hover:bg-blue-700">Save</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($complaints)): ?>
                        <tr><td colspan="6" class="text-center py-10 text-slate-500">No complaints found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <aside class="space-y-6">
            <div class="sticky top-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Quick overview</h3>
                <div class="mt-5 space-y-4 text-sm text-slate-600">
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 p-4">
                        <span>Total complaints</span>
                        <span class="font-semibold text-slate-900"><?php echo $totalComplaints; ?></span>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-sky-50 p-4">
                        <span>Submitted</span>
                        <span class="font-semibold text-slate-900"><?php echo $submittedCount; ?></span>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-amber-50 p-4">
                        <span>In progress</span>
                        <span class="font-semibold text-slate-900"><?php echo $progressCount; ?></span>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-emerald-50 p-4">
                        <span>Resolved</span>
                        <span class="font-semibold text-slate-900"><?php echo $resolvedCount; ?></span>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-rose-50 p-4">
                        <span>Dismissed</span>
                        <span class="font-semibold text-slate-900"><?php echo $dismissedCount; ?></span>
                    </div>
                </div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                <h4 class="text-sm font-semibold text-slate-900">Desktop tips</h4>
                <p class="mt-3 text-sm text-slate-600">Use the table view to scan complaints quickly and update details without leaving the page.</p>
            </div>
        </aside>
    </div>

    <div class="md:hidden space-y-4">
        <?php if (empty($complaints)): ?>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 text-center text-slate-500">No complaints found.</div>
        <?php endif; ?>
        <?php foreach ($complaints as $complaint): ?>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-3">
                    <div class="text-lg font-semibold text-slate-900"><?php echo e($complaint['first_name'] . ' ' . $complaint['last_name']); ?></div>
                    <div class="text-sm text-slate-500"><?php echo e($complaint['email']); ?></div>
                    <div class="text-sm text-slate-700"><span class="font-medium">Type:</span> <?php echo e(labelize($complaint['complaint_type'] ?? 'Complaint')); ?></div>
                    <?php if (!empty($complaint['subject'])): ?><div class="text-sm font-semibold text-slate-900"><?php echo e($complaint['subject']); ?></div><?php endif; ?>
                    <div class="text-sm text-slate-700"><?php echo e($complaint['description'] ?? 'N/A'); ?></div>
                    <div class="text-sm text-slate-500">Filed: <?php echo !empty($complaint['created_at']) ? date('M d, Y', strtotime($complaint['created_at'])) : 'N/A'; ?></div>
                </div>
                <div class="shrink-0 self-start">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo statusBadge($complaint['status'] ?? 'submitted'); ?>"><?php echo e(labelize($complaint['status'] ?? 'submitted')); ?></span>
                </div>
            </div>
            <form method="POST" class="mt-5 space-y-3">
                <input type="hidden" name="complaint_id" value="<?php echo (int)$complaint['complaint_id']; ?>">
                <div>
                    <select name="status" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-200">
                        <?php foreach (['submitted', 'in_progress', 'resolved', 'dismissed'] as $status): ?>
                        <option value="<?php echo $status; ?>" <?php echo ($complaint['status'] ?? '') == $status ? 'selected' : ''; ?>><?php echo labelize($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <input type="text" name="resolution_notes" placeholder="Resolution notes" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-200" value="<?php echo e($complaint['resolution_notes'] ?? ''); ?>">
                </div>
                <button class="w-full bg-blue-600 text-white rounded-xl px-4 py-3 text-sm font-semibold hover:bg-blue-700">Save</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php adminFooter(); ?>
