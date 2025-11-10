<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<h1>Upload Exam / Quiz</h1>
<form action="api/upload_exam.php" method="POST" enctype="multipart/form-data" class="card">
  <label>Subject</label>
  <select name="subject_id" required>
    <?php
    $stmt = $pdo->prepare("SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    while($s = $stmt->fetch()) echo "<option value='{$s['id']}'>{$s['name']}</option>";
    ?>
  </select>

  <label>Title</label>
  <input type="text" name="title" required>

  <label>File</label>
  <input type="file" name="file" accept=".pdf,.doc,.docx,.mp4" required>

  <label>Start Time</label>
  <input type="datetime-local" name="start_time" required>

  <label>End Time</label>
  <input type="datetime-local" name="end_time" required>

  <button type="submit" class="btn">Upload Exam</button>
</form>

<?php include 'includes/footer.php'; ?>