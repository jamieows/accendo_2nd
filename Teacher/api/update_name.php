<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');

if (empty($first_name) || empty($last_name)) {
    header("Location: ../profile.php?action=name&status=error&msg=Both names are required");
    exit();
}

// Update DB
$stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
$stmt->execute([$first_name, $last_name, $_SESSION['user_id']]);

// Update session
$_SESSION['first_name'] = $first_name;
$_SESSION['last_name'] = $last_name;

header("Location: ../profile.php?action=name&status=success");
exit();
?>