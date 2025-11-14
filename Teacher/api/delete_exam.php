<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../exams.php");
    exit();
}

$id = $_GET['id'] ?? 0;
if (!$id || !is_numeric($id)) {
    header("Location: ../exams.php");
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM exams WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    header("Location: ../exams.php?deleted=1");
    exit();
} catch (Exception $e) {
    header("Location: ../exams.php?error=db");
    exit();
}
?>