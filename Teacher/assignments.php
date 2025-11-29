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
    :root {
        --primary: #7B61FF;
        --danger: #EF4444;
        --success: #10B981;
        --text: #1f2937;
        --text-light: #6b7280;
        --bg: #ffffff;
        --card: #ffffff;
        --border: rgba(0,0,0,0.1);
        --table-header: #f3f4f6;
    }

    .dark-mode {
        --text: #f3f4f6;
        --text-light: #9ca3af;
        --bg: #0f172a;
        --card: #1e293b;
        --border: #334155;
        --table-header: #334155;
    }

    body {
        background: var(--bg);
        color: var(--text);
        font-family: 'Inter', sans-serif;
        transition: background 0.3s, color 0.3s;
    }

    /* Cards & Forms */
    .card {
        background: var(--card);
        border-radius: 12px;
        padding: 24px;
        margin: 20px 0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid var(--border);
    }

    h1, h2 {
        color: var(--text);
        background: linear-gradient(135deg, #7B61FF, #A78BFA);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 700;
    }

    h1 { font-size: 2.4rem; margin-bottom: 1rem; }
    h2 { font-size: 1.8rem; margin: 2rem 0 1rem; }

    /* Form Styling */
    form.card label,
    .edit-modal label,
    .submission-modal label {
        display: block;
        margin: 16px 0 8px;
        font-weight: 600;
        color: var(--text);
        font-size: 0.95rem;
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
        display: block;
        width: 100%;
        padding: 12px 14px;
        margin-bottom: 16px;
        border-radius: 10px;
        border: 2px solid var(--border);
        background: var(--card);
        color: var(--text);
        font-size: 1rem;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }

    form.card input:focus,
    form.card select:focus,
    form.card textarea:focus,
    .edit-modal input:focus,
    .edit-modal select:focus,
    .edit-modal textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(123,97,255,0.15);
    }

    /* Buttons */
    .btn, .btn-sm {
        padding: 10px 20px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        font-size: 0.95rem;
    }

    .btn { padding: 12px 28px; font-size: 1.05rem; }
    .btn-sm { padding: 8px 16px; font-size: 0.875rem; }

    .btn-primary, .btn-sm.btn-primary { background: var(--primary); color: white; }
    .btn-primary:hover, .btn-sm.btn-primary:hover { background: #6a78bfa; transform: translateY(-1px); }

    .btn-success, .btn-sm.btn-success { background: var(--success); color: white; }
    .btn-success:hover, .btn-sm.btn-success:hover { background: #059669; }

    .btn-danger, .btn-sm.btn-danger { background: var(--danger); color: white; }
    .btn-danger:hover, .btn-sm.btn-danger:hover { background: #dc2626; }

    /* Table */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
        background: var(--card);
    }

    table th, table td {
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid var(--border);
        color: var(--text);
    }

    table th {
        background: var(--table-header);
        font-weight: 600;
        color: var(--text);
        font-size: 0.95rem;
    }

    table tr:hover {
        background: rgba(123,97,255,0.08);
    }

    .due-over { color: var(--danger); font-weight: 600; }
    .due-soon { color: var(--text-light); }

    /* Modals */
    .modal {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.7);
        backdrop-filter: blur(6px);
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .modal.active { display: flex; }

    .modal-content {
        background: var(--card);
        padding: 28px;
        border-radius: 16px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        border: 1px solid var(--border);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.6rem;
        background: linear-gradient(135deg, #7B61FF, #A78BFA);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .modal-close {
        cursor: pointer;
        font-size: 2rem;
        color: var(--text-light);
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.2s;
    }

    .modal-close:hover {
        background: rgba(239,68,68,0.2);
        color: var(--danger);
    }

    /* Submission Table Inside Modal */
    .submission-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
    }

    .submission-table th,
    .submission-table td {
        border: 1px solid var(--border);
        padding: 12px;
        text-align: left;
        color: var(--text);
    }

    .submission-table th {
        background: var(--table-header);
        font-weight: 600;
    }

    .status-on-time { color: var(--success); font-weight: 600; }
    .status-late { color: var(--danger); font-weight: 600; }

    /* Alerts */
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        padding: 16px;
        border-radius: 12px;
        margin: 20px 0;
        font-weight: 500;
        border-left: 5px solid var(--success);
    }
</style>

<h1>Create Assignment</h1>

<?php if (isset($_GET['created'])): ?>
  <div class="alert-success">
    Assignment created successfully!
  </div>
<?php endif; ?>

<form action="api/create_assignment.php" method="POST" class="card" enctype="multipart/form-data">
  <label>Subject *</label>
  <select name="subject_id" required>
    <option value="">Select subject</option>
    <?php
    $stmt = $pdo->prepare("SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ? ORDER BY s.name");
    $stmt->execute([$_SESSION['user_id']]);
    while($s = $stmt->fetch()) {
        echo "<option value='".htmlspecialchars($s['id'])."'>".htmlspecialchars($s['name'])."</option>";
    }
    ?>
  </select>

  <label>Attachment (PDF, DOC, Image, ZIP)</label>
  <input type="file" name="attachment" accept=".pdf,.doc,.docx,.zip,.png,.jpg,.jpeg,.webp">

  <label>Title *</label>
  <input type="text" name="title" placeholder="e.g., Research Paper on Photosynthesis" required>

  <label>Description / Instructions</label>
  <textarea name="description" rows="4" placeholder="Provide clear instructions for students..."></textarea>

  <label>Due Date & Time *</label>
  <input type="datetime-local" name="due_date" required>

  <button type="submit" class="btn btn-primary">Create Assignment</button>
</form>

<h2>Your Assignments</h2>

<?php if (isset($_GET['deleted'])): ?>
  <div class="alert-success">
    Assignment deleted successfully.
  </div>
<?php endif; ?>

<div class="card">
  <table>
    <thead>
      <tr>
        <th>Title</th>
        <th>Subject</th>
        <th>Attachment</th>
        <th>Due Date</th>
        <th>Submissions</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $stmt = $pdo->prepare("
          SELECT a.id, a.title, a.description, a.due_date, a.file_path, a.subject_id,
                 s.name AS subject_name
          FROM assignments a
          JOIN subjects s ON a.subject_id = s.id
          WHERE a.teacher_id = ?
          ORDER BY a.due_date ASC
      ");
      $stmt->execute([$_SESSION['user_id']]);
      while($a = $stmt->fetch()){
          $isOverdue = $a['due_date'] && strtotime($a['due_date']) < time();
          $attachHtml = $a['file_path']
              ? '<a href="/accendo_2nd/'.htmlspecialchars($a['file_path']).'" target="_blank" class="btn-sm btn-primary">View File</a>'
              : '<span style="color:var(--text-light)">No file</span>';

          $subCount = 0;
          try {
              $countStmt = $pdo->prepare("SELECT COUNT(*) FROM assignment_submissions WHERE assignment_id = ?");
              $countStmt->execute([$a['id']]);
              $subCount = $countStmt->fetchColumn();
          } catch (Exception $e) {}

          echo "<tr>
                  <td><strong>" . htmlspecialchars($a['title']) . "</strong></td>
                  <td>" . htmlspecialchars($a['subject_name']) . "</td>
                  <td>$attachHtml</td>
                  <td class='" . ($isOverdue ? 'due-over' : 'due-soon') . "'>
                    " . ($a['due_date'] ? date('M j, Y <br> g:i A', strtotime($a['due_date'])) : 'No due date') . "
                  </td>
                  <td>
                    <button class='btn-sm btn-success' onclick='openSubmissionsModal(" . $a['id'] . ", " . json_encode(htmlspecialchars($a['title'])) . ")'>
                      View ($subCount)
                    </button>
                  </td>
                  <td>
                    <button class='btn-sm btn-primary' onclick='openEditModal(" . htmlspecialchars(json_encode($a), ENT_QUOTES) . ")'>Edit</button>
                    <a href='api/delete_assignment.php?id=" . $a['id'] . "' 
                       class='btn-sm btn-danger' 
                       onclick='return confirm(\"Delete this assignment? All submissions will be deleted.\")'>Delete</a>
                  </td>
                </tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Edit Assignment</h2>
      <span class="modal-close" onclick="closeEditModal()">×</span>
    </div>
    <form id="editForm" action="api/edit_assignment.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" id="edit_id">
      <label>Subject</label>
      <select name="subject_id" id="edit_subject_id" required>
        <?php
        $stmt = $pdo->prepare("SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        while($s = $stmt->fetch()) {
            echo "<option value='".htmlspecialchars($s['id'])."'>".htmlspecialchars($s['name'])."</option>";
        }
        ?>
      </select>
      <label>Title</label>
      <input type="text" name="title" id="edit_title" required>
      <label>Description</label>
      <textarea name="description" id="edit_description" rows="4"></textarea>
      <label>Current Attachment</label>
      <div id="current_file" style="margin: 8px 0 16px; color: var(--text-light);"></div>
      <label>New Attachment (optional)</label>
      <input type="file" name="attachment" accept=".pdf,.doc,.docx,.zip,.png,.jpg,.jpeg">
      <label>Due Date & Time</label>
      <input type="datetime-local" name="due_date" id="edit_due_date" required>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
  </div>
</div>

<!-- Submissions Modal -->
<div id="submissionsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Student Submissions: <span id="modalAssignmentTitle"></span></h2>
      <span class="modal-close" onclick="closeSubmissionsModal()">×</span>
    </div>
    <div id="submissionList">
      <p style="color: var(--text-light); text-align:center; padding: 40px 0;">Loading submissions...</p>
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
    fileDiv.innerHTML = data.file_path
        ? `<a href="/accendo_2nd/${data.file_path}" target="_blank" style="color: var(--primary);">Current file attached</a>`
        : '<span style="color:var(--text-light)">No file attached</span>';

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
                list.innerHTML = '<p style="color:var(--text-light); font-style:italic; text-align:center; padding:40px 0;">No submissions yet.</p>';
                return;
            }

            let html = `<table class="submission-table">
                <tr><th>Student</th><th>Submitted At</th><th>Status</th><th>File</th></tr>`;
            data.forEach(s => {
                const status = s.is_late ?
                    '<span class="status-late">Late</span>' :
                    '<span class="status-on-time">On Time</span>';
                const fileLink = s.file_path ?
                    `<a href="/accendo_2nd/${s.file_path}" target="_blank" class="btn-sm btn-primary">Download</a>` :
                    '<span style="color:var(--text-light)">—</span>';
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
            document.getElementById('submissionList').innerHTML = '<p style="color:#ef4444; text-align:center;">Failed to load.</p>';
        });
    document.getElementById('submissionsModal').classList.add('active');
}

function closeSubmissionsModal(){
    document.getElementById('submissionsModal').classList.remove('active');
}
</script>

<!-- GLOBAL THEME SYNC (Same as exams.php) -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const applyTheme = () => {
        const theme = localStorage.getItem('theme');
        if (theme === 'light') {
            document.body.classList.remove('dark-mode');
        } else {
            document.body.classList.add('dark-mode');
        }
    };

    applyTheme();

    // Listen for theme changes from Settings page
    window.addEventListener('storage', (e) => {
        if (e.key === 'theme') applyTheme();
    });
});
</script>

<?php include 'includes/footer.php'; ?>