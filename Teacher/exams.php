<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: /accendo_2nd/Auth/login.php");
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

    .alert-error {
        background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 16px; font-weight: 500;
    }

    /* TOAST NOTIFICATION */
    .toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        min-width: 300px;
        background: #10b981;
        color: white;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        transform: translateX(120%);
        transition: transform 0.4s ease;
        z-index: 9999;
        font-weight: 600;
    }
    .toast.show { transform: translateX(0); }
    .toast.error { background: #ef4444; }

    /* LOADING SPINNER */
    .loading {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 40px;
        height: 40px;
        border: 4px solid #f3f4f6;
        border-top: 4px solid #7B61FF;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        z-index: 1000;
    }
    @keyframes spin {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
    }
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.3);
        z-index: 999;
    }
</style>

<h1>Upload Exam / Quiz</h1>

<!-- Toast & Loading -->
<div id="toast" class="toast"></div>
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading" id="loading"></div>
</div>

<?php
// Show error from URL
if (isset($_GET['error'])):
    $msg = $_GET['error'] === 'missing' ? 'All fields are required.' :
           ($_GET['error'] === 'invalid_link' ? 'Invalid Google Form link.' :
           ($_GET['error'] === 'time' ? 'End time must be after start time.' : 'Operation failed.'));
?>
  <div class="alert-error"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<form id="uploadForm" action="api/upload_exam.php" method="POST" class="card">
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

<div class="card">
  <table id="examsTable">
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

        echo "<tr data-id='{$e['id']}'>
                <td>" . htmlspecialchars($e['title']) . "</td>
                <td>" . htmlspecialchars($e['subject_name']) . "</td>
                <td>$linkBtn</td>
                <td>" . date('M j, Y g:i A', strtotime($e['start_time'])) . "</td>
                <td>" . date('M j, Y g:i A', strtotime($e['end_time'])) . "</td>
                <td>" . date('M j, Y g:i A', strtotime($e['created_at'])) . "</td>
                <td>
                  <a href='javascript:void(0)' 
                     onclick='deleteExam({$e['id']})' 
                     class='btn-sm btn-delete'>Delete</a>
                </td>
              </tr>";
    }
    ?>
  </table>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Toast
function showToast(message, isError = false) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'toast' + (isError ? ' error' : '');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// Loading
function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'block';
    document.getElementById('loading').style.display = 'block';
}
function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
    document.getElementById('loading').style.display = 'none';
}

// Upload Form
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    showLoading();
});

// Delete Exam (AJAX)
function deleteExam(id) {
    if (!confirm("Delete this exam?")) return;
    
    showLoading();
    fetch('api/delete_exam.php?id=' + id, { method: 'GET' })
        .then(r => r.text())
        .then(() => {
            hideLoading();
            showToast("Exam deleted successfully!");
            document.querySelector(`tr[data-id="${id}"]`).remove();
            // Clean URL
            const url = new URL(window.location);
            url.searchParams.delete('deleted');
            history.replaceState({}, '', url);
        })
        .catch(() => {
            hideLoading();
            showToast("Delete failed.", true);
        });
}

// Check URL params on load
window.addEventListener('load', () => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('uploaded')) {
        showToast("Exam uploaded successfully!");
        params.delete('uploaded');
    }
    if (params.get('deleted')) {
        showToast("Exam deleted successfully!");
        params.delete('deleted');
    }
    if (params.get('error')) {
        const msg = params.get('error') === 'missing' ? 'All fields required.' :
                    params.get('error') === 'invalid_link' ? 'Invalid link.' :
                    params.get('error') === 'time' ? 'Invalid time.' : 'Operation failed.';
        showToast(msg, true);
        params.delete('error');
    }
    // Clean URL
    if (params.toString()) {
        history.replaceState({}, '', window.location.pathname);
    }
});
</script>

<script src="../assets/js/global.js"></script>
</body>
</html>