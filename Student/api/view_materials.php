<?php
require_once '../../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['material_id'])) {
    echo json_encode(['success' => false, 'message' => 'Material ID required']);
    exit;
}

$materialId = (int)$_POST['material_id'];
$userId = $_SESSION['user_id'];

// Check if student has access to this material
$stmt = $pdo->prepare("
    SELECT m.id FROM materials m
    JOIN subjects s ON m.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    WHERE m.id = ? AND ss.student_id = ?
");
$stmt->execute([$materialId, $userId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Record view (optional)
$stmt = $pdo->prepare("INSERT IGNORE INTO material_views (material_id, student_id, viewed_at) VALUES (?, ?, NOW())");
$stmt->execute([$materialId, $userId]);

echo json_encode(['success' => true, 'message' => 'Material access logged']);
?>
