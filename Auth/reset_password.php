<?php
require_once '../config/db.php';
require_once '../config/mailer.sample.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? null);
$error = null;
$success = null;

if (!$token) {
    $error = 'Invalid password reset link.';
} else {
    // Find token
    $stmt = $pdo->prepare('SELECT pr.user_id, pr.expires_at, u.email FROM password_resets pr JOIN users u ON u.id = pr.user_id WHERE pr.token = ? LIMIT 1');
    $stmt->execute([$token]);
    $row = $stmt->fetch();

    if (!$row) {
        $error = 'Invalid or expired password reset link.';
    } elseif (strtotime($row['expires_at']) < time()) {
        $error = 'This password reset link has expired.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if ($password === '' || $confirm === '') {
        $error = 'Please fill both password fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    }

    if (!$error) {
        // update password
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $row['user_id']]);
        // delete token(s)
        $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$row['user_id']]);
        $success = 'Your password has been reset. You may now <a href="login.php">log in</a>.';
    }
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reset Password | Accendo LMS</title>
  <link rel="stylesheet" href="../assets/css/global.css">
  <style>
    .center-card { max-width:640px; margin:80px auto; background:white; padding:28px; border-radius:12px; box-shadow:0 12px 30px rgba(0,0,0,0.08); }
    .muted { color:#6B7280; }
    .error { background:#FEE2E2; color:#DC2626; padding:10px 12px; border-radius:8px; margin-bottom:12px; }
    .success { background:#ECFDF5; color:#065F46; padding:10px 12px; border-radius:8px; margin-bottom:12px; }
    input[type="password"] { width:100%; padding:10px 12px; border:1px solid #E5E7EB; border-radius:8px; }
    .btn { display:inline-block; padding:10px 14px; background:#7B61FF; color:white; border-radius:8px; text-decoration:none; }
  </style>
</head>
<body>
  <div class="center-card">
    <h2>Reset Password</h2>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="success"><?= $success ?></div>
    <?php elseif (!$error): ?>
      <p class="muted">Enter a new password for your account.</p>
      <form method="post">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div style="margin:12px 0;">
          <label for="password">New password</label>
          <input id="password" name="password" type="password" required>
        </div>
        <div style="margin:12px 0;">
          <label for="password_confirm">Confirm password</label>
          <input id="password_confirm" name="password_confirm" type="password" required>
        </div>
        <div style="margin-top:12px;">
          <button class="btn" type="submit">Save new password</button>
          <a href="login.php" style="margin-left:12px;">Back to Login</a>
        </div>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
