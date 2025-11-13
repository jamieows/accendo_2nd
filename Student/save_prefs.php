<?php
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) exit;

$userId = $_SESSION['user_id'];
$updates = [];

if (isset($data['theme'])) {
    $updates['theme'] = $data['theme'] === 'dark' ? 'dark' : 'light';
}

if (isset($data['zoom'])) {
    $zoom = floatval($data['zoom']);
    $updates['zoom'] = max(0.8, min(1.5, $zoom));
}

if (!empty($updates)) {
    $setParts = [];
    $params = [':id' => $userId];
    foreach ($updates as $key => $val) {
        $setParts[] = "`$key` = :$key";
        $params[":$key"] = $val;
    }
    $sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}
?>
