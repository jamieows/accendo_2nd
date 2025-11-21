<!-- Teacher/includes/sidebar.php -->
<aside id="sidebar" class="sidebar">
  <button id="sidebar-toggle" class="sidebar-toggle" aria-label="Toggle sidebar" aria-expanded="true">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <line x1="3" y1="12" x2="21" y2="12"></line>
      <line x1="3" y1="6" x2="21" y2="6"></line>
      <line x1="3" y1="18" x2="21" y2="18"></line>
    </svg>
  </button>

  <div class="logo">
    <span class="brand">Accendo</span>
    <span class="tagline">when vision meets innovation</span>
  </div>

  <nav>
    <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
      <span class="icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zM13 21h8v-10h-8v10zM13 3v6h8V3h-8z"/></svg>
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
        <svg viewBox="0 0 24 24"><path d="M16 4h-1.5l-.71-1.42C13.6 1.22 13.32 1 12.99 1h-1.98c-.33 0-.61.22-.79.58L9.51 4H8C6.9 4 6 4.9 6 6v13c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zM12 17.5c-1.93 0-3.5-1.57-3.5-3.5S10.07 10.5 12 10.5s3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z"/></svg>
      </span>
      <span class="label">Assignments</span>
    </a>

    <a href="exams.php" class="<?= basename($_SERVER['PHP_SELF']) == 'exams.php' ? 'active' : '' ?>">
      <span class="icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zM14 3.5L18.5 8H14V3.5zM8 8h8v2H8V8zM8 12h8v2H8v-2z"/></svg>
      </span>
      <span class="label">Exams</span>
    </a>

    <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
      <span class="icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-9 1.7-9 5v3h18v-3c0-3.3-5.7-5-9-5z"/></svg>
      </span>
      <span class="label">Profile</span>
    </a>

   

    <!-- LOGOUT - Always at bottom -->
    <a href="#" id="logoutLink" style="margin-top: auto;">
      <span class="icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M16 13v-2h-5V8l-4 4 4 4v-3h5zM20 3h-8v2h8v14h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
      </span>
      <span class="label">Logout</span>
    </a>
  </nav>
</aside>

<!-- Logout Confirmation Dialog -->
<div id="logoutDialog" class="logout-dialog" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="logoutTitle">
  <div class="dialog-overlay"></div>
  <div class="dialog-content" tabindex="-1">
    <h3 id="logoutTitle">Are you sure you want to log out?</h3>
    <p>You'll need to sign in again to access Accendo.</p>
    <div class="dialog-actions">
      <button id="cancelLogout" class="btn btn-cancel">Cancel</button>
      <button id="confirmLogout" class="btn btn-danger">Log Out</button>
    </div>
  </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

:root {
  --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
  --font-heading: 'Inter', sans-serif;
  --font-mono: 'JetBrains Mono', monospace;
  
  --sidebar-width: 220px;
  --accent: #1e40af;
  --muted: #6b7280;
  --bg-dark: #0b111d;
  --card-dark: #1b243a;
  --text-light: #e6eef8;
  --danger: #dc2626;
}

/* Sidebar */
.sidebar {
  position: fixed;
  top: 0; left: 0; bottom: 0;
  width: var(--sidebar-width);
  background: var(--bg-dark);
  border-right: 1px solid rgba(255,255,255,0.05);
  display: flex;
  flex-direction: column;
  padding: 12px 8px;
  gap: 8px;
  box-shadow: 0 6px 24px rgba(0,0,0,0.4);
  transition: transform 0.3s ease;
  z-index: 1100;
  font-family: var(--font-primary);
  overflow: hidden;
}

.sidebar.hidden { 
  transform: translateX(-100%); 
}

/* Logo */
.logo { 
  display: flex; 
  flex-direction: column; 
  gap: 2px; 
  padding: 8px 10px; 
}
.logo .brand { 
  font-weight: 700; 
  font-size: 1.05rem; 
  color: #f0f4ff;
}
.logo .tagline { 
  font-size: 0.78rem; 
  color: #a0aec0; 
}

/* Navigation */
nav { 
  display: flex; 
  flex-direction: column; 
  gap: 6px; 
  margin-top: 6px; 
  flex: 1; 
}
nav a {
  display: flex; 
  align-items: center; 
  gap: 12px;
  padding: 10px; 
  text-decoration: none; 
  color: #cbd5e1;
  border-radius: 8px; 
  transition: all 0.2s ease;
}
nav a:hover { 
  background: rgba(30,64,175,0.1); 
  transform: translateX(4px); 
}
nav a.active { 
  background: rgba(30,64,175,0.2); 
  color: var(--accent); 
  font-weight: 600;
}

