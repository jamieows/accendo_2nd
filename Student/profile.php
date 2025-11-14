<?php 
require_once '../config/db.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}

$stmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: ../Auth/login.php");
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<div class="profile-container">
  <header class="page-header">
    <h1 class="page-title">Student Profile</h1>
    <p class="page-subtitle">Update your personal information</p>
  </header>

  <div class="card profile-card">
    <form id="profileForm" action="api/profile_update.php" method="POST" novalidate>
      
      <!-- Personal Info -->
      <div class="form-grid">
        <div class="form-group">
          <label for="first_name">First Name <span class="required">*</span></label>
          <input type="text" name="first_name" id="first_name" 
                 value="<?= htmlspecialchars($user['first_name']) ?>" required>
          <small class="error-text" id="first_name_error"></small>
        </div>

        <div class="form-group">
          <label for="last_name">Last Name <span class="required">*</span></label>
          <input type="text" name="last_name" id="last_name" 
                 value="<?= htmlspecialchars($user['last_name']) ?>" required>
          <small class="error-text" id="last_name_error"></small>
        </div>
      </div>

      <div class="form-group">
        <label for="email">Email Address <span class="required">*</span></label>
        <input type="email" name="email" id="email" 
               value="<?= htmlspecialchars($user['email']) ?>" required>
        <small class="error-text" id="email_error"></small>
      </div>

      <hr class="divider">

      <!-- Password Change (Optional) -->
      <fieldset class="password-section">
        <legend>Change Password (Optional)</legend>

        <div class="form-group password-wrapper">
          <label for="old_password">Current Password</label>
          <div class="input-with-toggle">
            <input type="password" name="old_password" id="old_password" 
                   placeholder="Enter current password" autocomplete="new-password">
            <button type="button" class="toggle-password" aria-label="Toggle visibility" data-target="old_password">
              <svg class="eye-open" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5C21.27 7.61 17 4.5 12 4.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
              <svg class="eye-closed" viewBox="0 0 24 24"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.22 2.71-2.85 3.34-4.75C21.27 7.61 17 4.5 12 4.5c-1.27 0-2.49.23-3.62.66l2.1 2.1C11.06 7.13 11.52 7 12 7zm5.66 9.16l-2.5-2.5c.2-.96.34-1.95.34-3 .0-3.31-2.69-6-6-6-.98 0-1.91.23-2.75.64L4.7 4.7 3.29 3.29 2 4.59l2.34 2.34C2.73 8.39 1.73 10.39 1 12c1.73 4.39 6 7.5 11 7.5 2.09 0 4-.61 5.61-1.62l2.8 2.8 1.41-1.41-3.16-3.16zM12 17c-2.76 0-5-2.24-5-5 0-.36.04-.71.11-1.05l1.68 1.68c-.09.28-.14.58-.14.89 0 1.66 1.34 3 3 3 .31 0 .61-.05.89-.14l1.68 1.68c-.34.07-.69.11-1.05.11z"/></svg>
            </button>
          </div>
          <small class="error-text" id="old_password_error"></small>
        </div>

        <div class="form-grid">
          <div class="form-group password-wrapper">
            <label for="new_password">New Password</label>
            <div class="input-with-toggle">
              <input type="password" name="new_password" id="new_password" 
                     placeholder="At least 8 characters" autocomplete="new-password">
              <button type="button" class="toggle-password" aria-label="Toggle visibility" data-target="new_password">
                <svg class="eye-open" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5C21.27 7.61 17 4.5 12 4.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                <svg class="eye-closed" viewBox="0 0 24 24"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.22 2.71-2.85 3.34-4.75C21.27 7.61 17 4.5 12 4.5c-1.27 0-2.49.23-3.62.66l2.1 2.1C11.06 7.13 11.52 7 12 7zm5.66 9.16l-2.5-2.5c.2-.96.34-1.95.34-3 .0-3.31-2.69-6-6-6-.98 0-1.91.23-2.75.64L4.7 4.7 3.29 3.29 2 4.59l2.34 2.34C2.73 8.39 1.73 10.39 1 12c1.73 4.39 6 7.5 11 7.5 2.09 0 4-.61 5.61-1.62l2.8 2.8 1.41-1.41-3.16-3.16zM12 17c-2.76 0-5-2.24-5-5 0-.36.04-.71.11-1.05l1.68 1.68c-.09.28-.14.58-.14.89 0 1.66 1.34 3 3 3 .31 0 .61-.05.89-.14l1.68 1.68c-.34.07-.69.11-1.05.11z"/></svg>
              </button>
            </div>
            <small class="error-text" id="new_password_error"></small>
          </div>

          <div class="form-group password-wrapper">
            <label for="confirm_password">Confirm New Password</label>
            <div class="input-with-toggle">
              <input type="password" name="confirm_password" id="confirm_password" 
                     placeholder="Repeat new password" autocomplete="new-password">
              <button type="button" class="toggle-password" aria-label="Toggle visibility" data-target="confirm_password">
                <svg class="eye-open" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5C21.27 7.61 17 4.5 12 4.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                <svg class="eye-closed" viewBox="0 0 24 24"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.22 2.71-2.85 3.34-4.75C21.27 7.61 17 4.5 12 4.5c-1.27 0-2.49.23-3.62.66l2.1 2.1C11.06 7.13 11.52 7 12 7zm5.66 9.16l-2.5-2.5c.2-.96.34-1.95.34-3 .0-3.31-2.69-6-6-6-.98 0-1.91.23-2.75.64L4.7 4.7 3.29 3.29 2 4.59l2.34 2.34C2.73 8.39 1.73 10.39 1 12c1.73 4.39 6 7.5 11 7.5 2.09 0 4-.61 5.61-1.62l2.8 2.8 1.41-1.41-3.16-3.16zM12 17c-2.76 0-5-2.24-5-5 0-.36.04-.71.11-1.05l1.68 1.68c-.09.28-.14.58-.14.89 0 1.66 1.34 3 3 3 .31 0 .61-.05.89-.14l1.68 1.68c-.34.07-.69.11-1.05.11z"/></svg>
              </button>
            </div>
            <small class="error-text" id="confirm_password_error"></small>
          </div>
        </div>
      </fieldset>

      <button type="submit" class="btn btn-primary btn-lg">
        Update Profile
      </button>
    </form>
  </div>
