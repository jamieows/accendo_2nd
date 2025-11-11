<?php
require_once '../../config/db.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../../Auth/login.php'); exit;
}

$uid = (int)$_SESSION['user_id'];

// handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // delete record and any uploaded file
    $stmt = $pdo->prepare("SELECT file_path FROM assignments WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$id, $uid]);
    $fp = $stmt->fetchColumn();
    $pdo->prepare("DELETE FROM assignments WHERE id = ? AND teacher_id = ?")->execute([$id, $uid]);
    if ($fp) {
        $path = dirname(__DIR__,2) . DIRECTORY_SEPARATOR . ltrim($fp, "/\\");