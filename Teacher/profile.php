<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
$user = $pdo->query("SELECT * FROM users WHERE id = {$_SESSION['user_id']}")->fetch();
?>
<?php include 'includes/header.php'; ?>

<h1>Teacher Profile</h1>
<div class="card">
  <form action="api/profile_update.php" method="POST">
    <label>First Name</label>
    <input type="text" name="first_name" value="<?= $user['first_name'] ?>" required>

    <label>Last Name</label>
    <input type="text" name="last_name" value="<?= $user['last_name'] ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= $user['email'] ?>" required>

    <hr>
    <label>Change Password (leave blank to keep)</label>
    <input type="password" name="old_password" placeholder="Current password">
    <input type="password" name="new_password" placeholder="New password">
    <input type="password" name="confirm_password" placeholder="Confirm new">

    <button type="submit" class="btn" style="margin-top:15px;">Update Profile</button>
  </form>
</div>

<?php include 'includes/footer.php'; ?>