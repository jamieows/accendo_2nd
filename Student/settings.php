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

      <hr>

      <div class="setting-item">
        <div class="setting-label">
          <span>Text Size</span>
          <small>Adjust readability</small>
        </div>
        <div class="zoom-controls">
          <button id="zoomOut" class="zoom-btn">A-</button>
          <span id="zoomLevel">100%</span>
          <button id="zoomIn" class="zoom-btn">A+</button>
        </div>
      </div>

      <div class="reset-zoom">
        <button id="resetZoom" class="btn btn-secondary">Reset to Default</button>
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

    .zoom-controls { display: flex; align-items: center; gap: 12px; }
    .zoom-btn { background: #374151; color: #e5e7eb; border: none; width: 36px; height: 36px; border-radius: 8px; font-weight: bold; cursor: pointer; }
    .zoom-btn:hover { background: #4b5563; transform: scale(1.05); }
    #zoomLevel { min-width: 50px; text-align: center; font-family: monospace; }

    .btn-secondary { background: #374151; color: #e5e7eb; padding: 8px 16px; border: none; border-radius: 8px; cursor: pointer; }
    .btn-secondary:hover { background: #4b5563; }
  </style>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
      const darkToggle = document.getElementById('darkModeToggle');
      const zoomIn = document.getElementById('zoomIn');
      const zoomOut = document.getElementById('zoomOut');
      const reset = document.getElementById('resetZoom');
      const level = document.getElementById('zoomLevel');

      // Init Dark Mode
      darkToggle.checked = localStorage.getItem('accendo_theme') === 'dark';
      darkToggle.addEventListener('change', () => window.toggleDarkMode(darkToggle.checked));

      // Init Zoom Display
      const updateLevel = () => {
          const z = parseFloat(localStorage.getItem('accendo_zoom') || '1');
          level.textContent = Math.round(z * 100) + '%';
      };
      updateLevel();

      zoomIn.addEventListener('click', () => { window.zoomIn(); updateLevel(); });
      zoomOut.addEventListener('click', () => { window.zoomOut(); updateLevel(); });
      reset.addEventListener('click', () => { window.zoomReset(); updateLevel(); });
  });
  </script>

  <?php include 'includes/footer.php'; ?>