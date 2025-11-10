<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>

<div class="card">
  <h2>My Enrolled Subjects</h2>
  <?php
  $stmt = $pdo->prepare("SELECT s.name FROM student_subjects ss JOIN subjects s ON ss.subject_id = s.id WHERE ss.student_id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $subjects = $stmt->fetchAll();
  if ($subjects) {
      foreach ($subjects as $s) {
          echo "<p class='material-card'>
                  <span class='material-icon'>Book</span>
                  <strong>{$s['name']}</strong>
                  <button class='voice-btn speak-btn' aria-label='Read subject name'>Speak</button>
                </p>";
      }
  } else {
      echo "<p>No subjects enrolled.</p>";
  }
  ?>
</div>

<?php include 'includes/footer.php'; ?>