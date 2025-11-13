<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<style>
    form.card label {
        display:block;
        margin:12px 0 8px;
        font-weight:600;
    }
    form.card select,
    form.card input,
    form.card textarea {
        display:block;
        width:100%;
        padding:8px;
        margin-bottom:16px;
        border-radius:6px;
        border:1px solid rgba(0,0,0,0.08);
        background:#fff;
        box-sizing:border-box;
    }
    table th, table td { padding:10px 12px; text-align:left; vertical-align:middle; }

    /* TEXT VISIBILITY */
    form.card input, form.card select, form.card option {
        color: #1f2937;
    }
    .dark-mode form.card input,
    .dark-mode form.card select,
    .dark-mode form.card option {
        color: #f3f4f6 !important;
        background: #1e293b !important;
    }

    /* BUTTONS */
    .btn {
        background: #7B61FF; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;
    }
    .btn-sm {
        padding: 6px 12px; font-size: 0.875rem; margin: 0 2px; display: inline-block; border-radius: 6px; text-decoration: none;
    }
    .btn-open { background: #7B61FF; color: white; }
    .btn-open:hover { background: #6a51e6; }
    .btn-delete { background: #EF4444; color: white; }
    .btn-delete:hover { background: #dc2626; }
</style>

<h1>Upload Exam / Quiz</h1>

<?php if (isset($_GET['uploaded'])): ?>
  <div style="background:#d1fae5; color:#065f46; padding:12px; border-radius:6px; margin-bottom:16px; font-weight:500;">
    Exam uploaded successfully!
  </div>
<?php endif; ?>

<form action="api/upload_exam.php" method="POST" class="card">
  <label>Subject</label>
  <select name="subject_id" required>
    <?php
    $stmt = $pdo->prepare("SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    while($s = $stmt->fetch()) echo "<option value='".htmlspecialchars($s['id'])."'>".htmlspecialchars($s['name'])."</option>";
    ?>
  </select>

  <label>Title</label>
  <input type="text" name="title" placeholder="e.g., Midterm Exam" required>

  <label>Google Form Link</label>
  <input type="url" name="google_form_link" placeholder="https://forms.gle/..." required>

  <label>Start Time</label>
  <input type="datetime-local" name="start_time" required>

  <label>End Time</label>
  <input type="datetime-local" name="end_time" required>

  <button type="submit" class="btn">Upload Exam</button>
</form>

<h2 style="margin-top:30px;">Uploaded Exams</h2>

<?php if (isset($_GET['deleted'])): ?>
  <div style="background:#d1fae5; color:#065f46; padding:12px; border-radius:6px; margin-bottom:16px; font-weight:500;">
    Exam deleted successfully.
  </div>
<?php endif; ?>

<div class="card">
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
        SELECT e.id, e.title, e.google_form_link, e.start_time, e.end_time, e.created_at,
               s.name AS subject_name
        FROM exams e
        JOIN subjects s ON e.subject_id = s.id
        WHERE e.teacher_id = ?
        ORDER BY e.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    while($e = $stmt->fetch()){
        $linkBtn = $e['google_form_link']
            ? '<a href="'.htmlspecialchars($e['google_form_link']).'" target="_blank" class="btn-sm btn-open">Open</a>'
            : '—';

        echo "<tr>
                <td>" . htmlspecialchars($e['title']) . "</td>
                <td>" . htmlspecialchars($e['subject_name']) . "</td>
                <td>$linkBtn</td>
                <td>" . date('M j, Y g:i A', strtotime($e['start_time'])) . "</td>
                <td>" . date('M j, Y g:i A', strtotime($e['end_time'])) . "</td>
                <td>" . date('M j, Y g:i A', strtotime($e['created_at'])) . "</td>
                <td>
                  <a href='api/delete_exam.php?id=" . urlencode($e['id']) . "' 
                     class='btn-sm btn-delete' 
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