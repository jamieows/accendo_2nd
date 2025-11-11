<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
$user = $pdo->query("SELECT * FROM users WHERE id = {$_SESSION['user_id']}")->fetch();
?>
<?php include 'includes/header.php'; ?>

<div class="profile-container">
  <h1 class="page-title">👤 Student Profile</h1>

  <div class="card profile-card">
    <form id="profileForm" action="api/profile_update.php" method="POST" enctype="multipart/form-data">
      
      <!-- Profile Picture -->
      <div class="profile-picture">
        <img id="profilePreview" 
             src="<?= !empty($user['profile_image']) ? '../uploads/'.$user['profile_image'] : '../assets/default-avatar.png' ?>" 
             alt="Profile Picture">
        <label class="upload-btn">
          Change Photo
          <input type="file" name="profile_image" id="profileImage" accept="image/*">
        </label>
      </div>

      <div class="form-group">
        <label>First Name</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
      </div>

      <div class="form-group">
        <label>Last Name</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
      </div>

      <hr>

      <div class="form-group">
        <label>Change Password</label>
        <input type="password" name="old_password" placeholder="Current password">
        <input type="password" name="new_password" placeholder="New password">
        <input type="password" name="confirm_password" placeholder="Confirm password">
      </div>

      <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
  </div>
</div>

<style>
  :root {
    --color-bg: #0b1220;
    --color-card: #1b243a;
    --color-text: #e6eef8;
    --color-accent: #3b82f6;
    --color-muted: #9ca3af;
    --color-btn: #2a3552;
    --color-btn-hover: #3c4a70;
    --transition: 0.2s ease;
  }

  body {
    background: var(--color-bg);
    color: var(--color-text);
    font-family: "Inter", system-ui, sans-serif;
    padding: 20px;
  }

  .page-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-align: center;
  }

  .card {
    background: var(--color-card);
    border-radius: 12px;
    padding: 20px;
    max-width: 600px;
    margin: 0 auto;
    box-shadow: 0 2px 8px rgba(0,0,0,0.4);
  }

  .profile-picture {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 1.5rem;
  }

  .profile-picture img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid var(--color-accent);
    margin-bottom: 10px;
  }

  .upload-btn {
    background: var(--color-btn);
    color: var(--color-text);
    padding: 6px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: background var(--transition);
    font-size: 0.9rem;
  }

  .upload-btn:hover {
    background: var(--color-btn-hover);
  }

  .upload-btn input {
    display: none;
  }

  .form-group {
    margin-bottom: 15px;
  }

  label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
  }

  input[type="text"], 
  input[type="email"],
  input[type="password"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #374151;
    border-radius: 8px;
    background: #111827;
    color: var(--color-text);
    font-size: 1rem;
  }

  .btn {
    display: inline-block;
    border: none;
    background: var(--color-btn);
    color: #e5e7eb;
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
    transition: background var(--transition);
  }

  .btn:hover {
    background: var(--color-btn-hover);
  }

  .btn-primary {
    background: var(--color-accent);
    color: #fff;
  }

  .btn-primary:hover {
    background: #2563eb;
  }

  hr {
    border: none;
    border-top: 1px solid #2f354d;
    margin: 20px 0;
  }
</style>

<?php include 'includes/footer.php'; ?>

<!-- Include your student.js at the bottom -->
<script src="student.js"></script>
