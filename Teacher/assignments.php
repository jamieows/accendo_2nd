<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

/* ───── AUTO-CREATE assignment_submissions IF MISSING ───── */
try {
    $pdo->query("SELECT 1 FROM assignment_submissions LIMIT 1");
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Table") !== false && strpos($e->getMessage(), "assignment_submissions") !== false) {
        $sql = "
        CREATE TABLE assignment_submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            assignment_id INT NOT NULL,
            student_id INT NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_late TINYINT(1) DEFAULT 0,
            FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_submission (assignment_id, student_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $pdo->exec($sql);
    }
}
?>
<?php include 'includes/header.php'; ?>

<style>
    /* ────────────────────── SPACING ────────────────────── */
    form.card label,
    .edit-modal label,
    .submission-modal label {
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
    form.card input[type="file"],
    .edit-modal select,
    .edit-modal input,
    .edit-modal textarea,
    .edit-modal input[type="datetime-local"],
    .edit-modal input[type="file"],
    .submission-modal input,
    .submission-modal textarea {
        display:block;
        width:100%;
        padding:8px;
        margin-bottom:16px;
        border-radius:6px;
        border:1px solid rgba(0,0,0,0.08);
        box-sizing:border-box;
        background:#fff;
    }

    table th, table td { padding:10px 12px; text-align:left; vertical-align:middle; }

    /* ────────────────────── TEXT VISIBILITY ────────────────────── */
    form.card select,
    form.card input,
    form.card textarea,
    form.card option,
    .edit-modal select,
    .edit-modal input,
    .edit-modal textarea,
    .edit-modal option,
    .submission-modal input,
    .submission-modal textarea {
      color: #1f2937;
    }
    .dark-mode form.card select,
    .dark-mode form.card input,
    .dark-mode form.card textarea,
    .dark-mode form.card option,
    .dark-mode .edit-modal select,
    .dark-mode .edit-modal input,
    .dark-mode .edit-modal textarea,
    .dark-mode .edit-modal option,
    .dark-mode .submission-modal input,
    .dark-mode .submission-modal textarea {
      color: #f3f4f6 !important;
      background: #1e293b !important;
    }

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
        margin: 0 2px;
        min-width: 44px;
    }
    .btn-primary { background: #7B61FF; color: white; }
    .btn-primary:hover { background: #6a51e6; }
    .btn-success { background: #10B981; color: white; }
    .btn-success:hover { background: #059669; }
    .btn-danger { background: #EF4444; color: white; }
    .btn-danger:hover { background: #dc2626; }

    /* ────────────────────── MODAL ────────────────────── */
    .modal{
        display:none;
        position:fixed;
        top:0; left:0; right:0; bottom:0;
        background:rgba(0,0,0,0.5);
        align-items:center;
        justify-content:center;
        z-index:9999;
    }
    .modal.active{ display:flex; }
    .modal-content{
        background:#fff;
        padding:20px;
        border-radius:8px;
        width:90%;
        max-width:720px;
        max-height:90vh;
        overflow-y:auto;
    }
    .dark-mode .modal-content{ background:#1e1e1e; color:#e0e0e0; }
    .modal-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
    .modal-close{ cursor:pointer; font-size:1.4rem; }

    /* Submission Table */
    .submission-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
    }
    .submission-table th,
    .submission-table td {
        border: 1px solid #374151;
        padding: 10px;
        text-align: left;
    }
    .submission-table th {
        background: #f3f4f6;
        font-weight: 600;
    }
    .dark-mode .submission-table th {
        background: #334155;
    }
    .status-on-time { color: #10B981; font-weight: 600; }
    .status-late { color: #EF4444; font-weight: 600; }
</style>

<h1>Create Assignment</h1>

<?php if (isset($_GET['created'])): ?>
  <div style="background:#d1fae5; color:#065f46; padding:12px; border-radius:6px; margin-bottom:16px; font-weight:500;">
    Assignment created successfully!
  </div>
<?php endif; ?>

<form action="api/create_assignment.php" method="POST" class="card" enctype="multipart/form-data">
  <label>Subject</label>
  <select name="subject_id" required>
    <?php
    $stmt = $pdo->prepare("SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    while($s = $stmt->fetch()) echo "<option value='".htmlspecialchars($s['id'])."'>".htmlspecialchars($s['name'])."</option>";
    ?>
  </select>

  <label>Attachment (optional)</label>
  <input type="file" name="attachment" accept=".pdf,.doc,.docx,.zip,.png,.jpg,.jpeg">

  <label>Title</label>
  <input type="text" name="title" required>

  <label>Description</label>
  <textarea name="description" rows="3" placeholder="Instructions..."></textarea>

  <label>Due Date & Time</label>
  <input type="datetime-local" name="due_date" required>

  <button type="submit" class="btn">Create Assignment</button>
</form>

<h2 style="margin-top:30px;">Your Assignments</h2>

<?php if (isset($_GET['deleted'])): ?>
  <div style="background:#d1fae5; color:#065f46; padding:12px; border-radius:6px; margin-bottom:16px; font-weight:500;">
    Assignment deleted successfully.
  </div>
<?php endif; ?>

<div class="card">
  <table>
    <tr><th>Title</th><th>Subject</th><th>Attachment</th><th>Due</th><th>Submissions</th><th>Action</th></tr>
    <?php
    $stmt = $pdo->prepare("
        SELECT a.id, a.title, a.description, a.due_date, a.file_path, a.subject_id,
               s.name AS subject_name
        FROM assignments a
        JOIN subjects s ON a.subject_id = s.id
        WHERE a.teacher_id = ?
        ORDER BY a.due_date
    ");
    $stmt->execute([$_SESSION['user_id']]);
    while($a = $stmt->fetch()){
        $dueClass = (!empty($a['due_date']) && strtotime($a['due_date']) < time()) ? 'due-over' : 'due-soon';
        $attachHtml = $a['file_path']
            ? '<a href="/accendo_2nd/'.htmlspecialchars($a['file_path']).'" target="_blank" class="btn-sm btn-primary">View</a>'
            : '<span style="color:#6b7280">—</span>';

        // ───── SAFE SUBMISSION COUNT ─────
        $subCount = 0;
        try {
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM assignment_submissions WHERE assignment_id = ?");
            $countStmt->execute([$a['id']]);
            $subCount = $countStmt->fetchColumn();
        } catch (PDOException $e) {
            $subCount = 0;   // table missing → 0 submissions
        }

        echo "<tr>
                <td>" . htmlspecialchars($a['title']) . "</td>
                <td>" . htmlspecialchars($a['subject_name']) . "</td>
                <td>$attachHtml</td>
                <td class='due-date $dueClass' data-due='" . htmlspecialchars($a['due_date']) . "'>"
                  . (empty($a['due_date']) ? '—' : date('M j, Y g:i A', strtotime($a['due_date']))) .
                "</td>
                <td>
                  <button class='btn-sm btn-success' onclick='openSubmissionsModal(" . $a['id'] . ", " . json_encode($a['title']) . ")'>
                    View ($subCount)
                  </button>
                </td>
                <td>
                  <button class='btn-sm btn-primary' onclick='openEditModal(" . json_encode($a) . ")'>Edit</button>
                  <a href='api/delete_assignment.php?id=" . urlencode($a['id']) . "' 
                     class='btn-sm btn-danger' 
                     onclick='return confirm(\"Delete this assignment? All submissions will be lost.\")'>Delete</a>
                </td>
              </tr>";
    }
    ?>
  </table>
</div>

<!-- ────────────────────── EDIT MODAL ────────────────────── -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Edit Assignment</h2>
      <span class="modal-close" onclick="closeEditModal()">×</span>
    </div>
    <form id="editForm" action="api/edit_assignment.php" method="POST" enctype="multipart/form-data" class="edit-modal">
      <input type="hidden" name="id" id="edit_id">
      <label>Subject</label>
      <select name="subject_id" id="edit_subject_id" required>
        <?php
        $stmt = $pdo->prepare("SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        while($s = $stmt->fetch()) echo "<option value='".htmlspecialchars($s['id'])."'>".htmlspecialchars($s['name'])."</option>";
        ?>
      </select>
      <label>Title</label>
      <input type="text" name="title" id="edit_title" required>
      <label>Description</label>
      <textarea name="description" id="edit_description" rows="3"></textarea>
      <label>Current Attachment</label>
      <div id="current_file"></div>
      <label>New Attachment (optional)</label>
      <input type="file" name="attachment" accept=".pdf,.doc,.docx,.zip,.png,.jpg,.jpeg">
      <label>Due Date & Time</label>
      <input type="datetime-local" name="due_date" id="edit_due_date" required>
      <button type="submit" class="btn">Save Changes</button>
    </form>
  </div>
</div>

<!-- ────────────────────── SUBMISSIONS MODAL ────────────────────── -->
<div id="submissionsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Student Submissions: <span id="modalAssignmentTitle"></span></h2>
      <span class="modal-close" onclick="closeSubmissionsModal()">×</span>
    </div>
    <div id="submissionList">
      <!-- Filled via JS -->
    </div>
  </div>
</div>

<script>
function openEditModal(data){
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_title').value = data.title;
    document.getElementById('edit_description').value = data.description || '';
    document.getElementById('edit_due_date').value = data.due_date ? data.due_date.slice(0,16) : '';
    document.getElementById('edit_subject_id').value = data.subject_id;

    const fileDiv = document.getElementById('current_file');
    if(data.file_path){
        fileDiv.innerHTML = `<a href="/accendo_2nd/${data.file_path}" target="_blank">Current file</a>`;
    }else{
        fileDiv.innerHTML = '<span style="color:#6b7280">—</span>';
    }

    document.getElementById('editModal').classList.add('active');
}
function closeEditModal(){
    document.getElementById('editModal').classList.remove('active');
}

function openSubmissionsModal(assignmentId, title){
    document.getElementById('modalAssignmentTitle').textContent = title;
    fetch(`api/get_submissions.php?assignment_id=${assignmentId}`)
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('submissionList');
            if(data.length === 0){
                list.innerHTML = '<p style="color:#9ca3af; font-style:italic;">No submissions yet.</p>';
                return;
            }

            let html = `<table class="submission-table">
                <tr>
                    <th>Student</th>
                    <th>Submitted At</th>
                    <th>Status</th>
                    <th>File</th>
                </tr>`;
            data.forEach(s => {
                const status = s.is_late ? 
                    '<span class="status-late">Late</span>' : 
                    '<span class="status-on-time">On Time</span>';
                const fileLink = s.file_path ? 
                    `<a href="/accendo_2nd/${s.file_path}" target="_blank" class="btn-sm btn-primary">Download</a>` :
                    '<span style="color:#6b7280">—</span>';
                html += `<tr>
                    <td>${s.first_name} ${s.last_name}</td>
                    <td>${new Date(s.submitted_at).toLocaleString()}</td>
                    <td>${status}</td>
                    <td>${fileLink}</td>
                </tr>`;
            });
            html += `</table>`;
            list.innerHTML = html;
        })
        .catch(() => {
            document.getElementById('submissionList').innerHTML = '<p style="color:#ef4444;">Failed to load submissions.</p>';
        });
    document.getElementById('submissionsModal').classList.add('active');
}
function closeSubmissionsModal(){
    document.getElementById('submissionsModal').classList.remove('active');
}
</script>

<?php include 'includes/footer.php'; ?>
<script src="../assets/js/global.js"></script>
</body>
</html>