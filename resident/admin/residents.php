<?php
require_once __DIR__ . '/../../config/database.php';

if (!isLoggedIn() || $_SESSION['user_type'] == 'resident') {
    redirect('../../index.php');
}

// Handle resident deletion
if (isset($_GET['delete']) && hasPermission('delete_residents')) {
    $user_id = $_GET['delete'];
    if (hardDeleteUserAccount($user_id, 'resident')) {
        logActivity($_SESSION['user_id'], 'Deleted resident', 'users', $user_id);
        redirect('residents.php?msg=deleted');
    }
    redirect('residents.php?msg=delete_failed');
}

// Get all residents with their profiles
$stmt = $pdo->query("
    SELECT u.*, rp.*, 
           (SELECT COUNT(*) FROM document_requests WHERE user_id = u.user_id) as total_requests,
           (SELECT COUNT(*) FROM complaints WHERE complainant_id = u.user_id) as total_complaints
    FROM users u
    LEFT JOIN resident_profiles rp ON u.user_id = rp.user_id
    JOIN user_role_assignments ura ON ura.user_id = u.user_id AND ura.is_active = 1
    JOIN roles r ON r.role_id = ura.role_id
    WHERE r.role_name = 'resident'
    ORDER BY u.created_at DESC
");
$residents = $stmt->fetchAll();

$totalResidents = count($residents);
$activeResidents = 0;
foreach ($residents as $resident) {
    if ($resident['is_active']) {
        $activeResidents++;
    }
}
$inactiveResidents = $totalResidents - $activeResidents;

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents Management - MyBalai</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="admin-shell min-h-screen lg:flex">
        <aside class="admin-sidebar hidden lg:flex lg:w-72 xl:w-80 flex-col bg-gradient-to-b from-slate-950 via-blue-900 to-cyan-800 text-white p-6">
            <div class="flex items-center gap-3 mb-10">
                <div class="rounded-2xl bg-slate-900 p-3 text-cyan-300 shadow-lg">
                    <i class="fas fa-home text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold">MyBalai</h1>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-300">LATROBE, PA</p>
                </div>
            </div>

            <nav class="space-y-2 flex-1">
                <a href="dashboard.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-100 hover:bg-slate-800 transition">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="residents.php" class="flex items-center gap-3 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-medium text-white shadow-inner">
                    <i class="fas fa-users"></i>
                    Residents
                </a>
                <a href="requests.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-100 hover:bg-slate-800 transition">
                    <i class="fas fa-file-alt"></i>
                    Document Requests
                </a>
                <a href="complaints.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-100 hover:bg-slate-800 transition">
                    <i class="fas fa-gavel"></i>
                    Complaints/Blotter
                </a>
                <a href="appointments.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-100 hover:bg-slate-800 transition">
                    <i class="fas fa-calendar-check"></i>
                    Appointments
                </a>
                <a href="finance.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-100 hover:bg-slate-800 transition">
                    <i class="fas fa-coins"></i>
                    Finance
                </a>
                <a href="announcements.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-100 hover:bg-slate-800 transition">
                    <i class="fas fa-bullhorn"></i>
                    Announcements
                </a>
                <a href="settings.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-100 hover:bg-slate-800 transition">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
            </nav>

            <div class="mt-auto rounded-3xl border border-white/10 bg-slate-950/80 p-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-2xl bg-slate-900 p-3 text-cyan-300">
                        <i class="fas fa-user-circle text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?php echo $_SESSION['user_name']; ?></p>
                        <a href="../logout.php" class="text-xs text-slate-300 hover:text-white">Logout</a>
                    </div>
                </div>
            </div>
        </aside>

        <div id="mobileSidebar" class="fixed inset-0 z-40 hidden bg-slate-950/80 lg:hidden">
            <div class="h-full w-72 bg-slate-950 p-6">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-3">
                        <div class="rounded-2xl bg-slate-900 p-3 text-cyan-300">
                            <i class="fas fa-home text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold">MyBalai</h1>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-300">Admin</p>
                        </div>
                    </div>
                    <button onclick="toggleMobileSidebar()" class="rounded-xl bg-slate-800 p-2 text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-100 hover:bg-slate-800 transition">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                    <a href="residents.php" class="flex items-center gap-3 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition">
                        <i class="fas fa-users"></i>
                        Residents
                    </a>
                    <a href="requests.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-100 hover:bg-slate-800 transition">
                        <i class="fas fa-file-alt"></i>
                        Document Requests
                    </a>
                    <a href="complaints.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-100 hover:bg-slate-800 transition">
                        <i class="fas fa-gavel"></i>
                        Complaints/Blotter
                    </a>
                    <a href="appointments.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-100 hover:bg-slate-800 transition">
                        <i class="fas fa-calendar-check"></i>
                        Appointments
                    </a>
                    <a href="finance.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-100 hover:bg-slate-800 transition">
                        <i class="fas fa-coins"></i>
                        Finance
                    </a>
                    <a href="announcements.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-100 hover:bg-slate-800 transition">
                        <i class="fas fa-bullhorn"></i>
                        Announcements
                    </a>
                    <a href="settings.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-100 hover:bg-slate-800 transition">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </nav>
            </div>
            <button onclick="toggleMobileSidebar()" class="absolute inset-0 bg-transparent"></button>
        </div>

        <main class="flex-1 lg:pl-72">
            <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur-sm px-4 py-4 lg:hidden">
                <div class="flex items-center justify-between">
                    <button onclick="toggleMobileSidebar()" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-700 shadow-sm">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="text-lg font-semibold">Residents</h2>
                    <button onclick="openAddModal()" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        <i class="fas fa-plus"></i>
                        Add
                    </button>
                </div>
            </header>

            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Residents Management</h1>
                        <p class="mt-2 text-sm text-slate-600">Manage resident records, view activity, and keep the roster up to date.</p>
                    </div>
                    <button onclick="openAddModal()" class="hidden lg:inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        <i class="fas fa-plus"></i>
                        Add Resident
                    </button>
                </div>

                <div class="mt-6 lg:hidden space-y-4">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-[28px] bg-white p-4 shadow-sm ring-1 ring-slate-200">
                            <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Total Residents</p>
                            <p class="mt-3 text-3xl font-semibold text-slate-900"><?php echo $totalResidents; ?></p>
                        </div>
                        <div class="rounded-[28px] bg-emerald-50 p-4 shadow-sm ring-1 ring-emerald-200">
                            <p class="text-sm uppercase tracking-[0.24em] text-emerald-700">Active</p>
                            <p class="mt-3 text-3xl font-semibold text-emerald-900"><?php echo $activeResidents; ?></p>
                        </div>
                        <div class="rounded-[28px] bg-rose-50 p-4 shadow-sm ring-1 ring-rose-200">
                            <p class="text-sm uppercase tracking-[0.24em] text-rose-700">Inactive</p>
                            <p class="mt-3 text-3xl font-semibold text-rose-900"><?php echo $inactiveResidents; ?></p>
                        </div>
                    </div>

                    <div class="rounded-[28px] bg-white p-4 shadow-sm ring-1 ring-slate-200">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <label class="sr-only" for="searchResidentsMobile">Search residents</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input id="searchResidentsMobile" type="text" placeholder="Search email or address" class="w-full rounded-3xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500/20">
                                </div>
                            </div>
                            <button onclick="openAddModal()" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                                <i class="fas fa-plus"></i>
                                Add
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 hidden lg:block">
                    <div class="grid gap-4 xl:grid-cols-[1fr_280px]">
                        <div class="rounded-[32px] bg-white p-6 shadow-sm ring-1 ring-slate-200">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Resident analytics</p>
                                    <h2 class="mt-2 text-3xl font-semibold text-slate-900">Roster overview</h2>
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <span class="rounded-3xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Total <?php echo $totalResidents; ?></span>
                                    <span class="rounded-3xl bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-800">Active <?php echo $activeResidents; ?></span>
                                    <span class="rounded-3xl bg-rose-100 px-4 py-2 text-sm font-semibold text-rose-800">Inactive <?php echo $inactiveResidents; ?></span>
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-3 gap-4">
                                <div class="rounded-3xl bg-slate-50 p-4">
                                    <p class="text-xs uppercase tracking-[0.28em] text-slate-500">Total</p>
                                    <p class="mt-3 text-2xl font-semibold text-slate-900"><?php echo $totalResidents; ?></p>
                                </div>
                                <div class="rounded-3xl bg-emerald-50 p-4">
                                    <p class="text-xs uppercase tracking-[0.28em] text-emerald-600">Active</p>
                                    <p class="mt-3 text-2xl font-semibold text-emerald-900"><?php echo $activeResidents; ?></p>
                                </div>
                                <div class="rounded-3xl bg-rose-50 p-4">
                                    <p class="text-xs uppercase tracking-[0.28em] text-rose-600">Inactive</p>
                                    <p class="mt-3 text-2xl font-semibold text-rose-900"><?php echo $inactiveResidents; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-[32px] bg-gradient-to-br from-slate-950 to-blue-900 p-6 text-white shadow-sm ring-1 ring-slate-900/10">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.24em] text-slate-300">Roster status</p>
                                    <h3 class="mt-2 text-2xl font-semibold">Ready to manage</h3>
                                </div>
                                <button onclick="openAddModal()" class="rounded-3xl bg-white/10 px-4 py-3 text-sm font-semibold text-white shadow-sm ring-1 ring-white/10 transition hover:bg-white/15">
                                    Add Resident
                                </button>
                            </div>
                            <div class="mt-6 space-y-4">
                                <div class="rounded-3xl bg-white/10 p-4">
                                    <p class="text-xs uppercase tracking-[0.28em] text-slate-300">Fast action</p>
                                    <p class="mt-2 text-sm text-slate-100">Use the desktop table to quickly edit, view, or delete resident records.</p>
                                </div>
                                <div class="rounded-3xl bg-white/10 p-4">
                                    <p class="text-xs uppercase tracking-[0.28em] text-slate-300">Quick tip</p>
                                    <p class="mt-2 text-sm text-slate-100">Toggle the mobile menu to jump between admin sections when on a phone.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($msg == 'deleted'): ?>
                    <div class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800 shadow-sm">
                        Resident deleted successfully!
                    </div>
                <?php endif; ?>

                <div class="mt-6 space-y-4">
                    <div class="lg:hidden space-y-4">
                        <?php foreach ($residents as $resident): ?>
                        <article class="rounded-[28px] bg-white p-5 shadow-sm ring-1 ring-slate-200">
                            <div class="flex items-start gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-slate-100 text-slate-500">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-base font-semibold text-slate-900"><?php echo $resident['first_name'] . ' ' . $resident['last_name']; ?></p>
                                    <p class="mt-1 text-sm text-slate-500"><?php echo $resident['email']; ?></p>
                                </div>
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800"><?php echo $resident['is_active'] ? 'Active' : 'Inactive'; ?></span>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-3xl bg-slate-50 p-4">
                                    <p class="text-xs uppercase tracking-[0.28em] text-slate-500">Contact</p>
                                    <p class="mt-2 text-sm text-slate-700"><?php echo $resident['phone_number'] ?: 'N/A'; ?></p>
                                </div>
                                <div class="rounded-3xl bg-slate-50 p-4">
                                    <p class="text-xs uppercase tracking-[0.28em] text-slate-500">Requests</p>
                                    <p class="mt-2 text-sm text-slate-700">Documents: <?php echo $resident['total_requests']; ?></p>
                                    <p class="text-sm text-slate-500">Complaints: <?php echo $resident['total_complaints']; ?></p>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <button onclick="viewResident(<?php echo $resident['user_id']; ?>)" class="inline-flex items-center gap-2 rounded-2xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200 transition">
                                    <i class="fas fa-eye"></i>
                                    View
                                </button>
                                <button onclick="editResident(<?php echo $resident['user_id']; ?>)" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-100 px-4 py-2 text-sm font-medium text-emerald-800 hover:bg-emerald-200 transition">
                                    <i class="fas fa-edit"></i>
                                    Edit
                                </button>
                                <button onclick="deleteResident(<?php echo $resident['user_id']; ?>)" class="inline-flex items-center gap-2 rounded-2xl bg-rose-100 px-4 py-2 text-sm font-medium text-rose-800 hover:bg-rose-200 transition">
                                    <i class="fas fa-trash"></i>
                                    Delete
                                </button>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="hidden lg:block overflow-hidden rounded-[32px] bg-white shadow-sm ring-1 ring-slate-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-slate-700">
                                <thead class="bg-slate-50 text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <tr class="sticky top-0 z-10 bg-slate-50/95 backdrop-blur-sm">
                                        <th class="px-6 py-4">Resident</th>
                                        <th class="px-6 py-4">Contact</th>
                                        <th class="px-6 py-4">Address</th>
                                        <th class="px-6 py-4">Requests</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    <?php foreach ($residents as $resident): ?>
                                    <tr class="transition hover:bg-slate-50">
                                        <td class="px-6 py-4 align-top">
                                            <div class="flex items-center gap-4">
                                                <div class="flex h-12 w-12 items-center justify-center rounded-3xl bg-slate-100 text-slate-500">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-slate-900"><?php echo $resident['first_name'] . ' ' . $resident['last_name']; ?></p>
                                                    <p class="text-sm text-slate-500"><?php echo $resident['email']; ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 align-top">
                                            <p class="font-medium text-slate-900"><?php echo $resident['phone_number'] ?: 'N/A'; ?></p>
                                        </td>
                                        <td class="px-6 py-4 align-top">
                                            <p class="text-sm text-slate-700"><?php echo $resident['house_number'] ? $resident['house_number'] . ' ' . $resident['street_address'] : 'N/A'; ?></p>
                                        </td>
                                        <td class="px-6 py-4 align-top">
                                            <p class="font-semibold text-slate-900">Docs: <?php echo $resident['total_requests']; ?></p>
                                            <p class="text-sm text-slate-500">Complaints: <?php echo $resident['total_complaints']; ?></p>
                                        </td>
                                        <td class="px-6 py-4 align-top">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo $resident['is_active'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'; ?>">
                                                <?php echo $resident['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 align-top text-sm font-medium">
                                            <button onclick="viewResident(<?php echo $resident['user_id']; ?>)" class="text-blue-600 hover:text-blue-900 mr-3" aria-label="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="editResident(<?php echo $resident['user_id']; ?>)" class="text-emerald-600 hover:text-emerald-900 mr-3" aria-label="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteResident(<?php echo $resident['user_id']; ?>)" class="text-rose-600 hover:text-rose-900" aria-label="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('mobileSidebar');
            sidebar.classList.toggle('hidden');
        }

        function viewResident(id) {
            window.location.href = 'view_resident.php?id=' + id;
        }

        function editResident(id) {
            window.location.href = 'edit_resident.php?id=' + id;
        }

        function deleteResident(id) {
            if (confirm('Are you sure you want to delete this resident?')) {
                window.location.href = 'residents.php?delete=' + id;
            }
        }

        function openAddModal() {
            window.location.href = 'add_resident.php';
        }
    </script>
</body>
</html>
