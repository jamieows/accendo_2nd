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

if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $allowed = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg'];
    if (!in_array(strtolower($ext), $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        exit;
    }

    $path = "../../uploads/sub_" . uniqid() . "." . $ext;
    if (move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
        $stmt = $pdo->prepare("INSERT INTO submissions (assignment_id, student_id, file_path, submitted_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$assignmentId, $userId, $path]);
        echo json_encode(['success' => true, 'message' => 'Assignment submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'File upload error']);
}
?>
