<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$subject_id = $_POST['subject_id'] ?? '';
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$due_date = $_POST['due_date'] ?? '';

if (empty($subject_id) || empty($title) || empty($due_date)) {
    die("Missing fields");
}

$uploadDir = "../../uploads/assignments/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$file_path = null;
if (!empty($_FILES['attachment']['name'])) {
    $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
    $filename = uniqid('assign_') . '.' . $ext;
    $fullPath = $uploadDir . $filename;
    $dbPath = "uploads/assignments/" . $filename;

    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $fullPath)) {
        $file_path = $dbPath;
    }
}

$stmt = $pdo->prepare("
    INSERT INTO assignments 
        (teacher_id, subject_id, title, description, file_path, due_date) 
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([$teacher_id, $subject_id, $title, $description, $file_path, $due_date]);

header("Location: ../assignments.php?created=1");
exit();
?>