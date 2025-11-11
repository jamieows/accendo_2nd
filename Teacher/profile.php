<?php
// Teacher/profile.php
// Fixed path + full UI restored
$is_dashboard = true;                     // tells footer to use ../assets/...
require_once '../config/db.php';          // CORRECT PATH from Teacher/ folder

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

$user = $pdo->query("SELECT * FROM users WHERE id = " . (int)$_SESSION['user_id'])->fetch();
?>
<?php include 'includes/header.php'; ?>

<style>
    :root {
        --label-color: #1a1a1a;
        --input-bg: #ffffff;
        --input-border: #cccccc;
        --input-focus: #7B61FF;
        --card-bg: #ffffff;
        --hr-color: #e2e2e2;
        --text-color: #1a1a1a;
    }
    .dark-mode {
        --label-color: #f0f0f0;
        --input-bg: #2a2a3a;
        --input-border: #444454;
        --input-focus: #a78bfa;
        --card-bg: #1a1a2e;
        --hr-color: #444454;
        --text-color: #e0e0e0;
    }
    .profile-card {
        background: var(--card-bg);
        padding: 2rem;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        max-width: 600px;
        margin: 2rem auto;
        color: var(--text-color);
        transition: all 0.3s ease;
    }
    .profile-card .field { margin-bottom: 1.75rem; }
    .profile-card .field:last-of-type { margin-bottom: 0; }
    .profile-card label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--label-color);
        text-transform: capitalize;
        letter-spacing: 0.3px;
    }
    .profile-card input[type="text"],
    .profile-card input[type="email"],
    .profile-card input[type="password"] {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border: 1.5px solid var(--input-border);
        border-radius: 0.6rem;
        background: var(--input-bg);
        color: var(--label-color);
        transition: all 0.25s ease;
        box-sizing: border-box;
    }
    .profile-card input:focus {
        outline: none;
        border-color: var(--input-focus);
        box-shadow: 0 0 0 3px rgba(123,97,255,0.15);
    }
    .dark-mode .profile-card input:focus {
        box-shadow: 0 0 0 3px rgba(167,139,250,0.25);
    }
    .profile-card hr {
        margin: 2.5rem 0;
        border: none;
        border-top: 1px solid var(--hr-color);
    }
    .profile-card .btn {
        background: #7B61FF;
        color: white;
        padding: 0.85rem 1.75rem;
        font-weight: 600;
        border: none;
        border-radius: 0.6rem;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.25s ease;
        width: 100%;
        margin-top: 1.5rem;
    }
    .profile-card .btn:hover {
        background: #6a51e6;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(123,97,255,0.3);
    }
</style>

<h1 style="text-align:center; margin-bottom:1.5rem;">Teacher Profile</h1>

<div class="profile-card">
    <form action="api/profile_update.php" method="POST">
        <div class="field">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name"
                   value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>

        <div class="field">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name"
                   value="<?= htmlspecialchars($user['last_name']) ?>" required>
        </div>

        <div class="field">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <hr>

        <div class="field">
            <label for="old_password">Current Password <small>(leave blank to keep)</small></label>
            <input type="password" id="old_password" name="old_password"
                   placeholder="Enter current password">
        </div>

        <div class="field">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password"
                   placeholder="Enter new password">
        </div>

        <div class="field">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   placeholder="Confirm new password">
        </div>

        <button type="submit" class="btn">Update Profile</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Load global JS (dark mode, font size, etc.) -->
<script src="../assets/js/global.js"></script>
</body>
</html>