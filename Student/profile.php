<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
$user = $pdo->query("SELECT * FROM users WHERE id = {$_SESSION['user_id']}")->fetch();
?>
<?php include 'includes/header.php'; ?>

<h1>Student Profile</h1>
<div class="card">
  <form action="api/profile_update.php" method="POST">
    <label>First Name</label>
    <input type="text" name="first_name" value="<?= $user['first_name'] ?>" required>

    <label>Last Name</label>
    <input type="text" name="last_name" value="<?= $user['last_name'] ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= $user['email'] ?>" required>

    <hr>
    <label>Change Password</label>
    <input type="password" name="old_password" placeholder="Current password">
    <input type="password" name="new_password" placeholder="New password">
    <input type="password" name="confirm_password" placeholder="Confirm">

    <button type="submit" class="btn">Update</button>
  </form>
</div>

<?php include 'includes/footer.php'; ?>