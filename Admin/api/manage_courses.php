<?php
require_once '../../config/db.php';

if ($_SESSION['role'] !== 'admin') {
    die('Access denied');
}

// Helper: Log activity
function logActivity($pdo, $actor, $action, $target, $details) {
    $check = $pdo->query("SELECT COUNT(*) FROM information_schema.tables 
                          WHERE table_schema = DATABASE() AND table_name = 'activity_logs'");
    if ($check->fetchColumn()) {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (actor_name, action, target, details, created_at) 
                               VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$actor, $action, $target, $details]);
    }
}

ob_start();
$redirect = "../manage_courses.php";
$message = "";
$messageType = "";

// === ASSIGN TEACHER ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['teacher_id']) && !empty($_POST['subject_id'])) {
    $teacherId = (int)$_POST['teacher_id'];
    $subjectId = (int)$_POST['subject_id'];

    $check = $pdo->prepare("SELECT id FROM teacher_subjects WHERE teacher_id = ? AND subject_id = ?");
    $check->execute([$teacherId, $subjectId]);

    if (!$check->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
        $stmt->execute([$teacherId, $subjectId]);

        $userStmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $userStmt->execute([$teacherId]);
        $u = $userStmt->fetch();

        $subjStmt = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
        $subjStmt->execute([$subjectId]);
        $s = $subjStmt->fetchColumn();

        $details = trim($u['first_name'] . ' ' . $u['last_name']) . ' → ' . $s;
        $actor = $_SESSION['full_name'] ?? 'Admin';

        logActivity($pdo, $actor, 'assign', 'teacher_subject', $details);
        $message = "Teacher assigned successfully.";
        $messageType = "success";
    } else {
        $message = "Already assigned.";
        $messageType = "warning";
    }
}

// === ENROLL STUDENT ===  ← FIXED HERE!
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['student_id']) && !empty($_POST['subject_id'])) {
    $studentId = (int)$_POST['student_id'];
    $subjectId = (int)$_POST['subject_id'];

    $check = $pdo->prepare("SELECT id FROM student_subjects WHERE student_id = ? AND subject_id = ?");
    $check->execute([$studentId, $subjectId]);

    if (!$check->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO student_subjects (student_id, subject_id) VALUES (?, ?)");
        $stmt->execute([$studentId, $subjectId]);

        $userStmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $userStmt->execute([$studentId]);  // ← WAS teacherId BEFORE! FIXED!
        $u = $userStmt->fetch();

        $subjStmt = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
        $subjStmt->execute([$subjectId]);
        $s = $subjStmt->fetchColumn();

        $details = trim($u['first_name'] . ' ' . $u['last_name']) . ' → ' . $s;
        $actor = $_SESSION['full_name'] ?? 'Admin';

        logActivity($pdo, $actor, 'enroll', 'student_subject', $details);
        $message = "Student enrolled successfully.";
        $messageType = "success";
    } else {
        $message = "Already enrolled.";
        $messageType = "warning";
    }
}

// === REMOVE ===
elseif (isset($_GET['remove'], $_GET['type']) && in_array($_GET['type'], ['teacher', 'student'])) {
    $id = (int)$_GET['remove'];
    $type = $_GET['type'];
    $table = $type === 'teacher' ? 'teacher_subjects' : 'student_subjects';
    $userCol = $type === 'teacher' ? 'teacher_id' : 'student_id';
    $action = $type === 'teacher' ? 'remove' : 'unenroll';
    $target = $type === 'teacher' ? 'teacher_subject' : 'student_subject';

    $q = $pdo->prepare("
        SELECT u.first_name, u.last_name, s.name AS subject_name
        FROM {$table} ts
        JOIN users u ON ts.{$userCol} = u.id
        JOIN subjects s ON ts.subject_id = s.id
        WHERE ts.id = ?
    ");
    $q->execute([$id]);
    $row = $q->fetch();

    if ($row) {
        $delete = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
        $delete->execute([$id]);

        $details = trim($row['first_name'] . ' ' . $row['last_name']) . ' → ' . $row['subject_name'];
        $actor = $_SESSION['full_name'] ?? 'Admin';
        logActivity($pdo, $actor, $action, $target, $details);

        $message = ucfirst($type) . " removed successfully.";
        $messageType = "success";
    }
}

// Flash message
if ($message) {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $messageType;
}

// Redirect
ob_clean();
header("Location: $redirect");
exit();