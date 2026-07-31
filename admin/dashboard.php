<?php
require_once '_admin_common.php';

$isSuperAdmin = hasRole('super_admin');
$isSecretary = hasRole('barangay_secretary');
$isTreasurer = hasRole('barangay_treasurer');
$isCaptain = hasRole('barangay_captain');

if ($isSuperAdmin) {
    $dashboardTitle = 'Super Admin Dashboard';
} elseif ($isSecretary) {
    $dashboardTitle = 'Secretary Dashboard';
} elseif ($isTreasurer) {
    $dashboardTitle = 'Treasurer Dashboard';
} else {
    $dashboardTitle = 'Admin Dashboard';
}

function dashboardEscape($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

ensureDocumentRequestPaymentColumns();

// Super Admin dashboard statistics
$totalActiveUsers = (int)dbFetchColumn("SELECT COUNT(*) FROM users WHERE is_active = 1 AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')");

$roleCounts = [
    'super_admin' => 0,
    'barangay_captain' => 0,
    'barangay_secretary' => 0,
    'barangay_treasurer' => 0,
    'resident' => 0,
];
foreach (dbFetchAll("SELECT r.role_name, COUNT(DISTINCT u.user_id) AS total
    FROM roles r
    LEFT JOIN user_role_assignments ura ON ura.role_id = r.role_id AND ura.is_active = 1
    LEFT JOIN users u ON u.user_id = ura.user_id AND u.is_active = 1
    WHERE r.role_name IN ('super_admin', 'barangay_captain', 'barangay_secretary', 'barangay_treasurer', 'resident')
    GROUP BY r.role_name") as $row) {
    $roleCounts[$row['role_name']] = (int)$row['total'];
}

$attentionAccounts = (int)dbFetchColumn("SELECT COUNT(*) FROM users WHERE is_locked = 1 OR is_active = 0 OR deleted_at IS NOT NULL");

    $recentAccounts = dbFetchAll("\n    SELECT u.user_id, u.first_name, u.last_name, u.email, u.username, u.is_active, u.created_at, r.role_name\n    FROM users u\n    LEFT JOIN user_role_assignments ura ON ura.user_id = u.user_id AND ura.is_active = 1\n    LEFT JOIN roles r ON r.role_id = ura.role_id\n    WHERE u.deleted_at IS NULL OR u.deleted_at = '0000-00-00 00:00:00'\n    ORDER BY u.created_at DESC\n    LIMIT 6\n");

    $recentActivity = dbFetchAll("\n    SELECT al.*, u.first_name, u.last_name\n    FROM activity_logs al\n    LEFT JOIN users u ON u.user_id = al.user_id\n    ORDER BY al.created_at DESC\n    LIMIT 6\n");

// Get dashboard statistics
$activeUnits = dbFetchColumn("SELECT COUNT(DISTINCT u.user_id) as active_units \n    FROM users u \n    JOIN resident_profiles rp ON u.user_id = rp.user_id \n    WHERE u.is_active = 1");

// This month charges
$thisMonthCharges = dbFetchColumn("SELECT COALESCE(SUM(amount), 0) as total \n    FROM subscriptions \n    WHERE MONTH(due_date) = MONTH(CURRENT_DATE) AND YEAR(due_date) = YEAR(CURRENT_DATE)");

// This month paid
$thisMonthPaid = dbFetchColumn("SELECT COALESCE(SUM(amount), 0) as total \n    FROM transactions \n    WHERE MONTH(transaction_date) = MONTH(CURRENT_DATE) \n    AND YEAR(transaction_date) = YEAR(CURRENT_DATE) \n    AND status = 'completed'");

// This month outstanding
$thisMonthOutstanding = $thisMonthCharges - $thisMonthPaid;

// Total outstanding
$totalOutstanding = dbFetchColumn("SELECT COALESCE(SUM(amount), 0) as total \n    FROM subscriptions WHERE status IN ('pending', 'overdue')");

// Delinquent units
$delinquentUnits = dbFetchColumn("SELECT COUNT(DISTINCT user_id) as delinquent \n    FROM subscriptions WHERE due_date < CURDATE() AND status = 'pending'");

// Total funds
$funds = dbFetchOne("SELECT \n    COALESCE(SUM(CASE WHEN payment_method = 'cash' AND status = 'completed' THEN amount ELSE 0 END), 0) as cash_total,\n    COALESCE(SUM(CASE WHEN payment_method = 'gcash' AND status = 'completed' THEN amount ELSE 0 END), 0) as gcash_total\n    FROM transactions");
$funds += ['cash_total' => 0, 'gcash_total' => 0];
$totalFunds = $funds['cash_total'] + $funds['gcash_total'];

// Pending requests
$pendingRequests = dbFetchColumn("SELECT COUNT(*) as pending FROM document_requests WHERE status = 'pending'");

$cashPaymentsAwaiting = (int)dbFetchColumn("SELECT COUNT(*) FROM document_requests WHERE status = 'approved' AND payment_status = 'unpaid' AND payment_method = 'cash'");

$gcashProofsAwaiting = (int)dbFetchColumn("SELECT COUNT(*) FROM document_requests WHERE status = 'approved' AND payment_status = 'unpaid' AND payment_method = 'gcash' AND payment_proof_status = 'submitted'");

// Pending complaints
$pendingComplaints = dbFetchColumn("SELECT COUNT(*) as pending FROM complaints WHERE status = 'submitted'");

// Recent transactions
$recentTransactions = dbFetchAll("SELECT t.*, u.first_name, u.last_name \n    FROM transactions t \n    JOIN users u ON t.user_id = u.user_id \n    ORDER BY t.transaction_date DESC LIMIT 10");

// Recent document requests
$recentRequests = dbFetchAll("SELECT dr.*, u.first_name, u.last_name \n    FROM document_requests dr \n    JOIN users u ON dr.user_id = u.user_id \n    ORDER BY dr.requested_at DESC LIMIT 5");

// Document request approvals waiting for payment
$awaitingDocumentPayments = (int)dbFetchColumn("SELECT COUNT(*) FROM document_requests WHERE status = 'approved' AND payment_status = 'unpaid'");

// Ready for pickup requests
$readyForPickupRequests = (int)dbFetchColumn("SELECT COUNT(*) FROM document_requests WHERE status = 'ready_for_pickup'");

// Recent document payment records
$recentDocumentPayments = dbFetchAll("\n    SELECT t.*, u.first_name, u.last_name, dr.reference_number, COALESCE(t.document_type, dr.document_type) AS document_type\n    FROM transactions t\n    JOIN users u ON t.user_id = u.user_id\n    LEFT JOIN document_requests dr ON dr.request_id = t.reference_id AND t.transaction_type = 'document_fee'\n    WHERE t.transaction_type = 'document_fee'\n    ORDER BY t.transaction_date DESC\n    LIMIT 5\n");

// Total document payments collected
$documentPaymentTotal = (float)dbFetchColumn("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE transaction_type = 'document_fee' AND status = 'completed'");

// Recent complaints
$recentComplaints = dbFetchAll("SELECT c.*, u.first_name, u.last_name \n    FROM complaints c \n    JOIN users u ON c.complainant_id = u.user_id \n    ORDER BY c.created_at DESC LIMIT 5");

// Upcoming appointments
$upcomingAppointments = dbFetchAll("SELECT a.*, u.first_name, u.last_name \n    FROM appointments a \n    JOIN users u ON a.user_id = u.user_id \n    WHERE a.preferred_date >= CURDATE() AND a.status = 'pending'\n    ORDER BY a.preferred_date ASC LIMIT 5");

// Get subscription due reminder
$dueReminder = dbFetchOne("SELECT * FROM subscriptions WHERE due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 10 DAY) AND status = 'pending' LIMIT 1");

$notificationItems = [];
if ($pendingRequests > 0) {
    $notificationItems[] = [
        'title' => 'Document Requests Pending',
        'message' => $pendingRequests . ' request' . ($pendingRequests === 1 ? '' : 's') . ' waiting for review.',
        'href' => 'requests.php',
        'icon' => 'fa-file-alt',
        'tone' => 'bg-blue-50 text-blue-600',
    ];
}
if ($pendingComplaints > 0) {
    $notificationItems[] = [
        'title' => 'Complaints Pending',
        'message' => $pendingComplaints . ' complaint' . ($pendingComplaints === 1 ? '' : 's') . ' waiting for action.',
        'href' => 'complaints.php',
        'icon' => 'fa-gavel',
        'tone' => 'bg-amber-50 text-amber-600',
    ];
}
if ($dueReminder) {
    $daysRemaining = (int)ceil((strtotime($dueReminder['due_date']) - time()) / 86400);
    $notificationItems[] = [
        'title' => 'Subscription Due Soon',
        'message' => 'Invoice of ₱' . number_format($dueReminder['amount'], 2) . ' is due on ' . date('F j, Y', strtotime($dueReminder['due_date'])) . '.',
        'href' => 'finance.php',
        'icon' => 'fa-coins',
        'tone' => 'bg-emerald-50 text-emerald-600',
    ];
}
$ifTreasurerNeedsCash = $isTreasurer && $cashPaymentsAwaiting > 0;
if ($ifTreasurerNeedsCash) {
    $notificationItems[] = [
        'title' => 'Cash Payments Awaiting',
        'message' => $cashPaymentsAwaiting . ' approved request' . ($cashPaymentsAwaiting === 1 ? '' : 's') . ' are waiting for cash payment recording.',
        'href' => 'finance.php',
        'icon' => 'fa-money-bill-wave',
        'tone' => 'bg-amber-50 text-amber-600',
    ];
}
if ($isTreasurer && $gcashProofsAwaiting > 0) {
    $notificationItems[] = [
        'title' => 'GCash Proofs Ready',
        'message' => $gcashProofsAwaiting . ' uploaded proof' . ($gcashProofsAwaiting === 1 ? '' : 's') . ' need review.',
        'href' => 'finance.php',
        'icon' => 'fa-receipt',
        'tone' => 'bg-purple-50 text-purple-600',
    ];
}
$notificationCount = count($notificationItems);

adminHeader($dashboardTitle, 'dashboard');
?>

<div class="space-y-6 mb-6">
    <div class="md:hidden rounded-[32px] bg-slate-950 text-white p-5 shadow-lg">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.24em] text-slate-300"><?php echo htmlspecialchars($dashboardTitle, ENT_QUOTES, 'UTF-8'); ?></p>
                <h1 class="mt-3 text-2xl font-semibold">Welcome back</h1>
                <p class="mt-2 text-sm text-slate-300">Quickly scan your most important metrics and take action from the mobile dashboard.</p>
            </div>
            <div class="rounded-3xl bg-slate-800 px-4 py-3 text-center">
                <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Alerts</p>
                <p class="mt-2 text-3xl font-semibold"><?php echo $notificationCount; ?></p>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="rounded-[28px] bg-white p-4 text-slate-900">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Due Soon</div>
                <div class="mt-2 text-2xl font-semibold"><?php echo max(0, $pendingRequests ?? 0); ?></div>
            </div>
            <div class="rounded-[28px] bg-white p-4 text-slate-900">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Pending</div>
                <div class="mt-2 text-2xl font-semibold"><?php echo max(0, $pendingComplaints ?? 0); ?></div>
            </div>
        </div>
    </div>

    <div class="hidden md:grid md:grid-cols-[1.6fr_0.9fr] gap-6">
        <section class="rounded-[32px] bg-white border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Dashboard overview</p>
                    <h2 class="mt-3 text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($dashboardTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                </div>
                <div class="rounded-3xl bg-blue-50 px-4 py-2 text-blue-700 text-sm font-semibold"><?php echo $notificationCount; ?> Alerts</div>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="rounded-[28px] bg-slate-50 p-5">
                    <div class="text-sm text-slate-500">Pending Requests</div>
                    <div class="mt-3 text-3xl font-semibold text-slate-900"><?php echo number_format($pendingRequests ?? 0); ?></div>
                </div>
                <div class="rounded-[28px] bg-slate-50 p-5">
                    <div class="text-sm text-slate-500">Pending Complaints</div>
                    <div class="mt-3 text-3xl font-semibold text-slate-900"><?php echo number_format($pendingComplaints ?? 0); ?></div>
                </div>
            </div>
        </section>
        <aside class="rounded-[32px] bg-white border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Notifications</p>
                    <h3 class="mt-2 text-xl font-semibold text-slate-900">Action items</h3>
                </div>
                <span class="text-sm text-slate-500"><?php echo $notificationCount; ?> total</span>
            </div>
            <div class="mt-5 space-y-4">
                <?php if (!empty($notificationItems)): ?>
                    <?php foreach ($notificationItems as $item): ?>
                    <a href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>" class="block rounded-3xl border border-slate-200 bg-slate-50 p-4 hover:bg-slate-100 transition">
                        <div class="flex items-center gap-3">
                            <div class="rounded-2xl <?php echo $item['tone']; ?> p-3 text-base"><i class="fas <?php echo htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-sm text-slate-500"><?php echo htmlspecialchars($item['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-500">No alerts at this time.</div>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<?php if ($isSuperAdmin): ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-4 md:p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-500 text-sm">ACTIVE USERS</p>
                <p class="text-2xl md:text-3xl font-bold mt-2"><?php echo number_format($totalActiveUsers); ?></p>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
                <i class="fas fa-users-cog text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 md:p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-500 text-sm">CAPTAIN ACCOUNTS</p>
                <p class="text-2xl md:text-3xl font-bold mt-2"><?php echo number_format($roleCounts['barangay_captain']); ?></p>
            </div>
            <div class="bg-indigo-100 p-3 rounded-full">
                <i class="fas fa-user-shield text-indigo-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 md:p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-500 text-sm">SECRETARY ACCOUNTS</p>
                <p class="text-2xl md:text-3xl font-bold mt-2"><?php echo number_format($roleCounts['barangay_secretary']); ?></p>
            </div>
            <div class="bg-purple-100 p-3 rounded-full">
                <i class="fas fa-user-tie text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 md:p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-500 text-sm">ACCOUNT ATTENTION</p>
                <p class="text-2xl md:text-3xl font-bold mt-2 text-red-600"><?php echo number_format($attentionAccounts); ?></p>
            </div>
            <div class="bg-red-100 p-3 rounded-full">
                <i class="fas fa-user-lock text-red-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-4 md:p-6 lg:col-span-1">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Role Distribution</h3>
            <a href="reports.php" class="text-blue-600 text-sm hover:underline">Reports</a>
        </div>
        <div class="space-y-3">
            <?php
            $roleSummary = [
                'Super Admin' => $roleCounts['super_admin'],
                'Barangay Captain' => $roleCounts['barangay_captain'],
                'Barangay Secretary' => $roleCounts['barangay_secretary'],
                'Barangay Treasurer' => $roleCounts['barangay_treasurer'],
                'Residents' => $roleCounts['resident'],
            ];
            foreach ($roleSummary as $label => $count):
            ?>
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-gray-600"><?php echo dashboardEscape($label); ?></span>
                    <span class="font-semibold"><?php echo number_format($count); ?></span>
                </div>
                <div class="h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-blue-600" style="width: <?php echo max(8, min(100, $totalActiveUsers > 0 ? (int)round(($count / max(1, $totalActiveUsers)) * 100) : 8)); ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden lg:col-span-1">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Recent Accounts</h3>
            <a href="users.php?role=barangay_captain" class="text-blue-600 text-sm hover:underline">Manage</a>
        </div>
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($recentAccounts as $account): ?>
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900"><?php echo dashboardEscape(trim(($account['first_name'] ?? '') . ' ' . ($account['last_name'] ?? ''))); ?></div>
                            <div class="text-xs text-gray-500"><?php echo dashboardEscape($account['username'] ?? ''); ?></div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600"><?php echo dashboardEscape(labelize($account['role_name'] ?? '')); ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full <?php echo !empty($account['is_active']) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo !empty($account['is_active']) ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentAccounts)): ?>
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">No accounts found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Mobile list fallback -->
        <div class="md:hidden p-4 space-y-3">
            <?php foreach ($recentAccounts as $account): ?>
            <div class="p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium"><?php echo dashboardEscape(trim(($account['first_name'] ?? '') . ' ' . ($account['last_name'] ?? ''))); ?></div>
                        <div class="text-xs text-gray-500"><?php echo dashboardEscape($account['username'] ?? ''); ?></div>
                    </div>
                    <div class="text-xs">
                        <div class="font-semibold"><?php echo dashboardEscape(labelize($account['role_name'] ?? '')); ?></div>
                        <div class="mt-1"><span class="px-2 py-1 text-xs rounded-full <?php echo !empty($account['is_active']) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>"><?php echo !empty($account['is_active']) ? 'Active' : 'Inactive'; ?></span></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($recentAccounts)): ?>
            <div class="text-center text-gray-500 py-4">No accounts found</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Recent Audit Activity</h3>
            <span class="text-sm text-gray-500">Latest system logs</span>
        </div>
        <div class="p-4 space-y-3">
            <?php foreach ($recentActivity as $activity): ?>
            <div class="p-3 bg-gray-50 rounded-lg">
                <div class="flex justify-between gap-3">
                    <div>
                        <p class="font-semibold"><?php echo dashboardEscape($activity['action']); ?></p>
                        <p class="text-sm text-gray-500">
                            <?php echo dashboardEscape(trim(($activity['first_name'] ?? '') . ' ' . ($activity['last_name'] ?? '')) ?: 'System'); ?>
                            <?php if (!empty($activity['entity_type'])): ?>
                                &middot; <?php echo dashboardEscape($activity['entity_type']); ?> #<?php echo (int)$activity['entity_id']; ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <span class="text-xs text-gray-400 whitespace-nowrap"><?php echo date('M d, g:i A', strtotime($activity['created_at'])); ?></span>
                </div>
                <?php if (!empty($activity['details'])): ?>
                <p class="text-sm text-gray-600 mt-2"><?php echo dashboardEscape($activity['details']); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if (empty($recentActivity)): ?>
            <p class="text-center text-gray-500 py-6">No audit activity yet</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php elseif ($isSecretary): ?>
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-lg p-6 text-white mb-6">
    <h3 class="text-2xl font-bold mb-2">Secretary Dashboard</h3>
    <p class="text-blue-100">Handle document requests only. Approved requests move to the Treasurer for payment before pickup can be scheduled.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-4 md:p-6 hover:shadow-lg transition">
        <p class="text-gray-500 text-sm">PENDING REQUESTS</p>
        <p class="text-2xl md:text-3xl font-bold mt-2"><?php echo number_format((int)($pendingRequests ?? 0)); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
        <p class="text-gray-500 text-sm">AWAITING PAYMENT</p>
        <p class="text-2xl md:text-3xl font-bold mt-2 text-amber-600"><?php echo number_format((int)($awaitingDocumentPayments ?? 0)); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
        <p class="text-gray-500 text-sm">READY FOR PICKUP</p>
        <p class="text-2xl md:text-3xl font-bold mt-2 text-blue-600"><?php echo number_format((int)($readyForPickupRequests ?? 0)); ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Recent Document Requests</h3>
            <a href="requests.php" class="text-blue-600 text-sm hover:underline">Open Queue</a>
        </div>
        <div class="p-4 space-y-3">
            <?php foreach ($recentRequests as $request): ?>
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-semibold"><?php echo dashboardEscape(documentTypeLabel($request['document_type'] ?? 'Document')); ?></p>
                    <p class="text-sm text-gray-500"><?php echo dashboardEscape(($request['first_name'] ?? '') . ' ' . ($request['last_name'] ?? '')); ?></p>
                    <p class="text-xs text-gray-400">Payment: <?php echo dashboardEscape(labelize($request['payment_status'] ?? 'unpaid')); ?></p>
                </div>
                <span class="px-2 py-1 text-xs rounded-full <?php echo statusBadge($request['status'] ?? 'pending'); ?>"><?php echo dashboardEscape(labelize($request['status'] ?? 'pending')); ?></span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($recentRequests)): ?>
            <p class="text-center text-gray-500 py-4">No document requests</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-3">Secretary Notes</h3>
        <p class="text-sm text-gray-600 leading-6">Approve, reject, and release document requests. Once a request is paid by the Treasurer, update the status to ready for pickup.</p>
        <div class="mt-6 grid grid-cols-1 gap-3">
            <button onclick="location.href='requests.php'" class="bg-blue-600 text-white p-4 rounded-lg hover:bg-blue-700 transition flex items-center justify-center space-x-2">
                <i class="fas fa-folder-open"></i>
                <span>Open Request Queue</span>
            </button>
            <button onclick="window.print()" class="bg-slate-700 text-white p-4 rounded-lg hover:bg-slate-800 transition flex items-center justify-center space-x-2">
                <i class="fas fa-print"></i>
                <span>Print Summary</span>
            </button>
        </div>
    </div>
</div>

<?php elseif ($isTreasurer): ?>
<div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-lg p-6 text-white mb-6">
    <h3 class="text-2xl font-bold mb-2">Treasurer Dashboard</h3>
    <p class="text-emerald-100">Record payment for approved document requests only. The Secretary handles request review and pickup release.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
        <p class="text-gray-500 text-sm">AWAITING PAYMENT</p>
        <p class="text-3xl font-bold mt-2 text-amber-600"><?php echo number_format((int)($awaitingDocumentPayments ?? 0)); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
        <p class="text-gray-500 text-sm">PAYMENTS RECORDED</p>
        <p class="text-3xl font-bold mt-2 text-green-600"><?php echo number_format(count($recentDocumentPayments)); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
        <p class="text-gray-500 text-sm">TOTAL DOCUMENT PAYMENTS</p>
        <p class="text-3xl font-bold mt-2 text-blue-600">₱<?php echo number_format($documentPaymentTotal, 2); ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Approved Requests For Payment</h3>
            <a href="finance.php" class="text-blue-600 text-sm hover:underline">Open Payment Page</a>
        </div>
        <div class="p-4 space-y-3">
            <?php foreach ($recentDocumentPayments as $payment): ?>
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-semibold"><?php echo dashboardEscape(documentTypeLabel($payment['document_type'] ?? 'Document')); ?></p>
                    <p class="text-sm text-gray-500"><?php echo dashboardEscape(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? '')); ?></p>
                    <p class="text-xs text-gray-400">Reference: <?php echo dashboardEscape($payment['reference_number'] ?? 'N/A'); ?></p>
                </div>
                <span class="font-semibold text-gray-700">₱<?php echo number_format((float)($payment['amount'] ?? 0), 2); ?></span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($recentDocumentPayments)): ?>
            <p class="text-center text-gray-500 py-4">No recorded document payments yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 md:p-6">
        <h3 class="text-lg font-semibold mb-3">Treasurer Notes</h3>
        <p class="text-sm text-gray-600 leading-6">Collect payment for approved requests only. After payment is posted, the Secretary can mark the document ready for pickup.</p>
        <div class="mt-6 grid grid-cols-1 gap-3">
            <button onclick="location.href='finance.php'" class="bg-emerald-600 text-white p-4 rounded-lg hover:bg-emerald-700 transition flex items-center justify-center space-x-2">
                <i class="fas fa-coins"></i>
                <span>Open Payment Queue</span>
            </button>
            <button onclick="location.href='settings.php'" class="bg-slate-100 text-slate-800 p-4 rounded-lg hover:bg-slate-200 transition flex items-center justify-center space-x-2 border border-slate-200">
                <i class="fas fa-cog"></i>
                <span>Open Settings</span>
            </button>
            <button onclick="window.print()" class="bg-slate-700 text-white p-4 rounded-lg hover:bg-slate-800 transition flex items-center justify-center space-x-2">
                <i class="fas fa-print"></i>
                <span>Print Summary</span>
            </button>
        </div>
    </div>
