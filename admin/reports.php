<?php
require_once '_admin_common.php';

$stats = [];
$queries = [
    'Residents' => "SELECT COUNT(DISTINCT u.user_id) FROM users u JOIN user_role_assignments ura ON ura.user_id = u.user_id AND ura.is_active = 1 JOIN roles r ON r.role_id = ura.role_id WHERE r.role_name = 'resident' AND u.is_active = 1",
    'Document Requests' => "SELECT COUNT(*) FROM document_requests",
    'Pending Requests' => "SELECT COUNT(*) FROM document_requests WHERE status = 'pending'",
    'Complaints' => "SELECT COUNT(*) FROM complaints",
    'Open Complaints' => "SELECT COUNT(*) FROM complaints WHERE status IN ('submitted','in_progress')",
    'Appointments' => "SELECT COUNT(*) FROM appointments",
    'Pending Appointments' => "SELECT COUNT(*) FROM appointments WHERE status = 'pending'",
    'Active Announcements' => "SELECT COUNT(*) FROM announcements WHERE is_active = 1",
];
foreach ($queries as $label => $sql) {
    try {
        $stats[$label] = $pdo->query($sql)->fetchColumn();
    } catch (Exception $e) {
        $stats[$label] = 0;
    }
}

$monthlyRequests = $pdo->query("
    SELECT DATE_FORMAT(requested_at, '%Y-%m') AS month, COUNT(*) AS total
    FROM document_requests
    GROUP BY DATE_FORMAT(requested_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
")->fetchAll();
$monthlyPayments = $pdo->query("
    SELECT DATE_FORMAT(transaction_date, '%Y-%m') AS month, COALESCE(SUM(amount), 0) AS total
    FROM transactions
    WHERE status = 'completed'
    GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
")->fetchAll();

adminHeader('Reports', 'reports');
?>

<div class="space-y-6">
    <section class="md:hidden rounded-[28px] bg-slate-950 text-white p-5 shadow-lg">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Reports Overview</p>
                <h1 class="mt-3 text-2xl font-semibold">Your dashboard summary</h1>
                <p class="mt-2 text-sm text-slate-300">Swipe through the latest report counts and monthly trends designed for mobile review.</p>
            </div>
            <div class="rounded-3xl bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-200">Mobile view</div>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-3">
            <div class="rounded-3xl bg-slate-900 border border-slate-700 p-4">
                <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Residents</p>
                <p class="mt-3 text-2xl font-semibold text-white"><?php echo e($stats['Residents']); ?></p>
            </div>
            <div class="rounded-3xl bg-slate-900 border border-slate-700 p-4">
                <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Requests</p>
                <p class="mt-3 text-2xl font-semibold text-white"><?php echo e($stats['Document Requests']); ?></p>
            </div>
            <div class="rounded-3xl bg-slate-900 border border-slate-700 p-4">
                <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Payments</p>
                <p class="mt-3 text-2xl font-semibold text-white"><?php echo count($monthlyPayments); ?></p>
            </div>
            <div class="rounded-3xl bg-slate-900 border border-slate-700 p-4">
                <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Complaints</p>
                <p class="mt-3 text-2xl font-semibold text-white"><?php echo e($stats['Complaints']); ?></p>
            </div>
        </div>
    </section>

    <section class="hidden md:grid md:grid-cols-[1.7fr_0.9fr] gap-6">
        <div class="rounded-[32px] bg-white border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Reports summary</p>
                    <h2 class="mt-2 text-3xl font-semibold text-slate-900">Performance Metrics</h2>
                </div>
                <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition"><i class="fas fa-print"></i> Print</button>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="rounded-[28px] bg-slate-50 border border-slate-200 p-5">
                    <p class="text-sm text-slate-500">Active Complaints</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-900"><?php echo e($stats['Open Complaints']); ?></p>
                </div>
                <div class="rounded-[28px] bg-slate-50 border border-slate-200 p-5">
                    <p class="text-sm text-slate-500">Pending Appointments</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-900"><?php echo e($stats['Pending Appointments']); ?></p>
                </div>
            </div>
            <div class="mt-6 grid grid-cols-3 gap-4">
                <?php foreach (['Residents','Document Requests','Pending Requests','Active Announcements'] as $label): ?>
                <div class="rounded-[28px] bg-white border border-slate-200 p-4 text-center">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-500"><?php echo e($label); ?></p>
                    <p class="mt-3 text-2xl font-semibold text-slate-900"><?php echo e($stats[$label]); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <aside class="rounded-[32px] bg-white border border-slate-200 p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Desktop insights</h3>
            <p class="mt-4 text-sm leading-6 text-slate-600">The desktop view highlights monthly trends and key report counts side-by-side for faster review. Use the tables below to compare request and payment volume with a clear visual hierarchy.</p>
        </aside>
    </section>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <?php foreach ($stats as $label => $value): ?>
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
            <p class="text-sm uppercase tracking-[0.24em] text-slate-500"><?php echo e($label); ?></p>
            <p class="mt-4 text-3xl font-semibold text-slate-900"><?php echo e($value); ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-[32px] shadow overflow-hidden">
            <div class="p-5 border-b flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Document Requests by Month</h3>
                    <p class="text-sm text-slate-500">Latest 12 months of request volume.</p>
                </div>
                <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition"><i class="fas fa-print"></i> Print</button>
            </div>
            <div class="overflow-x-auto hidden md:block">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-[0.24em]"><tr><th class="px-4 py-3">Month</th><th class="px-4 py-3">Requests</th></tr></thead>
                    <tbody class="divide-y">
                        <?php foreach ($monthlyRequests as $row): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3"><?php echo e($row['month']); ?></td>
                            <td class="px-4 py-3 font-semibold"><?php echo e($row['total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($monthlyRequests)): ?>
                        <tr><td colspan="2" class="px-4 py-6 text-center text-slate-500">No request data.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="md:hidden p-4 space-y-3">
                <?php if (empty($monthlyRequests)): ?>
                    <div class="text-center py-6 text-slate-500">No request data.</div>
                <?php endif; ?>
                <?php foreach ($monthlyRequests as $row): ?>
                <div class="rounded-3xl bg-slate-50 p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900"><?php echo e($row['month']); ?></p>
                            <p class="text-xs text-slate-500">Requests</p>
                        </div>
                        <p class="text-xl font-semibold text-slate-900"><?php echo e($row['total']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white rounded-[32px] shadow overflow-hidden">
            <div class="p-5 border-b">
                <h3 class="text-lg font-semibold text-slate-900">Completed Payments by Month</h3>
                <p class="text-sm text-slate-500 mt-1">Latest 12 months of collected payments.</p>
            </div>
            <div class="overflow-x-auto hidden md:block">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-[0.24em]"><tr><th class="px-4 py-3">Month</th><th class="px-4 py-3">Collected</th></tr></thead>
                    <tbody class="divide-y">
                        <?php foreach ($monthlyPayments as $row): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3"><?php echo e($row['month']); ?></td>
                            <td class="px-4 py-3 font-semibold"><?php echo peso($row['total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($monthlyPayments)): ?>
                        <tr><td colspan="2" class="px-4 py-6 text-center text-slate-500">No payment data.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="md:hidden p-4 space-y-3">
                <?php if (empty($monthlyPayments)): ?>
                    <div class="text-center py-6 text-slate-500">No payment data.</div>
                <?php endif; ?>
                <?php foreach ($monthlyPayments as $row): ?>
                <div class="rounded-3xl bg-slate-50 p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900"><?php echo e($row['month']); ?></p>
                            <p class="text-xs text-slate-500">Collected</p>
                        </div>
                        <p class="text-xl font-semibold text-slate-900"><?php echo peso($row['total']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php adminFooter(); ?>
