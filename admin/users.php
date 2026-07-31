<?php
require_once '_admin_common.php';

if (!hasRole(['super_admin', 'barangay_captain'])) {
    redirect('dashboard.php');
}

$message = '';
$error = '';
$requestedRole = sanitize($_GET['role'] ?? '');

if ($requestedRole === 'barangay_secretary' || $requestedRole === 'barangay_treasurer') {
    if (!hasRole('barangay_captain')) {
        redirect('dashboard.php');
    }
    $managedRole = $requestedRole;
    $managedLabel = $requestedRole === 'barangay_treasurer' ? 'Barangay Treasurer' : 'Barangay Secretary';
    $managerLabel = 'Barangay Captain';
} else {
    if (!hasRole('super_admin')) {
        redirect('dashboard.php');
    }
    $managedRole = 'barangay_captain';
    $managedLabel = 'Barangay Captain';
    $managerLabel = 'Super Admin';
}

$deleteAction = 'delete_' . $managedRole;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = sanitize($_POST['action'] ?? 'create');

    if ($action === $deleteAction) {
        $staffId = (int)($_POST['user_id'] ?? 0);

        if ($staffId <= 0) {
            $error = 'Invalid ' . $managedLabel . ' account selected.';
        } else {
            $stmt = $pdo->prepare("
                SELECT u.user_id, u.first_name, u.last_name
                FROM users u
                JOIN user_role_assignments ura ON ura.user_id = u.user_id AND ura.is_active = 1
                JOIN roles r ON r.role_id = ura.role_id
                WHERE u.user_id = ? AND r.role_name = ?
                LIMIT 1
            ");
            $stmt->execute([$staffId, $managedRole]);
            $staff = $stmt->fetch();

            if (!$staff) {
                $error = 'Only ' . $managedLabel . ' accounts can be deleted here.';
            } else {
                try {
                    if (hardDeleteUserAccount($staffId, 'staff')) {
                        logActivity($_SESSION['user_id'], 'Deleted ' . $managedLabel . ' account', 'users', $staffId, $staff['first_name'] . ' ' . $staff['last_name']);
                        $message = $managedLabel . ' account deleted successfully.';
                    } else {
                        $error = $managedLabel . ' account could not be deleted. Please try again.';
                    }
                } catch (Exception $e) {
                    $error = $managedLabel . ' account could not be deleted. Please try again.';
                }
            }
        }
    } else {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $username = sanitize($_POST['username'] ?? '');
    $phone = sanitize($_POST['phone_number'] ?? '');
    $roleName = $managedRole;
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!$firstName || !$lastName || !$email || !$username || !$password || !$confirmPassword) {
        $error = 'Please complete all required fields.';
    } elseif ($roleName === 'barangay_captain' && !hasRole('super_admin')) {
        $error = 'Only the Super Admin can create Barangay Captain accounts.';
    } elseif ($roleName === 'barangay_secretary' && !hasRole('barangay_captain')) {
        $error = 'Only the Barangay Captain can create Barangay Secretary accounts.';
    } elseif ($roleName === 'barangay_treasurer' && !hasRole('barangay_captain')) {
        $error = 'Only the Barangay Captain can create Barangay Treasurer accounts.';
    } elseif (!in_array($roleName, ['barangay_captain', 'barangay_secretary', 'barangay_treasurer'], true)) {
        $error = 'This account type cannot be created here.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            if ((int)dbFetchColumn("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?", [$email, $username]) > 0) {
                $error = 'Email or username is already registered.';
            } else {
                $roleId = getRoleId($roleName);
                if (!$roleId) {
                    $error = 'Selected role was not found.';
                } else {
                    $pdo->beginTransaction();
                    $userData = [
                        'primary_role_id' => $roleId,
                        'username' => $username,
                        'email' => $email,
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone_number' => $phone ?: null,
                        'is_active' => 1,
                        'is_verified' => 1,
                        'email_verified' => 1,
                        'created_by' => $_SESSION['user_id'],
                    ];
                    $userColumns = array_values(array_intersect(array_keys($userData), tableColumns('users')));
                    if (count($userColumns) < 6) {
                        throw new RuntimeException('The live users table is missing required account columns.');
                    }
                    $userFields = '`' . implode('`, `', $userColumns) . '`';
                    $userValues = array_map(fn($column) => $userData[$column], $userColumns);
                    $stmt = $pdo->prepare("INSERT INTO `users` ($userFields) VALUES (" . implode(', ', array_fill(0, count($userColumns), '?')) . ")");
                    $stmt->execute($userValues);
                    $userId = (int)$pdo->lastInsertId();

                    $roleAssigned = insertSubset('user_role_assignments', [
                        'user_id' => $userId,
                        'role_id' => $roleId,
                        'assigned_by' => $_SESSION['user_id'],
                        'is_active' => 1,
                    ]);
                    if (!$roleAssigned) {
                        throw new RuntimeException('The live role assignment table is missing required account columns.');
                    }

                    try {
                        logActivity($_SESSION['user_id'], 'Created staff account', 'users', $userId, $roleName);
                    } catch (Throwable $e) {
                        // Audit logging must not prevent account creation.
                    }
                    $pdo->commit();
                    $message = $managedLabel . ' account created successfully.';
                }
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Staff account creation failed: ' . $e->getMessage());
            if (empty($error)) {
                $error = 'Account could not be created. Please verify the database schema and try again.';
            }
        }
    }
    }
}

