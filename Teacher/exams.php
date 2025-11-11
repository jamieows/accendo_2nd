<?php 
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once '../config/db.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}

/* --------------------------------------------------------------
   AUTO-CREATE `uploaded_at` & `link` COLUMNS IN `exams` TABLE
   -------------------------------------------------------------- */
try {
    $pdo->query("SELECT uploaded_at FROM exams LIMIT 1");
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Unknown column 'uploaded_at'") !== false) {
        $pdo->exec("ALTER TABLE exams ADD COLUMN uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        $pdo->exec("UPDATE exams SET uploaded_at = NOW() WHERE uploaded_at IS NULL");
    }
}

try {
    $pdo->query("SELECT link FROM exams LIMIT 1");
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Unknown column 'link'") !== false) {
        $pdo->exec("ALTER TABLE exams ADD COLUMN link VARCHAR(500) NULL AFTER file_path");
        // Optional: copy existing file_path to link if it's a URL
        $pdo->exec("UPDATE exams SET link = file_path WHERE file_path LIKE 'http%' AND link IS NULL");
    }
}
?>
<?php include 'includes/header.php'; ?>

<style>
    /* === SPACING ONLY === */
    .exam-title {
        margin-bottom: 2.5rem;
    }

    .exam-form label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--text, #162447);
        text-transform: capitalize;
        letter-spacing: 0.3px;
    }

    .exam-form input,
    .exam-form select {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border: 1.5px solid #cccccc;
        border-radius: 0.6rem;
        background: #ffffff;
        color: #1a1a1a;
        transition: all 0.25s ease;
        box-sizing: border-box;
        margin-bottom: 1.75rem;
    }

    .exam-form input:focus,
    .exam-form select:focus {
        outline: none;
        border-color: #7B61FF;
        box-shadow: 0 0 0 3px rgba(123,97,255,0.15);
    }

    .dark-mode .exam-form input,
    .dark-mode .exam-form select {
        background: #2a2a3a;
        color: #f0f0f0;
        border-color: #444454;
    }

    .dark-mode .exam-form input:focus,
    .dark-mode .exam-form select:focus {
        box-shadow: 0 0 0 3px rgba(167,139,250,0.25);
    }

    .exam-form .btn {
        background: #7B61FF;
        color: white;
        padding: 0.85rem 1.75rem;
        font-weight: 600;
        border: none;
        border-radius: 0.6rem;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.25s ease;
        width: 100%;
        margin-top: 1.5rem;
    }

    .exam-form .btn:hover {
        background: #6a51e6;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(123,97,255,0.3);
    }

    /* ──────────────────────  TABLE & ACTION BUTTONS ────────────────────── */
    .exam-list table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    .exam-list th, .exam-list td {
        padding: 10px 12px;
        text-align: left;
        vertical-align: middle;
        border-bottom: 1px solid #e2e2e2;
    }
    .dark-mode .exam-list th, .dark-mode .exam-list td {
        border-color: #444454;
    }

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
</style>

<!-- Title with spacing below -->
<h1 class="exam-title">Upload Exam / Quiz</h1>

<!-- Upload Form – PASTE LINK ONLY -->
<form action="api/upload_exam.php" method="POST" class="exam-form">
  <label for="subject_id">Subject</label>
  <select name="subject_id" id="subject_id" required>
    <?php
    $stmt = $pdo->prepare("SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    while($s = $stmt->fetch()) echo "<option value='{$s['id']}'>{$s['name']}</option>";
    ?>
  </select>

  <label for="title">Title</label>
  <input type="text" id="title" name="title" placeholder="e.g., Midterm Exam" required>

  <!-- PASTE LINK -->
  <label for="link">Paste Link</label>
  <input type="url" id="link" name="link" 
         placeholder="https://youtube.com/watch?v=..." 
         required>

  <label for="start_time">Start Time</label>
  <input type="datetime-local" id="start_time" name="start_time" required>

  <label for="end_time">End Time</label>
  <input type="datetime-local" id="end_time" name="end_time" required>

  <button type="submit" class="btn">Upload Exam</button>
</form>

<!-- Uploaded Exams List -->
<h2 style="margin-top: 3rem;">Uploaded Exams</h2>
<div class="exam-list">
  <table>
    <tr>
      <th>Title</th>
      <th>Subject</th>
      <th>Link</th>
      <th>Start</th>
      <th>End</th>
      <th>Uploaded</th>
      <th>Action</th>
    </tr>
    <?php
    $stmt = $pdo->prepare("
        SELECT e.id, e.title, COALESCE(e.link, e.file_path) AS display_link,
               e.start_time, e.end_time, e.uploaded_at,
               s.name AS subject_name
        FROM exams e
        JOIN subjects s ON e.subject_id = s.id
        WHERE e.teacher_id = ?
        ORDER BY e.uploaded_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    while ($e = $stmt->fetch()) {
        $linkHtml = $e['display_link']
            ? '<a href="'.htmlspecialchars($e['display_link']).'" target="_blank" class="btn-sm btn-view">Open</a>'
            : '<span style="color:#6b7280">—</span>';

        $start = !empty($e['start_time']) ? date('M j, Y g:i A', strtotime($e['start_time'])) : '—';
        $end   = !empty($e['end_time'])   ? date('M j, Y g:i A', strtotime($e['end_time']))   : '—';
        $uploaded = !empty($e['uploaded_at']) ? date('M j, Y g:i A', strtotime($e['uploaded_at'])) : '—';

        echo "<tr>
                <td>".htmlspecialchars($e['title'])."</td>
                <td>".htmlspecialchars($e['subject_name'])."</td>
                <td>$linkHtml</td>
                <td>$start</td>
                <td>$end</td>
                <td>$uploaded</td>
                <td>
                  <a href='api/upload_exam.php?delete=".urlencode($e['id'])."' 
                     class='btn-sm btn-danger' 
                     onclick='return confirm(\"Delete this exam?\")'>Delete</a>
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