/* Icons */
nav a .icon { 
  width: 28px; 
  height: 28px; 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  color: var(--accent); 
}
nav a .icon svg { 
  width: 22px; 
  height: 22px; 
  fill: currentColor; 
}

/* Toggle Button */
.sidebar-toggle {
  position: absolute; 
  right: -18px; 
  top: 12px;
  width: 36px; 
  height: 36px; 
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.1);
  background: var(--bg-dark); 
  color: #f0f4ff;
  cursor: pointer; 
  box-shadow: 0 6px 18px rgba(0,0,0,0.4);
  display: flex; 
  align-items: center; 
  justify-content: center;
  transition: all 0.2s ease;
}
.sidebar-toggle:hover { 
  transform: scale(1.05); 
  background: #11172b; 
}

/* Logout Dialog */
.logout-dialog {
  position: fixed; 
  inset: 0; 
  display: flex; 
  justify-content: center; 
  align-items: center;
  z-index: 10000; 
  animation: fadeIn 0.3s ease;
}

.dialog-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.75);
  backdrop-filter: blur(5px);
}

.dialog-content {
  position: relative;
  background: var(--card-dark); 
  padding: 1.8rem; 
  border-radius: 16px;
  width: 90%; 
  max-width: 380px; 
  text-align: center;
  box-shadow: 0 12px 32px rgba(0,0,0,0.5); 
  color: var(--text-light);
}

.dialog-content h3 { 
  margin: 0 0 0.5rem; 
  font-size: 1.35rem; 
  font-weight: 600;
}
.dialog-content p { 
  color: #9ca3af; 
  margin: 0 0 1.5rem; 
  font-size: 0.95rem; 
}
.dialog-actions { 
  display: flex; 
  gap: 1rem; 
  justify-content: center; 
}
.dialog-actions .btn {
  flex: 1; 
  padding: 0.75rem; 
  border: none; 
  border-radius: 10px;
  font-weight: 600; 
  cursor: pointer; 
  font-size: 0.95rem; 
}
.btn-cancel { 
  background: #2a3552; 
  color: var(--text-light); 
}
.btn-cancel:hover { background: #3c4a70; }
.btn-danger { 
  background: var(--danger); 
  color: white; 
}
.btn-danger:hover { background: #b91c1c; }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

/* Responsive */
@media (max-width: 768px) {
  :root { --sidebar-width: 70px; }
  .sidebar .logo, .sidebar .label, .sidebar .tagline { display: none; }
  .sidebar { width: 70px; padding: 12px 8px; }
  nav a { justify-content: center; padding: 12px; }
  .sidebar-toggle { right: -14px; top: 16px; }
}
</style>

<script>
// Sidebar Toggle + Logout Dialog (100% working)
document.addEventListener('DOMContentLoaded', function () {
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('sidebar-toggle');
  const content = document.querySelector('.main-content') || document.querySelector('.content');

  // Restore sidebar state
  if (localStorage.getItem('sidebar-hidden') === '1') {
    sidebar.classList.add('hidden');
    if (content) content.style.marginLeft = '0';
  }

  toggleBtn?.addEventListener('click', () => {
    sidebar.classList.toggle('hidden');
    const hidden = sidebar.classList.contains('hidden');
    localStorage.setItem('sidebar-hidden', hidden ? '1' : '0');
    if (content) content.style.marginLeft = hidden ? '0' : 'var(--sidebar-width)';
  });

  // Logout Dialog
  const logoutLink = document.getElementById('logoutLink');
  const dialog = document.getElementById('logoutDialog');
  const cancelBtn = document.getElementById('cancelLogout');
  const confirmBtn = document.getElementById('confirmLogout');
  const overlay = dialog?.querySelector('.dialog-overlay');

  logoutLink?.addEventListener('click', (e) => {
    e.preventDefault();
    dialog.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  });

  const closeDialog = () => {
    dialog.style.display = 'none';
    document.body.style.overflow = '';
  };

  cancelBtn?.addEventListener('click', closeDialog);
  confirmBtn?.addEventListener('click', () => {
    window.location.href = '../Auth/logout.php';
  });
  overlay?.addEventListener('click', closeDialog);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && dialog.style.display === 'flex') closeDialog();
  });
});
</script>