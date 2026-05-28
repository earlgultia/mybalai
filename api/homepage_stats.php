<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$queries = [
    'active_residents' => "
        SELECT COUNT(DISTINCT u.user_id)
        FROM users u
        JOIN user_role_assignments ura ON ura.user_id = u.user_id AND ura.is_active = 1
        JOIN roles r ON r.role_id = ura.role_id
        WHERE r.role_name = 'resident' AND u.is_active = 1
    ",
    'document_requests' => "SELECT COUNT(*) FROM document_requests",
    'open_complaints' => "SELECT COUNT(*) FROM complaints WHERE status IN ('submitted', 'in_progress')",
    'appointments' => "SELECT COUNT(*) FROM appointments"
];

$stats = [];
foreach ($queries as $key => $sql) {
    $stats[$key] = (int) $pdo->query($sql)->fetchColumn();
}

echo json_encode($stats);
