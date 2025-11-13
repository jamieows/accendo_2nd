<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$old_pass   = $_POST['old_password'] ?? '';
$new_pass   = $_POST['new_password'] ?? '';
$confirm_pass = $_POST['confirm_password'] ?? '';

if (empty($first_name) || empty($last_name) || empty($email)) {
    header("Location: ../profile.php?error=invalid");
    exit();
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../profile.php?error=invalid_email");
    exit();
}

// Check if email already exists (except current user)
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->execute([$email, $user_id]);
if ($stmt->fetch()) {
    header("Location: ../profile.php?error=email_exists");
    exit();
}

// Password change logic
$update_pass = false;
if (!empty($old_pass) || !empty($new_pass) || !empty($confirm_pass)) {
    if (empty($old_pass) || empty($new_pass) || empty($confirm_pass)) {
        header("Location: ../profile.php?error=fill_password");
        exit();
    }
    if ($new_pass !== $confirm_pass) {
        header("Location: ../profile.php?error=password_mismatch");
        exit();
    }
    if (strlen($new_pass) < 6) {
        header("Location: ../profile.php?error=short_password");
        exit();
    }

    // Verify old password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!password_verify($old_pass, $user['password'])) {
        header("Location: ../profile.php?error=wrong_password");
        exit();
    }

    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
    $update_pass = true;
}

// Update name/email
$stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
$stmt->execute([$first_name, $last_name, $email, $user_id]);

// Update password if needed
if ($update_pass) {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $user_id]);
}

// Update session name
$_SESSION['first_name'] = $first_name;
$_SESSION['last_name'] = $last_name;

header("Location: ../profile.php?success=1");
exit();
?>