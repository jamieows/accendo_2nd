<?php
// includes/footer.php
?>
    </main>

    <script>
    (function () {
        const STORAGE_THEME = 'accendo_theme';
        const STORAGE_ZOOM  = 'accendo_zoom';
        const MIN_ZOOM = 0.8, MAX_ZOOM = 1.5, STEP = 0.1;

        // Apply saved theme
        const savedTheme = localStorage.getItem(STORAGE_THEME);
        if (savedTheme === 'dark') document.body.classList.add('dark-mode');

        // Apply saved zoom
        let zoom = parseFloat(localStorage.getItem(STORAGE_ZOOM) || '1');
        zoom = Math.max(MIN_ZOOM, Math.min(MAX_ZOOM, zoom));
        document.documentElement.style.fontSize = `calc(16px * ${zoom})`;

        // Save to server
        const save = (data) => {
            fetch('save_prefs.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            }).catch(() => {});
        };

        // Dark Mode
        window.toggleDarkMode = function (enable) {
            document.body.classList.toggle('dark-mode', enable);
            localStorage.setItem(STORAGE_THEME, enable ? 'dark' : 'light');
            save({ theme: enable ? 'dark' : 'light' });
        };

        // Zoom
        const applyZoom = (val) => {
            zoom = val;
            document.documentElement.style.fontSize = `calc(16px * ${zoom})`;
            localStorage.setItem(STORAGE_ZOOM, zoom);
            save({ zoom });
        };

        window.zoomIn    = () => { if (zoom < MAX_ZOOM) applyZoom(zoom + STEP); };
        window.zoomOut   = () => { if (zoom > MIN_ZOOM) applyZoom(zoom - STEP); };
        window.zoomReset = () => applyZoom(1);

        // Keyboard
        document.addEventListener('keydown', e => {
            if (e.ctrlKey || e.metaKey) {
                if (e.key === '+' || e.key === '=') { e.preventDefault(); window.zoomIn(); }
                else if (e.key === '-') { e.preventDefault(); window.zoomOut(); }
                else if (e.key === '0') { e.preventDefault(); window.zoomReset(); }
            }
        });
    })();
    </script>
</body>
</html>