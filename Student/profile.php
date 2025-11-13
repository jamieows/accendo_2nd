<?php 
require_once '../config/db.php'; 

// --- Secure session check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}

// --- Fixed: Use prepared statement to avoid SQL error ---
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Safety fallback
    session_destroy();
    header("Location: ../Auth/login.php");
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<div class="profile-container">
  <header class="page-header">
    <h1 class="page-title">Student Profile</h1>
    <p class="page-subtitle">Update your personal information and preferences</p>
  </header>

  <div class="card profile-card">
    <form id="profileForm" action="api/profile_update.php" method="POST" enctype="multipart/form-data" novalidate>
      
      <!-- Profile Picture -->
      <div class="profile-picture">
        <img id="profilePreview" 
             src="<?= !empty($user['profile_image']) ? '../uploads/'.$user['profile_image'] : '../assets/default-avatar.png' ?>" 
             alt="Profile picture of <?= htmlspecialchars($user['first_name']) ?>">
        <label class="upload-btn" for="profileImage">
          Change Photo
          <input type="file" name="profile_image" id="profileImage" accept="image/*" hidden>
        </label>
        <p class="upload-hint">JPG, PNG, GIF up to 5MB</p>
      </div>

      <!-- Personal Info -->
      <div class="form-grid">
        <div class="form-group">
          <label for="first_name">First Name <span class="required">*</span></label>
          <input type="text" name="first_name" id="first_name" 
                 value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>

        <div class="form-group">
          <label for="last_name">Last Name <span class="required">*</span></label>
          <input type="text" name="last_name" id="last_name" 
                 value="<?= htmlspecialchars($user['last_name']) ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label for="email">Email Address <span class="required">*</span></label>
        <input type="email" name="email" id="email" 
               value="<?= htmlspecialchars($user['email']) ?>" required>
      </div>

      <!-- REMOVED: Student ID field -->

      <hr class="divider">

      <!-- Password Change -->
      <fieldset class="password-section">
        <legend>Change Password (Optional)</legend>
        <div class="form-group">
          <label for="old_password">Current Password</label>
          <input type="password" name="old_password" id="old_password" 
                 placeholder="Enter current password">
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" name="new_password" id="new_password" 
                   placeholder="At least 8 characters">
          </div>
          <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirm_password" 
                   placeholder="Repeat new password">
          </div>
        </div>
      </fieldset>

      <button type="submit" class="btn btn-primary btn-lg">
        Update Profile
      </button>
    </form>
  </div>
</div>

<!-- Success Dialog -->
<dialog id="successDialog" class="dialog" aria-labelledby="successTitle">
  <div class="dialog-content" tabindex="-1">
    <h3 id="successTitle">Profile Updated Successfully!</h3>
    <p>Your changes have been saved. Refresh to see updated name in the header.</p>
    <div class="dialog-actions">
      <button id="closeDialog" class="btn btn-primary">OK</button>
    </div>
  </div>
