<?php
// config/db.php
// Accendo LMS - Core Configuration (PH Ready)
// Time: November 11, 2025 03:24 PM PST (PH Time: November 12, 2025 07:24 AM PHT)

date_default_timezone_set('Asia/Manila');   // Philippines Standard Time

// === FIX: Start session only if not already active ===
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// TEMP: show all errors while debugging (remove/disable in production)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// === DATABASE CONNECTION ===
$host     = 'localhost';
$dbname   = 'accendo_db';
$username = 'root';
$password = '';  // Default XAMPP

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

// === SECURITY: Regenerate session ID on first login ===
if (isset($_SESSION['user_id']) && !isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// === SMART REDIRECT FUNCTION (NO LOOPS) ===
function redirectByRole() {
    // Must be logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        safeRedirect('../Auth/login.php');
        exit();
    }

    $role = $_SESSION['role'];
    $currentScript = $_SERVER['PHP_SELF'];  // e.g., /ACCENDO_2ND/Student/index.php

    // Define correct dashboard for each role
    $dashboards = [
        'admin'   => '/Admin/index.php',
        'teacher' => '/Teacher/index.php',
        'student' => '/Student/index.php'
    ];

    $target = $dashboards[$role] ?? '/Auth/login.php';

    // Prevent redirect loop: only redirect if NOT already on correct page
    $isOnCorrectPage = (
        ($role === 'admin'   && str_ends_with($currentScript, '/Admin/index.php')) ||
        ($role === 'teacher' && str_ends_with($currentScript, '/Teacher/index.php')) ||
        ($role === 'student' && str_ends_with($currentScript, '/Student/index.php'))
    );

    if (!$isOnCorrectPage) {
        safeRedirect('..' . $target);
        exit();
    }
}

// === HELPER: Safe redirect with relative path fix ===
function safeRedirect($url) {
    // Ensure clean URL (no double slashes, etc.)
    $url = preg_replace('#/+#', '/', $url);
    header("Location: $url", true, 302);
}

// === AUTO UPGRADE PLAIN-TEXT PASSWORDS (on first login) ===
function upgradePlainPassword($userId, $plainPassword) {
    global $pdo;
    $hashed = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $userId]);
}
?>