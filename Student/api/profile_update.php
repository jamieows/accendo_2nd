<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo 'Unauthorized access.';
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo 'User not found.';
    exit();
}

// Handle profile image upload
$upload_dir = '../../uploads/';
$profile_image = $user['profile_image']; // keep old if no new upload

if (!empty($_FILES['profile_image']['name'])) {
    $file = $_FILES['profile_image'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'prof_' . uniqid() . '.' . $ext;
    $filepath = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Delete old image
        if ($user['profile_image'] && file_exists($upload_dir . $user['profile_image'])) {
            @unlink($upload_dir . $user['profile_image']);
        }
        $profile_image = $filename;
    }
}

// Get form data
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$email = trim($_POST['email']);

// Check email uniqueness
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->execute([$email, $user_id]);
if ($stmt->fetch()) {
    echo 'Email already in use.';
    exit();
}

// Password change logic
$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$password_changed = false;

if (!empty($old_password) || !empty($new_password) || !empty($confirm_password)) {
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        echo 'All password fields are required.';
        exit();
    }

    if (!password_verify($old_password, $user['password'])) {
        echo 'Current password is incorrect.';
        exit();
    }

    if ($new_password !== $confirm_password) {
        echo 'New passwords do not match.';
        exit();
    }

    if (strlen($new_password) < 6) {
        echo 'New password must be at least 6 characters.';
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $password_changed = true;
} else {
    $hashed_password = $user['password'];
}

// Update database
try {
    $sql = "UPDATE users SET 
            first_name = ?, 
            last_name = ?, 
            email = ?, 
            password = ?, 
            profile_image = ? 
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $first_name,
        $last_name,
        $email,
        $password_changed ? $hashed_password : $user['password'],
        $profile_image,
        $user_id
    ]);

    // Update email in session
    $_SESSION['email'] = $email;

    echo 'success';
} catch (Exception $e) {
    echo 'Update failed. Please try again.';
}
?>