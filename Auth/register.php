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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register | Accendo</title>
  <link rel="stylesheet" href="../assets/css/global.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; margin:0; }
    .register-container { display:flex; justify-content:center; align-items:center; min-height:100vh; padding:40px; background: linear-gradient(135deg,#071430,#123a7a); }
  /* Slightly lighter card so inputs stand out against the page background */
  .card { background: #1f3b7a; padding:30px; border-radius:16px; max-width:500px; width:100%; box-shadow:0 10px 30px rgba(0,0,0,0.25); color: #FFFFFF; }
  .card h2 { text-align:center; margin-top:0; color: #FFFFFF; }
  .card label { color: #D1D5DB; }
  /* Inputs are lighter (white) so users see where to type */
  .input, select { width:100%; padding:10px 12px; margin-top:6px; margin-bottom:12px; border:1px solid #E5E7EB; border-radius:8px; font-size:1rem; background:#FFFFFF; color:#162447; }
    .btn { display:block; width:100%; padding:12px; background:#7B61FF; color:#fff; border-radius:8px; border:none; text-align:center; font-weight:600; text-decoration:none; }
  .error { color:#FCA5A5; text-align:center; margin-bottom:12px; }
    .footer { text-align:center; margin-top:20px; }
  .muted { color:#D1D5DB; font-size:0.95rem; }

    /* Dark mode support */
    .dark-mode .register-container { background: linear-gradient(135deg, #071430, #123a7a); }
    .dark-mode .card { background: #162447; color: #F9F7FE; box-shadow:none; }
  .dark-mode .input, .dark-mode select { background: #1F2937; border-color: #374151; color: #F9F7FE; }
    .dark-mode .btn { background: #7B61FF; }
  </style>
</head>
<body>
  <div class="register-container">
    <div class="card">
      <h2>Create Account</h2>
      <?php if (isset($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
      <form method="POST">
        <label>First Name</label>
        <input class="input" type="text" name="first_name" required>

        <label>Last Name</label>
        <input class="input" type="text" name="last_name" required>

        <label>Role</label>
        <select class="input" name="role" required>
          <option value="student">Student</option>
          <option value="teacher">Teacher</option>
        </select>

        <label>Email</label>
        <input class="input" type="email" name="email" required>

        <label>Username</label>
        <input class="input" type="text" name="username" required>

        <label>Password</label>
        <input class="input" type="password" name="password" required minlength="6">

        <button type="submit" class="btn" style="margin-top:8px;">Register</button>
      </form>

      <p class="footer muted">
        <a href="login.php" style="color:#7B61FF;">Already have an account? Login</a>
      </p>
    </div>
  </div>
</body>
</html>