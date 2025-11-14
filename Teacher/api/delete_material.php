<?php
session_start();
require_once '../config/db.php'; // ← Now $pdo is available!

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $materialId = $_GET['delete'];

    $stmt = $pdo->prepare("SELECT file_path FROM materials WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$materialId, $_SESSION['user_id']]);
    $material = $stmt->fetch();

    if (!$material) {
        header("Location: ../my_courses.php?error=not_found");
        exit();
    }

    $fileFullPath = "../" . $material['file_path'];
    if ($material['file_path'] && file_exists($fileFullPath)) {
        unlink($fileFullPath);
    }

    $deleteStmt = $pdo->prepare("DELETE FROM materials WHERE id = ? AND teacher_id = ?");
    $deleteStmt->execute([$materialId, $_SESSION['user_id']]);

    header("Location: ../my_courses.php?deleted=1");
    exit();
}

// POST (AJAX) – optional
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['material_id'])) {
    $materialId = $_POST['material_id'];

    $stmt = $pdo->prepare("SELECT file_path FROM materials WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$materialId, $_SESSION['user_id']]);
    $material = $stmt->fetch();

    if ($material) {
        $fileFullPath = "../" . $material['file_path'];
        if ($material['file_path'] && file_exists($fileFullPath)) {
            unlink($fileFullPath);
        }

        $deleteStmt = $pdo->prepare("DELETE FROM materials WHERE id = ? AND teacher_id = ?");
        $deleteStmt->execute([$materialId, $_SESSION['user_id']]);
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
}

header("Location: ../my_courses.php");
exit();
?>