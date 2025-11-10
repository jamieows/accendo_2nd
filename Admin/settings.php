<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<h1>Admin Settings</h1>
<div class="card">
  <p><strong>System Time (PH):</strong> <?= date('F j, Y g:i A') ?> (Asia/Manila)</p>
  <p><strong>Database:</strong> accendo_db</p>
  <p><strong>Version:</strong> Accendo LMS v2.0</p>
  <hr>
  <p>More settings coming soon.</p>
</div>

<?php include 'includes/footer.php'; ?>