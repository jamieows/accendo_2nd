<!-- Teacher/includes/header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher | Accendo LMS</title>

  <!-- Fonts (same as student) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  <!-- Global Styles (you already have these) -->
  <link rel="stylesheet" href="../assets/css/global.css">
  <link rel="stylesheet" href="../assets/css/teacher.css">

  <!-- EXACT SAME COLORS & STYLE AS STUDENT -->
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

    :root {
      --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
      --font-heading: 'Inter', sans-serif;
      --font-mono: 'JetBrains Mono', monospace;

      --sidebar-width: 220px;
      --accent: #1e40af;
      --bg-dark: #0b111d;
      --card-dark: #1b243a;
      --text-light: #e6eef8;
      --text-muted: #9ca3af;
      --border: rgba(255,255,255,0.08);
      --danger: #dc2626;
      --success: #10b981;
      --warning: #f59e0b;
    }

    body {
      margin: 0;
      font-family: var(--font-primary);
      background: var(--bg-dark);
      color: var(--text-light);
      min-height: 100vh;
    }

    /* Main content area - pushes right when sidebar is open */
    .main-content {
      margin-left: var(--sidebar-width);
      padding: 24px;
      transition: margin-left 0.3s ease;
      min-height: 100vh;
    }

    /* When sidebar is collapsed */
    .sidebar.hidden ~ .main-content {
      margin-left: 0;
    }

    /* Cards - exact same look as student */
    .card {
      background: var(--card-dark);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.3);
      margin-bottom: 24px;
    }

    h1, h2, h3, h4 {
      color: #f0f4ff;
      margin: 0 0 20px 0;
      font-weight: 600;
    }

    h1 { font-size: 1.8rem; }
    h2 { font-size: 1.5rem; color: #cbd5e1; }

    /* Buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 18px;
      background: var(--accent);
      color: white;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 0.95rem;
      cursor: pointer;
      transition: all 0.2s ease;
      text-decoration: none;
    }
    .btn:hover {
      background: #1e3a8a;
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(30,64,175,0.4);
    }

    .btn-sm {
      padding: 8px 14px;
      font-size: 0.875rem;
    }

    .btn-danger {
      background: var(--danger);
    }
    .btn-danger:hover {
      background: #b91c1c;
    }

    .btn-success {
      background: var(--success);
    }
    .btn-success:hover {
      background: #059669;
    }

    /* Tables */
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 16px 0;
      font-size: 0.95rem;
    }
    th, td {
      padding: 14px 16px;
      text-align: left;
      border-bottom: 1px solid var(--border);
    }
    th {
      background: rgba(30,64,175,0.15);
      color: #a0aec0;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
    }
    tr:hover {
      background: rgba(30,64,175,0.08);
    }

    /* Toast Notification
    #toast {
      position: fixed;
      bottom: 30px;
      right: 30px;
      min-width: 320px;
      padding: 16px 24px;
      background: var(--success);
      color: white;
      border-radius: 12px;
      font-weight: 600;
      box-shadow: 0 10px 30px rgba(0,0,0,0.4);
      transform: translateX(400px);
      transition: transform 0.4s ease;
      z-index: 9999;
    }
    #toast.show { transform: translateX(0); }
    #toast.error { background: var(--danger); }
  </style>
</head>
<body>

  <!-- SIDEBAR (already included in sidebar.php) -->
  <?php include 'sidebar.php'; ?>

  <!-- MAIN CONTENT AREA -->
  <main class="main-content">
    <div class="container">