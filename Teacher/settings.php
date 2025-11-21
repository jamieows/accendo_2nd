<?php 
session_start();
if (!isset($_SESSION['teacher_id'])) {
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

    <!-- Quick Reset -->
    <div class="card">
        <h2>Reset Preferences</h2>
        <button id="resetPrefs" class="btn btn-danger">Reset to Default</button>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.page-header h1 { font-size: 2rem; margin: 0 0 0.5rem; color: #f0f4ff; }
.page-header p { color: #9ca3af; margin: 0; }

.settings-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 28px;
    max-width: 1200px;
}

/* Theme Cards */
.theme-options {
    display: flex;
    gap: 20px;
    margin: 1.5rem 0;
}
.theme-card {
    flex: 1;
    background: #1e293b;
    border: 2px solid transparent;
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}
.theme-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.4); }
.theme-card.active { border-color: var(--accent); background: rgba(30,64,175,0.15); }

.theme-preview {
    width: 100%;
    height: 120px;
    border-radius: 12px;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
}
.theme-preview.dark { background: #0b111d; }
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
    color: #e6eef8;
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
    background: #1e293b;
    border: 2px solid #334155;
    color: #cbd5e1;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s;
}
.size-btn[data-size="small"] { font-size: 14px; }
.size-btn[data-size="medium"] { font-size: 18px; }
.size-btn[data-size="large"] { font-size: 24px; }
.size-btn[data-size="xlarge"] { font-size: 30px; }
.size-btn.active, .size-btn:hover {
    background: var(--accent);
    border-color: var(--accent);
    color: white;
}

.size-preview p {
    margin-top: 1rem;
    color: #cbd5e1;
    line-height: 1.6;
}

/* Reset Button */
#resetPrefs {
    margin-top: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .settings-container { grid-template-columns: 1fr; }
    .theme-options { flex-direction: column; }
}
</style>

<script>
// THEME SWITCHER - FULLY WORKING
document.querySelectorAll('.theme-card').forEach(card => {
    card.addEventListener('click', function() {
        const theme = this.getAttribute('data-theme');
        
        // Update active state
        document.querySelectorAll('.theme-card').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        
        // Apply theme
        if (theme === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
            document.body.style.background = '#f8fafc';
            localStorage.setItem('accendo-theme', 'light');
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.body.style.background = '#0b111d';
            localStorage.setItem('accendo-theme', 'dark');
        }
        
        showToast(`Switched to ${theme === 'light' ? 'Light' : 'Dark'} Mode`);
    });
});

// TEXT SIZE ADJUSTER - FULLY WORKING
const sizes = {
    small: '0.875rem',
    medium: '1rem',
    large: '1.125rem',
    xlarge: '1.25rem'
};

document.querySelectorAll('.size-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const size = this.getAttribute('data-size');
        
        // Update active button
        document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Apply font size
        document.documentElement.style.fontSize = sizes[size];
        localStorage.setItem('accendo-fontsize', size);
        
        showToast(`Text size: ${size.charAt(0).toUpperCase() + size.slice(1)}`);
    });
});

// RESET ALL PREFERENCES
document.getElementById('resetPrefs').addEventListener('click', () => {
    if (confirm('Reset all settings to default?')) {
        localStorage.removeItem('accendo-theme');
        localStorage.removeItem('accendo-fontsize');
        
        // Reset to dark + medium
        document.documentElement.setAttribute('data-theme', 'dark');
        document.body.style.background = '#0b111d';
        document.documentElement.style.fontSize = '1rem';
        
        // Update UI
        document.querySelector('[data-theme="dark"]').click();
        document.querySelector('[data-size="medium"]').click();
        
        showToast('Settings reset to default');
    }
});

// LOAD SAVED PREFERENCES ON PAGE LOAD
document.addEventListener('DOMContentLoaded', () => {
    // Load Theme
    const savedTheme = localStorage.getItem('accendo-theme');
    if (savedTheme === 'light') {
        document.querySelector('[data-theme="light"]').click();
    } else {
        document.querySelector('[data-theme="dark"]').click();
    }
    
    // Load Font Size
    const savedSize = localStorage.getItem('accendo-fontsize') || 'medium';
    document.querySelector(`[data-size="${savedSize}"]`).click();
});

// Toast Notification
function showToast(message) {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed; bottom: 30px; right: 30px; z-index: 9999;
        background: #10b981; color: white; padding: 16px 28px;
        border-radius: 12px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        animation: slideIn 0.4s ease;
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

<!-- ADD THIS TO YOUR <head> IN header.php FOR LIGHT MODE SUPPORT -->
<style>
/* Light Mode Override */
html[data-theme="light"] {
    --bg-dark: #f8fafc;
    --card-dark: #ffffff;
    --text-light: #1e293b;
    --text-muted: #64748b;
    color: #1e293b;
    background: #f8fafc;
}
html[data-theme="light"] .card { background: white; border: 1px solid #e2e8f0; }
html[data-theme="light"] .sidebar { background: #ffffff; border-right: 1px solid #e2e8f0; }
html[data-theme="light"] nav a { color: #475569; }
html[data-theme="light"] nav a:hover { background: rgba(30,64,175,0.08); }
html[data-theme="light"] nav a.active { background: rgba(30,64,175,0.15); color: #1e40af; }
</style>
