<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<div class="settings-container">
  <h1 class="page-title">Settings</h1>

  <!-- Voice Assistant -->
  <div class="card">
    <h2 class="card-title">🎤 Voice Assistant</h2>
    <div class="form-group">
      <label for="nlp-volume" class="label">
        Volume: <span id="vol-value" class="value">70</span>%
      </label>
      <input type="range" id="nlp-volume" min="0" max="100" value="70" class="slider">
    </div>

    <button 
      onclick="speak('Testing voice assistant. Volume is now at ' + document.getElementById('nlp-volume').value + ' percent.')"
      class="btn btn-primary"
    >
      Test Voice
    </button>
  </div>

  <!-- Appearance -->
  <div class="card">
    <h2 class="card-title">🎨 Appearance</h2>

    <div class="appearance-controls">
      <button id="theme-toggle" class="btn btn-secondary" aria-pressed="true">
        Dark Mode: ON
      </button>

      <div class="font-controls">
        <button id="font-decrease" class="btn">A-</button>
        <button id="font-reset" class="btn">Reset</button>
        <button id="font-increase" class="btn">A+</button>
      </div>

      <div id="appearance-status" class="status-text"></div>
    </div>
  </div>
</div>

<style>
  :root {
    --accendo-font-scale: 1;
    --color-bg: #0b1220;
    --color-card: #1b243a;
    --color-text: #e6eef8;
    --color-accent: #3b82f6;
    --color-muted: #9ca3af;
    --color-btn: #2a3552;
    --color-btn-hover: #3c4a70;
    --transition: 0.2s ease;
  }

  html {
    font-size: calc(100% * var(--accendo-font-scale));
    transition: font-size var(--transition);
  }

  body {
    background: var(--color-bg);
    color: var(--color-text);
    font-family: "Inter", system-ui, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 20px;
  }

  .settings-container {
    max-width: 700px;
    margin: 0 auto;
  }

  .page-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
  }

  .card {
    background: var(--color-card);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.4);
  }

  .card-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 1rem;
  }

  .form-group {
    margin-bottom: 15px;
  }

  .label {
    display: block;
    font-weight: 500;
    margin-bottom: 8px;
  }

  .slider {
    width: 100%;
    cursor: pointer;
  }

  .btn {
    display: inline-block;
    border: none;
    background: var(--color-btn);
    color: #e5e7eb;
    padding: 8px 14px;
    border-radius: 8px;
    cursor: pointer;
    transition: background var(--transition);
  }

  .btn:hover {
    background: var(--color-btn-hover);
  }

  .btn-primary {
    background: var(--color-accent);
    color: #fff;
  }

  .btn-primary:hover {
    background: #2563eb;
  }

  .appearance-controls {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
  }

  .font-controls {
    display: flex;
    gap: 6px;
  }

  .status-text {
    margin-left: auto;
    color: var(--color-muted);
    font-size: 0.9rem;
  }

  @media (prefers-reduced-motion: reduce) {
    * { transition: none !important; }
  }
</style>

<script>
(function(){
  const volRange = document.getElementById('nlp-volume');
  const volValue = document.getElementById('vol-value');
  volRange.addEventListener('input', ()=> volValue.textContent = volRange.value);

  window.speak = function(text){
    const synth = window.speechSynthesis;
    if(!synth) return alert('Speech Synthesis not supported in this browser.');
    synth.cancel();
    const utter = new SpeechSynthesisUtterance(text);
    utter.volume = (parseInt(volRange.value,10) || 70) / 100;
    synth.speak(utter);
  };

  const THEME_KEY = 'accendo-theme';
  const FONT_KEY  = 'accendo-font-scale';
  const btnTheme = document.getElementById('theme-toggle');
  const btnInc = document.getElementById('font-increase');
  const btnDec = document.getElementById('font-decrease');
  const btnReset = document.getElementById('font-reset');
  const status = document.getElementById('appearance-status');

  const applyTheme = (theme) => {
    const el = document.documentElement;
    if(theme === 'dark'){
      el.classList.add('dark-mode');
      btnTheme.setAttribute('aria-pressed','true');
      btnTheme.textContent = 'Dark Mode: ON';
    } else {
      el.classList.remove('dark-mode');
      btnTheme.setAttribute('aria-pressed','false');
      btnTheme.textContent = 'Dark Mode: OFF';
    }
    localStorage.setItem(THEME_KEY, theme);
    updateStatus();
  };

  const applyFontScale = (scale) => {
    scale = Math.max(0.7, Math.min(1.4, Number(scale)));
    document.documentElement.style.setProperty('--accendo-font-scale', scale);
    localStorage.setItem(FONT_KEY, scale);
    updateStatus();
  };

  const updateStatus = () => {
    const currentTheme = localStorage.getItem(THEME_KEY) || 'dark';
    const currentScale = localStorage.getItem(FONT_KEY) || getComputedStyle(document.documentElement).getPropertyValue('--accendo-font-scale') || 1;
    status.textContent = `Theme: ${currentTheme.toUpperCase()} · Font: ${(parseFloat(currentScale)*100).toFixed(0)}%`;
  };

  // Initialize in dark mode by default
  (function init(){
    const storedTheme = localStorage.getItem(THEME_KEY) || 'dark';
    applyTheme(storedTheme);
    const storedScale = parseFloat(localStorage.getItem(FONT_KEY));
    if(!isNaN(storedScale)) applyFontScale(storedScale);
    else applyFontScale(1);
  })();

  btnTheme.addEventListener('click', () => {
    const next = document.documentElement.classList.contains('dark-mode') ? 'light' : 'dark';
    applyTheme(next);
  });

  btnInc.addEventListener('click', () => {
    const cur = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--accendo-font-scale')) || 1;
    applyFontScale((cur + 0.1).toFixed(2));
  });
  btnDec.addEventListener('click', () => {
    const cur = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--accendo-font-scale')) || 1;
    applyFontScale((cur - 0.1).toFixed(2));
  });
  btnReset.addEventListener('click', () => {
    applyFontScale(1);
  });
})();
</script>

<?php include 'includes/footer.php'; ?>
