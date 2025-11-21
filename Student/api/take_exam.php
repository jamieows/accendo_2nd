<?php
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['exam_id'])) {
    echo json_encode(['success' => false, 'message' => 'Exam ID required']);
    exit;
}

$examId = (int)$_POST['exam_id'];
$userId = $_SESSION['user_id'];

// Check if exam is available
$stmt = $pdo->prepare("
    SELECT e.id FROM exams e
    JOIN subjects s ON e.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    WHERE e.id = ? AND ss.student_id = ? AND NOW() BETWEEN e.start_time AND e.end_time
");
$stmt->execute([$examId, $userId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Exam not available']);
    exit;
}

// Record attempt
$stmt = $pdo->prepare("INSERT IGNORE INTO exam_attempts (exam_id, student_id, taken_at) VALUES (?, ?, NOW())");
$stmt->execute([$examId, $userId]);

echo json_encode(['success' => true, 'message' => 'Exam access recorded']);
?>