<?php
// student/api/profile_update.php
require_once '../../config/db.php';
session_start();

header('Content-Type: application/json');

// ========================================================
// 1. REQUEST & AUTHENTICATION
// ========================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

// ========================================================
// 2. FETCH CURRENT USER DATA
// ========================================================
$stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

// ========================================================
// 3. INPUT COLLECTION
// ========================================================
$first_name       = trim($_POST['first_name'] ?? '');
$last_name        = trim($_POST['last_name'] ?? '');
$email            = trim($_POST['email'] ?? '');
$old_password     = $_POST['old_password'] ?? '';
$new_password     = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// ========================================================
// 4. VALIDATION: REQUIRED FIELDS
// ========================================================
if ($first_name === '') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'First name is required',
        'field'   => 'first_name'
    ]);
    exit;
}

if ($last_name === '') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Last name is required',
        'field'   => 'last_name'
    ]);
    exit;
}

if ($email === '') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Email address is required',
        'field'   => 'email'
    ]);
    exit;
}

// ========================================================
// 5. VALIDATION: EMAIL FORMAT
// ========================================================
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid email format',
        'field'   => 'email'
    ]);
    exit;
}

// ========================================================
// 6. VALIDATION: EMAIL UNIQUENESS (skip current user)
// ========================================================
if ($email !== $user['email']) {
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->execute([$email, $userId]);
    if ($check->fetch()) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'This email is already taken',
            'field'   => 'email'
        ]);
        exit;
    }
}

// ========================================================
// 7. PASSWORD CHANGE LOGIC (only if new password given)
// ========================================================
$updates = [
    'first_name' => $first_name,
    'last_name'  => $last_name,
    'email'      => $email
];

if (!empty($new_password)) {

    // 7.1 Current password required
    if (empty($old_password)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Current password is required',
            'field'   => 'old_password'
        ]);
        exit;
    }

    // 7.2 Confirm password match
    if ($new_password !== $confirm_password) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Passwords do not match',
            'field'   => 'confirm_password'
        ]);
        exit;
    }

    // 7.3 Verify current password
    if (!password_verify($old_password, $user['password'])) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Current password is incorrect',
            'field'   => 'old_password'
        ]);
        exit;
    }

    // 7.4 Minimum length
    if (strlen($new_password) < 8) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Password must be at least 8 characters',
            'field'   => 'new_password'
        ]);
        exit;
    }

    // 7.5 Hash new password
    $updates['password'] = password_hash($new_password, PASSWORD_BCRYPT);
}

// ========================================================
// 8. DATABASE UPDATE
// ========================================================
if (empty($updates)) {
    echo json_encode(['status' => 'success']);
    exit;
}

$setParts = [];
$params   = [':id' => $userId];

foreach ($updates as $column => $value) {
    $placeholder = ':' . $column;
    $setParts[] = "`$column` = $placeholder";
    $params[$placeholder] = $value;
}

$sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = :id";
$updateStmt = $pdo->prepare($sql);

if ($updateStmt->execute($params)) {
    // Update session name
    $_SESSION['name'] = "$first_name $last_name";
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
}
exit;