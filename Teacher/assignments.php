<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<h1>Create Assignment</h1>
<form action="api/create_assignment.php" method="POST" class="card">
  <label>Subject</label>
  <select name="subject_id" required>
    <?php
    $stmt = $pdo->prepare("SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    while($s = $stmt->fetch()) echo "<option value='{$s['id']}'>{$s['name']}</option>";
    ?>
  </select>

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
    <tr><th>Title</th><th>Subject</th><th>Due</th><th>Action</th></tr>
    <?php
    $stmt = $pdo->prepare("SELECT a.id, a.title, s.name, a.due_date 
                           FROM assignments a JOIN subjects s ON a.subject_id = s.id 
                           WHERE a.teacher_id = ? ORDER BY a.due_date");
    $stmt->execute([$_SESSION['user_id']]);
    while($a = $stmt->fetch()){
        $dueClass = (strtotime($one['due_date']) < time()) ? 'due-over' : 'due-soon';
        echo "<tr>
                <td>{$a['title']}</td>
                <td>{$a['name']}</td>
                <td class='due-date $dueClass' data-due='{$a['due_date']}'>
                  ".date('M j, Y g:i A', strtotime($a['due_date']))."
                </td>
                <td>
                  <a href='api/create_assignment.php?delete={$a['id']}' 
                     class='btn btn-danger btn-sm' 
                     onclick='return confirm(\"Delete assignment?\")'>Delete</a>
                </td>
              </tr>";
    }
    ?>
  </table>
</div>

<?php include 'includes/footer.php'; ?>