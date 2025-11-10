// Student/assets/js/student.js
document.addEventListener('DOMContentLoaded', () => {
    // Read aloud on click
    document.querySelectorAll('.speak-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const text = btn.closest('.material-card')?.querySelector('h3')?.textContent || 
                        btn.closest('.assignment-card')?.querySelector('strong')?.textContent;
            if (text) speak(text);
        });
    });

    // Progress animation
    document.querySelectorAll('.progress-fill').forEach(bar => {
        const target = bar.dataset.percent || 0;
        setTimeout(() => bar.style.width = target + '%', 300);
    });

    // Auto-enroll voice welcome
    if (window.location.pathname.includes('index.php')) {
        setTimeout(() => speak(`Welcome back, ${document.title.split('|')[0].trim()}. You have enrolled subjects.`), 1000);
    }
});