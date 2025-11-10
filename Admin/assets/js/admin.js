// Admin/assets/js/admin.js
document.addEventListener('DOMContentLoaded', () => {
    // Auto-refresh dashboard stats every 30 seconds
    setInterval(() => {
        if (window.location.pathname.includes('index.php')) {
            fetch('../api/get_stats.php')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('stat-users').textContent = data.total;
                    document.getElementById('stat-teachers').textContent = data.teachers;
                    document.getElementById('stat-students').textContent = data.students;
                });
        }
    }, 30000);

    // Confirm delete with voice (accessibility)
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (!confirm('Delete this user? This cannot be undone.')) {
                e.preventDefault();
            } else {
                speak('User will be deleted.');
            }
        });
    });
});