<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<div class="page-header">
  <h1>Manage Courses</h1>
  <p class="subtitle">Assign subjects to teachers and enroll students • <?= date('l, F j, Y') ?> • PH Time: <span id="ph-clock"></span></p>
</div>

<!-- Teacher Assignment Section -->
<div class="card">
  <h2>Assign Subjects to Teachers</h2>
  <form method="POST" action="api/manage_courses.php" class="assignment-form">
    <div class="form-row">
      <div class="form-group">
        <label for="teacher_id">Teacher</label>
        <select id="teacher_id" name="teacher_id" required>
          <option value="">Select Teacher</option>
          <?php
          $stmt = $pdo->query("SELECT id, first_name, last_name FROM users WHERE role='teacher' ORDER BY last_name");
          while($t = $stmt->fetch()) 
            echo "<option value='{$t['id']}'>{$t['first_name']} {$t['last_name']}</option>";
          ?>
        </select>
      </div>
      <div class="form-group">
        <label for="teacher_subject">Subject</label>
        <select id="teacher_subject" name="subject_id" required>
          <option value="">Select Subject</option>
          <?php
          $stmt = $pdo->query("SELECT id, name FROM subjects ORDER BY name");
          while($s = $stmt->fetch()) 
            echo "<option value='{$s['id']}'>{$s['name']}</option>";
          ?>
        </select>
      </div>
      <button type="submit" class="btn">Assign to Teacher</button>
    </div>
  </form>

  <h3>Current Teacher Assignments</h3>
  <div class="table-responsive">
    <table class="assignment-table">
      <thead>
        <tr><th>Teacher</th><th>Subject</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php
        $stmt = $pdo->query("SELECT u.first_name, u.last_name, s.name, ts.id 
                             FROM teacher_subjects ts
                             JOIN users u ON ts.teacher_id = u.id
                             JOIN subjects s ON ts.subject_id = s.id
                             ORDER BY u.last_name");
        while($r = $stmt->fetch()){
            echo "<tr>
                    <td>{$r['first_name']} {$r['last_name']}</td>
                    <td><span class='subject-tag'>{$r['name']}</span></td>
                    <td>
                      <a href='api/manage_courses.php?remove={$r['id']}&type=teacher' 
                         class='btn btn-danger btn-sm' 
                         onclick='return confirm(\"Remove this assignment?\")'>Remove</a>
                    </td>
                  </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Student Enrollment Section -->
<div class="card">
  <h2>Enroll Students in Subjects</h2>
  <form method="POST" action="api/manage_courses.php" class="assignment-form">
    <div class="form-row">
      <div class="form-group">
        <label for="student_id">Student</label>
        <select id="student_id" name="student_id" required>
          <option value="">Select Student</option>
          <?php
          $stmt = $pdo->query("SELECT id, first_name, last_name, year_level, course FROM users WHERE role='student' ORDER BY last_name");
          while($stu = $stmt->fetch()) 
            echo "<option value='{$stu['id']}'>{$stu['first_name']} {$stu['last_name']} (Yr {$stu['year_level']}, {$stu['course']})</option>";
          ?>
        </select>
      </div>
      <div class="form-group">
        <label for="student_subject">Subject</label>
        <select id="student_subject" name="subject_id" required>
          <option value="">Select Subject</option>
          <?php
          $stmt = $pdo->query("SELECT id, name FROM subjects ORDER BY name");
          while($s = $stmt->fetch()) 
            echo "<option value='{$s['id']}'>{$s['name']}</option>";
          ?>
        </select>
      </div>
      <button type="submit" class="btn btn-success">Enroll Student</button>
    </div>
  </form>

  <h3>Current Student Enrollments</h3>
  <div class="table-responsive">
    <table class="assignment-table">
      <thead>
        <tr><th>Student</th><th>Subject</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php
        $stmt = $pdo->query("SELECT u.first_name, u.last_name, s.name, ss.id 
                             FROM student_subjects ss
                             JOIN users u ON ss.student_id = u.id
                             JOIN subjects s ON ss.subject_id = s.id
                             ORDER BY u.last_name");
        while($r = $stmt->fetch()){
            echo "<tr>
                    <td>{$r['first_name']} {$r['last_name']}</td>
                    <td><span class='subject-tag'>{$r['name']}</span></td>
                    <td>
                      <a href='api/manage_courses.php?remove={$r['id']}&type=student' 
                         class='btn btn-danger btn-sm' 
                         onclick='return confirm(\"Unenroll this student?\")'>Unenroll</a>
                    </td>
                  </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>