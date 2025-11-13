<?php
session_start(); // ← Must start session first
require_once '../config/db.php'; // ← Path from api/ to root

// === SECURITY CHECK ===
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

// === GET METHOD: Delete via URL (e.g., ?delete=123) ===
if (isset($_GET['delete'])) {
    $materialId = $_GET['delete'];

    // === 1. Verify ownership ===
    $stmt = $pdo->prepare("SELECT file_path FROM materials WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$materialId, $_SESSION['user_id']]);
    $material = $stmt->fetch();

    if (!$material) {
        header("Location: ../my_courses.php?error=not_found");
        exit();
    }

    // === 2. Delete file from server ===
    $fileFullPath = "../" . $material['file_path']; // e.g., ../uploads/materials/file.pdf
    if ($material['file_path'] && file_exists($fileFullPath)) {
        unlink($fileFullPath);
    }

    // === 3. Delete from database ===
    $deleteStmt = $pdo->prepare("DELETE FROM materials WHERE id = ? AND teacher_id = ?");
    $deleteStmt->execute([$materialId, $_SESSION['user_id']]);

    // === 4. Redirect with success ===
    header("Location: ../my_courses.php?deleted=1");
    exit();
}

// === POST METHOD: Optional AJAX delete (you can ignore if not using) ===
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

    // For AJAX: return JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
}

// === Fallback: if no action, redirect ===
header("Location: ../my_courses.php");
exit();
?>