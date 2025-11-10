<?php
// ACCENDO_2ND/index.php
// Root landing page – auto-redirects based on login status
// Time: November 10, 2025 07:39 PM PST → 11:39 AM PHT (PH)

require_once 'config/db.php';

// If user is already logged in → redirect to correct dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $map = [
        'admin'   => 'Admin/index.php',
        'teacher' => 'Teacher/index.php',
        'student' => 'Student/index.php'
    ];
    $target = $map[$_SESSION['role']] ?? 'Auth/login.php';
    header("Location: $target");
    exit();
}

// Not logged in → show welcome page
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accendo LMS | Learning Made Simple</title>
  <link rel="stylesheet" href="assets/css/global.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Hero Section */
    .hero {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #7B61FF, #A78BFA);
      color: white;
      text-align: center;
      padding: 40px 20px;
    }
    .hero h1 {
      font-size: 3.5rem;
      margin-bottom: 16px;
      font-weight: 700;
    }
    .hero p {
      font-size: 1.3rem;
      max-width: 700px;
      margin: 0 auto 30px;
      opacity: 0.9;
    }
    .btn-large {
      background: white;
      color: #7B61FF;
      padding: 14px 32px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 12px;
      text-decoration: none;
      display: inline-block;
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
      transition: all 0.3s;
    }
    .btn-large:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    /* Features */
    .features {
      padding: 80px 20px;
      background: #F9F7FE;
      text-align: center;
    }
    .features h2 {
      font-size: 2.5rem;
      margin-bottom: 50px;
      color: #162447;
    }
    .feature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      max-width: 1200px;
      margin: 0 auto;
    }
    .feature-card {
      background: white;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      transition: transform 0.3s;
    }
    .feature-card:hover {
      transform: translateY(-10px);
    }
    .feature-card i {
      font-size: 2.5rem;
      color: #7B61FF;
      margin-bottom: 15px;
    }
    .feature-card h3 {
      font-size: 1.4rem;
      margin-bottom: 10px;
      color: #162447;
    }

    /* Footer */
    .landing-footer {
      background: #1A1A2E;
      color: #ccc;
      text-align: center;
      padding: 30px;
      font-size: 0.95rem;
    }
    .landing-footer a {
      color: #7B61FF;
      text-decoration: none;
    }

    /* Dark mode */
    .dark-mode .hero { background: linear-gradient(135deg, #5A4BDA, #7C3AED); }
    .dark-mode .features { background: #0F0F1A; }
    .dark-mode .feature-card { background: #162447; }
    .dark-mode .feature-card h3, .dark-mode .features h2 { color: #F9F7FE; }
  </style>
</head>
<body>
  <!-- Hero Section -->
  <section class="hero">
    <div>
      <h1>Accendo LMS</h1>
      <p>Empowering Filipino students and teachers with accessible, modern, and inclusive learning.</p>
      <a href="Auth/login.php" class="btn-large">Get Started</a>
    </div>
  </section>

  <!-- Features -->
  <section class="features">
    <h2>Why Choose Accendo?</h2>
    <div class="feature-grid">
      <div class="feature-card">
        <div><strong>Accessible</strong></div>
        <h3>WCAG 2.1 AA Compliant</h3>
        <p>Full support for screen readers, keyboard navigation, and high-contrast mode.</p>
      </div>
      <div class="feature-card">
        <div><strong>Voice Assistant</strong></div>
        <h3>NLP-Powered Reading</h3>
        <p>Students can listen to materials with adjustable volume and natural voice.</p>
      </div>
      <div class="feature-card">
        <div><strong>Offline-Ready</strong></div>
        <h3>Download & Learn</h3>
        <p>PDFs, videos, and assignments work even with slow internet.</p>
      </div>
      <div class="feature-card">
        <div><strong>Filipino Time</strong></div>
        <h3>Asia/Manila Sync</h3>
        <p>All deadlines and schedules in Philippine Standard Time (PHT).</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="landing-footer">
    <p>&copy; <?= date('Y') ?> <strong>Accendo LMS</strong> • Made with love in the Philippines</p>
    <p>
      <a href="Auth/login.php">Login</a> | 
      <a href="Auth/register.php">Register</a> | 
      <a href="#" onclick="document.body.classList.toggle('dark-mode'); return false;">Dark Mode</a>
    </p>
  </footer>

  <!-- Global JS -->
  <script src="assets/js/global.js"></script>
  <script>
    // Auto dark mode from localStorage
    if (localStorage.getItem('darkMode') === 'true') {
      document.body.classList.add('dark-mode');
    }
  </script>
</body>
</html>