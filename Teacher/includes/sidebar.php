<!-- Teacher/includes/sidebar.php -->
<aside id="sidebar" class="sidebar">

  <div class="logo">
    <span class="brand">Accendo</span>
    <span class="tagline">when vision meets innovation</span>
  </div>

  <nav>
    <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-tachometer-alt"></i></span>
      <span class="label">Dashboard</span>
    </a>

    <a href="my_courses.php" class="<?= basename($_SERVER['PHP_SELF']) == 'my_courses.php' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-book-open"></i></span>
      <span class="label">My Courses</span>
    </a>

    <a href="assignments.php" class="<?= basename($_SERVER['PHP_SELF']) == 'assignments.php' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-tasks"></i></span>
      <span class="label">Assignments</span>
    </a>

    <a href="exams.php" class="<?= basename($_SERVER['PHP_SELF']) == 'exams.php' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-file-alt"></i></span>
      <span class="label">Exams</span>
    </a>

    <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-user"></i></span>
      <span class="label">Profile</span>
    </a>

    <!-- CONTROLS: Font Size + Dark Mode -->
    <div class="sidebar-controls">
      <div class="control-group">
        <button id="font-decrease" class="control-btn" title="Smaller text">
          <i class="fas fa-search-minus"></i>
        </button>
        <button id="font-increase" class="control-btn" title="Larger text">
          <i class="fas fa-search-plus"></i>
        </button>
      </div>

      <button id="dark-toggle" class="control-btn full-width" title="Toggle dark mode">
        <span id="dark-icon"><i class="fas fa-moon"></i></span>
        <span id="dark-text" class="ms-2">Dark Mode</span>
      </button>
    </div>

    <a href="../Auth/logout.php" class="logout-link">
      <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
      <span class="label">Logout</span>
    </a>
  </nav>
</aside>

