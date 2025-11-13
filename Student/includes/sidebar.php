<<!-- Teacher/includes/sidebar.php -->
<aside id="sidebar" class="sidebar">
  <button id="sidebar-toggle" class="sidebar-toggle" aria-label="Toggle sidebar" aria-expanded="true">Menu</button>

  <div class="logo">
    <span class="brand">Accendo</span>
    <span class="tagline">when vision meets innovation</span>
  </div>

  <nav>
    <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
      <span class="icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zM13 21h8v-10h-8v10zM13 3v6h8V3h-8z"/></svg>
      </span>
      <span class="label">Dashboard</span>
    </a>

    <a href="my_courses.php" class="<?= basename($_SERVER['PHP_SELF']) == 'my_courses.php' ? 'active' : '' ?>">
      <span class="icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M18 2H6c-1.1 0-2 .9-2 2v16l7-3 7 3V4c0-1.1-.9-2-2-2z"/></svg>
      </span>
      <span class="label">My Courses</span>
    </a>

    <a href="assignment.php" class="<?= basename($_SERVER['PHP_SELF']) == 'assignment.php' ? 'active' : '' ?>">
      <span class="icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M16 4h-1.5l-.71-1.42C13.6 1.22 13.32 1 12.99 1h-1.98c-.33 0-.61.22-.79.58L9.51 4H8C6.9 4 6 4.9 6 6v13c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zM12 17.5c-1.93 0-3.5-1.57-3.5-3.5S10.07 10.5 12 10.5s3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z"/></svg>
      </span>
      <span class="label">Assignments</span>
    </a>

    <a href="exams.php" class="<?= basename($_SERVER['PHP_SELF']) == 'exams.php' ? 'active' : '' ?>">
      <span class="icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zM14 3.5L18.5 8H14V3.5zM8 8h8v2H8V8zM8 12h8v2H8v-2z"/></svg>
      </span>
      <span class="label">Exams</span>
    </a>

    <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
      <span class="icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-9 1.7-9 5v3h18v-3c0-3.3-5.7-5-9-5z"/></svg>
      </span>
      <span class="label">Profile</span>
    </a>

    <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
      <span class="icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19.14 12.94c.04-.3.06-.6.06-.94s-.02-.64-.06-.94l2.03-1.58c.18-.14.23-.41.12-.62l-1.92-3.32c-.11-.2-.35-.28-.55-.2l-2.39.96c-.5-.38-1.05-.7-1.62-.94l-.36-2.54A.49.49 0 0 0 14 2h-4c-.24 0-.45.17-.49.41l-.36 2.54c-.57.22-1.11.5-1.62.94l-2.39-.96a.5.5 0 0 0-.55.2L2.01 9.86c-.11.2-.06.47.12.62l2.03 1.58c-.04.3-.06.63-.06.96s.02.63.06.94L2.13 16.3c-.18.14-.23.41-.12.62l1.92 3.32c.11.2.35.28.55.2l2.39-.96c.5.44 1.05.82 1.62.94l.36 2.54c.04.24.25.41.49.41h4c.24 0 .45-.17.49-.41l.36-2.54c.57-.22 1.11-.5 1.62-.94l2.39.96c.2.08.44 0 .55-.2l1.92-3.32c.11-.2.06-.47-.12-.62l-2.03-1.58zM12 15.5A3.5 3.5 0 1 1 12 8.5a3.5 3.5 0 0 1 0 7z"/></svg>
      </span>
      <span class="label">Settings</span>
    </a>

    <!-- DARK MODE & FONT SIZE AT BOTTOM -->
    <div style="margin-top:auto; padding:12px 8px;">
      <!-- DARK MODE TOGGLE -->
      <a href="#" id="dark-toggle" class="d-flex align-items-center p-2 rounded text-decoration-none" style="color:inherit;">
        <span class="icon me-2" id="dark-icon"><i class="fas fa-moon"></i></span>
        <span id="dark-text" class="label">Dark Mode</span>
      </a>

      <!-- FONT SIZE -->
      <div class="d-flex gap-2 mt-2">
        <button id="font-increase" class="btn flex-fill">A+</button>
        <button id="font-decrease" class="btn flex-fill">A-</button>
      </div>
    </div>

    <a href="../Auth/logout.php">
      <span class="icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M16 13v-2h-5V8l-4 4 4 4v-3h5zM20 3h-8v2h8v14h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
      </span>
      <span class="label">Logout</span>
    </a>
  </nav>
</aside>

