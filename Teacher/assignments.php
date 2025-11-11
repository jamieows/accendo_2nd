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
  /* spacing between label and control, and more readable label word spacing */
  form.card label {
    display:block;
    margin:12px 0 8px;
    font-weight:600;
    word-spacing:0.28rem;   /* extra space between words in label text */
    letter-spacing:0.15px;  /* subtle letter spacing for a professional look */
  }

  form.card select,
  form.card input[type="text"],
  form.card textarea,
  form.card input[type="datetime-local"],
  form.card input[type="file"]{
    display:block;
    width:100%;
    padding:8px;
    margin-bottom:16px; /* spacing between controls */
    border-radius:6px;
    border:1px solid rgba(0,0,0,0.08);
    box-sizing:border-box;
    background:#fff;
  }

  /* table spacing */
  table th, table td{ padding:10px 12px; text-align:left; vertical-align:middle; }
</style>

<h1>Create Assignment</h1>
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
<div class="card">
  <table>
    <tr><th>Title</th><th>Subject</th><th>Attachment</th><th>Due</th><th>Action</th></tr>
    <?php
    $stmt = $pdo->prepare("SELECT a.id, a.title, s.name, a.due_date
                           FROM assignments a
                           JOIN subjects s ON a.subject_id = s.id
                           WHERE a.teacher_id = ? ORDER BY a.due_date");
    $stmt->execute([$_SESSION['user_id']]);
    while($a = $stmt->fetch()){
        $dueClass = (!empty($a['due_date']) && strtotime($a['due_date']) < time()) ? 'due-over' : 'due-soon';
        // no file_path column available — show placeholder
        $attachHtml = "<span style='color:#6b7280'>—</span>";
        echo "<tr>
                <td>".htmlspecialchars($a['title'])."</td>
                <td>".htmlspecialchars($a['name'])."</td>
                <td>$attachHtml</td>
                <td class='due-date $dueClass' data-due='".htmlspecialchars($a['due_date'])."'>"
                  .(empty($a['due_date']) ? '—' : date('M j, Y g:i A', strtotime($a['due_date'])))."
                </td>
                <td>
                  <a href='api/create_assignment.php?delete=".urlencode($a['id'])."' 
                     class='btn btn-danger btn-sm' 
                     onclick='return confirm(\"Delete assignment?\")'>Delete</a>
                </td>
              </tr>";
    }
    ?>
  </table>
</div>

<?php include 'includes/footer.php'; ?>