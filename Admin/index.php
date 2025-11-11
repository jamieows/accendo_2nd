<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<div class="page-header">
  <h1>Admin Dashboard</h1>
  <p class="subtitle">Manage Accendo LMS • <?= date('l, F j, Y') ?> • PH Time: <span id="ph-clock"></span></p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
  <div class="stat-card" data-aos="fade-up">
    <div class="stat-icon users">Users</div>
    <div class="stat-value" id="total-users">
      <?php echo $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(); ?>
    </div>
    <div class="stat-label">Total User/s</div>
  </div>

  <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
    <div class="stat-icon teachers">Teachers</div>
    <div class="stat-value" id="total-teachers">
      <?php echo $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn(); ?>
    </div>
    <div class="stat-label">Teacher/s</div>
  </div>

  <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
    <div class="stat-icon students">Students</div>
    <div class="stat-value" id="total-students">
      <?php echo $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(); ?>
    </div>
    <div class="stat-label">Student/s</div>
  </div>

  <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
    <div class="stat-icon subjects">Subjects</div>
    <div class="stat-value" id="total-subjects">
      <?php echo $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn(); ?>
    </div>
    <div class="stat-label">Subject/s</div>
  </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
  <a href="manage_users.php" class="action-card">
    <span class="action-icon">Users</span>
    <h3>Manage Users</h3>
    <p>Remove users</p>
  </a>
  <a href="manage_courses.php" class="action-card">
    <span class="action-icon">Courses</span>
    <h3>Assign Courses</h3>
    <p>Link teachers to subjects</p>
    <p>Enroll students in courses</p>
  </a>
  <a href="settings.php" class="action-card">
    <span class="action-icon">Settings</span>
    <h3>System Settings</h3>
    <p>Configure LMS options</p>
    <p>Manage theme preferences</p>
    <p>Track Activity History</p>
  </a>
</div>

<!-- Recent Activity -->
<div class="card">
  <h2>Recent Logins</h2>
  <div class="table-responsive">
    <table class="activity-table">
      <thead>
        <tr>
          <th>User</th>
          <th>Role</th>
          <th>Last Login</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $pdo->query("
          SELECT first_name, last_name, role, created_at 
          FROM users 
          ORDER BY created_at DESC 
          LIMIT 8
        ");
        while ($u = $stmt->fetch()) {
          $time = new DateTime($u['created_at'], new DateTimeZone('UTC'));
          $time->setTimezone(new DateTimeZone('Asia/Manila'));
          $status = (time() - strtotime($u['created_at']) < 300) ? 'online' : 'offline';
          echo "<tr>
                  <td><strong>{$u['first_name']} {$u['last_name']}</strong></td>
                  <td><span class='role-badge {$u['role']}'>" . ucfirst($u['role']) . "</span></td>
                  <td>{$time->format('M j, Y g:i A')}</td>
                  <td><span class='status-dot $status'></span> $status</td>
                </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>