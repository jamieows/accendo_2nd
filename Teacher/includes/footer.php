  </div>
  <footer style="text-align:center;padding:20px;color:#6B7280;font-size:0.9rem;margin-top:50px;">
    &copy; <?= date('Y') ?> Accendo LMS | For Teachers
  </footer>
  <script>
  // Load Font Awesome
  if (!document.querySelector('link[href*="font-awesome"]')) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css';
    document.head.appendChild(link);
  }

  // === APPLY DARK MODE ON LOAD ===
  if (localStorage.getItem('dark-mode') === '1') {
    document.body.classList.add('dark-mode');
    const icon = document.getElementById('dark-icon');
    const text = document.getElementById('dark-text');
    if (icon) icon.innerHTML = '<i class="fas fa-sun"></i>';
    if (text) text.textContent = 'Light Mode';
  }

  // === DARK MODE TOGGLE ===
  document.getElementById('dark-toggle')?.addEventListener('click', (e) => {
    e.preventDefault();
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('dark-mode', isDark ? '1' : '0');
    const icon = document.getElementById('dark-icon');
    const text = document.getElementById('dark-text');
    if (icon) icon.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
    if (text) text.textContent = isDark ? 'Light Mode' : 'Dark Mode';
  });

  // === FONT SIZE CONTROLS ===
  let fontSize = parseInt(localStorage.getItem('font-size') || '16', 10);
  document.documentElement.style.fontSize = fontSize + 'px';

  document.getElementById('font-increase')?.addEventListener('click', () => {
    if (fontSize < 24) {
      fontSize += 2;
      document.documentElement.style.fontSize = fontSize + 'px';
      localStorage.setItem('font-size', fontSize);
    }
  });

  document.getElementById('font-decrease')?.addEventListener('click', () => {
    if (fontSize > 12) {
      fontSize -= 2;
      document.documentElement.style.fontSize = fontSize + 'px';
      localStorage.setItem('font-size', fontSize);
    }
  });
</script>
</body>
</html>
</body>
</html>