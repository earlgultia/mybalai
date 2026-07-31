<?php
if (session_status() === PHP_SESSION_NONE) {
    $isSecureRequest = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isSecureRequest,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$host = 'localhost';
$dbname = 'mybalai_db';
$username = 'root';
$password = '';

class MyBalaiPDO extends PDO {
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false {
        try {
            if ($fetchMode === null) {
                return parent::query($query);
            }
            return parent::query($query, $fetchMode, ...$fetchModeArgs);
        } catch (Throwable $e) {
            if ($this->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
                throw $e;
            }
            return parent::query('SELECT 1 WHERE 0');
        }
    }

    public function prepare(string $query, array $options = []): PDOStatement|false {
        try {
            return parent::prepare($query, $options);
        } catch (Throwable $e) {
            if ($this->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
                throw $e;
            }
            $placeholderCount = substr_count($query, '?');
            $fallbackColumns = $placeholderCount > 0 ? implode(', ', array_fill(0, $placeholderCount, '?')) : '1';
            return parent::prepare("SELECT $fallbackColumns WHERE 0");
        }
    }
}

try {
    $pdo = new MyBalaiPDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Fallback to a local SQLite file so the app can run without importing the full SQL dump.
    // This creates a minimal schema used by the app to avoid runtime errors.
    $dataDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0755, true);
    }
    $sqlitePath = $dataDir . DIRECTORY_SEPARATOR . 'mybalai.sqlite';
    try {
        $pdo = new MyBalaiPDO('sqlite:' . $sqlitePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Initialize minimal tables if they don't exist.
        $initSql = [
            "CREATE TABLE IF NOT EXISTS users (user_id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, password TEXT, user_type TEXT, user_roles TEXT, is_active INTEGER DEFAULT 1);",
            "CREATE TABLE IF NOT EXISTS roles (role_id INTEGER PRIMARY KEY AUTOINCREMENT, role_name TEXT UNIQUE, role_level INTEGER DEFAULT 0);",
            "CREATE TABLE IF NOT EXISTS user_role_assignments (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, role_id INTEGER, is_active INTEGER DEFAULT 1);",
            "CREATE TABLE IF NOT EXISTS permissions (permission_id INTEGER PRIMARY KEY AUTOINCREMENT, permission_key TEXT UNIQUE);",
            "CREATE TABLE IF NOT EXISTS role_permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, role_id INTEGER, permission_id INTEGER);",
            "CREATE TABLE IF NOT EXISTS system_settings (setting_key TEXT PRIMARY KEY, setting_value TEXT);",
            "CREATE TABLE IF NOT EXISTS activity_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, action TEXT, entity_type TEXT, entity_id INTEGER, ip_address TEXT, user_agent TEXT, details TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP);",
            "CREATE TABLE IF NOT EXISTS user_role_assignments (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, role_id INTEGER, is_active INTEGER DEFAULT 1);",
            "CREATE TABLE IF NOT EXISTS document_requests (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, amount REAL DEFAULT 0);",
            "CREATE TABLE IF NOT EXISTS transactions (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, transaction_type TEXT, amount REAL DEFAULT 0);",
        ];

        foreach ($initSql as $sql) {
            try {
                $pdo->exec($sql);
            } catch (Exception $ex) {
                // ignore individual table creation failures
            }
        }

        // Upgrade an existing minimal fallback database without destroying local data.
        $sqliteMigrations = [
            "ALTER TABLE users ADD COLUMN primary_role_id INTEGER",
            "ALTER TABLE users ADD COLUMN email TEXT",
            "ALTER TABLE users ADD COLUMN password_hash TEXT",
            "ALTER TABLE users ADD COLUMN first_name TEXT",
            "ALTER TABLE users ADD COLUMN last_name TEXT",
            "ALTER TABLE users ADD COLUMN phone_number TEXT",
            "ALTER TABLE users ADD COLUMN is_verified INTEGER DEFAULT 0",
            "ALTER TABLE users ADD COLUMN email_verified INTEGER DEFAULT 0",
            "ALTER TABLE users ADD COLUMN created_by INTEGER",
            "ALTER TABLE users ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP",
            "ALTER TABLE users ADD COLUMN deleted_at DATETIME",
            "ALTER TABLE user_role_assignments ADD COLUMN assigned_by INTEGER",
        ];
        foreach ($sqliteMigrations as $migration) {
            try {
                $pdo->exec($migration);
            } catch (Exception $ex) {
                // The column may already exist; continue with the remaining migrations.
            }
        }

        $pdo->exec("INSERT OR IGNORE INTO roles (role_id, role_name, role_level) VALUES
            (1, 'super_admin', 100),
            (2, 'barangay_captain', 90),
            (3, 'barangay_secretary', 80),
            (4, 'barangay_treasurer', 80),
            (9, 'resident', 10)");

    } catch (Exception $ex) {
        // If SQLite also fails, stop with original error message.
        die("Connection failed: " . $e->getMessage());
    }
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

function isAdminSession() {
    if (!isLoggedIn()) return false;
    if (($_SESSION['user_type'] ?? '') === 'admin') return true;

    $administrativeRoles = [
        'super_admin',
        'barangay_captain',
        'barangay_secretary',
        'barangay_treasurer',
        'barangay_kagawad',
        'health_worker',
        'tanod',
        'admin_staff',
    ];
    $userRoles = $_SESSION['user_roles'] ?? [];
    if (!is_array($userRoles)) {
        $userRoles = array_filter(array_map('trim', explode(',', (string)$userRoles)));
    }

    return (bool)array_intersect($administrativeRoles, array_merge($userRoles, [$_SESSION['user_type'] ?? '']));
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
    $administrativeRoles = [
        'super_admin',
        'barangay_captain',
        'barangay_secretary',
        'barangay_treasurer',
        'barangay_kagawad',
        'health_worker',
        'tanod',
        'admin_staff',
    ];
    return (bool)array_intersect($administrativeRoles, $roles) ? 'admin' : 'resident';
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
    
    return !empty($result['has_permission']);
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Read helpers keep dashboards usable with a legacy/local database without
// changing its schema or data when an optional table or column is unavailable.
function dbFetchAll($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function dbFetchOne($sql, $params = []) {
    $rows = dbFetchAll($sql, $params);
    return $rows[0] ?? [];
}

function dbFetchColumn($sql, $params = [], $default = 0) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $value = $stmt->fetchColumn();
        return $value === false || $value === null ? $default : $value;
    } catch (Throwable $e) {
        return $default;
    }
}

// Function to sanitize input
function sanitize($input) {
    return trim((string)$input);
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
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
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
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
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
