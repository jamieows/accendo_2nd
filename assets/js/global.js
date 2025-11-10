// assets/js/global.js
document.addEventListener('DOMContentLoaded', () => {
    // Dark mode
    const toggle = document.getElementById('theme-toggle');
    if (toggle) {
        toggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        });
    }

    // Font size
    let fSize = 16;
    document.getElementById('font-increase')?.addEventListener('click', () => {
        fSize = Math.min(fSize + 2, 24);
        document.body.style.fontSize = fSize + 'px';
    });
    document.getElementById('font-decrease')?.addEventListener('click', () => {
        fSize = Math.max(fSize - 2, 14);
        document.body.style.fontSize = fSize + 'px';
    });

    // Load saved dark mode
    // Prefer new admin_theme key if present (values: 'light'|'dark'|'system')
    const adminTheme = localStorage.getItem('admin_theme');
    if (adminTheme) {
        if (adminTheme === 'dark') document.documentElement.classList.add('dark-mode');
        else if (adminTheme === 'light') document.documentElement.classList.remove('dark-mode');
        else if (adminTheme === 'system' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) document.documentElement.classList.add('dark-mode');
    } else if (localStorage.getItem('darkMode') === 'true') {
        // fallback for older flag
        document.documentElement.classList.add('dark-mode');
    }

    // NLP volume display
    const vol = document.getElementById('nlp-volume');
    const val = document.getElementById('vol-value');
    if (vol && val) vol.addEventListener('input', () => val.textContent = vol.value);
});

/* Text-to-Speech */
function speak(text) {
    const utter = new SpeechSynthesisUtterance(text);
    utter.lang = 'en-US';
    const vol = document.getElementById('nlp-volume');
    utter.volume = (vol ? vol.value : 70) / 100;
    speechSynthesis.speak(utter);
}