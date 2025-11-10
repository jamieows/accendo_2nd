// Teacher/assets/js/teacher.js
document.addEventListener('DOMContentLoaded', () => {
    // Drag & drop upload
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');

    if (dropZone && fileInput) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(event => {
            dropZone.addEventListener(event, e => e.preventDefault());
        });

        dropZone.addEventListener('dragenter', () => dropZone.classList.add('dragover'));
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        dropZone.addEventListener('drop', e => {
            dropZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length) fileInput.files = files;
        });

        dropZone.addEventListener('click', () => fileInput.click());
    }

    // Auto-highlight due dates
    document.querySelectorAll('.due-date').forEach(el => {
        const due = new Date(el.dataset.due);
        const now = new Date();
        const diff = due - now;
        if (diff < 0) el.classList.add('due-over');
        else if (diff < 86400000) el.classList.add('due-soon'); // < 24h
    });
});