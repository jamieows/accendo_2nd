<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

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
  <h1>Manage Users</h1>
  <p class="subtitle">Manage user accounts and roles • <?= date('l, F j, Y') ?> • PH Time: <span id="ph-clock"></span></p>
</div>

<div class="card">
  <div class="table-responsive">
    <table>
      <tr><th>Name</th><th>ID Number</th><th>Role</th><th>Action</th></tr>
      <?php
      $stmt = $pdo->query("SELECT * FROM users WHERE role != 'admin' ORDER BY role, first_name");
      while($u = $stmt->fetch()){
          echo "<tr>
                  <td>{$u['first_name']} {$u['last_name']}</td>
                  <td>{$u['id_number']}</td>
                  <td><strong>".ucfirst($u['role'])."</strong></td>
                  <td>
                    <a href='api/manage_users.php?action=delete&id={$u['id']}' 
                       class='btn btn-danger btn-sm delete-btn' 
                       onclick='return confirm(\"Delete {$u['first_name']}?\")'>Delete</a>
                  </td>
                </tr>";
      }
      ?>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>