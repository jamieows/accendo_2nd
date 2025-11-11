<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<!-- FLASH MESSAGE (RIGHT TOP - SAME AS BEFORE) -->
<?php if (!empty($_SESSION['flash_message'])): ?>
  <div style="position:fixed; top:20px; right:20px; padding:16px 24px; border-radius:12px; color:white; font-weight:600; z-index:9999; box-shadow:0 10px 30px rgba(0,0,0,0.3); background: <?= $_SESSION['flash_type'] === 'warning' ? '#f39c12' : '#27ae60' ?>;">
    <?= htmlspecialchars($_SESSION['flash_message']) ?>
    <span style="float:right; cursor:pointer; margin-left:20px;" onclick="this.parentElement.remove()">Close</span>
  </div>
  <?php 
  unset($_SESSION['flash_message']); 
  unset($_SESSION['flash_type']); 
  ?>
  <script>setTimeout(() => document.querySelector('[style*="position:fixed"]')?.remove(), 5000);</script>
<?php endif; ?>

<div class="page-header">
  <h1>Manage Courses</h1>
  <p class="subtitle">Assign subjects to teachers and enroll students • <?= date('l, F j, Y') ?> • PH Time: <span id="ph-clock"></span></p>
</div>

<!-- TWO SEPARATE ASSIGNMENT CARDS -->
<div class="cards-container">
  <!-- CARD 1: ASSIGN TEACHER -->
  <div class="card">
    <h2>Assign Subject to Teacher</h2><br>
    <form method="POST" action="api/manage_courses.php" class="assignment-form">
      <div class="form-row">
        <div class="form-group">
          <label>Teacher</label>
          <select name="teacher_id" required>
            <option value="">Select Teacher</option>
            <?php
            $stmt = $pdo->query("SELECT id, first_name, last_name FROM users WHERE role='teacher' ORDER BY last_name");
            while($t = $stmt->fetch()) 
              echo "<option value='{$t['id']}'>{$t['first_name']} {$t['last_name']}</option>";
            ?>
          </select>
        </div>
        <div class="form-group">
          <label>Subject</label>
          <select name="subject_id" required>
            <option value="">Select Subject</option>
            <?php
            $stmt = $pdo->query("SELECT id, name FROM subjects ORDER BY name");
            while($s = $stmt->fetch()) 
              echo "<option value='{$s['id']}'>{$s['name']}</option>";
            ?>
          </select>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Assign Teacher</button>
    </form>
  </div>

  <!-- CARD 2: ENROLL STUDENT -->
  <div class="card">
    <h2>Enroll Student in Subject</h2><br>
    <form method="POST" action="api/manage_courses.php" class="assignment-form">
      <div class="form-row">
        <div class="form-group">
          <label>Student</label>
          <select name="student_id" required>
            <option value="">Select Student</option>
            <?php
            $stmt = $pdo->query("SELECT id, first_name, last_name, year_level, course FROM users WHERE role='student' ORDER BY last_name");
            while($stu = $stmt->fetch()) 
              echo "<option value='{$stu['id']}'>{$stu['first_name']} {$stu['last_name']} (Yr {$stu['year_level']}, {$stu['course']})</option>";
            ?>
          </select>
        </div>
        <div class="form-group">
          <label>Subject</label>
          <select name="subject_id" required>
            <option value="">Select Subject</option>
            <?php
            $stmt = $pdo->query("SELECT id, name FROM subjects ORDER BY name");
            while($s = $stmt->fetch()) 
              echo "<option value='{$s['id']}'>{$s['name']}</option>";
            ?>
          </select>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Enroll Student</button>
    </form>
  </div>
</div>

<!-- SEARCH BAR - NOW BETWEEN ASSIGNMENT AND LIST -->
<div class="search-container">
  <input type="text" id="searchInput" placeholder="Search by name..." class="search-input" />
</div>

