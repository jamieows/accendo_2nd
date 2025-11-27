<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

/* --------------------------------------------------------------
   AUTO-CREATE `uploaded_at` COLUMN IF NOT EXISTS
   -------------------------------------------------------------- */
try {
    $pdo->query("SELECT uploaded_at FROM materials LIMIT 1");
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Unknown column 'uploaded_at'") !== false) {
        $pdo->exec("ALTER TABLE materials ADD COLUMN uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        $pdo->exec("UPDATE materials SET uploaded_at = NOW() WHERE uploaded_at IS NULL");
    }
}
?>
<?php include 'includes/header.php'; ?>

<style>
  /* spacing between label and control, and more readable label word spacing */
  form.card label {
    display:block;
    margin:12px 0 8px;
    font-weight:600;
    word-spacing:0.28rem;
    letter-spacing:0.15px;
  }

  form.card select,
  form.card input[type="text"],
  form.card textarea,
  form.card input[type="datetime-local"],
  form.card input[type="file"]{
    display:block;
    width:100%;
    padding:8px;
    margin-bottom:16px;
    border-radius:6px;
    border:1px solid rgba(0,0,0,0.08);
    box-sizing:border-box;
    background:#fff;
  }

  /* ==== TEXT VISIBILITY FIX (light/dark mode) ==== */
  form.card select,
  form.card input,
  form.card textarea,
  form.card option {
      color: #1f2937;
  }
  .dark-mode form.card select,
  .dark-mode form.card input,
  .dark-mode form.card textarea,
  .dark-mode form.card option {
      color: #f3f4f6 !important;
      background: #1e293b !important;
  }

  table th, table td{ padding:10px 12px; text-align:left; vertical-align:middle; }

  /* ────────────────────── BUTTONS ────────────────────── */
  .btn-sm {
    display: inline-block;
    padding: 6px 12px;
    font-size: 0.875rem;
    font-weight: 600;
    text-align: center;
    border-radius: 6px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    margin: 0 2px;
    min-width: 44px;
  }

  .btn-view {
    background: #7B61FF;
    color: white;
  }
  .btn-view:hover {
    background: #6a51e6;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(123,97,255,0.3);
  }

  .btn-danger {
    background: #EF4444;
    color: white;
  }
  .btn-danger:hover {
    background: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(239,68,68,0.3);
  }

  .dark-mode .btn-view,
  .dark-mode .btn-danger {
    background: #7B61FF;
  }
  .dark-mode .btn-view:hover { background: #6a51e6; }
  .dark-mode .btn-danger { background: `EF4444; }
  .dark-mode .btn-danger:hover { background: #dc2626; }

  /* ———————————————————————
     FIXED: Headings perfectly visible in BOTH Light & Dark Mode
     ——————————————————————— */
  h1, h2 {
    margin: 0 0 1.5rem 0;
    font-weight: 700;
    color: #1f2937; /* Dark gray in light mode */
  }

  h1 {
    font-size: 2.6rem;
    background: linear-gradient(135deg, #7B61FF, #A78BFA);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  h2 {
    font-size: 1.8rem;
    color: #374151;
  }

  /* Dark Mode: Bright & beautiful headings */
  .dark-mode h1,
  .dark-mode h2 {
    color: #f1f5f9 !important;
  }

  .dark-mode h1 {
    background: linear-gradient(135deg, #C4B5FD, #E0D4FF);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .dark-mode h2 {
    color: #e2e8f0;
  }
</style>

<h1>My Courses</h1>

<form action="api/upload_material.php" method="POST" class="card" enctype="multipart/form-data">
  <label>Subject</label>
  <select name="subject_id" required>
    <?php
    $stmt = $pdo->prepare("SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    while($s = $stmt->fetch()) echo "<option value='".htmlspecialchars($s['id'])."'>".htmlspecialchars($s['name'])."</option>";
    ?>
  </select>

  <label>Title</label>
  <input type="text" name="title" required>

  <label>Description</label>
  <textarea name="description" rows="3" placeholder="Optional description..."></textarea>

  <label>File (DOC, PDF, Video)</label>
  <input type="file" name="file" 
         accept=".doc,.docx,.pdf,video/mp4,video/webm,video/ogg" required>

  <button type="submit" class="btn">Upload Material</button>
</form>

<h2 style="margin-top:30px;">Uploaded Materials</h2>

<?php if (isset($_GET['deleted'])): ?>
  <div style="background:#d1fae5; color:#065f46; padding:12px; border-radius:6px; margin-bottom:16px; font-weight:500;">
    Material deleted successfully.
  </div>
<?php endif; ?>

<?php if (isset($_GET['uploaded'])): ?>
  <div style="background:#d1fae5; color:#065f46; padding:12px; border-radius:6px; margin-bottom:16px; font-weight:500;">
    Material uploaded successfully!
  </div>
<?php endif; ?>

<div class="card">
  <table>
    <tr>
      <th>Title</th>
      <th>Description</th>
      <th>Subject</th>
      <th>File</th>
      <th>Uploaded</th>
      <th>Action</th>
    </tr>
    <?php
    $stmt = $pdo->prepare("
        SELECT m.id, m.title, m.description, s.name AS subject, m.file_path, m.uploaded_at
        FROM materials m
        JOIN subjects s ON m.subject_id = s.id
        WHERE m.teacher_id = ?
        ORDER BY m.uploaded_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    while($m = $stmt->fetch()){
        // FIXED: Use absolute path from web root
        $fileLink = $m['file_path']
            ? '<a href="/accendo_2nd/'.htmlspecialchars($m['file_path']).'" target="_blank" class="btn-sm btn-view">View</a>'
            : '<span style="color:#6b7280">—</span>';

        $uploadedDate = !empty($m['uploaded_at'])
            ? date('M j, Y g:i A', strtotime($m['uploaded_at']))
            : '—';

        $description = !empty($m['description']) ? htmlspecialchars($m['description']) : '—';

        echo "<tr>
                <td>" . htmlspecialchars($m['title']) . "</td>
                <td>" . $description . "</td>
                <td>" . htmlspecialchars($m['subject']) . "</td>
                <td>$fileLink</td>
                <td>$uploadedDate</td>
                <td>
                  <a href='api/delete_material.php?delete=" . urlencode($m['id']) . "' 
                     class='btn-sm btn-danger' 
                     onclick='return confirm(\"Delete this material? This cannot be undone.\")'>
                     Delete
                  </a>
                </td>
              </tr>";
    }
    ?>
  </table>
</div>

<?php include 'includes/footer.php'; ?>
<script src="../assets/js/global.js"></script>

</body>
</html>