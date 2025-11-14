<?php
// includes/footer.php
?>
    </main>

    <!-- Dark Mode Toggle Button (Fixed Position) -->
    <button id="darkModeToggle" class="dark-mode-toggle" aria-label="Toggle dark mode">
        <svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="5"></circle>
            <line x1="12" y1="1" x2="12" y2="3"></line>
            <line x1="12" y1="21" x2="12" y2="23"></line>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
            <line x1="1" y1="12" x2="3" y2="12"></line>
            <line x1="21" y1="12" x2="23" y2="12"></line>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>
        <svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
    </button>

    <style>
        .dark-mode-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--card);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .dark-mode-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        
        .dark-mode-toggle svg {
            color: var(--text);
        }
        
        .dark-mode .sun-icon {
            display: none;
        }
        
        .dark-mode .moon-icon {
            display: block !important;
        }
    </style>

    <script>
    (function () {
        const STORAGE_THEME = 'accendo_theme';

        // Apply saved theme on load
        const savedTheme = localStorage.getItem(STORAGE_THEME) || 'light';
        document.documentElement.classList.toggle('dark-mode', savedTheme === 'dark');
        document.body.classList.toggle('dark-mode', savedTheme === 'dark');

        // Dark Mode Toggle
        const toggleBtn = document.getElementById('darkModeToggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const isDark = document.body.classList.toggle('dark-mode');
                document.documentElement.classList.toggle('dark-mode', isDark);
                
                localStorage.setItem(STORAGE_THEME, isDark ? 'dark' : 'light');
                
                // Save to server
                fetch('save_prefs.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ theme: isDark ? 'dark' : 'light' })
                }).catch(() => {});
            });
        }

        // Check sidebar state and adjust content
        const sidebar = document.getElementById('sidebar');
        const content = document.querySelector('.content');
        if (sidebar && content) {
            const isHidden = localStorage.getItem('sidebar-hidden') === '1';
            if (isHidden) {
                content.style.marginLeft = '0';
            }
        }
    })();
    </script>
</body>
</html>