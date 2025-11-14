<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../exams.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../exams.php");
    exit();
}

$subject_id = $_POST['subject_id'] ?? '';
$title      = trim($_POST['title'] ?? '');
$link       = trim($_POST['google_form_link'] ?? '');
$start      = $_POST['start_time'] ?? '';
$end        = $_POST['end_time'] ?? '';

if (empty($subject_id) || empty($title) || empty($link) || empty($start) || empty($end)) {
    header("Location: ../exams.php?error=missing");
    exit();
}
if (!filter_var($link, FILTER_VALIDATE_URL)) {
    header("Location: ../exams.php?error=invalid_link");
    exit();
}
if (strtotime($start) >= strtotime($end)) {
    header("Location: ../exams.php?error=time");
    exit();
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO exams (teacher_id, subject_id, title, google_form_link, start_time, end_time)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $subject_id, $title, $link, $start, $end]);
    header("Location: ../exams.php?uploaded=1");
    exit();
} catch (Exception $e) {
    header("Location: ../exams.php?error=db");
    exit();
}
?>