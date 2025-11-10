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
    }
}

// === REMOVE ASSIGNMENT ===
if (isset($_GET['remove']) && isset($_GET['type'])) {
    $id = (int)$_GET['remove'];
    $type = $_GET['type'];
    
    if ($type === 'teacher') {
        $pdo->prepare("DELETE FROM teacher_subjects WHERE id = ?")->execute([$id]);
    } elseif ($type === 'student') {
        $pdo->prepare("DELETE FROM student_subjects WHERE id = ?")->execute([$id]);
    }
}

header("Location: ../manage_courses.php");
?>