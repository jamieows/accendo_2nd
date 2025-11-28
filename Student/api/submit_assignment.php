<?php
session_start();
require_once '../../config/db.php';  // This goes up to accendo_2nd/config/db.php

// Security: Only logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$assignment_id = $_POST['assignment_id'] ?? '';
if (empty($assignment_id) || !is_numeric($assignment_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid assignment']);
    exit();
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit();
}

$file = $_FILES['file'];
$originalName = $file['name'];
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

$allowed = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'gif', 'mp4', 'webm', 'ogg', 'zip'];
if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'File type not allowed']);
    exit();
}

// === CORRECT UPLOAD PATH ===
$uploadDir = "../../uploads/submissions/";  // Physical path
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = uniqid('sub_') . '_' . time() . '.' . $ext;
$fullPath = $uploadDir . $filename;

// Move the file
if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    exit();
}

// Path to save in database (relative from project root)
$dbPath = "uploads/submissions/" . $filename;

// Check if student already submitted
$stmt = $pdo->prepare("SELECT id FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?");
$stmt->execute([$assignment_id, $_SESSION['user_id']]);
$existing = $stmt->fetch();

try {
    if ($existing) {
        // Update existing submission
        $stmt = $pdo->prepare("UPDATE assignment_submissions SET file_path = ?, submitted_at = NOW() WHERE id = ?");
        $stmt->execute([$dbPath, $existing['id']]);
    } else {
        // Get due date to check if late
        $dueStmt = $pdo->prepare("SELECT due_date FROM assignments WHERE id = ?");
        $dueStmt->execute([$assignment_id]);
        $dueDate = $dueStmt->fetchColumn();
        $isLate = (!empty($dueDate) && new DateTime() > new DateTime($dueDate)) ? 1 : 0;

        // Insert new submission
        $stmt = $pdo->prepare("
            INSERT INTO assignment_submissions 
            (assignment_id, student_id, file_path, submitted_at, is_late) 
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $stmt->execute([$assignment_id, $_SESSION['user_id'], $dbPath, $isLate]);
    }

    echo json_encode(['success' => true, 'message' => 'Assignment submitted successfully!']);

} catch (Exception $e) {
    // If DB fails, delete the uploaded file to avoid junk
    if (file_exists($fullPath)) unlink($fullPath);
    error_log("Submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>