<style>
  :root{
    --sidebar-w:220px;
    --sidebar-collapsed-w:72px;
    --accent:#1e40af;
    --muted:#6b7280;
  }

  .sidebar{
    position:fixed; left:0; top:0; bottom:0;
    width:var(--sidebar-w);
    background:#fff; border-right:1px solid rgba(7,17,34,0.06);
    display:flex; flex-direction:column; padding:12px 8px; gap:8px;
    box-shadow:0 6px 24px rgba(2,6,23,0.04);
    transition:width .28s cubic-bezier(.2,.9,.2,1);
    z-index:1100;
  }

  .sidebar.collapsed{ width:var(--sidebar-collapsed-w); }
  .sidebar .logo{ display:flex; flex-direction:column; gap:2px; padding:8px 10px; align-items:flex-start; }
  .sidebar .logo .brand{ font-weight:700; font-size:1.05rem; }
  .sidebar .logo .tagline{ font-size:0.78rem; color:var(--muted); opacity:1; transition:opacity .18s ease; }

  .sidebar.collapsed .logo .tagline{ opacity:0; transform:translateX(-6px); }

  nav{ display:flex; flex-direction:column; gap:6px; margin-top:6px; flex:1; }
  nav a{
    display:flex; align-items:center; gap:12px; padding:10px; text-decoration:none; color:inherit;
    border-radius:8px; transition: background .18s ease, transform .18s ease;
    transform-origin:left center;
  }
  nav a .icon{ width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; flex:0 0 28px; color:var(--accent); }
  nav a .icon svg{ width:22px; height:22px; fill:currentColor; }

  nav a .label{ white-space:nowrap; font-weight:600; transition:opacity .18s ease, transform .18s ease, font-size .18s ease; }

  .sidebar.collapsed nav a{ justify-content:center; }
  .sidebar.collapsed nav a .label{ opacity:0; transform:translateX(-8px); width:0; font-size:0.85rem; pointer-events:none; }

  .sidebar.collapsed:hover{ width:var(--sidebar-w); }
  .sidebar.collapsed:hover nav a{ justify-content:flex-start; }
  .sidebar.collapsed:hover .logo .tagline{ opacity:1; transform:none; }

  nav a:hover{ background:linear-gradient(90deg, rgba(30,64,175,0.06), rgba(30,64,175,0.02)); transform:translateX(4px) scale(1.02); box-shadow:0 6px 18px rgba(2,6,23,0.04); }
  nav a.active{ background:linear-gradient(90deg, rgba(30,64,175,0.12), rgba(30,64,175,0.04)); color:var(--accent); }

  .sidebar-toggle{
    position:absolute; right:-18px; top:12px;
    width:36px; height:36px; border-radius:10px; border:1px solid rgba(7,17,34,0.06);
    background:#fff; cursor:pointer; box-shadow:0 6px 18px rgba(2,6,23,0.08);
    display:flex; align-items:center; justify-content:center; font-size:16px;
    transition:transform .18s ease;
  }
  .sidebar.collapsed .sidebar-toggle{ transform:rotate(180deg); right:-18px; }

  @media (max-width:840px){
    .sidebar{ width:var(--sidebar-collapsed-w); }
  }
</style>

<script>
  // Font Awesome for moon icon
  if (!document.querySelector('link[href*="font-awesome"]')) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css';
    document.head.appendChild(link);
  }

  // Sidebar Collapse Logic
  (function(){
    const sidebar = document.getElementById('sidebar');
    const btn = document.getElementById('sidebar-toggle');

    const stored = localStorage.getItem('sidebar-collapsed');
    if (stored === '1') sidebar.classList.add('collapsed');

    function updateAria(){
      const collapsed = sidebar.classList.contains('collapsed');
      btn.setAttribute('aria-expanded', String(!collapsed));
    }
    updateAria();

    btn.addEventListener('click', function(){
      sidebar.classList.toggle('collapsed');
      const collapsed = sidebar.classList.contains('collapsed');
      localStorage.setItem('sidebar-collapsed', collapsed ? '1' : '0');
      updateAria();
    });

    btn.addEventListener('keydown', function(e){
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); btn.click(); }
    });
  })();

  // Dark Mode Toggle
  document.getElementById('dark-toggle').addEventListener('click', function(e){
    e.preventDefault();
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('dark-mode', isDark ? '1' : '0');
    document.getElementById('dark-icon').innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
    document.getElementById('dark-text').textContent = isDark ? 'Light Mode' : 'Dark Mode';
  });

  // Restore Dark Mode
  if (localStorage.getItem('dark-mode') === '1') {
    document.body.classList.add('dark-mode');
    document.getElementById('dark-icon').innerHTML = '<i class="fas fa-sun"></i>';
    document.getElementById('dark-text').textContent = 'Light Mode';
  }

  // Font Size Controls
  let fontSize = parseInt(localStorage.getItem('font-size') || '16', 10);
  document.documentElement.style.fontSize = fontSize + 'px';

  document.getElementById('font-increase').addEventListener('click', () => {
    if (fontSize < 24) {
      fontSize += 2;
      document.documentElement.style.fontSize = fontSize + 'px';
      localStorage.setItem('font-size', fontSize);
    }
  });

  document.getElementById('font-decrease').addEventListener('click', () => {
    if (fontSize > 12) {
      fontSize -= 2;
      document.documentElement.style.fontSize = fontSize + 'px';
      localStorage.setItem('font-size', fontSize);
    }
  });
</script>
