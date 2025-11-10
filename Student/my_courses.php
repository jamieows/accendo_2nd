<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<h1>Learning Materials</h1>
<?php
$stmt = $pdo->prepare("SELECT m.title, m.file_path, m.file_type, s.name AS subj
                       FROM materials m
                       JOIN subjects s ON m.subject_id = s.id
                       JOIN student_subjects ss ON ss.subject_id = s.id
                       WHERE ss.student_id = ?");
$stmt->execute([$_SESSION['user_id']]);
while($m = $stmt->fetch()){
    $icon = $m['file_type']=='pdf' ? 'PDF' : ($m['file_type']=='doc' ? 'DOC' : 'Video');
    echo "<div class='material-card'>
            <span class='material-icon'>$icon</span>
            <div style='flex:1;'>
              <h3>{$m['subj']} – {$m['title']}</h3>
            </div>
            <a href='../{$m['file_path']}' target='_blank' class='btn btn-success'>Open</a>
            <button class='voice-btn speak-btn'>Speak</button>
          </div>";
}
?>
<?php include 'includes/footer.php'; ?>