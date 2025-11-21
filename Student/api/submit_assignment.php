<?php
require_once '../../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['assignment_id']) || !isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

$assignmentId = (int)$_POST['assignment_id'];
$userId = $_SESSION['user_id'];

// Check if already submitted
$stmt = $pdo->prepare("SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?");
$stmt->execute([$assignmentId, $userId]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Already submitted']);
    exit;
}

$file = $_FILES['file'];
if ($file['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf','doc','docx','txt','png','jpg','jpeg','gif','mp4','webm','ogg'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        exit;
    }

    // Create uploads folder if it doesn't exist
    $uploadDir = "../../uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // Create a safe unique filename
    $filename = "sub_{$userId}_" . uniqid() . "." . $ext;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Save relative path in DB (without ../../)
        $relativePath = "uploads/" . $filename;

        $stmt = $pdo->prepare("INSERT INTO submissions (assignment_id, student_id, file_path, submitted_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$assignmentId, $userId, $relativePath]);

        echo json_encode(['success' => true, 'message' => 'Assignment submitted successfully', 'file' => $relativePath]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'File upload error']);
}
