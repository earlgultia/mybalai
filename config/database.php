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
    return htmlspecialchars(strip_tags(trim($input)));
}

// Function to get user data
function getUserData($user_id = null) {
    global $pdo;
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'];
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Function to log activity
function logActivity($user_id, $action, $entity_type = null, $entity_id = null, $details = null) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, details) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $action, $entity_type, $entity_id, $ip, $user_agent, $details]);
}

// Function to generate unique reference number
function generateReferenceNumber($prefix = 'REF') {
    return $prefix . '-' . date('Ymd') . '-' . rand(1000, 9999);
}
?>
