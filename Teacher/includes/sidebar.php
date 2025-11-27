<!-- Teacher/includes/sidebar.php -->
<aside id="sidebar" class="sidebar" aria-label="Main navigation">
    <button id="sidebar-toggle" class="sidebar-toggle" aria-label="Toggle sidebar">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>

    <!-- Logo -->
    <div class="logo">
        <div class="logo-text">
            <span class="brand">Accendo</span>
            <span class="tagline">when vision meets innovation</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="nav">
        <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
            <span class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
            </span>
            <span class="label">Dashboard</span>
        </a>

        <a href="my_courses.php" class="<?= basename($_SERVER['PHP_SELF']) == 'my_courses.php' ? 'active' : '' ?>">
            <span class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M18 2H6c-1.1 0-2 .9-2 2v16l7-3 7 3V4c0-1.1-.9-2-2-2z"/></svg>
            </span>
            <span class="label">My Courses</span>
        </a>

        <a href="assignments.php" class="<?= basename($_SERVER['PHP_SELF']) == 'assignments.php' ? 'active' : '' ?>">
            <span class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/></svg>
            </span>
            <span class="label">Assignments</span>
        </a>

        <a href="exams.php" class="<?= basename($_SERVER['PHP_SELF']) == 'exams.php' ? 'active' : '' ?>">
            <span class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
            </span>
            <span class="label">Exams</span>
        </a>

        <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
            <span class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            </span>
            <span class="label">Profile</span>
        </a>

        <!-- SETTINGS → NOW GOES TO settings.php + ACTIVE STATE -->
        <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
            <span class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.6.06-.94s-.02-.64-.06-.94l2.03-1.58c.18-.14.23-.41.12-.62l-1.92-3.32c-.11-.2-.35-.28-.55-.2l-2.39.96c-.5-.38-1.05-.7-1.62-.94l-.36-2.54A.49.49 0 0 0 14 2h-4c-.24 0-.45.17-.49.41l-.36 2.54c-.57.22-1.11.5-1.62.94l-2.39-.96a.5.5 0 0 0-.55.2L2.01 9.86c-.11.2-.06.47.12.62l2.03 1.58c-.04.3-.06.63-.06.96s.02.63.06.94L2.13 16.3c-.18.14-.23.41-.12.62l1.92 3.32c.11.2.35.28.55.2l2.39-.96c.5.44 1.05.82 1.62.94l.36 2.54c.04.24.25.41.49.41h4c.24 0 .45-.17.49-.41l.36-2.54c.57-.22 1.11-.5 1.62-.94l2.39.96c.2.08.44 0 .55-.2l1.92-3.32c.11-.2.06-.47-.12-.62l-2.03-1.58zM12 15.5A3.5 3.5 0 1 1 12 8.5a3.5 3.5 0 0 1 0 7z"/></svg>
            </span>
            <span class="label">Settings</span>
        </a>

        <!-- Logout -->
        <a href="#" id="logoutLink" class="logout-link">
            <span class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.59L17 17l5-5-5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
            </span>
            <span class="label">Logout</span>
        </a>
    </nav>
</aside>

<!-- Logout Confirmation Modal -->
<dialog id="logoutModal" class="logout-modal">
    <div class="modal-content" role="document">
        <div class="modal-header">
            <h3>Log out of Accendo?</h3>
            <button class="btn-close" onclick="closeLogoutModal()" aria-label="Close">×</button>
        </div>
        <p>You'll need to sign in again to continue.</p>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeLogoutModal()">Cancel</button>
            <button class="btn btn-danger" id="confirmLogout">Logout</button>
        </div>
    </div>
