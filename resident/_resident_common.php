<?php
require_once '../config/database.php';

if (!isLoggedIn() || $_SESSION['user_type'] != 'resident') {
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
        'approved' => 'bg-green-100 text-green-800',
        'completed' => 'bg-green-100 text-green-800',
        'confirmed' => 'bg-green-100 text-green-800',
        'resolved' => 'bg-green-100 text-green-800',
        'ready_for_pickup' => 'bg-blue-100 text-blue-800',
        'in_progress' => 'bg-blue-100 text-blue-800',
        'cancelled' => 'bg-red-100 text-red-800',
        'rejected' => 'bg-red-100 text-red-800',
    ];
    return $map[$status] ?? 'bg-gray-100 text-gray-800';
}

function residentHeader($title, $active) {
    $items = [
        ['dashboard.php', 'Dashboard', 'dashboard'],
        ['requests.php', 'Document Requests', 'requests'],
        ['complaints.php', 'Complaints', 'complaints'],
        ['appointments.php', 'Appointments', 'appointments'],
        ['profile.php', 'My Profile', 'profile'],
        ['view_qr.php', 'My QR ID', 'qr'],
    ];
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
        /* Shared header styles for resident pages - normalize alignment */
        .resident-nav .mx-auto > div { display: flex; align-items: center; gap: 0.75rem; }
        /* ensure the inner container can be a positioning context for the centered brand */
        .resident-nav .mx-auto { position: relative; }
        .resident-nav .brand-block p { margin: 0; }
        .resident-nav .brand-block .title { font-size: 1rem; font-weight: 600; }
        .resident-nav .brand-block .subtitle { font-size: 0.75rem; opacity: 0.9; }
        .resident-nav .nav-links a { display: inline-flex; align-items: center; justify-content: center; height: 36px; line-height: 1; padding: 0 10px; border-radius: 999px; }
        .resident-nav .user-box { display: flex; align-items: center; }
        .resident-nav .user-box p { margin: 0; }

        /* Make header compact on all sizes by default */
        .resident-nav { padding-top: 6px; padding-bottom: 6px; }

        /* Shared mobile header styles for resident pages */
        @media (max-width: 768px) {
            .container { padding-left: 1rem; padding-right: 1rem; }
            .resident-nav { position: relative; }
            /* Stack and center brand on mobile for better readability */
            .resident-nav .mx-auto > .flex { flex-direction: column; gap: 0.5rem; align-items: stretch; padding-right: 6.25rem; }
            .resident-nav .brand-block { width: 100%; text-align: center; }
            .resident-nav .brand-block .title { font-size: 1.05rem; font-weight: 700; }
            .resident-nav .brand-block .subtitle { font-size: 0.8rem; opacity: 0.95; margin-top: 2px; }
            /* Pin the mobile actions to the top-right so they're always reachable and never overlap the brand */
            .mobile-actions {
                position: absolute;
                right: 0.75rem;
                top: 8px;
                z-index: 90;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            #mobileNavToggle {
                z-index: 90;
                width: 40px;
                height: 40px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.25rem;
                border-radius: 8px;
                background: rgba(255,255,255,0.06);
                backdrop-filter: blur(4px);
            }
            #mobileNavToggle:focus { outline: none; box-shadow: 0 0 0 4px rgba(99,102,241,0.12); }
            .resident-nav .nav-links { display: none; width: 100%; margin-top: 0.25rem; padding: 0.6rem; background: linear-gradient(180deg, rgba(15,23,42,0.98), rgba(10,16,26,0.98)); border-radius: 12px; box-shadow: 0 10px 30px rgba(2,6,23,0.20); z-index: 50; flex-direction: column; gap: 0.5rem; pointer-events: auto; }
            .resident-nav .nav-links.is-open { display: flex; animation: mb-slide-down .18s ease-out; }
            .resident-nav .nav-links a { color: #c7d2fe; padding: 0.55rem 0.75rem; border-radius: 8px; }
            .resident-nav .nav-links a:hover { background: rgba(255,255,255,0.03); }
            .resident-nav .nav-links .mobile-logout-link {
                width: auto;
                align-self: flex-start;
                padding: 0.45rem 0.85rem;
                border-radius: 999px;
                font-size: 0.8rem;
                font-weight: 600;
            }
            .resident-nav .user-box { display: none; }
            /* ensure the toggle stays absolutely positioned; remove conflicting relative rule */
            #residentNavLinks { width: 100%; }
        }
        /* Keep desktop header elements in separate lanes so they never overlap */
        @media (min-width: 1024px) {
            .resident-nav {
                padding-top: 4px;
                padding-bottom: 4px;
            }
            .resident-nav .mx-auto > .flex {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
                grid-template-rows: auto auto;
                align-items: center;
                column-gap: 0.75rem;
                row-gap: 0.1rem;
            }
            .resident-nav .mx-auto > .flex > :first-child {
                grid-column: 1 / -1;
                justify-self: center;
            }
            .resident-nav .brand-block {
                position: static;
                transform: none;
                text-align: center;
                pointer-events: auto;
                justify-self: center;
                min-width: 0;
            }
            .resident-nav .brand-block p {
                white-space: nowrap;
            }
            .resident-nav .nav-links {
                grid-column: 2;
                grid-row: 2;
                justify-self: center;
                min-width: 0;
                gap: 0.5rem;
            }
            .resident-nav .nav-links a {
                height: 34px;
                padding: 0 8px;
            }
            .resident-nav .user-box {
                grid-column: 3;
                grid-row: 2;
                justify-self: end;
            }
            /* ensure brand text is readable and not too large on desktop */
            .resident-nav .brand-block .title { font-size: 1rem; }
            .resident-nav .brand-block .subtitle { font-size: 0.75rem; }
        }
        @keyframes mb-slide-down { from { transform: translateY(-6px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body class="resident-page bg-gray-100">
    <nav class="resident-nav sticky top-0 z-30 border-b border-white/10 bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-800 text-white shadow-lg">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
                <div class="flex flex-col gap-2 py-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="brand-block">
                            <p class="title text-base font-semibold leading-tight">MyBalai Resident Portal</p>
                            <p class="subtitle text-xs text-blue-100">Your barangay services in one place</p>
                        </div>
                    </div>
                <div class="mobile-actions lg:hidden">
                    <button id="mobileNavToggle" aria-expanded="false" aria-controls="residentNavLinks" class="inline-flex items-center rounded-md bg-white/10 p-2 text-white hover:bg-white/20">
                        <i id="mobileNavIcon" class="fas fa-bars"></i>
                    </button>
                </div>
                <div id="residentNavLinks" class="nav-links flex flex-wrap items-center gap-2 text-sm font-medium sm:gap-2 lg:justify-center">
                    <?php foreach ($items as $item): ?>
                    <a href="<?php echo $item[0]; ?>" class="inline-flex items-center rounded-full px-2 py-1 transition text-sm hover:bg-white/10 <?php echo $item[2] === 'logout' ? 'bg-red-500/20 text-red-100 hover:bg-red-500/30' : ($active == $item[2] ? 'bg-white/15 text-white' : 'text-blue-100'); ?>">
                        <?php echo $item[1]; ?>
                    </a>
                    <?php endforeach; ?>
                    <a href="../logout.php" class="lg:hidden mobile-logout-link inline-flex items-center justify-center bg-red-500/20 text-red-100 transition hover:bg-red-500/30">
                        Logout
                    </a>
                </div>
                <div class="flex items-center gap-2 self-start rounded-lg bg-white/8 px-3 py-2 backdrop-blur-sm lg:self-auto user-box">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/12">
                        <i class="fas fa-user-circle text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium leading-tight"><?php echo e($_SESSION['user_name']); ?></p>
                        <p class="text-xs text-blue-100">Resident account</p>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <script>
        (function(){
            var toggle = document.getElementById('mobileNavToggle');
            var navLinks = document.getElementById('residentNavLinks') || document.querySelector('.resident-nav .nav-links');
            var icon = document.getElementById('mobileNavIcon');
            var nav = document.querySelector('.resident-nav');
            if(!toggle || !navLinks || !nav) return;

            function openNav(){
                navLinks.classList.add('is-open');
                nav.classList.add('nav-open');
                toggle.setAttribute('aria-expanded','true');
                if(icon){ icon.classList.remove('fa-bars'); icon.classList.add('fa-times'); }
            }

            function closeNav(){
                navLinks.classList.remove('is-open');
                nav.classList.remove('nav-open');
                toggle.setAttribute('aria-expanded','false');
                if(icon){ icon.classList.remove('fa-times'); icon.classList.add('fa-bars'); }
            }

            toggle.addEventListener('click', function(){
                if(navLinks.classList.contains('is-open')) closeNav(); else openNav();
            });

            function clickIsInsideNav(event) {
                var target = event.target;
                while (target) {
                    if (target === navLinks || target === toggle || target === nav) {
                        return true;
                    }
                    target = target.parentNode;
                }
                return false;
            }

            navLinks.addEventListener('click', function(event){
                var target = event.target;
                while (target && target !== navLinks) {
                    if (target.tagName === 'A') {
                        closeNav();
                        return;
                    }
                    target = target.parentNode;
                }
            });

            document.addEventListener('click', function(event){
                if (!navLinks.classList.contains('is-open')) return;
                if (clickIsInsideNav(event)) return;
                closeNav();
            }, true);
            document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && navLinks.classList.contains('is-open')) closeNav(); });
            window.addEventListener('resize', function(){
                if (window.innerWidth > 768) {
                    closeNav();
                }
            });
        })();
    </script>
    <main class="container max-w-screen-2xl mx-auto px-6 py-8">
<?php
}

function residentFooter() {
    ?>
    </main>
</body>
</html>
<?php
}
?>