<!-- CURRENT ASSIGNMENTS & ENROLLMENTS -->
<div class="card">
  <h2>Current Assignments & Enrollments</h2><br>
  <div id="peopleContainer">
    <?php
    $usersStmt = $pdo->query("
      SELECT DISTINCT u.id, u.first_name, u.last_name
      FROM users u
      LEFT JOIN teacher_subjects ts ON u.id = ts.teacher_id
      LEFT JOIN student_subjects ss ON u.id = ss.student_id
      WHERE ts.teacher_id IS NOT NULL OR ss.student_id IS NOT NULL
      ORDER BY u.last_name, u.first_name
    ");
    $users = $usersStmt->fetchAll();

    if (empty($users)): ?>
      <p style="text-align:center; color:#666; font-style:italic; padding:40px; font-size:1.2rem;">
        No assignments or enrollments yet.
      </p>
    <?php else: foreach ($users as $user):
        $userId = $user['id'];
        $fullName = htmlspecialchars("{$user['first_name']} {$user['last_name']}");

        $teachingStmt = $pdo->prepare("SELECT ts.id AS link_id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ? ORDER BY s.name");
        $teachingStmt->execute([$userId]);
        $teaching = $teachingStmt->fetchAll();

        $enrolledStmt = $pdo->prepare("SELECT ss.id AS link_id, s.name FROM student_subjects ss JOIN subjects s ON ss.subject_id = s.id WHERE ss.student_id = ? ORDER BY s.name");
        $enrolledStmt->execute([$userId]);
        $enrolled = $enrolledStmt->fetchAll();

        $total = count($teaching) + count($enrolled);
    ?>
      <div class="person-card" data-name="<?= strtolower($fullName) ?>">
        <div class="person-header">
          <h3><?= $fullName ?></h3>
          <span class="subject-count"><?= $total ?> subject(s)</span>
        </div>
        <div class="person-body">
          <?php if (!empty($teaching)): ?>
            <div class="section-title teacher">Teaching</div>
            <ul class="subject-list">
              <?php foreach ($teaching as $sub): ?>
                <li class="subject-item">
                  <span class="subject-name">• <?= htmlspecialchars($sub['name']) ?></span>
                  <a href="api/manage_courses.php?remove=<?= $sub['link_id'] ?>&type=teacher"
                     class="remove-link"
                     onclick="return confirm('Remove <?= $fullName ?> from teaching <?= htmlspecialchars($sub['name']) ?>?')">
                    [Remove]
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>

          <?php if (!empty($enrolled)): ?>
            <div class="section-title student">Enrolled In</div>
            <ul class="subject-list">
              <?php foreach ($enrolled as $sub): ?>
                <li class="subject-item">
                  <span class="subject-name">• <?= htmlspecialchars($sub['name']) ?></span>
                  <a href="api/manage_courses.php?remove=<?= $sub['link_id'] ?>&type=student"
                     class="remove-link"
                     onclick="return confirm('Unenroll <?= $fullName ?> from <?= htmlspecialchars($sub['name']) ?>?')">
                    [Remove]
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- SAME DESIGN + SEARCH IN MIDDLE -->
<style>
  .search-container {
    max-width: 900px;
    margin: 30px auto;
    padding: 0 20px;
  }
  .search-input {
    width: 100%; 
    padding: 16px 20px; 
    border: 2px solid #e0d6ff; 
    border-radius: 12px;
    font-size: 1.1rem; 
    background: white; 
    box-sizing: border-box;
    transition: 0.3s;
  }
  .search-input:focus { 
    outline: none; 
    border-color: #7B61FF; 
    box-shadow: 0 0 0 4px rgba(123,97,255,0.2); 
  }

  .cards-container {
    display: flex;
    gap: 25px;
    margin: 20px 0;
    flex-wrap: wrap;
  }
  .cards-container .card {
    flex: 1;
    min-width: 300px;
  }

  .person-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    margin-bottom: 22px;
    border: 1px solid #e0d6ff;
  }

  .person-header {
    padding: 18px 25px;
    background: linear-gradient(135deg, #7B61FF, #A78BFA);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
  }
  .person-header h3 { margin: 0; font-size: 1.35rem; }
  .subject-count { font-size: 0.95rem; background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 20px; }

  .person-body { 
    padding: 25px; 
    background: #fdfbff; 
  }

  .section-title {
    font-weight: 600; 
    font-size: 1.15rem; 
    margin: 18px 0 12px; 
    padding-left: 5px;
  }
  .section-title.teacher { color: #7B61FF; }
  .section-title.student { color: #2980b9; }

  .subject-list { list-style: none; padding: 0; margin: 0; }
  .subject-item {
    padding: 11px 0;
    border-bottom: 1px dashed #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .subject-item:last-child { border-bottom: none; }

  .subject-name {
    font-weight: 700;
    color: #1a1a1a;
    font-size: 1.15rem;
    letter-spacing: 0.4px;
  }

  .remove-link {
    color: #e74c3c;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 6px 12px;
    background: #ffeaea;
    border-radius: 8px;
    text-decoration: none;
    transition: 0.2s;
  }
  .remove-link:hover {
    background: #e74c3c;
    color: white;
  }

  @media (max-width: 768px) {
    .cards-container { flex-direction: column; }
    .form-row { flex-direction: column; }
    select, button { width: 100%; margin-bottom: 10px; }
    .search-container { padding: 0 15px; }
    .person-header { flex-direction: column; text-align: center; gap: 10px; }
    .person-header h3 { font-size: 1.25rem; }
    .subject-item { flex-direction: column; align-items: flex-start; gap: 10px; }
    .remove-link { align-self: flex-end; }
  }
</style>

<script>
  // Real-time PH Clock
  function updateClock() {
    const now = new Date();
    const options = { timeZone: "Asia/Manila", hour12: true, hour: 'numeric', minute: '2-digit', second: '2-digit' };
    document.getElementById("ph-clock").textContent = now.toLocaleTimeString("en-PH", options);
  }
  updateClock();
  setInterval(updateClock, 1000);

  // Search
  document.getElementById('searchInput').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    document.querySelectorAll('.person-card').forEach(card => {
      const name = card.dataset.name || '';
      card.style.display = name.includes(term) ? '' : 'none';
    });
  });
</script>