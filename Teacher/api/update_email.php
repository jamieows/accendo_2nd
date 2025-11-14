<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../profile.php?action=email&status=error&msg=Invalid email format");
    exit();
}

// Check if email is already taken
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->execute([$email, $_SESSION['user_id']]);
if ($stmt->fetch()) {
    header("Location: ../profile.php?action=email&status=error&msg=Email already in use");
    exit();
}

// Update DB
$stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
$stmt->execute([$email, $_SESSION['user_id']]);

header("Location: ../profile.php?action=email&status=success");
exit();
?>