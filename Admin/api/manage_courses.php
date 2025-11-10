<?php
require_once '../../config/db.php';
if ($_SESSION['role'] !== 'admin') die('Access denied');

// === TEACHER ASSIGNMENT ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teacher_id'])) {
    $teacherId = (int)$_POST['teacher_id'];
    $subjectId = (int)$_POST['subject_id'];
    
    // Check if already assigned
    $check = $pdo->prepare("SELECT id FROM teacher_subjects WHERE teacher_id = ? AND subject_id = ?");
    $check->execute([$teacherId, $subjectId]);
    if (!$check->fetch()) {
        $pdo->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)")
            ->execute([$teacherId, $subjectId]);
        // Log activity if activity_logs table exists
        $actor = $_SESSION['name'] ?? 'System';
        // fetch names for details
        $t = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $t->execute([$teacherId]); $tn = $t->fetch();
        $s = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
        $s->execute([$subjectId]); $sn = $s->fetchColumn();
        $details = trim(($tn['first_name'] ?? '') . ' ' . ($tn['last_name'] ?? '')) . ' -> ' . ($sn ?? '');
        $tbl = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'activity_logs'");
        $tbl->execute();
        if ($tbl->fetchColumn()) {
            $ins = $pdo->prepare("INSERT INTO activity_logs (actor_name, action, target, details) VALUES (?, ?, ?, ?)");
            $ins->execute([$actor, 'assign', 'teacher_subject', $details]);
        }
    }
}

// === STUDENT ENROLLMENT ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $studentId = (int)$_POST['student_id'];
    $subjectId = (int)$_POST['subject_id'];
    
    // Check if already enrolled
    $check = $pdo->prepare("SELECT id FROM student_subjects WHERE student_id = ? AND subject_id = ?");
    $check->execute([$studentId, $subjectId]);
    if (!$check->fetch()) {
        $pdo->prepare("INSERT INTO student_subjects (student_id, subject_id) VALUES (?, ?)")
            ->execute([$studentId, $subjectId]);
        // Log activity
        $actor = $_SESSION['name'] ?? 'System';
        $st = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $st->execute([$studentId]); $snm = $st->fetch();
        $s = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
        $s->execute([$subjectId]); $subName = $s->fetchColumn();
        $details = trim(($snm['first_name'] ?? '') . ' ' . ($snm['last_name'] ?? '')) . ' -> ' . ($subName ?? '');
        $tbl = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'activity_logs'");
        $tbl->execute();
        if ($tbl->fetchColumn()) {
            $ins = $pdo->prepare("INSERT INTO activity_logs (actor_name, action, target, details) VALUES (?, ?, ?, ?)");
            $ins->execute([$actor, 'enroll', 'student_subject', $details]);
        }
    }
}

// === REMOVE ASSIGNMENT ===
if (isset($_GET['remove']) && isset($_GET['type'])) {
    $id = (int)$_GET['remove'];
    $type = $_GET['type'];
    
    if ($type === 'teacher') {
        // capture details before delete
        $q = $pdo->prepare("SELECT ts.teacher_id, ts.subject_id, u.first_name, u.last_name, s.name as subject_name FROM teacher_subjects ts JOIN users u ON ts.teacher_id=u.id JOIN subjects s ON ts.subject_id=s.id WHERE ts.id = ?");
        $q->execute([$id]);
        $row = $q->fetch();
        $pdo->prepare("DELETE FROM teacher_subjects WHERE id = ?")->execute([$id]);
        if ($row) {
            $actor = $_SESSION['name'] ?? 'System';
            $details = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) . ' -> ' . ($row['subject_name'] ?? '');
            $tbl = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'activity_logs'");
            $tbl->execute();
            if ($tbl->fetchColumn()) {
                $ins = $pdo->prepare("INSERT INTO activity_logs (actor_name, action, target, details) VALUES (?, ?, ?, ?)");
                $ins->execute([$actor, 'remove', 'teacher_subject', $details]);
            }
        }
    } elseif ($type === 'student') {
        $q = $pdo->prepare("SELECT ss.student_id, ss.subject_id, u.first_name, u.last_name, s.name as subject_name FROM student_subjects ss JOIN users u ON ss.student_id=u.id JOIN subjects s ON ss.subject_id=s.id WHERE ss.id = ?");
        $q->execute([$id]);
        $row = $q->fetch();
        $pdo->prepare("DELETE FROM student_subjects WHERE id = ?")->execute([$id]);
        if ($row) {
            $actor = $_SESSION['name'] ?? 'System';
            $details = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) . ' -> ' . ($row['subject_name'] ?? '');
            $tbl = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'activity_logs'");
            $tbl->execute();
            if ($tbl->fetchColumn()) {
                $ins = $pdo->prepare("INSERT INTO activity_logs (actor_name, action, target, details) VALUES (?, ?, ?, ?)");
                $ins->execute([$actor, 'unenroll', 'student_subject', $details]);
            }
        }
    }
}

header("Location: ../manage_courses.php");
?>