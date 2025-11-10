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
    <div id="drop-zone" class="upload-area"></div>

    <label style="margin-top: 15px;">Subject</label>
    <select name="subject_id" required style="margin-bottom: 20px;">
      <?php
      $stmt = $pdo->prepare("SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ?");
      $stmt->execute([$_SESSION['user_id']]);
      while($s = $stmt->fetch()) echo "<option value='{$s['id']}'>{$s['name']}</option>";
      ?>
    </select>

    <label style="margin-top: 15px;">Upload material here</label>
    <input type="file" id="file-input" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4" required style="margin-bottom: 20px;">

    <label style="margin-top: 15px;">Title</label>
    <input type="text" name="title" placeholder="e.g., Week 1 Lecture" required style="margin-bottom: 20px;">

    <button type="submit" class="btn" style="margin-top:15px;">Upload Material</button>
  </form>
</div>

<script>
// Make drop-zone clickable and support simple drag/drop to the visible file input
(function(){
  var drop = document.getElementById('drop-zone');
  var input = document.getElementById('file-input');
  if(!drop || !input) return;

  drop.style.cursor = 'pointer';
  drop.addEventListener('click', function(){ input.click(); });

  drop.addEventListener('dragover', function(e){ e.preventDefault(); drop.classList.add('dragover'); });
  drop.addEventListener('dragleave', function(e){ e.preventDefault(); drop.classList.remove('dragover'); });
  drop.addEventListener('drop', function(e){
    e.preventDefault(); drop.classList.remove('dragover');
    if(e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length){
      // assign FileList to input
      input.files = e.dataTransfer.files;
    }
  });
})();
</script>

<?php
// Fetch uploaded materials for this teacher
$materialsStmt = $pdo->prepare(
    "SELECT m.id, m.title, m.file_path, m.file_type, s.name AS subject_name
     FROM materials m
     LEFT JOIN subjects s ON m.subject_id = s.id
     WHERE m.teacher_id = ?
     ORDER BY m.id DESC"
);
$materialsStmt->execute([$_SESSION['user_id']]);
$materials = $materialsStmt->fetchAll();
?>

<h2 style="margin-top: 30px;">Your Uploaded Materials</h2>
<div class="card">
  <?php if (empty($materials)): ?>
    <p style="color: #666;">No materials uploaded yet.</p>
  <?php else: ?>
    <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
      <thead>
        <tr style="background-color: #f5f5f5; border-bottom: 2px solid #ddd;">
          <th style="padding: 12px; text-align: left;">Title</th>
          <th style="padding: 12px; text-align: left;">Subject</th>
          <th style="padding: 12px; text-align: left;">Type</th>
          <th style="padding: 12px; text-align: center;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($materials as $m): 
          $title = htmlspecialchars($m['title']);
          $subject = htmlspecialchars($m['subject_name'] ?? '-');
          $type = htmlspecialchars(ucfirst($m['file_type']));
          $file = htmlspecialchars($m['file_path']);
          $fileHref = 'uploads/' . $file;
          $materialId = $m['id'];
        ?>
        <tr style="border-bottom: 1px solid #eee;">
          <td style="padding: 12px; vertical-align: middle;"><?php echo $title; ?></td>
          <td style="padding: 12px; vertical-align: middle;"><?php echo $subject; ?></td>
          <td style="padding: 12px; vertical-align: middle;"><?php echo $type; ?></td>
          <td style="padding: 12px; vertical-align: middle; text-align: center;">
            <a href="<?php echo $fileHref; ?>" target="_blank" rel="noopener" style="display: inline-block; padding: 6px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px; margin-right: 8px; font-size: 0.9rem;">View</a>
            <form method="POST" action="api/delete_material.php" style="display: inline-block;">
              <input type="hidden" name="material_id" value="<?php echo $materialId; ?>">
              <button type="submit" style="padding: 6px 10px; background-color: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem;" onclick="return confirm('Are you sure you want to delete this material?');">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>