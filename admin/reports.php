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
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <?php foreach ($stats as $label => $value): ?>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500"><?php echo e($label); ?></p>
        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo e($value); ?></p>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="font-semibold">Document Requests by Month</h3>
            <button onclick="window.print()" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700"><i class="fas fa-print mr-1"></i>Print</button>
        </div>

        <div class="overflow-x-auto hidden md:block">
            <table class="w-full">
                <thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left">Month</th><th class="px-4 py-2 text-left">Requests</th></tr></thead>
                <tbody class="divide-y">
                    <?php foreach ($monthlyRequests as $row): ?>
                    <tr>
                        <td class="px-4 py-2"><?php echo e($row['month']); ?></td>
                        <td class="px-4 py-2"><?php echo e($row['total']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($monthlyRequests)): ?>
                    <tr><td colspan="2" class="text-center py-6 text-gray-500">No request data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile stacked cards for requests -->
        <div class="md:hidden p-4 space-y-3">
            <?php if (empty($monthlyRequests)): ?>
                <div class="text-center py-6 text-gray-500">No request data.</div>
            <?php endif; ?>
            <?php foreach ($monthlyRequests as $row): ?>
            <div class="bg-white p-3 rounded-lg shadow flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600"><?php echo e($row['month']); ?></div>
                    <div class="text-xs text-gray-400">Requests</div>
                </div>
                <div class="text-lg font-semibold text-gray-900"><?php echo e($row['total']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b font-semibold">Completed Payments by Month</div>

        <div class="overflow-x-auto hidden md:block">
            <table class="w-full">
                <thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left">Month</th><th class="px-4 py-2 text-left">Collected</th></tr></thead>
                <tbody class="divide-y">
                    <?php foreach ($monthlyPayments as $row): ?>
                    <tr>
                        <td class="px-4 py-2"><?php echo e($row['month']); ?></td>
                        <td class="px-4 py-2"><?php echo peso($row['total']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($monthlyPayments)): ?>
                    <tr><td colspan="2" class="text-center py-6 text-gray-500">No payment data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile stacked cards for payments -->
        <div class="md:hidden p-4 space-y-3">
            <?php if (empty($monthlyPayments)): ?>
                <div class="text-center py-6 text-gray-500">No payment data.</div>
            <?php endif; ?>
            <?php foreach ($monthlyPayments as $row): ?>
            <div class="bg-white p-3 rounded-lg shadow flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600"><?php echo e($row['month']); ?></div>
                    <div class="text-xs text-gray-400">Collected</div>
                </div>
                <div class="text-lg font-semibold text-gray-900"><?php echo peso($row['total']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php adminFooter(); ?>
