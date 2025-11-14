  </div>
  <footer style="text-align:center;padding:20px;color:#6B7280;font-size:0.9rem;margin-top:50px;">
    &copy; <?= date('Y') ?> Accendo LMS | For Teachers
  </footer>
  <script>
  // === DARK MODE (Keep Existing) ===
  const darkToggle = document.getElementById('dark-toggle');
  const darkIcon = document.getElementById('dark-icon');
  const darkText = document.getElementById('dark-text');

  if (localStorage.getItem('dark-mode') === '1') {
    document.body.classList.add('dark-mode');
    if (darkIcon) darkIcon.innerHTML = 'Sun';
    if (darkText) darkText.textContent = 'Light Mode';
  }

  darkToggle?.addEventListener('click', (e) => {
    e.preventDefault();
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('dark-mode', isDark ? '1' : '0');
    if (darkIcon) darkIcon.innerHTML = isDark ? 'Sun' : 'Moon';
    if (darkText) darkText.textContent = isDark ? 'Light Mode' : 'Dark Mode';
  });

  // === FONT SIZE ADJUSTMENT (NEW FEATURE) ===
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