<?php
require_once '../config/db.php';
require_once '../config/mailer.sample.php';

// Determine if we should display the reset link directly (local/dev)
// We'll show the link when running on localhost to simplify testing.
$is_local = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) || in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);

$notice = null;
$show_link = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    if ($identifier === '') {
        $notice = 'Please enter your email or username.';
    } else {
        // Generic message regardless of whether the account exists
        $notice = 'If an account exists for that email or username, you will receive password reset instructions shortly.';

        // Find user
        $stmt = $pdo->prepare("SELECT id, email, username, first_name FROM users WHERE email = ? OR username = ? LIMIT 1");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user) {
            // Remove previous tokens
            $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$user['id']]);

            // Create token
            $token = bin2hex(random_bytes(32));
            $expires = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

            $ins = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
            $ins->execute([$user['id'], $token, $expires]);

      // Build absolute reset URL safely
      $scheme = !empty($_SERVER['REQUEST_SCHEME']) ? rtrim($_SERVER['REQUEST_SCHEME'], ':/') : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
      $resetUrl = sprintf('%s://%s/accendo_2nd/Auth/reset_password.php?token=%s', $scheme, $_SERVER['HTTP_HOST'], $token);

            $subject = 'Accendo LMS — Password reset instructions';
            $body = "Hello " . ($user['first_name'] ?? $user['username'] ?? '') . ",\n\n" .
                "We received a request to reset your password. Click the link below to choose a new password (this link expires in 1 hour):\n\n" .
                $resetUrl . "\n\nIf you didn't request this, you can ignore this message.\n";

            // Try to send mail; on local/dev we'll also display the link
            send_mail($user['email'], $subject, $body);
            if ($is_local) {
                $show_link = $resetUrl;
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Forgot Password | Accendo LMS</title>
  <link rel="stylesheet" href="../assets/css/global.css">
  <style>
    .center-card { max-width:640px; margin:80px auto; background:white; padding:28px; border-radius:12px; box-shadow:0 12px 30px rgba(0,0,0,0.08); }
    .muted { color:#6B7280; }
    .notice { background:#F3F4F6; padding:10px 12px; border-radius:8px; margin-bottom:12px; }
    .btn { display:inline-block; padding:10px 14px; background:#7B61FF; color:white; border-radius:8px; text-decoration:none; }
    .form-row { margin-bottom:14px; }
    input[type="text"] { width:100%; padding:10px 12px; border:1px solid #E5E7EB; border-radius:8px; }
    pre.link { background:#0f172a; color:#fff; padding:10px; border-radius:6px; overflow:auto }
  </style>
</head>
<body>
  <div class="center-card">
    <h2>Forgot Password?</h2>
    <p class="muted">Enter the email address or username associated with your account. We'll send instructions to reset your password.</p>

    <?php if ($notice): ?>
      <div class="notice"><?= htmlspecialchars($notice) ?></div>
    <?php endif; ?>

    <?php if ($show_link): ?>
      <p>Dev mode: here is your reset link (expires in 1 hour):</p>
      <pre class="link"><?= htmlspecialchars($show_link) ?></pre>
      <p><a class="btn" href="<?= htmlspecialchars($show_link) ?>">Open reset link</a></p>
    <?php endif; ?>

    <form method="post">
      <div class="form-row">
        <label for="identifier">Email or Username</label>
        <input id="identifier" name="identifier" type="text" required value="<?= isset($_POST['identifier']) ? htmlspecialchars($_POST['identifier']) : '' ?>">
      </div>
      <div class="form-row">
        <button type="submit" class="btn">Send reset instructions</button>
        <a href="login.php" style="margin-left:12px;">Back to Login</a>
      </div>
    </form>
  </div>
</body>
</html>