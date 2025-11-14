<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

$old = $_POST['old_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (empty($old) || empty($new) || empty($confirm)) {
    header("Location: ../profile.php?action=password&status=error&msg=All fields required");
    exit();
}
if ($new !== $confirm) {
    header("Location: ../profile.php?action=password&status=error&msg=New passwords do not match");
    exit();
}
if (strlen($new) < 6) {
    header("Location: ../profile.php?action=password&status=error&msg=Password too short");
    exit();
}

// Verify current password
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!password_verify($old, $user['password'])) {
    header("Location: ../profile.php?action=password&status=error&msg=Current password is wrong");
    exit();
}

// Update password
$hashed = password_hash($new, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->execute([$hashed, $_SESSION['user_id']]);

header("Location: ../profile.php?action=password&status=success");
exit();
?>