<?php
// api/edit_assignment.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

$id          = $_POST['id'] ?? '';
$subject_id  = $_POST['subject_id'] ?? '';
$title       = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$due_date    = $_POST['due_date'] ?? '';

if (!$id || !$subject_id || !$title || !$due_date) {
    die('Missing required fields');
}

// Verify ownership
$stmt = $pdo->prepare("SELECT file_path FROM assignments WHERE id = ? AND teacher_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$assignment = $stmt->fetch();

if (!$assignment) {
    die('Assignment not found or not yours');
}

$oldFile = $assignment['file_path'];
$newFilePath = $oldFile; // keep old unless new uploaded

// ---- FILE UPLOAD ----
if (!empty($_FILES['attachment']['name'])) {
    $allowed = ['pdf','doc','docx','zip','png','jpg','jpeg'];
    $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        die('Invalid file type');
    }

    $targetDir = '../uploads/assignments/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

    $newFileName = $id . '_' . time() . '.' . $ext;
    $newFilePath = $targetDir . $newFileName;

    if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $newFilePath)) {
        die('File upload failed');
    }

    // delete old file if exists
    if ($oldFile && file_exists('../' . $oldFile)) {
        unlink('../' . $oldFile);
    }
}

// ---- UPDATE DB ----
$stmt = $pdo->prepare("
    UPDATE assignments
    SET subject_id = ?, title = ?, description = ?, due_date = ?, file_path = ?
    WHERE id = ? AND teacher_id = ?
");
$stmt->execute([$subject_id, $title, $description, $due_date, $newFilePath, $id, $_SESSION['user_id']]);

header("Location: ../Teacher/assignment.php");
exit();