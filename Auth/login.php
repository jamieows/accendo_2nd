<?php 
require_once '../config/db.php';

// === LOGIN PROCESS ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Support both plain-text and hashed passwords
        $valid = ($user['password'] === $password) || password_verify($password, $user['password']);

        if ($valid) {
            // === AUTO UPGRADE PLAIN-TEXT PASSWORD ===
            if ($user['password'] === $password && !password_verify($password, $user['password'])) {
                upgradePlainPassword($user['id'], $password); // Uses function from db.php
            }

            // === SET SESSION ===
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['name']    = $user['first_name'] . ' ' . $user['last_name'];

            // === REDIRECT SAFELY (NO LOOP) ===
            redirectByRole(); // Smart function from db.php
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Accendo LMS</title>
  <link rel="stylesheet" href="../assets/css/global.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Enhanced Login Styling */
    .login-container {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
      background: linear-gradient(135deg, #7B61FF, #A78BFA);
    }
    .login-box {
      background: white;
      padding: 40px;
      border-radius: 16px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    .logo {
      width: 80px;
      height: 80px;
      background: #7B61FF;
      border-radius: 50%;
      margin: 0 auto 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 2rem;
      font-weight: bold;
    }
    .login-box h2 {
      margin: 0 0 24px;
      color: #162447;
      font-size: 1.8rem;
    }
    .error-msg {
      background: #FEE2E2;
      color: #DC2626;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 0.95rem;
      border: 1px solid #FCA5A5;
    }
    .form-group {
      margin-bottom: 18px;
      text-align: left;
    }
    .form-group label {
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
      color: #374151;
    }
    .form-group input {
      width: 100%;
      padding: 12px 14px;
      border: 2px solid #E5E7EB;
      border-radius: 8px;
      font-size: 1rem;
      transition: border 0.2s;
    }
    .form-group input:focus {
      border-color: #7B61FF;
      box-shadow: 0 0 0 3px rgba(123, 97, 255, 0.2);
      outline: none;
    }
    .btn-login {
      width: 100%;
      padding: 14px;
      background: #7B61FF;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: 10px;
      transition: background 0.3s;
    }
    .btn-login:hover {
      background: #6D53E6;
    }
    .login-footer {
      margin-top: 24px;
      font-size: 0.95rem;
      color: #6B7280;
    }
    .login-footer a {
      color: #7B61FF;
      text-decoration: none;
      font-weight: 500;
    }
    .login-footer a:hover {
      text-decoration: underline;
    }

    /* Dark Mode */
    .dark-mode .login-container { 
      background: linear-gradient(135deg, #5A4BDA, #7C3AED); 
    }
    .dark-mode .login-box { 
      background: #162447; 
      color: #F9F7FE; 
    }
    .dark-mode .form-group label { 
      color: #D1D5DB; 
    }
    .dark-mode .form-group input { 
      background: #1F2937; 
      border-color: #374151; 
      color: #F9F7FE; 
    }
    .dark-mode .form-group input:focus { 
      border-color: #7B61FF; 
    }
    .dark-mode .error-msg { 
      background: #7F1D1D; 
      color: #FCA5A5; 
      border-color: #DC2626; 
    }
    .dark-mode .btn-login { 
      background: #7B61FF; 
    }
    .dark-mode .btn-login:hover { 
      background: #6D53E6; 
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-box">
      <div class="logo">A</div>
      <h2>Welcome Back</h2>
      <p style="color:#6B7280;font-size:0.95rem;margin-bottom:20px;">
        Sign in to your Accendo LMS account
      </p>

      <?php if (isset($error)): ?>
        <div class="error-msg" role="alert" aria-live="assertive">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" aria-label="Login Form" novalidate>
        <div class="form-group">
          <label for="username">Username</label>
          <input 
            type="text" 
            id="username" 
            name="username" 
            required 
            autofocus 
            autocomplete="username"
            aria-describedby="username-help"
            value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
          >
          <small id="username-help" style="color:#6B7280;font-size:0.8rem;">Enter your username</small>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input 
            type="password" 
            id="password" 
            name="password" 
            required 
            autocomplete="current-password"
          >
        </div>

        <button type="submit" class="btn-login">Sign In</button>
      </form>

      <div class="login-footer">
        <a href="register.php">Create an account</a> • 
        <a href="forgot_password.php">Forgot password?</a>
      </div>

      <p style="margin-top:20px;font-size:0.8rem;color:#9CA3AF;">
        © <?= date('Y') ?> Accendo LMS • Philippines<br>
        <span id="ph-time"></span>
      </p>
    </div>
  </div>

  <!-- Global JS -->
  <script src="../assets/js/global.js" defer></script>
  <script>
    // Live PH Time (Asia/Manila)
    function updateTime() {
      const now = new Date().toLocaleString('en-PH', {
        timeZone: 'Asia/Manila',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      });
      document.getElementById('ph-time').textContent = 'PH Time: ' + now;
    }
    updateTime();
    setInterval(updateTime, 1000);

    // Auto dark mode from localStorage
    if (localStorage.getItem('darkMode') === 'true') {
      document.body.classList.add('dark-mode');
    }

    // Accessibility: Focus management
    document.querySelectorAll('input').forEach(input => {
      input.addEventListener('invalid', () => {
        input.focus();
      });
    });
  </script>
</body>
</html>