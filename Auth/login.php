<?php 
require_once '../config/db.php';

// === LOGO: prefer uploaded logo, fallback to letter ===
$logo_candidates = [
  '../uploads/accendo_logo.jpeg',
  '../uploads/accendo_logo.jpg',
  '../uploads/accendo_logo.png',
  '../Admin/uploads/accendo_logo.jpeg',
  '../Admin/uploads/accendo_logo.jpg',
  '../Admin/uploads/accendo_logo.png',
];
$logo_rel = null;
$logo_exists = false;
foreach ($logo_candidates as $cand) {
  $abs_path = __DIR__ . '/' . $cand;
  if (file_exists($abs_path) && is_file($abs_path)) {
    $logo_rel = $cand;
    $logo_exists = true;
    break;
  }
}
// If none found, $logo_rel remains null and we'll show the text fallback

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
      /* Dark blue background behind the floating login */
      background: linear-gradient(135deg, #071430, #123a7a);
    }
    /* Card with left logo column and right form column */
    .login-box {
      background: white;
      border-radius: 16px;
      width: 100%;
      max-width: 900px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      display: flex;
      overflow: hidden;
      align-items: stretch;
      text-align: left;
    }
    .logo-column {
      flex: 0 0 260px;
      /* Darker blue gradient for a deeper, professional look */
      background: linear-gradient(135deg, #0f2a6b, #2746b2);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 28px;
    }
    .form-column {
      flex: 1 1 auto;
      padding: 40px 36px;
    }
    /* Rectangle logo tile (adjusted size to fit design) */
    .logo {
      width: 300px;
      height: 300px;
      border-radius: 10px; /* rounded rectangle */
      margin: 0 auto 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      background: transparent; /* image will show */
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }
    .logo img {
      max-width: 300px;
      max-height: 300px;
      width: auto;
      height: auto;
      object-fit: contain; /* show full logo without cropping */
      display: block;
    }
    /* Fallback text styling when logo file is missing */
    .logo.logo-text {
      background: #7B61FF;
      color: white;
      font-size: 1.8rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0 8px;
    }
    .form-column h2 {
      margin: 0 0 12px;
      color: #ffffffff;
      font-size: 1.8rem;
      text-align: left;
      text-transform: uppercase;
      letter-spacing: 0.6px;
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
    /* Password row: input + toggle button */
    .password-row {
      display: flex;
      gap: 8px;
      align-items: center;
    }
    .password-row .toggle-password {
      background: #F3F4F6;
      border: 1px solid #E5E7EB;
      color: #374151;
      width: 44px;
      height: 40px;
      padding: 6px;
      border-radius: 8px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      line-height: 0;
    }
    .password-row .toggle-password .icon { display: block; }
    .password-row .toggle-password .icon-hide { display: none; }
    .password-row .toggle-password.active { background: #E9EFFD; color: #0f2a6b; }
    .password-row .toggle-password.active .icon-show { display: none; }
    .password-row .toggle-password.active .icon-hide { display: block; }
    .password-row .toggle-password:focus {
      outline: 2px solid rgba(123,97,255,0.25);
      outline-offset: 2px;
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
      background: linear-gradient(135deg, #162447,); 
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
    /* Responsive: stack columns on small screens */
    @media (max-width: 720px) {
      .login-box {
        flex-direction: column;
        max-width: 420px;
      }
      .logo-column {
        flex: none;
        width: 100%;
        padding: 18px 20px;
      }
      .form-column {
        padding: 16px 18px;
      }
      .login-container { padding: 12px; }
      .form-group { text-align: left; }
      .form-column h2 { text-align: center; }
      .logo { width: 120px; height: 56px; }
      .logo img { max-width: 110px; max-height: 48px; }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-box">
      <div class="logo-column">
        <?php if ($logo_exists): ?>
          <div class="logo">
            <img src="<?= htmlspecialchars($logo_rel) ?>" alt="Accendo logo" loading="lazy" />
          </div>
        <?php else: ?>
          <div class="logo logo-text">A</div>
        <?php endif; ?>
      </div>

      <div class="form-column">
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
            <div class="password-row">
              <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                autocomplete="current-password"
                aria-describedby="password-help"
              >
              <button type="button" id="togglePassword" class="toggle-password" aria-pressed="false" aria-controls="password" aria-label="Show password">
                <!-- Eye open icon (visible when password is hidden) -->
                <svg class="icon icon-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <!-- Eye with slash icon (visible when password is shown) -->
                <svg class="icon icon-hide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.64 21.64 0 0 1 5.06-5.94"></path>
                  <path d="M1 1l22 22"></path>
                  <path d="M9.88 9.88A3 3 0 0 0 14.12 14.12"></path>
                </svg>
              </button>
            </div>
            <small id="password-help" style="color:#6B7280;font-size:0.8rem;">Toggle to show or hide password</small>
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

    // Password visibility toggle
    (function(){
      const pwd = document.getElementById('password');
      const btn = document.getElementById('togglePassword');
      if (!pwd || !btn) return;
      btn.addEventListener('click', function(){
        const isPassword = pwd.type === 'password';
        pwd.type = isPassword ? 'text' : 'password';
        btn.setAttribute('aria-pressed', String(!isPassword));
        btn.classList.toggle('active', !isPassword);
        btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
      });
      // Allow Enter/Space to toggle when focused
      btn.addEventListener('keydown', function(e){
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          btn.click();
        }
      });
    })();

    // Accessibility: Focus management
    document.querySelectorAll('input').forEach(input => {
      input.addEventListener('invalid', () => {
        input.focus();
      });
    });
  </script>
</body>
</html>