</dialog>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

  :root {
    --bg: #0b1220;
    --card: #1b243a;
    --text: #e6eef8;
    --text-muted: #9ca3af;
    --accent: #7c3aed;
    --accent-hover: #6d28d9;
    --border: #2f354d;
    --danger: #dc2626;
    --success: #10b981;
    --radius: 12px;
    --shadow: 0 8px 32px rgba(0,0,0,0.4);
    --transition: all 0.25s ease;
  }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Inter', system-ui, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
  }

  .profile-container {
    max-width: 720px;
    margin: 2rem auto;
    padding: 0 1rem;
  }

  .page-header {
    text-align: center;
    margin-bottom: 2rem;
  }

  .page-title {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 0.5rem;
    background: linear-gradient(90deg, #7c3aed, #c084fc);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
  }

  .page-subtitle {
    color: var(--text-muted);
    font-size: 1rem;
    margin: 0;
  }

  .card {
    background: var(--card);
    border-radius: var(--radius);
    padding: 2rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
  }

  /* Profile Picture */
  .profile-picture {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 2rem;
    text-align: center;
  }

  .profile-picture img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid var(--accent);
    box-shadow: 0 4px 12px rgba(124,58,237,0.3);
    margin-bottom: 1rem;
  }

  .upload-btn {
    background: rgba(124,58,237,0.15);
    color: #c084fc;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.9rem;
    transition: var(--transition);
    border: 1px solid rgba(124,58,237,0.3);
  }

  .upload-btn:hover {
    background: rgba(124,58,237,0.25);
    transform: translateY(-1px);
  }

  .upload-hint {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 0.5rem;
  }

  /* Form Layout */
  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }

  .form-group {
    margin-bottom: 1.25rem;
  }

  label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #e2e8f0;
    font-size: 0.95rem;
  }

  .required {
    color: var(--danger);
  }

  input[type="text"],
  input[type="email"],
  input[type="password"] {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: #111827;
    color: var(--text);
    font-size: 1rem;
    transition: var(--transition);
  }

  input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(124,58,237,0.2);
  }

  .divider {
    border: none;
    border-top: 1px solid var(--border);
    margin: 1.5rem 0;
  }

  /* Password Section */
  .password-section {
    border: none;
    padding: 0;
    margin: 0 0 1.5rem;
  }

  .password-section legend {
    font-weight: 600;
    color: #cbd5e1;
    margin-bottom: 1rem;
    font-size: 1.1rem;
  }

  /* Buttons */
  .btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    font-size: 1rem;
  }

  .btn-primary {
    background: var(--accent);
    color: white;
  }

  .btn-primary:hover {
    background: var(--accent-hover);
    transform: translateY(-1px);
  }

  .btn-lg {
    width: 100%;
    padding: 1rem;
    font-size: 1.1rem;
  }

  /* Dialog */
  .dialog {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.75);
    backdrop-filter: blur(8px);
    justify-content: center;
    align-items: center;
    z-index: 10000;
    padding: 1rem;
  }

  .dialog[open] {
    display: flex;
  }

  .dialog-content {
    background: var(--card);
    padding: 2rem;
    border-radius: var(--radius);
    max-width: 380px;
    width: 100%;
    text-align: center;
    box-shadow: var(--shadow);
    animation: dialogIn 0.3s ease-out;
  }

  @keyframes dialogIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
  }

  .dialog-actions {
    margin-top: 1.5rem;
  }

  /* Responsive */
  @media (max-width: 640px) {
    .form-grid {
      grid-template-columns: 1fr;
    }
    .profile-container {
      margin: 1rem;
      padding: 0;
    }
    .card {
      padding: 1.5rem;
    }
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('profileForm');
  const fileInput = document.getElementById('profileImage');
  const preview = document.getElementById('profilePreview');
  const dialog = document.getElementById('successDialog');
  const closeBtn = document.getElementById('closeDialog');

  // Image preview
  fileInput.addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = e => preview.src = e.target.result;
      reader.readAsDataURL(file);
    }
  });

  // Form submit
  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    const formData = new FormData(form);

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        body: formData
      });

      const data = await res.json();

      if (data.status === 'success') {
        dialog.showModal();
        // Update header name if exists
        const fullName = `${form.first_name.value.trim()} ${form.last_name.value.trim()}`;
        const headerName = document.querySelector('.welcome-title');
        if (headerName) {
          headerName.textContent = `Welcome back, ${fullName}!`;
        }
      } else {
        alert('Error: ' + (data.message || 'Update failed'));
      }
    } catch (err) {
      console.error(err);
      alert('Network error. Please try again.');
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Update Profile';
    }
  });

  // Close dialog
  closeBtn.addEventListener('click', () => dialog.close());

  // Close on backdrop
  dialog.addEventListener('click', e => {
    if (e.target === dialog) dialog.close();
  });

  // ESC key
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && dialog.open) dialog.close();
  });
});
</script>

<?php include 'includes/footer.php'; ?>