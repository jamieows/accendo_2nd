<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../my_courses.php");
    exit();
}

// === FORM DATA ===
$teacher_id  = $_SESSION['user_id'];
$subject_id  = $_POST['subject_id'] ?? '';
$title       = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($subject_id) || empty($title) || !isset($_FILES['file'])) {
    die("Missing data");
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    die("Upload error: " . $file['error']);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['pdf', 'doc', 'docx', 'mp4', 'webm', 'ogg'];
if (!in_array($ext, $allowed)) {
    die("Invalid file type: .$ext");
}

// === CORRECT PATH FROM api/ FOLDER ===
$uploadDir = "../../uploads/materials/";  // → D:\xampp\htdocs\accendo_2nd\uploads\materials\
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        die("Failed to create folder: $uploadDir");
    }
}

$filename = uniqid('mat_') . '.' . $ext;
$fullPath = $uploadDir . $filename;           // Physical path
$dbPath   = "uploads/materials/" . $filename; // Save in DB

// === MOVE FILE ===
if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
    die("Failed to save file: $fullPath");
}

// === INSERT INTO DB ===
$stmt = $pdo->prepare("
    INSERT INTO materials 
        (teacher_id, subject_id, title, description, file_path, uploaded_at) 
    VALUES (?, ?, ?, ?, ?, NOW())
");
$stmt->execute([$teacher_id, $subject_id, $title, $description, $dbPath]);

header("Location: ../my_courses.php?uploaded=1");
exit();
?>d