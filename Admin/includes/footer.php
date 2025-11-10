  </div>

  <script>
    // Live PH Clock (Asia/Manila)
    function updateClock() {
      const now = new Date().toLocaleString('en-PH', {
        timeZone: 'Asia/Manila',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      });
      const clockEl = document.getElementById('ph-clock');
      if (clockEl) clockEl.textContent = now;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // Dark Mode Persistence
    const savedDarkMode = localStorage.getItem('darkMode') === 'true';
    document.documentElement.classList.toggle('dark-mode', savedDarkMode);
  </script>
</body>
</html>