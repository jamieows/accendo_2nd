<?php
// Teacher/api/delete_exam.php
// AJAX endpoint to delete an exam (used by exams.php)

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once '../../config/db.php';  // Important: goes up two levels from Teacher/api/

// Security: Only allow logged-in teachers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get and validate exam ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid or missing exam ID']);
    exit();
}

$exam_id = (int)$_GET['id'];
$teacher_id = $_SESSION['user_id'];

try {
    // Optional: Double-check ownership (security best practice)
    $check = $pdo->prepare("SELECT id FROM exams WHERE id = ? AND teacher_id = ?");
    $check->execute([$exam_id, $teacher_id]);

    if ($check->rowCount() === 0) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Exam not found or you do not have permission']);
        exit();
    }

    // Perform deletion
    // Note: exam_attempts will be auto-deleted thanks to ON DELETE CASCADE in your DB
    $delete = $pdo->prepare("DELETE FROM exams WHERE id = ? AND teacher_id = ?");
    $deleted = $delete->execute([$exam_id, $teacher_id]);

    if ($deleted && $delete->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Exam deleted successfully'
        ]);
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to delete exam']);
    }

} catch (Exception $e) {
    // Log error in production (optional)
    error_log("Delete exam error: " . $e->getMessage());

    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>