<?php
require_once '../../config/db.php';

// Ensure student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Unauthorized access");
}

$uid = $_SESSION['user_id'];

// Fetch current password hash
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$uid]);
$current_hash = $stmt->fetchColumn();

// Prepare SQL updates
$updateFields = [];
$params = [];

// Update name & email
if (!empty($_POST['first_name']) && !empty($_POST['last_name']) && !empty($_POST['email'])) {
    $updateFields[] = "first_name = ?";
    $updateFields[] = "last_name = ?";
    $updateFields[] = "email = ?";
    $params[] = $_POST['first_name'];
    $params[] = $_POST['last_name'];
    $params[] = $_POST['email'];
}

// Handle profile image upload
if (!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] === 0) {
    $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
    $filename = "profile_" . uniqid() . "." . $ext;
    $destination = "../../uploads/" . $filename;

    if (!is_dir("../../uploads")) {
        mkdir("../../uploads", 0777, true);
    }

    move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination);
    $updateFields[] = "profile_image = ?";
    $params[] = $filename;
}

// Handle password change
if (!empty($_POST['old_password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password'])) {
    if (empty($_POST['old_password']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
        die("Please fill all password fields.");
    }

    if (!password_verify($_POST['old_password'], $current_hash)) {
        die("Current password is incorrect.");
    }

    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        die("New passwords do not match.");
    }

    if (strlen($_POST['new_password']) < 6) {
        die("New password must be at least 6 characters long.");
    }

    $new_hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $updateFields[] = "password = ?";
    $params[] = $new_hashed_password;
}

// If there are updates, execute
if (!empty($updateFields)) {
    $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
    $params[] = $uid;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo "Profile updated successfully!";
} else {
    echo "No changes detected.";
}