</dialog>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    :root {
        --sidebar-width: 260px;
        --accent: #3b82f6;
        --accent-hover: #2563eb;
        --bg: #0f172a;
        --card: #1e293b;
        --text: #f1f5f9;
        --text-muted: #94a3b8;
        --border: #334155;
        --danger: #ef4444;
        --radius: 12px;
        --transition: all 0.2s ease;
        --shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .light-mode {
        --bg: #f8fafc;
        --card: #ffffff;
        --text: #0f172a;
        --text-muted: #64748b;
        --border: #e2e8f0;
    }

    body {
        background: var(--bg);
        color: var(--text);
        font-family: 'Inter', sans-serif;
        transition: var(--transition);
    }

    .sidebar {
        position: fixed;
        top: 0; left: 0; bottom: 0;
        width: var(--sidebar-width);
        background: var(--bg);
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        padding: 1rem;
        gap: 1.5rem;
        z-index: 1000;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .logo-text { display: flex; flex-direction: column; }
    .brand { font-weight: 700; font-size: 1.15rem; color: var(--text); }
    .tagline { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; }

    .nav { display: flex; flex-direction: column; gap: 0.35rem; flex: 1; }
    .nav a {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.75rem; border-radius: 10px;
        color: var(--text-muted); font-weight: 500;
        text-decoration: none; transition: var(--transition);
        position: relative;
    }
    .nav a:hover {
        background: rgba(59, 130, 246, 0.1);
        color: var(--text);
        transform: translateX(4px);
    }
    .nav a.active {
        background: rgba(59, 130, 246, 0.15);
        color: var(--accent);
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
    }
    .nav a.active::before {
        content: ''; position: absolute; left: 0; top: 50%;
        transform: translateY(-50%); width: 4px; height: 24px;
        background: var(--accent); border-radius: 0 4px 4px 0;
    }
    .icon svg { width: 20px; height: 20px; fill: currentColor; }

    .logout-link { margin-top: auto; color: #fca5a5 !important; }
    .logout-link:hover { background: rgba(239, 68, 68, 0.1) !important; color: var(--danger) !important; }

    .sidebar-toggle {
        position: absolute; right: -18px; top: 16px;
        width: 40px; height: 40px; background: var(--bg);
        border: 1px solid var(--border); border-radius: 12px;
        color: var(--text-muted); cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 1001;
    }

    /* Logout Modal */
    .logout-modal {
        border: none; border-radius: var(--radius); padding: 0;
        max-width: 400px; width: 90%; background: var(--card);
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        animation: modalIn 0.3s ease;
    }
    .logout-modal::backdrop { background: rgba(0,0,0,0.6); backdrop-filter: blur(6px); }
    .modal-content { padding: 1.5rem; color: var(--text); }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .modal-header h3 { margin: 0; font-size: 1.25rem; font-weight: 600; }
    .btn-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); }
    .btn-close:hover { background: rgba(255,255,255,0.1); color: var(--text); }
    .modal-actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; }
    .btn { flex: 1; padding: 0.75rem; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; }
    .btn-secondary { background: rgba(255,255,255,0.1); color: var(--text); }
    .btn-secondary:hover { background: rgba(255,255,255,0.2); }
    .btn-danger { background: var(--danger); color: white; }
    .btn-danger:hover { background: #dc2626; }

    @keyframes modalIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }

    @media (max-width: 768px) {
        :root { --sidebar-width: 70px; }
        .sidebar .logo-text, .sidebar .label { display: none; }
        .sidebar { width: 70px; padding: 1rem 0.5rem; }
        .nav a { justify-content: center; }
        .sidebar-toggle { right: 10px; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const content = document.querySelector('.main-content') || document.body;
        const modal = document.getElementById('logoutModal');
        const logoutLink = document.getElementById('logoutLink');
        const confirmBtn = document.getElementById('confirmLogout');

        // Push content to make space for sidebar
        content.style.marginLeft = 'var(--sidebar-width)';

        // Sidebar toggle (optional)
        document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            const collapsed = sidebar.classList.contains('collapsed');
            content.style.marginLeft = collapsed ? '70px' : 'var(--sidebar-width)';
        });

        // Apply saved theme & font size
        const savedTheme = localStorage.getItem('theme') || 'dark';
        const savedSize = localStorage.getItem('fontSize') || 'medium';

        if (savedTheme === 'light') {
            document.body.classList.add('light-mode');
        }

        document.documentElement.style.fontSize = {
            small: '0.875rem',
            medium: '1rem',
            large: '1.125rem',
            xlarge: '1.35rem'
        }[savedSize];

        // Logout functionality
        logoutLink.addEventListener('click', e => {
            e.preventDefault();
            modal.showModal();
        });

        window.closeLogoutModal = () => modal.close();

        confirmBtn.addEventListener('click', () => {
            window.location.href = '../Auth/logout.php';
        });

        modal.addEventListener('click', e => {
            if (e.target === modal) modal.close();
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && modal.open) modal.close();
        });
    });
</script>