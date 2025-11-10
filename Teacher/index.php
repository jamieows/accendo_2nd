<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>

<div class="card">
  <h2>My Assigned Subjects</h2>
  <?php
  $stmt = $pdo->prepare("SELECT s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $subjects = $stmt->fetchAll();
  if ($subjects) {
      foreach ($subjects as $s) {
          echo "<p class='assignment-card'>• <strong>{$s['name']}</strong></p>";
      }
  } else {
      echo "<p>No subjects assigned yet.</p>";
  }
  ?>
</div>

<?php include 'includes/footer.php'; ?>