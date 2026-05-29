<?php
require_once '../config/database.php';

if (!isLoggedIn() || $_SESSION['user_type'] == 'resident') {
    redirect('../index.php');
}

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function labelize($value) {
    return ucwords(str_replace('_', ' ', trim((string)$value)));
}

function documentTypeLabel($type) {
    $map = [
        'barangay_clearance' => 'Barangay Clearance',
        'certificate_of_residency' => 'Certificate of Residency',
        'certificate_of_indigency' => 'Certificate of Indigency',
        'business_clearance' => 'Business Clearance',
        'business_permit' => 'Business Clearance',
        'sedula' => 'Sedula',
        'cedula' => 'Sedula',
    ];
    $type = trim((string)$type);
    if ($type === '') {
        return 'Document';
    }
    return $map[$type] ?? labelize($type);
}

function peso($amount) {
    return 'PHP ' . number_format((float)$amount, 2);
}

function tableColumns($table) {
    global $pdo;
    static $cache = [];
    if (!isset($cache[$table])) {
        $cache[$table] = [];
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
            foreach ($stmt->fetchAll() as $column) {
                $cache[$table][] = $column['Field'];
            }
        } catch (Exception $e) {
            $cache[$table] = [];
        }
    }
    return $cache[$table];
}

function insertSubset($table, $data) {
    global $pdo;
    $columns = array_values(array_intersect(array_keys($data), tableColumns($table)));
    if (empty($columns)) {
        return false;
    }
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $fieldList = '`' . implode('`, `', $columns) . '`';
    $stmt = $pdo->prepare("INSERT INTO `$table` ($fieldList) VALUES ($placeholders)");
    return $stmt->execute(array_map(fn($column) => $data[$column], $columns));
}

function updateSubset($table, $data, $idColumn, $idValue) {
    global $pdo;
    $columns = array_values(array_intersect(array_keys($data), tableColumns($table)));
    if (empty($columns)) {
        return false;
    }
    $sets = implode(', ', array_map(fn($column) => "`$column` = ?", $columns));
    $values = array_map(fn($column) => $data[$column], $columns);
    $values[] = $idValue;
    $stmt = $pdo->prepare("UPDATE `$table` SET $sets WHERE `$idColumn` = ?");
    return $stmt->execute($values);
}

function statusBadge($status) {
    $status = (string)$status;
    $map = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'submitted' => 'bg-yellow-100 text-yellow-800',
        'unpaid' => 'bg-yellow-100 text-yellow-800',
        'approved' => 'bg-green-100 text-green-800',
        'completed' => 'bg-green-100 text-green-800',
        'confirmed' => 'bg-green-100 text-green-800',
        'resolved' => 'bg-green-100 text-green-800',
        'ready_for_pickup' => 'bg-blue-100 text-blue-800',
        'claimed' => 'bg-blue-100 text-blue-800',
        'in_progress' => 'bg-blue-100 text-blue-800',
        'cancelled' => 'bg-red-100 text-red-800',
        'rejected' => 'bg-red-100 text-red-800',
        'overdue' => 'bg-red-100 text-red-800',
        'paid' => 'bg-green-100 text-green-800',
    ];
    return $map[$status] ?? 'bg-gray-100 text-gray-800';
}

