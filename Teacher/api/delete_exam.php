<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../exam.php");
    exit();
}

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM exams WHERE id = ? AND teacher_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);

header("Location: ../exam.php?deleted=1");
exit();
?>