$staffUsers = dbFetchAll("
    SELECT u.user_id, u.first_name, u.last_name, u.email, u.username, u.is_active, u.created_at, r.role_name
    FROM users u
    JOIN user_role_assignments ura ON ura.user_id = u.user_id AND ura.is_active = 1
    JOIN roles r ON r.role_id = ura.role_id
    WHERE r.role_name = ? AND (u.deleted_at IS NULL OR u.deleted_at = '0000-00-00 00:00:00')
    ORDER BY u.created_at DESC
", [$managedRole]);
$staffCount = count($staffUsers);
$activeStaffCount = count(array_filter($staffUsers, fn($staff) => !empty($staff['is_active'])));
$inactiveStaffCount = $staffCount - $activeStaffCount;

adminHeader($managedLabel . ' Accounts', 'users');
?>
<?php if ($message): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4"><?php echo e($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4"><?php echo e($error); ?></div><?php endif; ?>

<div class="space-y-4">
    <div class="md:hidden bg-white rounded-[28px] border border-slate-200 p-5 shadow-lg">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-slate-500"><?php echo e($managedLabel); ?> Snapshot</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Admin account overview</h2>
                <p class="mt-1 text-sm text-slate-500">Quick actions and status for mobile review.</p>
            </div>
            <div class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-sm font-semibold text-sky-700">
                <?php echo $staffCount; ?> total
            </div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="rounded-3xl bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Active</p>
                <p class="mt-3 text-3xl font-semibold text-emerald-700"><?php echo $activeStaffCount; ?></p>
            </div>
            <div class="rounded-3xl bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Inactive</p>
                <p class="mt-3 text-3xl font-semibold text-rose-700"><?php echo $inactiveStaffCount; ?></p>
            </div>
            <div class="rounded-3xl bg-slate-50 p-4 col-span-2">
                <p class="text-xs uppercase tracking-wide text-slate-500">Managed Role</p>
                <p class="mt-3 text-xl font-semibold text-slate-900"><?php echo e($managedLabel); ?></p>
            </div>
        </div>
    </div>

    <div class="hidden md:grid md:grid-cols-3 gap-6">
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500 uppercase tracking-[0.24em]">Total Accounts</p>
            <p class="mt-4 text-4xl font-semibold text-slate-900"><?php echo $staffCount; ?></p>
        </div>
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500 uppercase tracking-[0.24em]">Active</p>
            <p class="mt-4 text-4xl font-semibold text-emerald-700"><?php echo $activeStaffCount; ?></p>
        </div>
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500 uppercase tracking-[0.24em]">Inactive</p>
            <p class="mt-4 text-4xl font-semibold text-rose-600"><?php echo $inactiveStaffCount; ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <form method="POST" class="lg:col-span-1 bg-white rounded-lg shadow p-6 space-y-4">
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="role_name" value="<?php echo e($managedRole); ?>">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Create <?php echo e($managedLabel); ?></h3>
            <p class="text-sm text-gray-500 mt-1">Only the <?php echo e($managerLabel); ?> can create <?php echo e($managedLabel); ?> logins.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                <input type="text" name="first_name" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                <input type="text" name="last_name" required class="w-full border rounded-lg px-3 py-2">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" required class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input type="text" name="username" required class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input type="text" name="phone_number" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="relative">
                <input id="passwordInputUser" type="password" name="password" required minlength="8" class="w-full border rounded-lg px-3 py-2 pr-10">
                <button type="button" id="togglePasswordUser" class="absolute inset-y-0 right-2 flex items-center text-slate-500 hover:text-slate-800 focus:outline-none" aria-label="Toggle password visibility">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <div class="relative">
                <input id="confirmPasswordUser" type="password" name="confirm_password" required minlength="8" class="w-full border rounded-lg px-3 py-2 pr-10">
                <button type="button" id="toggleConfirmPasswordUser" class="absolute inset-y-0 right-2 flex items-center text-slate-500 hover:text-slate-800 focus:outline-none" aria-label="Toggle confirm password visibility">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
        <button class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-semibold">Create Account</button>
    </form>

    <div class="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800"><?php echo e($managedLabel); ?> Accounts</h3>
            <p class="text-sm text-gray-500">Resident accounts are created from the public registration page.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Account</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($staffUsers as $staff): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?php echo e($staff['first_name'] . ' ' . $staff['last_name']); ?></div>
                            <div class="text-sm text-gray-500">Created <?php echo e(date('M d, Y', strtotime($staff['created_at']))); ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div><?php echo e($staff['email']); ?></div>
                            <div class="text-gray-500"><?php echo e($staff['username']); ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm"><?php echo e(labelize($staff['role_name'])); ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full <?php echo !empty($staff['is_active']) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo !empty($staff['is_active']) ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST" onsubmit="return confirm('Delete this <?php echo e($managedLabel); ?> account?');">
                                <input type="hidden" name="action" value="<?php echo e($deleteAction); ?>">
                                <input type="hidden" name="user_id" value="<?php echo (int)$staff['user_id']; ?>">
                                <button class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($staffUsers)): ?>
                    <tr><td colspan="5" class="text-center py-8 text-gray-500">No <?php echo e($managedLabel); ?> accounts yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="md:hidden p-4 space-y-4">
        <?php if (empty($staffUsers)): ?>
            <div class="text-center py-8 text-gray-500">No <?php echo e($managedLabel); ?> accounts yet.</div>
        <?php endif; ?>
        <?php foreach ($staffUsers as $staff): ?>
        <div class="bg-white rounded-3xl shadow p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-base font-semibold text-slate-900"><?php echo e($staff['first_name'] . ' ' . $staff['last_name']); ?></div>
                    <div class="text-sm text-slate-500"><?php echo e($staff['email']); ?></div>
                    <div class="text-sm text-slate-500"><?php echo e($staff['username']); ?></div>
                </div>
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?php echo !empty($staff['is_active']) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'; ?>">
                    <?php echo !empty($staff['is_active']) ? 'Active' : 'Inactive'; ?>
                </span>
            </div>
            <div class="mt-3 text-sm text-slate-600">Role: <?php echo e(labelize($staff['role_name'])); ?></div>
            <div class="mt-4 flex flex-col gap-2">
                <form method="POST" onsubmit="return confirm('Delete this <?php echo e($managedLabel); ?> account?');">
                    <input type="hidden" name="action" value="<?php echo e($deleteAction); ?>">
                    <input type="hidden" name="user_id" value="<?php echo (int)$staff['user_id']; ?>">
                    <button class="w-full bg-red-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-red-700">Delete</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    </div>

    <script>
        (function(){
            const pwd = document.getElementById('passwordInputUser');
            const togglePwd = document.getElementById('togglePasswordUser');
            const cpwd = document.getElementById('confirmPasswordUser');
            const toggleCpwd = document.getElementById('toggleConfirmPasswordUser');

            if (togglePwd && pwd) {
                togglePwd.addEventListener('click', () => {
                    const isHidden = pwd.type === 'password';
                    pwd.type = isHidden ? 'text' : 'password';
                    togglePwd.querySelector('i').className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
                });
            }

            if (toggleCpwd && cpwd) {
                toggleCpwd.addEventListener('click', () => {
                    const isHidden = cpwd.type === 'password';
                    cpwd.type = isHidden ? 'text' : 'password';
                    toggleCpwd.querySelector('i').className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
                });
            }
        })();
    </script>

<?php adminFooter(); ?>
