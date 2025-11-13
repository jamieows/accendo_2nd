<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

$old_pass = $_POST['old_password'] ?? '';
$new_pass = $_POST['new_password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

if (empty($old_pass) || empty($new_pass) || empty($confirm)) {
    header("Location: ../profile.php?action=password&status=error&msg=All fields required");
    exit();
}
if ($new_pass !== $confirm) {
    header("Location: ../profile.php?action=password&status=error&msg=Passwords do not match");
    exit();
}
if (strlen($new_pass) < 6) {
    header("Location: ../profile.php?action=password&status=error&msg=Password too short");
    exit();
}

// Verify old password
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!password_verify($old_pass, $user['password'])) {
    header("Location: ../profile.php?action=password&status=error&msg=Current password incorrect");
    exit();
}

$hashed = password_hash($new_pass, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->execute([$hashed, $_SESSION['user_id']]);

header("Location: ../profile.php?action=password&status=success");
exit();
?>