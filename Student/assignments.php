<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<h1>Assignments</h1>
<?php
$stmt = $pdo->prepare("SELECT a.id, a.title, a.description, a.due_date, s.name AS subj,
                       sub.file_path AS submitted
                       FROM assignments a
                       JOIN subjects s ON a.subject_id = s.id
                       JOIN student_subjects ss ON ss.subject_id = s.id
                       LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
                       WHERE ss.student_id = ?");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
while($a = $stmt->fetch()){
    $due = strtotime($a['due_date']);
    $status = $a['submitted'] ? "<span style='color:green'>Submitted</span>" : (time() > $due ? "<span style='color:red'>Missed</span>" : "Pending");
    echo "<div class='card assignment-card'>
            <strong>{$a['subj']} – {$a['title']}</strong><br>
            Due: <span class='due-date' data-due='{$a['due_date']}'>".date('M j, Y g:i A', $due)."</span><br>
            {$a['description']}<br>
            <em>Status: $status</em><br>";
    if(!$a['submitted'] && time() <= $due){
        echo "<form action='api/submit_assignment.php' method='POST' enctype='multipart/form-data' style='margin-top:10px;'>
                <input type='hidden' name='assignment_id' value='{$a['id']}'>
                <input type='file' name='file' required>
                <button type='submit' class='btn btn-success'>Submit</button>
              </form>";
    } else if($a['submitted']){
        echo "<a href='../{$a['submitted']}' target='_blank' class='btn btn-success'>View File</a>";
    }
    echo "</div>";
}
?>
<?php include 'includes/footer.php'; ?>