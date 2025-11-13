<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../assignments.php");
    exit();
}

$id = $_POST['id'] ?? '';
$subject_id = $_POST['subject_id'] ?? '';
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$due_date = $_POST['due_date'] ?? '';

if (empty($id) || empty($subject_id) || empty($title) || empty($due_date)) {
    die("Invalid data");
}

// Get old file
$stmt = $pdo->prepare("SELECT file_path FROM assignments WHERE id = ? AND teacher_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$old = $stmt->fetch();

$file_path = $old['file_path'];

$uploadDir = "../../uploads/assignments/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (!empty($_FILES['attachment']['name'])) {
    $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
    $filename = uniqid('assign_') . '.' . $ext;
    $fullPath = $uploadDir . $filename;
    $dbPath = "uploads/assignments/" . $filename;

    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $fullPath)) {
        if ($old['file_path'] && file_exists("../../" . $old['file_path'])) {
            unlink("../../" . $old['file_path']);
        }
        $file_path = $dbPath;
    }
}

$stmt = $pdo->prepare("
    UPDATE assignments 
    SET subject_id = ?, title = ?, description = ?, file_path = ?, due_date = ?
    WHERE id = ? AND teacher_id = ?
");
$stmt->execute([$subject_id, $title, $description, $file_path, $due_date, $id, $_SESSION['user_id']]);

header("Location: ../assignments.php");
exit();
?>