</div>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

  :root {
    --bg: #0b1220; --card: #1b243a; --text: #e6eef8; --text-muted: #9ca3af;
    --accent: #7c3aed; --accent-hover: #6d28d9; --border: #2f354d;
    --danger: #dc2626; --success: #10b981; --radius: 12px;
    --shadow: 0 8px 32px rgba(0,0,0,0.4); --transition: all 0.25s ease;
  }

  body {background:var(--bg);color:var(--text);font-family:'Inter',system-ui,sans-serif;margin:0;line-height:1.6;}
  .profile-container {max-width:720px;margin:2rem auto;padding:0 1rem;}
  .page-header {text-align:center;margin-bottom:2rem;}
  .page-title {font-size:2rem;font-weight:700;margin:0 0 .5rem;background:linear-gradient(90deg,#7c3aed,#c084fc);-webkit-background-clip:text;color:transparent;}
  .page-subtitle {color:var(--text-muted);font-size:1rem;margin:0;}

  .card {background:var(--card);border-radius:var(--radius);padding:2rem;box-shadow:var(--shadow);border:1px solid var(--border);}
  .form-grid {display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
  .form-group {margin-bottom:1.25rem;position:relative;}
  label {display:block;margin-bottom:.5rem;font-weight:600;color:#e2e8f0;font-size:.95rem;}
  .required {color:var(--danger);}

  .password-wrapper .input-with-toggle {
    position: relative; display: flex; align-items: center;
  }
  .password-wrapper input { flex: 1; padding-right: 3rem; }
  .toggle-password {
    position: absolute; right: .75rem; background: none; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center; color: var(--text-muted);
  }
  .toggle-password:hover { color: var(--text); }
  .toggle-password svg { fill: currentColor; width: 18px; height: 18px; }
  .toggle-password .eye-open { display: none; }
  .toggle-password .eye-closed { display: block; }
  .toggle-password.show .eye-open { display: block; }
  .toggle-password.show .eye-closed { display: none; }

  input[type=text], input[type=email], input[type=password] {
    width:100%; padding:.75rem 1rem; border:1px solid var(--border); border-radius:8px;
    background:#111827; color:var(--text); font-size:1rem; transition:var(--transition);
  }
  input:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px rgba(124,58,237,.2); }
  .error-text { color:var(--danger); font-size:.8rem; margin-top:.25rem; display:block; min-height:1.2em; }
  .divider { border:none; border-top:1px solid var(--border); margin:1.5rem 0; }
  .password-section { border:none; padding:0; margin:0 0 1.5rem; }
  .password-section legend { font-weight:600; color:#cbd5e1; margin-bottom:1rem; font-size:1.1rem; }

  .btn {
    padding:.75rem 1.5rem; border:none; border-radius:8px; font-weight:600;
    cursor:pointer; transition:var(--transition); font-size:1rem;
  }
  .btn-primary { background:var(--accent); color:#fff; }
  .btn-primary:hover:not(:disabled) { background:var(--accent-hover); transform:translateY(-1px); }
  .btn-secondary { background:#374151; color:#e5e7eb; }
  .btn-secondary:hover { background:#4b5563; }
  .btn:disabled { background:#4b5563; cursor:not-allowed; }
  .btn-lg { width:100%; padding:1rem; font-size:1.1rem; }

  @media (max-width:640px) {
    .form-grid { grid-template-columns:1fr; }
    .profile-container { margin:1rem; padding:0; }
    .card { padding:1.5rem; }
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('profileForm');

  // === PASSWORD TOGGLE ===
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.target);
      const isPassword = target.type === 'password';
      target.type = isPassword ? 'text' : 'password';
      btn.classList.toggle('show', isPassword);
    });
  });

  // === FORM VALIDATION ===
  const clearErrors = () => document.querySelectorAll('.error-text').forEach(el => el.textContent = '');
  const validate = () => {
    clearErrors();
    let valid = true;
    const first = form.first_name.value.trim();
    const last = form.last_name.value.trim();
    const email = form.email.value.trim();
    const np = form.new_password.value;
    const cp = form.confirm_password.value;
    const op = form.old_password.value;

    if (!first) { document.getElementById('first_name_error').textContent = 'First name required'; valid = false; }
    if (!last) { document.getElementById('last_name_error').textContent = 'Last name required'; valid = false; }
    if (!email) { document.getElementById('email_error').textContent = 'Email required'; valid = false; }
    else if (!/^\S+@\S+\.\S+$/.test(email)) {
      document.getElementById('email_error').textContent = 'Invalid email format'; valid = false;
    }
    if (np || cp || op) {
      if (!op) { document.getElementById('old_password_error').textContent = 'Current password required'; valid = false; }
      if (np && np.length < 8) { document.getElementById('new_password_error').textContent = 'Password must be >= 8 characters'; valid = false; }
      if (np !== cp) { document.getElementById('confirm_password_error').textContent = 'Passwords do not match'; valid = false; }
    }
    return valid;
  };

  // === FORM SUBMISSION (NO DIALOG OR NOTIFICATION) ===
  form.addEventListener('submit', async e => {
    e.preventDefault();
    if (!validate()) {
      return; // Errors are shown inline
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      let data;
      try {
        data = await res.json();
      } catch (jsonError) {
        throw new Error('Invalid response from server.');
      }

      if (data.status === 'success') {
        // Optionally update welcome message silently
        const fullName = `${form.first_name.value.trim()} ${form.last_name.value.trim()}`;
        const welcome = document.querySelector('.welcome-title');
        if (welcome) welcome.textContent = `Welcome back, ${fullName}!`;
      } else {
        // Show errors inline
        let msg = data.message || 'Please correct the highlighted fields.';
        if (data.field === 'old_password') {
          msg = 'Current password is incorrect.';
          form.old_password.focus();
        }
        const fieldMap = {
          first_name: 'first_name_error',
          last_name: 'last_name_error',
          email: 'email_error',
          new_password: 'new_password_error'
        };
        const errEl = fieldMap[data.field] ? document.getElementById(fieldMap[data.field]) : null;
        if (errEl) errEl.textContent = data.message;
      }
    } catch (err) {
      console.error('Update failed:', err);
      // Silent fail — user sees button re-enable
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    }
  });
});
</script>

<?php include 'includes/footer.php'; ?>