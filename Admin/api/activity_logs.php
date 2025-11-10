<?php
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$rows = [];

// Check if activity_logs table exists
$tblStmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'activity_logs'");
$tblStmt->execute();
$hasActivityTable = (bool) $tblStmt->fetchColumn();

if ($hasActivityTable) {
    // Return latest 200 events
    $q = "SELECT id, actor_name, action, target, details, created_at FROM activity_logs ORDER BY created_at DESC LIMIT 200";
    $s = $pdo->query($q);
    $rows = $s->fetchAll();
} else {
    // Fallback: return recent user registrations as 'create' events
    $s = $pdo->query("SELECT id, first_name, last_name, username, created_at FROM users ORDER BY created_at DESC LIMIT 200");
    while ($u = $s->fetch()) {
        $rows[] = [
            'id' => 'user_' . ($u['id'] ?? ''),
            'actor_name' => $u['first_name'] . ' ' . $u['last_name'],
            'action' => 'create',
            'target' => 'user_account',
            'details' => $u['username'],
            'created_at' => $u['created_at']
        ];
    }
}

// Convert time to Asia/Manila formatted string for convenience
foreach ($rows as &$r) {
    try {
        $dt = new DateTime($r['created_at'], new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone('Asia/Manila'));
        $r['created_at'] = $dt->format('M j, Y g:i A');
    } catch (Exception $e) {
        // leave as-is
    }
    // ensure every row has an id for client-side diffing
    if (empty($r['id'])) {
        $r['id'] = md5(($r['actor_name'] ?? '') . '|' . ($r['action'] ?? '') . '|' . ($r['target'] ?? '') . '|' . ($r['details'] ?? '') . '|' . ($r['created_at'] ?? ''));
    }
}

echo json_encode(array_values($rows));
