document.addEventListener('DOMContentLoaded', () => {
    // ---------- READ ALOUD ----------
    document.querySelectorAll('.speak-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const text = btn.closest('.material-card')?.querySelector('h3')?.textContent ||
                        btn.closest('.assignment-card')?.querySelector('strong')?.textContent;
            if (text) speak(text);
        });
    });

    // ---------- PROGRESS ANIMATION ----------
    document.querySelectorAll('.progress-fill').forEach(bar => {
        const target = bar.dataset.percent || 0;
        setTimeout(() => bar.style.width = target + '%', 300);
    });

    // ---------- VOICE WELCOME ----------
    if (window.location.pathname.includes('index.php')) {
        setTimeout(() => speak(`Welcome back, ${document.title.split('|')[0].trim()}. You have enrolled subjects.`), 1000);
    }

    // ---------- PROFILE FORM ----------
    const profileForm = document.getElementById('profileForm');
    const fileInput = document.getElementById('profileImage');
    const preview = document.getElementById('profilePreview');

    function showDialog(message, type = 'info') {
        const dialog = document.createElement('div');
        dialog.className = 'dialog';
        dialog.innerHTML = `
            <div class="dialog-content">
                <h3>${type === 'success' ? '✅ Success' : type === 'error' ? '⚠️ Error' : 'ℹ️ Info'}</h3>
                <p>${message}</p>
                <button class="btn btn-primary">OK</button>
            </div>`;
        document.body.appendChild(dialog);
        dialog.style.display = 'flex';
        dialog.querySelector('button').addEventListener('click', () => {
            dialog.remove();
            if (type === 'success') location.reload();
        });
    }

    // Preview uploaded image
    if (fileInput && preview) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => preview.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // Handle form submission
    if (profileForm) {
        profileForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Check new password & confirm password client-side
            const newPassword = formData.get('new_password');
            const confirmPassword = formData.get('confirm_password');

            if (newPassword || confirmPassword) {
                if (newPassword !== confirmPassword) {
                    showDialog('New password and confirm password do not match.', 'error');
                    return;
                }
                if (!formData.get('old_password')) {
                    showDialog('Please enter your current password to change it.', 'error');
                    return;
                }
            }

            try {
                const res = await fetch(this.action, { method: 'POST', body: formData });
                const data = await res.json();

                if (!res.ok) {
                    showDialog('Server error. Please try again later.', 'error');
                    return;
                }

                if (data.status === 'success') {
                    showDialog(data.message, 'success');
                } else if (data.status === 'error') {
                    showDialog(data.message, 'error');
                } else {
                    showDialog('Invalid input. Please check your details.', 'error');
                }

            } catch (err) {
                console.error(err);
                showDialog('Network error. Please check your connection.', 'error');
            }
        });
    }
});

// ---------- DARK MODE ----------
const themeBtn = document.getElementById('theme-toggle');
if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-mode');
}
if (themeBtn) {
    themeBtn.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('theme',
            document.body.classList.contains('dark-mode') ? 'dark' : 'light');
    });
}

// ---------- SPEECH SYNTHESIS ----------
function speak(text) {
    if (!window.speechSynthesis) return;
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.rate = 1;
    utterance.pitch = 1;
    utterance.volume = 1;
    speechSynthesis.cancel();
    speechSynthesis.speak(utterance);
}
