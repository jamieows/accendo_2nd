<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<h1>Manage Users</h1>
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