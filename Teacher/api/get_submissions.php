<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    exit();
}

$assignment_id = $_GET['assignment_id'] ?? 0;
if (!$assignment_id) {
    echo json_encode([]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT s.file_path, s.submitted_at, s.is_late,
           u.first_name, u.last_name
    FROM assignment_submissions s
    JOIN users u ON s.student_id = u.id
    WHERE s.assignment_id = ?
    ORDER BY s.submitted_at DESC
");
$stmt->execute([$assignment_id]);
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($subs);
?>