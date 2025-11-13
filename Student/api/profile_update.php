<?php
// student/api/profile_update.php
require_once '../../config/db.php';   // adjust if your db.php lives elsewhere
session_start();

header('Content-Type: application/json');

// ---------------------------------------------------
// 1. BASIC SECURITY
// ---------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

// ---------------------------------------------------
// 2. FETCH CURRENT USER
// ---------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

// ---------------------------------------------------
// 3. COLLECT & VALIDATE INPUT
// ---------------------------------------------------
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');

$old_password       = $_POST['old_password'] ?? '';
$new_password       = $_POST['new_password'] ?? '';
$confirm_password   = $_POST['confirm_password'] ?? '';

if ($first_name === '' || $last_name === '' || $email === '') {
    echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled']);
    exit;
}

// E-mail format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid e-mail address']);
    exit;
}

// ---------------------------------------------------
// 4. BUILD UPDATE ARRAY
// ---------------------------------------------------
$updates = [
    'first_name' => $first_name,
    'last_name'  => $last_name,
    'email'      => $email,
];
$params = [':id' => $userId];

// ---- E-MAIL UNIQUENESS (except current user) ----
if ($email !== $user['email']) {
    $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $chk->execute([$email, $userId]);
    if ($chk->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'E-mail already taken']);
        exit;
    }
}

// ---- PASSWORD CHANGE (optional) ----
if (!empty($new_password)) {
    if (empty($old_password)) {
        echo json_encode(['status' => 'error', 'message' => 'Current password required']);
        exit;
    }
    if ($new_password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'New passwords do not match']);
        exit;
    }
    if (!password_verify($old_password, $user['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Incorrect current password']);
        exit;
    }
    if (strlen($new_password) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'New password ≥ 8 characters']);
        exit;
    }

    $updates['password'] = password_hash($new_password, PASSWORD_BCRYPT);
}

// ---- PROFILE IMAGE ----
$profileImage = null;
if (!empty($_FILES['profile_image']['name'])) {
    $file = $_FILES['profile_image'];

    // allowed types & max 5 MB
    $allowed = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024;

    if (!in_array($file['type'], $allowed)) {
        echo json_encode(['status' => 'error', 'message' => 'Only JPG, PNG, GIF allowed']);
        exit;
    }
    if ($file['size'] > $maxSize) {
        echo json_encode(['status' => 'error', 'message' => 'File too large (max 5 MB)']);
        exit;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Upload error']);
        exit;
    }

    // unique filename + keep extension
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $userId . '_' . time() . '.' . $ext;
    $dest     = '../../uploads/' . $filename;   // <-- make sure this folder exists & is writable

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save image']);
        exit;
    }

    // delete old image if any
    if (!empty($user['profile_image'])) {
        $old = '../../uploads/' . $user['profile_image'];
        if (file_exists($old)) @unlink($old);
    }

    $updates['profile_image'] = $filename;
}

// ---------------------------------------------------
// 5. BUILD & EXECUTE SQL
// ---------------------------------------------------
if (empty($updates)) {
    // nothing changed – still a success for UX
    echo json_encode(['status' => 'success']);
    exit;
}

$setParts = [];
foreach ($updates as $col => $val) {
    $placeholder          = ':' . $col;
    $setParts[]           = "`$col` = $placeholder";
    $params[$placeholder] = $val;
}

$sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = :id";

$upd = $pdo->prepare($sql);
$ok  = $upd->execute($params);

if ($ok) {
    // refresh session name
    $_SESSION['name'] = $first_name . ' ' . $last_name;

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
}
exit;