<style>
  :root {
    --sidebar-width: 260px;
    --icon-size: 38px;
    --primary: #6d28d9;
    --primary-light: #a78bfa;
    --text: #1f2937;
    --text-muted: #6b7280;
    --bg: #ffffff;
    --sidebar-bg: #ffffff;
    --border: #e5e7eb;
    --shadow: 0 10px 30px rgba(0,0,0,0.08);
    --radius: 14px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .dark-mode {
    --text: #f3f4f6;
    --text-muted: #9ca3af;
    --bg: #0f172a;
    --sidebar-bg: #1e293b;
    --border: #334155;
    --shadow: 0 10px 30px rgba(0,0,0,0.3);
  }

  /* ---------- BODY & MAIN CONTENT ---------- */
  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Inter', 'Segoe UI', sans-serif;
    transition: var(--transition);
    margin: 0;
    padding: 0;               /* <-- removed padding-left */
  }

  .main-content {
    margin-left: var(--sidebar-width);   /* <-- pushes content after sidebar */
    padding: 24px;
    min-height: 100vh;
    box-sizing: border-box;
  }

  /* ---------- SIDEBAR ---------- */
  .sidebar {
    position: fixed;
    left: 0; top: 0; bottom: 0;
    width: var(--sidebar-width);
    background: var(--sidebar-bg);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    padding: 22px 18px;
    box-shadow: var(--shadow);
    z-index: 1100;
    overflow-y: auto;
  }

  /* LOGO */
  .logo { padding: 0 10px 20px; margin-bottom: 12px; line-height: 1.3; }
  .logo .brand { font-weight: 800; font-size: 1.42rem; color: var(--primary); letter-spacing: -0.6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .logo .tagline { font-size: 0.78rem; color: var(--text-muted); font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

  /* NAV */
  nav { flex: 1; display: flex; flex-direction: column; gap: 6px; padding: 0 8px; }
  nav a { display: flex; align-items: center; gap: 14px; padding: 13px 16px; border-radius: var(--radius); color: var(--text); text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: var(--transition); min-height: 52px; overflow: hidden; }
  nav a .icon { width: var(--icon-size); height: var(--icon-size); display: flex; align-items: center; justify-content: center; font-size: 1.15rem; color: var(--primary); flex-shrink: 0; }
  nav a .label { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; min-width: 0; }
  nav a:hover { background: rgba(109, 40, 217, 0.1); transform: translateX(3px); }
  nav a.active { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; box-shadow: 0 4px 14px rgba(109, 40, 217, 0.3); }
  nav a.active .icon { color: white; }

  /* CONTROLS */
  .sidebar-controls { margin-top: auto; padding: 20px 8px 8px; border-top: 1px solid var(--border); }
  .control-group { display: flex; gap: 10px; margin-bottom: 14px; }
  .control-btn { flex: 1; background: var(--bg); border: 1px solid var(--border); color: var(--text); padding: 10px; border-radius: var(--radius); font-size: 0.87rem; font-weight: 600; cursor: pointer; transition: var(--transition); display: flex; align-items: center; justify-content: center; gap: 5px; min-height: 44px; }
  .control-btn:hover { background: var(--primary); color: white; transform: translateY(-1.5px); box-shadow: 0 6px 16px rgba(109, 40, 217, 0.25); }
  .control-btn.full-width { justify-content: flex-start; font-size: 0.9rem; }
  .logout-link { margin-top: 12px; color: #ef4444 !important; font-size: 0.94rem; }
  .logout-link:hover { background: rgba(239, 68, 68, 0.1); }

  /* ---------- RESPONSIVE (sidebar stays fixed) ---------- */
  @media (max-width: 840px) {
    .main-content { padding: 20px 16px; }
    /* optional – shrink sidebar a bit on tiny screens */
    .sidebar { width: 220px; }
    :root { --sidebar-width: 220px; }
  }
  @media (max-width: 480px) {
    .sidebar { width: 70px; padding: 22px 8px; }
    .sidebar .label, .sidebar .logo .tagline { display: none; }
    .sidebar nav a { justify-content: center; }
    .sidebar .icon { margin: 0; }
    :root { --sidebar-width: 70px; }
  }
</style>

<script>
  // Load Font Awesome + Inter (only once)
  if (!document.querySelector('link[href*="font-awesome"]')) {
    const fa = document.createElement('link');
    fa.rel = 'stylesheet';
    fa.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css';
    document.head.appendChild(fa);
  }
  if (!document.querySelector('link[href*="inter"]')) {
    const inter = document.createElement('link');
    inter.rel = 'stylesheet';
    inter.href = 'https://rsms.me/inter/inter.css';
    document.head.appendChild(inter);
  }

  // Dark Mode
  const darkToggle = document.getElementById('dark-toggle');
  const darkIcon   = document.getElementById('dark-icon');
  const darkText   = document.getElementById('dark-text');

  if (localStorage.getItem('dark-mode') === '1') {
    document.body.classList.add('dark-mode');
    darkIcon.innerHTML = '<i class="fas fa-sun"></i>';
    darkText.textContent = 'Light Mode';
  }

  darkToggle?.addEventListener('click', (e) => {
    e.preventDefault();
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('dark-mode', isDark ? '1' : '0');
    darkIcon.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
    darkText.textContent = isDark ? 'Light Mode' : 'Dark Mode';
  });

  // Font Size
  let fontSize = parseInt(localStorage.getItem('font-size') || '16', 10);
  document.documentElement.style.fontSize = fontSize + 'px';

  document.getElementById('font-increase')?.addEventListener('click', () => {
    if (fontSize < 24) { fontSize += 2; document.documentElement.style.fontSize = fontSize + 'px'; localStorage.setItem('font-size', fontSize); }
  });
  document.getElementById('font-decrease')?.addEventListener('click', () => {
    if (fontSize > 12) { fontSize -= 2; document.documentElement.style.fontSize = fontSize + 'px'; localStorage.setItem('font-size', fontSize); }
  });
</script>