<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../Auth/login.php");
    exit();
}
$pageTitle = "Settings";
?>
<?php include 'includes/header.php'; ?>

<div class="settings-container">
    <h1 class="page-title">Settings</h1>

    <div class="card settings-card">
        <h2>Appearance</h2>

        <div class="setting-item">
            <div class="setting-label">
                <span>Dark Mode</span>
                <small>Reduce eye strain in low light</small>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="darkModeToggle">
                <span class="slider"></span>
            </label>
        </div>
    </div>
</div>

<style>
    .settings-container { max-width: 600px; margin: 0 auto; padding: 20px; }
    .page-title { font-size: 1.8rem; text-align: center; margin-bottom: 1.5rem; }
    .settings-card { padding: 24px; border-radius: var(--radius); }
    .setting-item { display: flex; justify-content: space-between; align-items: center; padding: 14px 0; }
    .setting-item:not(:last-child) { border-bottom: 1px solid var(--border); }
    .setting-label span { font-weight: 600; }
    .setting-label small { color: var(--text-muted); font-size: 0.85rem; }

    .toggle-switch { position: relative; width: 52px; height: 28px; }
    .toggle-switch input { opacity: 0; width: 0; }
    .slider { position: absolute; inset: 0; background: #374151; border-radius: 28px; transition: .3s; }
    .slider:before { content: ""; position: absolute; width: 22px; height: 22px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: .3s; }
    input:checked + .slider { background: var(--accent); }
    input:checked + .slider:before { transform: translateX(24px); }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const darkToggle = document.getElementById('darkModeToggle');

    // Initialize Dark Mode
    darkToggle.checked = localStorage.getItem('accendo_theme') === 'dark';

    // Dark Mode Toggle
    darkToggle.addEventListener('change', () => {
        const isDark = darkToggle.checked;
        document.body.classList.toggle('dark-mode', isDark);
        document.documentElement.classList.toggle('dark-mode', isDark);
        localStorage.setItem('accendo_theme', isDark ? 'dark' : 'light');
        fetch('save_prefs.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ theme: isDark ? 'dark' : 'light' })
        }).catch(() => {});
    });
});
</script>

<?php include 'includes/footer.php'; ?>