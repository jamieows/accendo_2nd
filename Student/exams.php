<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
$now = date('Y-m-d H:i:s');
?>
<?php include 'includes/header.php'; ?>

<h1>Available Exams</h1>
<?php
$stmt = $pdo->prepare("SELECT e.id, e.title, e.file_path, e.start_time, e.end_time, s.name AS subj
                       FROM exams e
                       JOIN subjects s ON e.subject_id = s.id
                       JOIN student_subjects ss ON ss.subject_id = s.id
                       WHERE ss.student_id = ? AND ? BETWEEN e.start_time AND e.end_time");
$stmt->execute([$_SESSION['user_id'], $now]);
while($e = $stmt->fetch()){
    echo "<div class='material-card'>
            <span class='material-icon'>Exam</span>
            <div style='flex:1;'>
              <h3>{$e['subj']} – {$e['title']}</h3>
              <small>Available until: ".date('M j, Y g:i A', strtotime($e['end_time']))."</small>
            </div>
            <a href='../{$e['file_path']}' target='_blank' class='btn btn-success'>Take Exam</a>
          </div>";
}
if($stmt->rowCount() == 0){
    echo "<p>No exams available right now.</p>";
}
?>
<?php include 'includes/footer.php'; ?>