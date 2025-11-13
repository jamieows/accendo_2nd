<?php
require_once '../../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

// Count pending assignments
$stmt = $pdo->prepare("
    SELECT COUNT(*) as pending
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
    WHERE ss.student_id = ? AND sub.id IS NULL AND a.due_date > NOW()
");
$stmt->execute([$userId, $userId]);
$assignments = $stmt->fetch()['pending'];

// Next exam
$stmt = $pdo->prepare("
    SELECT e.title, e.start_time
    FROM exams e
    JOIN subjects s ON e.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    WHERE ss.student_id = ? AND e.start_time > NOW()
    ORDER BY e.start_time ASC
    LIMIT 1
");
$stmt->execute([$userId]);
$exam = $stmt->fetch();
$nextExam = $exam ? date('M j', strtotime($exam['start_time'])) : null;

echo json_encode([
    'assignments' => $assignments,
    'next_exam' => $nextExam
]);
?>