function adminHeader($title, $active) {
    $isSecretary = hasRole('barangay_secretary');
    $isTreasurer = hasRole('barangay_treasurer');
    $isCaptain = hasRole('barangay_captain');

    if (hasRole('super_admin')) {
        $items = [
            ['dashboard.php', 'tachometer-alt', 'Dashboard', 'dashboard'],
            ['users.php?role=barangay_captain', 'user-shield', 'Captain Accounts', 'users'],
            ['reports.php', 'chart-bar', 'Reports', 'reports'],
            ['settings.php', 'cog', 'Settings', 'settings'],
        ];
    } elseif ($isSecretary) {
        $items = [
            ['dashboard.php', 'tachometer-alt', 'Dashboard', 'dashboard'],
            ['requests.php', 'file-alt', 'Document Requests', 'requests'],
            ['reports.php', 'chart-bar', 'Finance Report', 'reports'],
        ];
    } elseif ($isTreasurer) {
        $items = [
            ['dashboard.php', 'tachometer-alt', 'Dashboard', 'dashboard'],
            ['finance.php', 'coins', 'Document Payments', 'finance'],
            ['reports.php', 'chart-bar', 'Finance Report', 'reports'],
            ['settings.php', 'cog', 'Settings', 'settings'],
        ];
    } else {
        $items = [
            ['dashboard.php', 'tachometer-alt', 'Dashboard', 'dashboard'],
            ['residents.php', 'users', 'Residents', 'residents'],
            ['requests.php', 'file-alt', 'Document Requests', 'requests'],
            ['complaints.php', 'gavel', 'Complaints/Blotter', 'complaints'],
            ['appointments.php', 'calendar-check', 'Appointments', 'appointments'],
            ['finance.php', 'coins', 'Finance', 'finance'],
            ['announcements.php', 'bullhorn', 'Announcements', 'announcements'],
            ['reports.php', 'chart-bar', 'Reports', 'reports'],
            ['settings.php', 'cog', 'Settings', 'settings'],
        ];
        if ($isCaptain) {
            array_splice($items, 2, 0, [['create-account', 'user-plus', 'Create Account', 'users']]);
        }

        // Add QR Scanner link for relevant admin/staff roles (avoid duplicates)
        $scannerRoles = ['super_admin','barangay_captain','barangay_secretary','barangay_treasurer','barangay_kagawad','admin_staff','health_worker','tanod'];
        if (hasRole($scannerRoles)) {
            $found = false;
            foreach ($items as $it) {
                if (is_array($it) && ($it[0] ?? '') === 'scan_qr.php') { $found = true; break; }
            }
            if (!$found) {
                // prefer to insert before settings when present
                $inserted = false;
                foreach ($items as $idx => $it) {
                    if (is_array($it) && ($it[3] ?? '') === 'settings') {
                        array_splice($items, $idx, 0, [['scan_qr.php', 'qrcode', 'QR Scanner', 'qr_scanner']]);
                        $inserted = true;
                        break;
                    }
                }
                if (!$inserted) {
                    $items[] = ['scan_qr.php', 'qrcode', 'QR Scanner', 'qr_scanner'];
                }
            }
        }
    }

    // Admin-wide request notifications for document and related approvals
    $notificationCount = 0;
    $notificationLabel = '';
    $notificationHref = 'dashboard.php';

    if (isset($GLOBALS['pdo'])) {
        try {
            $pendingDocRequests = (int) $GLOBALS['pdo']->query("SELECT COUNT(*) FROM document_requests WHERE status = 'pending'")->fetchColumn();
            $pendingAppointments = (int) $GLOBALS['pdo']->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn();
            $pendingComplaints = (int) $GLOBALS['pdo']->query("SELECT COUNT(*) FROM complaints WHERE status = 'submitted'")->fetchColumn();

            $requestTypeCount = 0;
            if ($pendingDocRequests > 0) { $requestTypeCount++; }
            if ($pendingAppointments > 0) { $requestTypeCount++; }
            if ($pendingComplaints > 0) { $requestTypeCount++; }

            if ($pendingDocRequests > 0 || $pendingAppointments > 0 || $pendingComplaints > 0) {
                $notificationCount = $pendingDocRequests + $pendingAppointments + $pendingComplaints;
                if ($requestTypeCount === 1) {
                    if ($pendingDocRequests > 0) {
                        $notificationLabel = $pendingDocRequests . ' New Document Request' . ($pendingDocRequests === 1 ? '' : 's');
                        $notificationHref = 'requests.php';
                    } elseif ($pendingAppointments > 0) {
                        $notificationLabel = $pendingAppointments . ' New Appointment Request' . ($pendingAppointments === 1 ? '' : 's');
                        $notificationHref = 'appointments.php';
                    } else {
                        $notificationLabel = $pendingComplaints . ' New Complaint' . ($pendingComplaints === 1 ? '' : 's');
                        $notificationHref = 'complaints.php';
                    }
                } else {
                    $notificationLabel = $notificationCount . ' Pending Requests';
                    $notificationHref = 'dashboard.php';
                }
            }
        } catch (Throwable $e) {
            $notificationCount = 0;
            $notificationLabel = '';
            $notificationHref = 'dashboard.php';
        }
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?> - MyBalai</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
    <style>
        @media (max-width: 1023px) {
            .admin-shell {
                position: relative !important;
                min-height: 100vh !important;
                display: flex !important;
                align-items: stretch !important;
            }
            /* Sidebar layout: keep footer outside the scrollable nav so it always
               remains visible and tappable on mobile. */
            .admin-sidebar {
                display: flex !important;
                flex-direction: column !important;
                height: 100vh !important;
                /* allow the footer to sit outside the scrolling region */
                overflow: visible !important;
            }
            .admin-sidebar > .p-4 {
                flex: 1 1 auto !important;
                display: block !important;
                /* cap the scroll region to leave room for the footer */
                max-height: calc(100vh - 64px) !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
                padding: 1rem !important;
                box-sizing: border-box !important;
            }
            .admin-sidebar .admin-user {
                flex: 0 0 auto !important;
                display: flex !important;
                align-items: center !important;
                height: 64px !important;
                padding: 0.75rem 1rem !important;
                box-sizing: border-box !important;
                pointer-events: auto !important;
                z-index: 10002 !important;
                background: linear-gradient(180deg, rgba(79, 70, 229, 0.06), rgba(79,70,229,0.02)) !important;
                box-shadow: 0 -6px 18px rgba(2,6,23,0.12) !important;
            }
            /* Ensure footer contents align left and don't stretch the name block */
            .admin-sidebar .admin-user { justify-content: flex-start !important; }
            .admin-sidebar .admin-user .flex-1 { flex: 0 0 auto !important; width: auto !important; }
            .admin-sidebar .admin-user a { color: #bfdbfe !important; }
            .admin-sidebar.is-open > .p-4 {
                /* reserve space equal to footer height (64px) plus some breathing room */
                padding-bottom: calc(64px + 1rem) !important;
            }
            .admin-sidebar nav {
                display: flex !important;
                flex-direction: column !important;
                gap: 0.5rem !important;
            }
            .admin-sidebar nav a,
            .admin-sidebar nav button {
                min-height: 44px !important;
                padding: 0.6rem 0.9rem !important;
                border-radius: 0.875rem !important;
                font-size: 0.95rem !important;
                line-height: 1.2 !important;
                justify-content: flex-start !important;
            }
            /* ensure smooth scrolling and larger tap targets */
            .admin-sidebar {
                -webkit-overflow-scrolling: touch !important;
                z-index: 80 !important;
                /* hardware-accelerate transform animations for smoothness */
                will-change: transform;
            }
            .admin-backdrop { z-index: 70 !important; }
            .admin-sidebar nav a,
            .admin-sidebar nav button {
                display: flex !important;
                align-items: center !important;
                gap: 0.6rem !important;
                padding: 0.6rem 0.9rem !important;
                width: 100% !important;
                font-size: 0.95rem !important;
            }
            .admin-sidebar nav a i,
            .admin-sidebar nav button i { min-width: 28px; text-align: center; }
            .admin-sidebar .admin-user { padding: 0.75rem 1rem !important; }
            .admin-sidebar .sidebar-close {
                position: absolute !important;
                top: 1rem !important;
                right: 1rem !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 44px !important;
                height: 44px !important;
                border-radius: 12px !important;
                background: rgba(255,255,255,0.16) !important;
                box-shadow: 0 8px 20px rgba(0,0,0,0.18) !important;
                color: white !important;
                border: none !important;
                z-index: 102 !important;
                pointer-events: auto !important;
            }
            /* smooth scrolling for content areas on mobile */
            .admin-content .overflow-x-auto, .admin-sidebar { -webkit-overflow-scrolling: touch; }
            .admin-sidebar nav span { line-height: 1.2 !important; }
            .admin-user {
                /* Keep footer visible inside the scrollable sidebar using flexbox */
                position: relative !important;
                left: 0 !important;
                width: 100% !important;
                margin-top: auto !important;
                padding: 1rem !important;
                border-top-color: rgba(255, 255, 255, 0.12) !important;
                z-index: 85 !important;
                background: linear-gradient(180deg, rgba(79, 70, 229, 0.06), rgba(79,70,229,0.02)) !important;
            }
            .admin-main {
                margin-left: 0 !important;
                min-width: 0 !important;
                width: 100% !important;
            }
            .admin-topbar {
                z-index: 60 !important;
                background: rgba(255, 255, 255, 0.96) !important;
                backdrop-filter: blur(10px);
                border-bottom: 1px solid rgba(226, 232, 240, 0.9) !important;
            }
            .admin-mobile-toggle {
                display: inline-flex !important;
                position: relative !important;
                z-index: 70 !important;
                width: 44px !important;
                height: 44px !important;
                flex: 0 0 auto !important;
            }
            .admin-content {
                padding: 0.875rem !important;
            }
            .admin-content > .grid,
            .admin-content .grid {
                gap: 0.875rem !important;
            }
            .admin-content .bg-white.rounded-lg,
            .admin-content .bg-white.rounded-xl,
            .admin-content .bg-white.rounded-2xl {
                border: 1px solid #e5e7eb !important;
                box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06) !important;
            }
            .admin-content .p-6 {
                padding: 1rem !important;
            }
            .admin-content .p-4 {
                padding: 0.875rem !important;
            }
            .admin-content .overflow-x-auto {
                margin-left: -0.875rem !important;
                margin-right: -0.875rem !important;
                padding-left: 0.875rem !important;
                padding-right: 0.875rem !important;
                -webkit-overflow-scrolling: touch;
            }
            .admin-content table {
                min-width: 680px;
            }
            .admin-content th,
            .admin-content td {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            .admin-content button,
            .admin-content .action-button,
            .admin-content a.inline-flex,
            .admin-content a.flex,
            .admin-content select,
            .admin-content input,
            .admin-content textarea {
                min-height: 44px;
            }

            /* Mobile-friendly adjustments: reduce large paddings and font sizes
               inside admin content to prevent cramped/overflowing layouts. */
            .admin-content .p-6 { padding: 1rem !important; }
            .admin-content .text-3xl { font-size: 1.5rem !important; }
            .admin-content .text-4xl { font-size: 2rem !important; }
            .admin-content table { min-width: 0 !important; }
        }
        @media (max-width: 639px) {
            /* Reduce admin sidebar width on small mobile screens */
            .admin-sidebar {
                width: min(12rem, 90vw) !important;
            }

            .admin-topbar {
                padding: 0.65rem 1rem;
            }
            .admin-topbar-inner {
                flex-direction: row;
                align-items: center;
                gap: 0.75rem;
            }
            .admin-topbar-left {
                flex: 1;
                min-width: 0;
                justify-content: flex-start;
            }
            .page-title {
                font-size: 1rem;
                line-height: 1.15;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .admin-topbar-right {
                display: none;
            }
            .admin-content .grid {
                gap: 1rem !important;
            }
            .admin-content [class*="grid-cols-"] {
                grid-template-columns: 1fr !important;
            }
            .admin-content .overflow-x-auto {
                -webkit-overflow-scrolling: touch;
            }
            .admin-content table {
                min-width: 640px;
            }
            .admin-content .flex.justify-between.items-center {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }
            .admin-content .flex.justify-between.items-center > * {
                width: 100%;
            }
            .admin-content .space-y-2 > * + * {
                margin-top: 0.5rem !important;
            }
            .admin-content form,
            .admin-content .bg-white,
            .admin-content .rounded-lg,
            .admin-content .rounded-xl,
            .admin-content .rounded-2xl {
                word-break: break-word;
            }
            .admin-content input,
            .admin-content select,
            .admin-content textarea,
            .admin-content button {
                max-width: 100%;
            }
            .admin-content .p-6 {
                padding: 1rem !important;
            }
            .admin-content button,
            .admin-content .action-button,
            .admin-content a.inline-flex,
            .admin-content a.flex {
                max-width: 100%;
            }
            .admin-content .mt-6.grid.grid-cols-2,
            .admin-content .grid.grid-cols-2.md\:grid-cols-4 {
                grid-template-columns: 1fr;
            }
        }
        @media (min-width: 1024px) {
            .admin-mobile-toggle {
                display: none;
            }
        }
        /* When sidebar is open on small screens, hide the topbar to avoid white gaps
           caused by stacking contexts. Also ensure the backdrop covers the full viewport. */
        .sidebar-open .admin-topbar,
        .sidebar-open .topbar {
            display: none !important;
            pointer-events: none !important;
        }
        /* Strong overlay rules for mobile: ensure backdrop fully covers header and sidebar overlays */
        @media (max-width: 1023px) {
            #adminBackdrop, #sidebarBackdrop, .admin-backdrop {
                position: fixed !important;
                inset: 0 !important;
                top: 0 !important;
                height: 100vh !important;
                z-index: 9999 !important;
                background: rgba(0, 0, 0, 0.44) !important;
            }
            .admin-sidebar, #mobileSidebar, #adminSidebar {
                z-index: 10000 !important;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
            }
            .admin-topbar, .topbar, .admin-main, main {
                transform: none !important;
                z-index: 50 !important;
            }
        }

        /* Smooth mobile sidebar and backdrop animations (override earlier rules) */
        @media (max-width: 1023px) {
            .admin-sidebar {
                /* keep sidebar off-screen by default and animate via transform */
                transform: translateX(-110%) !important;
                transition: transform 320ms cubic-bezier(.22,.9,.35,1) !important;
                will-change: transform;
                pointer-events: none !important;
            }
            .admin-sidebar.is-open {
                transform: translateX(0) !important;
                pointer-events: auto !important;
            }
            .admin-backdrop {
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transition: opacity 260ms ease, visibility 260ms ease;
            }
            .admin-backdrop.show {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
            }
        }
        /* Final admin mobile fix: adjust sidebar width, remove footer, add user info to nav */
        @media (max-width: 900px) {
            .admin-sidebar {
                width: min(18rem, 75vw) !important;
                max-width: 75vw !important;
                display: flex !important;
                flex-direction: column !important;
                position: relative !important;
            }
            .admin-sidebar > .p-4 {
                flex: 1 1 auto !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
                padding-top: 4rem !important;
            }
            .admin-sidebar .admin-user {
                display: none !important;
            }
            .sidebar-mobile-user {
                display: block !important;
            }
            .admin-sidebar .sidebar-close {
                position: absolute !important;
                top: 1rem !important;
                right: 1rem !important;
                width: 44px !important;
                height: 44px !important;
                border-radius: 12px !important;
                z-index: 100 !important;
                background: rgba(255,255,255,0.16) !important;
                box-shadow: 0 8px 20px rgba(0,0,0,0.18) !important;
                color: #fff !important;
                cursor: pointer !important;
                pointer-events: auto !important;
                border: none !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="admin-shell flex min-h-screen">
        <div id="adminSidebar" class="admin-sidebar bg-gradient-to-b from-blue-800 to-indigo-900 text-white fixed h-full overflow-y-auto">
            <button id="adminSidebarClose" class="sidebar-close lg:hidden" aria-label="Close menu"><i class="fas fa-times"></i></button>
            <div class="p-4">
                <div class="flex items-center space-x-2 mb-8">
                    <i class="fas fa-home text-2xl"></i>
                    <h1 class="text-xl font-bold">MyBalai</h1>
                </div>
                <nav class="space-y-2">
                    <?php foreach ($items as $item): ?>
                    <?php if ($item[0] === 'create-account'): ?>
                    <button type="button" onclick="openCreateAccountModal()" class="flex w-full items-center space-x-2 rounded-lg px-4 py-2 text-left transition <?php echo $active == $item[3] ? 'bg-blue-700' : 'hover:bg-blue-700'; ?>">
                        <i class="fas fa-<?php echo $item[1]; ?>"></i>
                        <span><?php echo $item[2]; ?></span>
                    </button>
                    <?php else: ?>
                    <a href="<?php echo $item[0]; ?>" class="flex items-center space-x-2 px-4 py-2 rounded-lg transition <?php echo $active == $item[3] ? 'bg-blue-700' : 'hover:bg-blue-700'; ?>">
                        <i class="fas fa-<?php echo $item[1]; ?>"></i>
                        <span><?php echo $item[2]; ?></span>
                    </a>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <!-- Mobile: Logout button added to nav menu -->
                    <div class="sidebar-mobile-user hidden lg:hidden mt-4 pt-4 border-t border-blue-700">
                        <a href="../logout.php" class="flex items-center space-x-2 px-4 py-2 rounded-lg transition hover:bg-blue-700 text-blue-300 hover:text-white">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </nav>
                </div>
                <div class="admin-user w-full p-4 border-t border-blue-700 bg-indigo-900 hidden lg:block">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-user-circle text-2xl"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold"><?php echo e($_SESSION['user_name']); ?></p>
                            <p class="text-xs text-blue-300"><?php echo e(labelize($_SESSION['primary_role'] ?? $_SESSION['user_type'])); ?></p>
                        </div>
                        <a href="../logout.php" class="text-blue-300 hover:text-white"><i class="fas fa-sign-out-alt"></i></a>
                    </div>
                </div>
        </div>
        <div id="adminBackdrop" class="admin-backdrop fixed inset-0 z-40 bg-black/40 lg:hidden"></div>
        <main class="admin-main flex-1">
            <div class="admin-topbar topbar bg-white shadow-sm p-4 sticky top-0 z-10">
                <div class="admin-topbar-inner flex flex-wrap items-center justify-between gap-3">
                    <div class="admin-topbar-left flex min-w-0 items-center gap-3">
                        <button type="button" id="adminSidebarToggle" class="admin-mobile-toggle inline-flex items-center justify-center rounded-lg border border-gray-200 text-gray-700 shadow-sm hover:bg-gray-50 lg:hidden" aria-label="Open menu" aria-controls="adminSidebar" aria-expanded="false">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h2 class="page-title min-w-0 text-2xl font-bold text-gray-800"><?php echo e($title); ?></h2>
                    </div>
                    <div class="admin-topbar-right flex items-center text-sm text-gray-600">
                        <?php if (!empty($notificationLabel)): ?>
                            <a href="<?php echo e($notificationHref); ?>" class="inline-flex items-center rounded-full bg-blue-50 px-3 py-2 text-blue-700 hover:bg-blue-100 mr-3">
                                <i class="fas fa-bell mr-2"></i>
                                <?php echo e($notificationLabel); ?>
                            </a>
                        <?php endif; ?>
                        <?php echo date('F j, Y'); ?>
                    </div>
                </div>
            </div>
            <div class="admin-content p-6">
<?php
}

function adminFooter() {
    ?>
            </div>
        </main>
        <div id="createAccountModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 px-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Create Account</h3>
                        <p class="mt-1 text-sm text-gray-500">Choose which staff account you want to create.</p>
                    </div>
                    <button type="button" onclick="closeCreateAccountModal()" class="text-gray-400 transition hover:text-gray-600">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                <div class="mt-5">
                    <label for="createAccountRole" class="mb-2 block text-sm font-medium text-gray-700">Account type</label>
                    <select id="createAccountRole" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        <option value="barangay_secretary">Barangay Secretary</option>
                        <option value="barangay_treasurer">Barangay Treasurer</option>
                    </select>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeCreateAccountModal()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Cancel</button>
                    <button type="button" onclick="goToCreateAccount()" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Continue</button>
                </div>
            </div>
        </div>
        <script>
            function openCreateAccountModal() {
                const modal = document.getElementById('createAccountModal');
                if (!modal) {
                    return;
                }
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeCreateAccountModal() {
                const modal = document.getElementById('createAccountModal');
                if (!modal) {
                    return;
                }
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function goToCreateAccount() {
                const select = document.getElementById('createAccountRole');
                if (!select || !select.value) {
                    return;
                }
                window.location.href = 'users.php?role=' + encodeURIComponent(select.value);
            }

            document.addEventListener('click', function (event) {
                const modal = document.getElementById('createAccountModal');
                if (modal && event.target === modal) {
                    closeCreateAccountModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeCreateAccountModal();
                }
            });
        </script>
        <script>
            (function () {
                const sidebar = document.getElementById('adminSidebar');
                const backdrop = document.getElementById('adminBackdrop');
                const toggle = document.getElementById('adminSidebarToggle');

                if (!sidebar || !backdrop || !toggle) {
                    return;
                }

                /* Open the sidebar: add class to trigger CSS transform and fade-in backdrop */
                function openSidebar() {
                    // ensure transform transition applies
                    sidebar.classList.add('is-open');
                    backdrop.classList.add('show');
                    toggle.setAttribute('aria-expanded', 'true');
                    document.body.classList.add('overflow-hidden');
                    document.body.classList.add('sidebar-open');
                }

                /* Close the sidebar: remove classes so CSS handles animation */
                function closeSidebar() {
                    sidebar.classList.remove('is-open');
                    backdrop.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                    document.body.classList.remove('overflow-hidden');
                    document.body.classList.remove('sidebar-open');
                }

                toggle.addEventListener('click', function (event) {
                    event.stopPropagation();
                    if (sidebar.classList.contains('is-open')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
                const closeBtn = document.getElementById('adminSidebarClose');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function (event) {
                        event.stopPropagation();
                        closeSidebar();
                    });
                }

                backdrop.addEventListener('click', closeSidebar);

                document.addEventListener('click', function (event) {
                    if (window.innerWidth >= 1024) {
                        return;
                    }
                    if (!sidebar.classList.contains('is-open')) {
                        return;
                    }
                    if (sidebar.contains(event.target) || toggle.contains(event.target)) {
                        return;
                    }
                    closeSidebar();
                }, true);

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeSidebar();
                    }
                });

                window.addEventListener('resize', function () {
                    if (window.innerWidth >= 1024) {
                        closeSidebar();
                    }
                });

                sidebar.querySelectorAll('a, button').forEach(function (element) {
                    element.addEventListener('click', function (ev) {
                        // keep clicks inside dropdowns or non-navigation buttons from closing immediately
                        var shouldClose = true;
                        // If element has data-no-close attribute, skip closing
                        if (element.hasAttribute && element.hasAttribute('data-no-close')) {
                            shouldClose = false;
                        }
                        if (window.innerWidth < 1024 && shouldClose) {
                            closeSidebar();
                        }
                    });
                });

                // Add touch-swipe to close for better UX on phones
                (function () {
                    var touchStartX = 0;
                    var touchCurrentX = 0;
                    var tracking = false;
                    sidebar.addEventListener('touchstart', function (e) {
                        if (!sidebar.classList.contains('is-open')) return;
                        touchStartX = e.touches[0].clientX;
                        tracking = true;
                    }, { passive: true });
                    sidebar.addEventListener('touchmove', function (e) {
                        if (!tracking) return;
                        touchCurrentX = e.touches[0].clientX;
                    }, { passive: true });
                    sidebar.addEventListener('touchend', function () {
                        if (!tracking) return;
                        var delta = touchCurrentX - touchStartX;
                        // swipe left to close
                        if (delta < -40) {
                            closeSidebar();
                        }
                        tracking = false;
                        touchStartX = touchCurrentX = 0;
                    });
                })();
            })();
        </script>
        <script>
            (function () {
                try {
                    var params = new URLSearchParams(window.location.search);
                    if (!params.has('openSidebar')) return;
                    if (window.innerWidth >= 1024) return;
                    setTimeout(function () {
                        var toggle = document.getElementById('adminSidebarToggle');
                        var sidebar = document.getElementById('adminSidebar');
                        var backdrop = document.getElementById('adminBackdrop');
                        if (!sidebar || !toggle) return;
                        if (!sidebar.classList.contains('is-open')) {
                            if (typeof toggle.click === 'function') {
                                toggle.click();
                            } else {
                                sidebar.classList.add('is-open');
                                if (backdrop) backdrop.classList.add('show');
                                toggle.setAttribute('aria-expanded', 'true');
                                document.body.classList.add('overflow-hidden');
                                document.body.classList.add('sidebar-open');
                            }
                        }
                    }, 80);
                } catch (e) {
                    console.warn('openSidebar debug helper failed', e);
                }
            })();
        </script>
        <script>
            (function () {
                // Remove inline width forcing to avoid layout jank; rely on CSS for sizing
                var sidebar = document.getElementById('adminSidebar');
                if (!sidebar) return;

                function applyForcedWidth() {
                    sidebar.style.width = '';
                    sidebar.style.maxWidth = '';
                    sidebar.style.boxSizing = '';
                }

                window.addEventListener('resize', applyForcedWidth);
                // Apply once on load
                applyForcedWidth();
            })();
        </script>
    </div>
</body>
</html>
<?php
}
?>
