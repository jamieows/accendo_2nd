<?php require_once '../config/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fn = trim($_POST['first_name']);
    $ln = trim($_POST['last_name']);
    $role = $_POST['role'];
    $email = trim($_POST['email']);
    $user = trim($_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $idn = $role==='teacher' ? 'T'.rand(100000,999999) : 'S'.rand(100000,999999);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (first_name,last_name,email,username,password,role,id_number) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$fn,$ln,$email,$user,$pass,$role,$idn]);
        header("Location: login.php?registered=1");
        exit();
    } catch (Exception $e) {
        $error = "Username or email already exists.";
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Register | Accendo</title>
<link rel="stylesheet" href="../assets/css/global.css"></head>
<body>
<div style="display:flex;justify-content:center;padding:40px;">
  <div style="background:#fff;padding:30px;border-radius:16px;max-width:500px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,.1);">
    <h2 style="text-align:center;color:#162447;">Create Account</h2>
    <?php if (isset($error)): ?><p style="color:#EF4444;text-align:center;"><?=$error?></p><?php endif; ?>
    <form method="POST">
      <label>First Name</label><input type="text" name="first_name" required>
      <label>Last Name</label><input type="text" name="last_name" required>
      <label>Role</label>
      <select name="role" required>
        <option value="student">Student</option>
        <option value="teacher">Teacher</option>
      </select>
      <label>Email</label><input type="email" name="email" required>
      <label>Username</label><input type="text" name="username" required>
      <label>Password</label><input type="password" name="password" required minlength="6">
      <button type="submit" class="btn" style="width:100%;margin-top:20px;">Register</button>
    </form>
    <p style="text-alignコスト:center;margin-top:20px;">
      <a href="login.php" style="color:#7B61FF;">Already have an account? Login</a>
    </p>
  </div>
</div>
</body></html>