<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<h1>Upload Learning Materials</h1>

<div class="card">
  <form action="api/upload_material.php" method="POST" enctype="multipart/form-data">
    <div id="drop-zone" class="upload-area">
      <p><strong>Drop files here or click to upload</strong></p>
      <p>PDF, DOC, MP4 only</p>
      <input type="file" id="file-input" name="file" accept=".pdf,.doc,.docx,.mp4" required style="display:none;">
    </div>

    <label>Subject</label>
    <select name="subject_id" required>
      <?php
      $stmt = $pdo->prepare("SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ?");
      $stmt->execute([$_SESSION['user_id']]);
      while($s = $stmt->fetch()) echo "<option value='{$s['id']}'>{$s['name']}</option>";
      ?>
    </select>

    <label>Title</label>
    <input type="text" name="title" placeholder="e.g., Week 1 Lecture" required>

    <button type="submit" class="btn" style="margin-top:15px;">Upload Material</button>
  </form>
</div>

<?php include 'includes/footer.php'; ?>