<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

$teacher_id       = $_SESSION['user_id'];
$subject_id       = $_POST['subject_id'] ?? '';
$title            = trim($_POST['title'] ?? '');
$google_form_link = trim($_POST['google_form_link'] ?? '');
$start_time       = $_POST['start_time'] ?? '';
$end_time         = $_POST['end_time'] ?? '';

if (empty($subject_id) || empty($title) || empty($google_form_link) || empty($start_time) || empty($end_time)) {
    die("All fields are required.");
}

if (strtotime($end_time) <= strtotime($start_time)) {
    die("End time must be after start time.");
}

// Validate Google Form URL
if (!preg_match('/^https?:\/\/(forms\.gle|docs\.google\.com\/forms)\//', $google_form_link)) {
    die("Please enter a valid Google Form link (forms.gle or docs.google.com/forms).");
}

$stmt = $pdo->prepare("
    INSERT INTO exams 
        (teacher_id, subject_id, title, google_form_link, start_time, end_time, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt->execute([$teacher_id, $subject_id, $title, $google_form_link, $start_time, $end_time]);

header("Location: ../exam.php?uploaded=1");
exit();
?>