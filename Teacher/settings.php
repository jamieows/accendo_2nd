<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}
include 'includes/header.php';
?>

<div class="page-header">
    <h1>Settings</h1>
    <p>Customize your Accendo experience</p>
</div>

<div class="settings-container">
    <!-- Theme Settings -->
    <div class="card">
        <h2>Theme</h2>
        <div class="theme-options">
            <label class="theme-card <?= (!isset($_COOKIE['theme']) || $_COOKIE['theme'] === 'dark') ? 'active' : '' ?>" data-theme="dark">
                <div class="theme-preview dark">
                    <div class="preview-header"></div>
                    <div class="preview-sidebar"></div>
                    <div class="preview-content"></div>
                </div>
                <span>Dark Mode</span>
            </label>
            <label class="theme-card <?= (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light') ? 'active' : '' ?>" data-theme="light">
                <div class="theme-preview light">
                    <div class="preview-header"></div>
                    <div class="preview-sidebar"></div>
                    <div class="preview-content"></div>
                </div>
                <span>Light Mode</span>
            </label>
        </div>
        <small>Your preference is saved across all devices</small>
    </div>

    <!-- Text Size -->
    <div class="card">
        <h2>Text Size</h2>
        <div class="text-size-control">
            <button class="size-btn" data-size="small">A</button>
            <button class="size-btn active" data-size="medium">A</button>
            <button class="size-btn" data-size="large">A</button>
            <button class="size-btn" data-size="xlarge">A</button>
        </div>
        <div class="size-preview">
            <p>This is how your text will appear across Accendo</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
    .page-header { padding: 2rem 0; text-align: center; }
    .page-header h1 { font-size: 2.2rem; margin: 0; color: var(--text); }
    .page-header p { color: var(--text-muted); margin-top: 0.5rem; }

    .settings-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 28px;
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    .card h2 {
        margin: 0 0 1.5rem;
        font-size: 1.4rem;
        color: var(--text);
    }

    /* Theme Cards */
    .theme-options {
        display: flex;
        gap: 20px;
        margin: 1.5rem 0;
    }
    .theme-card {
        flex: 1;
        background: var(--card);
        border: 2px solid transparent;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .theme-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.2); }
    .theme-card.active { border-color: #3b82f6; background: rgba(59,130,246,0.1); }

    .theme-preview {
        width: 100%; height: 120px; border-radius: 12px;
        margin-bottom: 16px; position: relative; overflow: hidden;
    }
    .theme-preview.dark { background: #0f172a; }
    .theme-preview.light { background: #f8fafc; }
    .preview-header { height: 20px; background: rgba(255,255,255,0.1); }
    .preview-sidebar { position: absolute; left: 0; top: 20px; bottom: 0; width: 60px; background: rgba(255,255,255,0.08); }
    .preview-content { margin-left: 70px; margin-top: 10px; padding: 10px; }
    .preview-content::before, .preview-content::after {
        content: ""; display: block; height: 12px; background: rgba(255,255,255,0.15); margin: 8px 0; border-radius: 4px;
    }
    .theme-preview.light .preview-header { background: #e2e8f0; }
    .theme-preview.light .preview-sidebar { background: #cbd5e1; }
    .theme-preview.light .preview-content::before,
    .theme-preview.light .preview-content::after { background: #94a3b8; }

    .theme-card span {
        font-weight: 600;
        color: var(--text);
    }

    /* Text Size */
    .text-size-control {
        display: flex;
        gap: 12px;
        margin: 1.5rem 0;
    }
    .size-btn {
        width: 50px; height: 50px;
        border-radius: 12px;
        background: var(--card);
        border: 2px solid var(--border);
        color: var(--text);
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s;
    }
    .size-btn[data-size="small"] { font-size: 14px; }
    .size-btn[data-size="medium"] { font-size: 18px; }
    .size-btn[data-size="large"] { font-size: 24px; }
    .size-btn[data-size="xlarge"] { font-size: 30px; }
    .size-btn.active, .size-btn:hover {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }

    .size-preview p {
        margin-top: 1rem;
        color: var(--text);
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        .settings-container { grid-template-columns: 1fr; }
        .theme-options { flex-direction: column; }
    }
</style>

<script>
    // Theme Switcher
    document.querySelectorAll('.theme-card').forEach(card => {
        card.addEventListener('click', function() {
            const theme = this.getAttribute('data-theme');
            document.querySelectorAll('.theme-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');

            if (theme === 'light') {
                document.body.classList.add('light-mode');
                document.body.classList.remove('dark-mode');
            } else {
                document.body.classList.add('dark-mode');
                document.body.classList.remove('light-mode');
            }
            localStorage.setItem('theme', theme);
        });
    });

    // Text Size
    const sizes = { small: '0.875rem', medium: '1rem', large: '1.125rem', xlarge: '1.35rem' };
    document.querySelectorAll('.size-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const size = this.getAttribute('data-size');
            document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            document.documentElement.style.fontSize = sizes[size];
            localStorage.setItem('fontSize', size);
        });
    });

    // Load saved settings
    document.addEventListener('DOMContentLoaded', () => {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        const savedSize = localStorage.getItem('fontSize') || 'medium';

        if (savedTheme === 'light') document.body.classList.add('light-mode');
        else document.body.classList.add('dark-mode');

        document.documentElement.style.fontSize = sizes[savedSize];
        document.querySelector(`[data-theme="${savedTheme}"]`).classList.add('active');
        document.querySelector(`[data-size="${savedSize}"]`).classList.add('active');
    });
</script>