<?php // includes/footer.php ?>
    </main>

    <style>
        /* Remove any residual styles for the toggle button */
        .dark-mode-toggle {
            display: none !important;
        }
    </style>

    <script>
    (function(){
        const STORAGE_KEY = 'accendo_theme';
        const savedTheme = localStorage.getItem(STORAGE_KEY) || 'light';

        // Apply saved theme on page load
        const applyTheme = (theme) => {
            const isDark = theme === 'dark';
            document.documentElement.classList.toggle('dark-mode', isDark);
            document.body.classList.toggle('dark-mode', isDark);
        };

        applyTheme(savedTheme);

        // Sync theme preference with server (optional)
        fetch('save_prefs.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ theme: savedTheme })
        }).catch(() => {
            // Silently fail if server is unreachable
        });
    })();
    </script>
</body>
</html>