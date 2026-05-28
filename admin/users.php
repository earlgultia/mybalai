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
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("UPDATE users SET is_active = 0, deleted_at = NOW() WHERE user_id = ?");
                    $stmt->execute([$staffId]);

                    $stmt = $pdo->prepare("
                        UPDATE user_role_assignments ura
                        JOIN roles r ON r.role_id = ura.role_id
                        SET ura.is_active = 0
                        WHERE ura.user_id = ? AND r.role_name = ?
                    ");
                    $stmt->execute([$staffId, $managedRole]);

                    logActivity($_SESSION['user_id'], 'Deleted ' . $managedLabel . ' account', 'users', $staffId, $staff['first_name'] . ' ' . $staff['last_name']);
                    $pdo->commit();
                    $message = $managedLabel . ' account deleted successfully.';
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
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
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Email or username is already registered.';
        } else {
            $roleId = getRoleId($roleName);
            if (!$roleId) {
                $error = 'Selected role was not found.';
            } else {
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("
                        INSERT INTO users (primary_role_id, username, email, password_hash, first_name, last_name, phone_number, is_active, is_verified, email_verified, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1, 1, ?)
                    ");
                    $stmt->execute([
                        $roleId,
                        $username,
                        $email,
                        password_hash($password, PASSWORD_DEFAULT),
                        $firstName,
                        $lastName,
                        $phone ?: null,
                        $_SESSION['user_id'],
                    ]);
                    $userId = (int)$pdo->lastInsertId();

                    $stmt = $pdo->prepare("INSERT INTO user_role_assignments (user_id, role_id, assigned_by, is_active) VALUES (?, ?, ?, 1)");
                    $stmt->execute([$userId, $roleId, $_SESSION['user_id']]);

                    logActivity($_SESSION['user_id'], 'Created staff account', 'users', $userId, $roleName);
                    $pdo->commit();
                    $message = $managedLabel . ' account created successfully.';
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $error = 'Account could not be created. Please try again.';
                }
            }
        }
    }
    }
}

$staffStmt = $pdo->prepare("
    SELECT u.user_id, u.first_name, u.last_name, u.email, u.username, u.is_active, u.created_at, r.role_name
    FROM users u
    JOIN user_role_assignments ura ON ura.user_id = u.user_id AND ura.is_active = 1
    JOIN roles r ON r.role_id = ura.role_id
    WHERE r.role_name = ? AND (u.deleted_at IS NULL OR u.deleted_at = '0000-00-00 00:00:00')
    ORDER BY u.created_at DESC
");
$staffStmt->execute([$managedRole]);
$staffUsers = $staffStmt->fetchAll();

adminHeader($managedLabel . ' Accounts', 'users');
?>
<?php if ($message): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4"><?php echo e($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4"><?php echo e($error); ?></div><?php endif; ?>

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
