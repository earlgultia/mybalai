<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'mybalai_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check user role
function hasRole($roles) {
    if (!isLoggedIn()) return false;
    if (!is_array($roles)) $roles = [$roles];
    $userRoles = $_SESSION['user_roles'] ?? [];
    if (!is_array($userRoles)) {
        $userRoles = array_filter(array_map('trim', explode(',', (string)$userRoles)));
    }
    return (bool)array_intersect($roles, array_merge($userRoles, [$_SESSION['user_type'] ?? '']));
}

function getUserRoleNames($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.role_name
        FROM user_role_assignments ura
        JOIN roles r ON r.role_id = ura.role_id
        WHERE ura.user_id = ? AND ura.is_active = 1
        ORDER BY r.role_level DESC, r.role_name
    ");
    $stmt->execute([$user_id]);
    return array_column($stmt->fetchAll(), 'role_name');
}

function getRoleId($role_name) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT role_id FROM roles WHERE role_name = ? LIMIT 1");
    $stmt->execute([$role_name]);
    return $stmt->fetchColumn();
}

function getUserData($user_id = null) {
    global $pdo;
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    if (empty($user_id)) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

    function getSystemSetting($key, $default = null) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1");
            $stmt->execute([$key]);
            $value = $stmt->fetchColumn();
            return ($value === false || $value === null) ? $default : $value;
        } catch (Exception $e) {
            return $default;
        }
    }

function sessionUserTypeFromRoles($roles) {
    return in_array('resident', $roles, true) ? 'resident' : 'admin';
}

function refreshUserSessionRoles($user_id) {
    $roles = getUserRoleNames($user_id);
    $_SESSION['user_roles'] = $roles;
    $_SESSION['primary_role'] = $roles[0] ?? ($_SESSION['user_type'] ?? '');
    $_SESSION['user_type'] = sessionUserTypeFromRoles($roles);
    return $roles;
}

// Function to get user permissions
function hasPermission($permission_key) {
    if (!isLoggedIn()) return false;
    
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as has_permission 
        FROM user_role_assignments ura
        JOIN role_permissions rp ON ura.role_id = rp.role_id
        JOIN permissions p ON rp.permission_id = p.permission_id
        WHERE ura.user_id = ? AND ura.is_active = 1 AND p.permission_key = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $permission_key]);
    $result = $stmt->fetch();
    
    return $result['has_permission'] > 0;
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim((string)$input)));
}

// Function to log activity
function logActivity($user_id, $action, $entity_type = null, $entity_id = null, $details = null) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $stmt = $pdo->prepare("\n        INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, details) \n        VALUES (?, ?, ?, ?, ?, ?, ?)\n    ");
    $stmt->execute([$user_id, $action, $entity_type, $entity_id, $ip, $user_agent, $details]);
}

function ensureDocumentRequestPaymentColumns() {
    global $pdo;
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $columns = [
        'payment_method' => "ALTER TABLE document_requests ADD COLUMN payment_method ENUM('cash','gcash') NOT NULL DEFAULT 'cash' AFTER amount",
        'payment_proof' => "ALTER TABLE document_requests ADD COLUMN payment_proof VARCHAR(500) DEFAULT NULL AFTER payment_method",
        'payment_proof_status' => "ALTER TABLE document_requests ADD COLUMN payment_proof_status ENUM('none','submitted','verified','rejected') NOT NULL DEFAULT 'none' AFTER payment_proof",
        'payment_proof_submitted_at' => "ALTER TABLE document_requests ADD COLUMN payment_proof_submitted_at TIMESTAMP NULL DEFAULT NULL AFTER payment_proof_status",
        'payment_proof_reviewed_at' => "ALTER TABLE document_requests ADD COLUMN payment_proof_reviewed_at TIMESTAMP NULL DEFAULT NULL AFTER payment_proof_submitted_at",
        'payment_proof_reviewed_by' => "ALTER TABLE document_requests ADD COLUMN payment_proof_reviewed_by INT(11) DEFAULT NULL AFTER payment_proof_reviewed_at",
    ];

    foreach ($columns as $column => $sql) {
        try {
            $stmt = $pdo->prepare("SHOW COLUMNS FROM document_requests LIKE ?");
            $stmt->execute([$column]);
            if (!$stmt->fetchColumn()) {
                $pdo->exec($sql);
            }
        } catch (Exception $e) {
            // ignore schema bootstrap issues on read-only environments
        }
    }
}

function getDocumentRequestFee($documentType) {
    $fees = [
        'barangay_clearance' => 150,
        'certificate_of_residency' => 150,
        'certificate_of_indigency' => 100,
        'business_clearance' => 200,
        'business_permit' => 200,
        'sedula' => 100,
        'cedula' => 100,
    ];

    return (float)($fees[(string)$documentType] ?? 0);
}

function ensureTransactionDocumentTypeColumn() {
    global $pdo;
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM transactions LIKE 'document_type'");
        $stmt->execute();
        if (!$stmt->fetchColumn()) {
            $pdo->exec("ALTER TABLE transactions ADD COLUMN document_type VARCHAR(100) DEFAULT NULL AFTER transaction_type");
        }
    } catch (Exception $e) {
        // ignore schema bootstrap issues on read-only environments
    }
}

function hardDeleteUserAccount($userId, $mode = 'resident') {
    global $pdo;

    $userId = (int)$userId;
    if ($userId <= 0) {
        return false;
    }

    try {
        $pdo->beginTransaction();

        if ($mode === 'resident') {
            $residentCleanupTables = [
                'document_requests' => 'user_id',
                'appointments' => 'user_id',
                'complaints' => 'complainant_id',
                'subscriptions' => 'user_id',
                'transactions' => 'user_id',
            ];

            foreach ($residentCleanupTables as $table => $column) {
                $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$column` = ?");
                $stmt->execute([$userId]);
            }

            $stmt = $pdo->prepare("DELETE FROM resident_profiles WHERE user_id = ?");
            $stmt->execute([$userId]);
        } else {
            $nullifyQueries = [
                "UPDATE announcements SET created_by = NULL WHERE created_by = ?",
                "UPDATE appointments SET confirmed_by = NULL WHERE confirmed_by = ?",
                "UPDATE complaints SET assigned_staff_id = NULL WHERE assigned_staff_id = ?",
                "UPDATE document_requests SET processed_by = NULL, approved_by = NULL WHERE processed_by = ? OR approved_by = ?",
                "UPDATE transactions SET collected_by = NULL WHERE collected_by = ?",
                "UPDATE user_role_assignments SET assigned_by = NULL WHERE assigned_by = ?",
            ];

            foreach ($nullifyQueries as $sql) {
                $stmt = $pdo->prepare($sql);
                if (substr_count($sql, '?') === 2) {
                    $stmt->execute([$userId, $userId]);
                } else {
                    $stmt->execute([$userId]);
                }
            }

            $stmt = $pdo->prepare("DELETE FROM barangay_officials WHERE user_id = ?");
            $stmt->execute([$userId]);
        }

        $stmt = $pdo->prepare("DELETE FROM user_role_assignments WHERE user_id = ?");
        $stmt->execute([$userId]);

        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return false;
    }
}

// Function to generate unique reference number
function generateReferenceNumber($prefix = 'REF') {
    return $prefix . '-' . date('Ymd') . '-' . rand(1000, 9999);
}
?>
