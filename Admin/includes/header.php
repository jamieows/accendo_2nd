<!DOCTYPE html>
<html lang="en" class="dark-mode">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin | Accendo LMS</title>
  <link rel="stylesheet" href="../assets/css/global.css">
  <link rel="stylesheet" href="../a<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin | Accendo LMS</title>
  <link rel="stylesheet" href="../../assets/css/global.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --primary: #7B61FF;
      --primary-dark: #6D53E6;
      --bg: #F9F7FE;
      --card: #FFFFFF;
      --text: #162447;
      --border: #E5E7EB;
      --success: #10B981;
      --warning: #F59E0B;
      --danger: #EF4444;
    }
    .dark-mode {
      --bg: #0F0F1A;
      --card: #162447;
      --text: #F9F7FE;
      --border: #374151;
    }
    body { background: var(--bg); color: var(--text); font-family: 'Inter', sans-serif; margin: 0; }
    .navbar { background: var(--card); border-bottom: 1px solid var(--border); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    .logo { font-weight: 700; font-size: 1.5rem; color: var(--primary); }
    .nav-links a { margin: 0 1rem; color: var(--text); text-decoration: none; font-weight: 500; }
  .nav-links a:hover { color: var(--primary); }
  /* Active/selected tab styling */
  .nav-links a.active { color: var(--primary); font-weight: 600; border-bottom: 2px solid var(--primary); padding-bottom: 3px; }
    .user-menu { position: relative; }
    .user-avatar { width: 40px; height: 40px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; cursor: pointer; }
    .dropdown { display: none; position: absolute; right: 0; background: var(--card); border: 1px solid var(--border); border-radius: 12px; width: 200px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 100; }
    .dropdown a { display: block; padding: 12px 16px; color: var(--text); text-decoration: none; }
    .dropdown a:hover { background: var(--bg); }
    .user-menu:hover .dropdown { display: block; }

    .container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
    .page-header h1 { font-size: 2.2rem; margin: 0; color: var(--text); }
    .page-header .subtitle { color: #6B7280; font-size: 1rem; margin: 8px 0 0; }
    #ph-clock { font-weight: 600; color: var(--primary); }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin: 2rem 0; }
    .stat-card { background: var(--card); padding: 1.5rem; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; transition: transform 0.3s; }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-icon { font-size: 2.5rem; margin-bottom: 1rem; }
    .stat-icon.users { color: #10B981; }
    .stat-icon.teachers { color: #F59E0B; }
    .stat-icon.students { color: #3B82F6; }
    .stat-icon.subjects { color: #8B5CF6; }
    .stat-value { font-size: 2.2rem; font-weight: 700; color: var(--text); }
    .stat-label { color: #6B7280; font-size: 0.95rem; }

    .quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin: 3rem 0; }
    .action-card { background: var(--card); padding: 1.5rem; border-radius: 16px; text-decoration: none; color: var(--text); box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.3s; }
    .action-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    .action-icon { font-size: 2rem; color: var(--primary); margin-bottom: 1rem; }
    .action-card h3 { margin: 0 0 0.5rem; font-size: 1.3rem; }
    .action-card p { margin: 0; color: #6B7280; font-size: 0.95rem; }

    .card { background: var(--card); padding: 1.5rem; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--border); }
    th { background: var(--bg); font-weight: 600; color: var(--text); }
    .role-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
    .role-badge.admin { background: #FEE2E2; color: #DC2626; }
    .role-badge.teacher { background: #FEF3C7; color: #D97706; }
    .role-badge.student { background: #DBEAFE; color: #2563EB; }
    .status-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 8px; }
    .status-dot.online { background: #10B981; }
    .status-dot.offline { background: #6B7280; }

    @media (max-width: 768px) {
      .navbar { flex-direction: column; gap: 1rem; }
      .nav-links { display: flex; flex-wrap: wrap; justify-content: center; gap: 1rem; }
      .stats-grid, .quick-actions { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="logo">Accendo</div>
    <div class="nav-links">
      <a href="index.php">Dashboard</a>
      <a href="manage_users.php">Users</a>
      <a href="manage_courses.php">Courses</a>
      <a href="settings.php">Settings</a>
    </div>
    <div class="user-menu">
      <div class="user-avatar"><?= strtoupper(substr($_SESSION['name'], 0, 1)) ?></div>
      <div class="dropdown">
        <a href="../Auth/logout.php">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="../assets/js/global.js" defer></script>
  <script src="../assets/js/admin.js" defer></script>
  <script>
    // Small helper: mark current nav link active based on URL and on click
    document.addEventListener('DOMContentLoaded', function() {
      try {
        const links = Array.from(document.querySelectorAll('.nav-links a'));
        if (!links.length) return;
        const current = (location.pathname || '').split('/').pop();
        links.forEach(a => {
          const href = (a.getAttribute('href') || '').split('/').pop();
          if (href && current && href === current) a.classList.add('active');
          a.addEventListener('click', function() {
            links.forEach(x => x.classList.remove('active'));
            this.classList.add('active');
          });
        });
        // Add lightweight page marker classes so we can apply page-specific CSS
        try {
          if (current === 'manage_users.php') document.documentElement.classList.add('page-manage-users');
          if (current === 'manage_courses.php') document.documentElement.classList.add('page-manage-courses');
        } catch (e) { /* silent */ }
      } catch (e) { /* silent */ }
    });
  </script>
</head>
<body>
  <header class="admin-header">
    <span>Accendo LMS</span>
    <div style="float:right;font-size:0.9rem;">
      <?= htmlspecialchars($_SESSION['name']) ?> (Admin)
    </div>
  </header>