</div>

<?php else: ?>
<?php if ($dueReminder): ?>
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded">
    <div class="flex justify-between items-center">
        <div>
            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
            <span class="font-semibold">Subscription Due Reminder:</span>
            <span>Invoice of ₱<?php echo number_format($dueReminder['amount'], 2); ?> is due on <?php echo date('F j, Y', strtotime($dueReminder['due_date'])); ?> — <?php echo ceil((strtotime($dueReminder['due_date']) - time()) / 86400); ?> days remaining.</span>
        </div>
        <button class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Pay Now</button>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-500 text-sm">ACTIVE UNITS</p>
                <p class="text-3xl font-bold mt-2"><?php echo $activeUnits; ?></p>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
                <i class="fas fa-building text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-500 text-sm">THIS MONTH CHARGES</p>
                <p class="text-3xl font-bold mt-2">₱<?php echo number_format($thisMonthCharges, 2); ?></p>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-chart-line text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-500 text-sm">THIS MONTH PAID</p>
                <p class="text-3xl font-bold mt-2">₱<?php echo number_format($thisMonthPaid, 2); ?></p>
            </div>
            <div class="bg-purple-100 p-3 rounded-full">
                <i class="fas fa-check-circle text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-500 text-sm">THIS MONTH OUTSTANDING</p>
                <p class="text-3xl font-bold mt-2 text-red-600">₱<?php echo number_format($thisMonthOutstanding, 2); ?></p>
            </div>
            <div class="bg-red-100 p-3 rounded-full">
                <i class="fas fa-clock text-red-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>
    
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">TOTAL OUTSTANDING</h3>
            <a href="finance.php" class="text-blue-600 text-sm hover:underline">View →</a>
        </div>
        <p class="text-4xl font-bold text-red-600">₱<?php echo number_format($totalOutstanding, 2); ?></p>
        <p class="text-gray-500 mt-2">DELINQUENT UNITS: <?php echo $delinquentUnits; ?></p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">TOTAL FUNDS</h3>
            <a href="finance.php" class="text-blue-600 text-sm hover:underline">View →</a>
        </div>
        <p class="text-4xl font-bold text-green-600">₱<?php echo number_format($totalFunds, 2); ?></p>
        <div class="mt-4 grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-gray-500 text-sm">Cash</p>
                <p class="font-semibold text-lg">₱<?php echo number_format($funds['cash_total'], 2); ?></p>
            </div>
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-gray-500 text-sm">Gcash</p>
                <p class="font-semibold text-lg">₱<?php echo number_format($funds['gcash_total'], 2); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Recent Transactions</h3>
            <a href="finance.php" class="text-blue-600 text-sm">View All</a>
        </div>
        <div class="p-4">
            <div class="overflow-x-auto hidden md:block">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-600 text-sm border-b">
                            <th class="pb-2">Date</th>
                            <th class="pb-2">Resident</th>
                            <th class="pb-2">Amount</th>
                            <th class="pb-2">Method</th>
                            <th class="pb-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTransactions as $transaction): ?>
                        <tr class="border-t">
                            <td class="py-2 text-sm"><?php echo date('M d', strtotime($transaction['transaction_date'])); ?></td>
                            <td class="py-2"><?php echo $transaction['first_name'] . ' ' . $transaction['last_name']; ?></td>
                            <td class="py-2 font-semibold">₱<?php echo number_format($transaction['amount'], 2); ?></td>
                            <td class="py-2"><?php echo ucfirst($transaction['payment_method']); ?></td>
                            <td class="py-2">
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $transaction['status'] == 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo ucfirst($transaction['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentTransactions)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-500">No transactions found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Mobile stacked list -->
            <div class="md:hidden space-y-3">
                <?php foreach ($recentTransactions as $transaction): ?>
                <div class="p-3 bg-gray-50 rounded-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-sm font-semibold"><?php echo date('M d', strtotime($transaction['transaction_date'])); ?> — <?php echo $transaction['first_name'] . ' ' . $transaction['last_name']; ?></div>
                            <div class="text-xs text-gray-500"><?php echo ucfirst($transaction['payment_method']); ?></div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold">₱<?php echo number_format($transaction['amount'], 2); ?></div>
                            <div class="text-xs mt-1"><span class="px-2 py-1 text-xs rounded-full <?php echo $transaction['status'] == 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>"><?php echo ucfirst($transaction['status']); ?></span></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($recentTransactions)): ?>
                <div class="text-center text-gray-500 py-4">No transactions found</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Document Requests -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Recent Document Requests</h3>
            <a href="requests.php" class="text-blue-600 text-sm">View All</a>
        </div>
        <div class="p-4">
            <div class="space-y-3">
                <?php foreach ($recentRequests as $request): ?>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold"><?php echo dashboardEscape(documentTypeLabel($request['document_type'] ?? 'Document')); ?></p>
                        <p class="text-sm text-gray-500"><?php echo $request['first_name'] . ' ' . $request['last_name']; ?></p>
                    </div>
                    <div>
                        <span class="px-2 py-1 text-xs rounded-full 
                            <?php echo $request['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                ($request['status'] == 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                            <?php echo ucfirst($request['status']); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($recentRequests)): ?>
                <p class="text-center text-gray-500 py-4">No document requests</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Complaints -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Recent Complaints</h3>
            <a href="complaints.php" class="text-blue-600 text-sm">View All</a>
        </div>
        <div class="p-4">
            <div class="space-y-3">
                <?php foreach ($recentComplaints as $complaint): ?>
                <div class="p-3 bg-gray-50 rounded-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold"><?php echo ucfirst($complaint['complaint_type']); ?></p>
                            <p class="text-sm text-gray-500">By: <?php echo $complaint['first_name'] . ' ' . $complaint['last_name']; ?></p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full 
                            <?php echo $complaint['status'] == 'submitted' ? 'bg-yellow-100 text-yellow-800' : 
                                ($complaint['status'] == 'resolved' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'); ?>">
                            <?php echo ucfirst($complaint['status']); ?>
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mt-2 line-clamp-2"><?php echo substr($complaint['description'], 0, 100); ?>...</p>
                </div>
                <?php endforeach; ?>
                <?php if (empty($recentComplaints)): ?>
                <p class="text-center text-gray-500 py-4">No complaints filed</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Appointments -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Upcoming Appointments</h3>
            <a href="appointments.php" class="text-blue-600 text-sm">View All</a>
        </div>
        <div class="p-4">
            <div class="space-y-3">
                <?php foreach ($upcomingAppointments as $appointment): ?>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold"><?php echo str_replace('_', ' ', ucfirst($appointment['appointment_type'])); ?></p>
                        <p class="text-sm text-gray-500"><?php echo $appointment['first_name'] . ' ' . $appointment['last_name']; ?></p>
                        <p class="text-xs text-gray-400"><?php echo date('M d, Y', strtotime($appointment['preferred_date'])); ?> at <?php echo date('g:i A', strtotime($appointment['preferred_time'])); ?></p>
                    </div>
                    <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Confirm</button>
                </div>
                <?php endforeach; ?>
                <?php if (empty($upcomingAppointments)): ?>
                <p class="text-center text-gray-500 py-4">No upcoming appointments</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
    <?php if (hasRole('super_admin')): ?>
    <button onclick="location.href='users.php?role=barangay_captain'" class="bg-indigo-600 text-white p-4 rounded-lg hover:bg-indigo-700 transition flex items-center justify-center space-x-2">
        <i class="fas fa-user-shield"></i>
        <span>Create Captain</span>
    </button>
    <?php endif; ?>
    <?php if (hasRole('barangay_captain')): ?>
    <button type="button" onclick="openCreateAccountModal()" class="bg-emerald-600 text-white p-4 rounded-lg hover:bg-emerald-700 transition flex items-center justify-center space-x-2">
        <i class="fas fa-user-plus"></i>
        <span>Create Account</span>
    </button>
    <?php endif; ?>
    <button onclick="location.href='residents.php?action=add'" class="bg-blue-600 text-white p-4 rounded-lg hover:bg-blue-700 transition flex items-center justify-center space-x-2">
        <i class="fas fa-user-plus"></i>
        <span>Add Resident</span>
    </button>
    <button onclick="location.href='announcements.php?action=new'" class="bg-green-600 text-white p-4 rounded-lg hover:bg-green-700 transition flex items-center justify-center space-x-2">
        <i class="fas fa-bullhorn"></i>
        <span>New Announcement</span>
    </button>
    <button onclick="location.href='finance.php?action=record'" class="bg-purple-600 text-white p-4 rounded-lg hover:bg-purple-700 transition flex items-center justify-center space-x-2">
        <i class="fas fa-receipt"></i>
        <span>Record Payment</span>
    </button>
    <button onclick="location.href='reports.php'" class="bg-orange-600 text-white p-4 rounded-lg hover:bg-orange-700 transition flex items-center justify-center space-x-2">
        <i class="fas fa-chart-bar"></i>
        <span>Generate Report</span>
    </button>
</div>

<?php endif; ?>

<?php adminFooter(); ?>
