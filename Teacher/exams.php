<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: /accendo_2nd/Auth/login.php');
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <!-- Page Header -->
    <header class="page-header">
        <h1 class="page-title">Upload Exam / Quiz</h1>
        <p class="page-subtitle">Create and manage assessments for your students</p>
    </header>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Error Alert -->
    <?php if (isset($_GET['error'])): 
        $msg = match ($_GET['error']) {
            'missing'      => 'All required fields must be filled.',
            'invalid_link' => 'Please use a valid forms.gle short link.',
            'time'         => 'End time must be after start time.',
            default        => 'Something went wrong. Please try again.'
        };
    ?>
        <div class="alert alert-danger">
            Error: <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <!-- Upload Form Card -->
    <div class="upload-card">
        <div class="card-header">
            <h2>New Exam / Quiz</h2>
        </div>
        <form id="uploadForm" action="api/upload_exam.php" method="POST" novalidate>
            <div class="form-grid">

                <!-- Subject -->
                <div class="form-group">
                    <label for="subject_id">Subject <span class="required">*</span></label>
                    <select id="subject_id" name="subject_id" required>
                        <option value="">Choose subject</option>
                        <?php
                        $stmt = $pdo->prepare("SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ? ORDER BY s.name");
                        $stmt->execute([$_SESSION['user_id']]);
                        while ($row = $stmt->fetch()) {
                            echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['name']).'</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Title -->
                <div class="form-group">
                    <label for="title">Exam Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" placeholder="e.g., Midterm Exam in Algebra" required>
                </div>

                <!-- Google Form Link -->
                <div class="form-group full-width">
                    <label for="google_form_link">Google Form Link <span class="required">*</span></label>
                    <input type="url" 
                           id="google_form_link" 
                           name="google_form_link" 
                           placeholder="https://forms.gle/AbCdEf12345" 
                           pattern="^https://forms\.gle/.*" 
                           required>
                    <small>Only <code>forms.gle</code> short links are accepted</small>
                </div>

                <!-- DateTime Pickers -->
                <div class="form-group">
                    <label for="start_time">Start Time <span class="required">*</span></label>
                    <input type="datetime-local" id="start_time" name="start_time" required>
                </div>

                <div class="form-group">
                    <label for="end_time">End Time <span class="required">*</span></label>
                    <input type="datetime-local" id="end_time" name="end_time" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary-large">
                    Upload Exam
                </button>
                <button type="reset" class="btn-secondary-large">Clear Form</button>
            </div>
        </form>
    </div>

    <!-- Exams List -->
    <div class="table-card">
        <div class="table-header">
            <h2>Uploaded Exams & Quizzes</h2>
            <span class="count-badge">
                <?php
                $count = $pdo->prepare("SELECT COUNT(*) FROM exams WHERE teacher_id = ?");
                $count->execute([$_SESSION['user_id']]);
                $total = $count->fetchColumn();
                echo $total . " exam" . ($total == 1 ? '' : 's');
                ?>
            </span>
        </div>

        <?php
        $stmt = $pdo->prepare("SELECT e.*, s.name as subject_name FROM exams e JOIN subjects s ON e.subject_id = s.id WHERE e.teacher_id = ? ORDER BY e.created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $exams = $stmt->fetchAll();
        ?>

        <?php if (empty($exams)): ?>
            <div class="empty-state">
                <p>No exams uploaded yet.</p>
                <p>Start creating one using the form above!</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="exams-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Subject</th>
                            <th>Availability</th>
                            <th>Link</th>
                            <th>Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exams as $exam): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($exam['title']) ?></strong></td>
                            <td><span class="subject-tag"><?= htmlspecialchars($exam['subject_name']) ?></span></td>
                            <td>
                                <small>
                                    <?= date('M j, Y', strtotime($exam['start_time'])) ?> <br>
                                    <?= date('g:i A', strtotime($exam['start_time'])) ?> – <?= date('g:i A', strtotime($exam['end_time'])) ?>
                                </small>
                            </td>
                            <td>
                                <a href="<?= htmlspecialchars($exam['google_form_link']) ?>" target="_blank" class="btn-open">
                                    Open Form
                                </a>
                            </td>
                            <td><?= date('M j, Y at g:i A', strtotime($exam['created_at'])) ?></td>
                            <td>
                                <button onclick="deleteExam(<?= $exam['id'] ?>)" class="btn-delete-sm" title="Delete exam">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ==================== FIXED & ENHANCED CSS (ONLY ADDED/FIXED LINES) ==================== -->
<style>
    :root {
        --primary: #7B61FF;
        --primary-dark: #6A51E6;
        --bg: #F9F7FE;
        --card: #FFFFFF;
        --text: #162447;
        --text-light: #64748B;
        --border: #E2E8F0;
        --danger: #EF4444;
        --success: #10B981;
        --radius: 16px;
        --shadow: 0 10px 25px rgba(123,97,255,0.1);
    }

    /* Dark Mode - This is what makes it look like your screenshot */
    .dark-mode {
        --bg: #0f172a;
        --card: #1e293b;
        --text: #f1f5f9;
        --text-light: #94a3b8;
        --border: #334155;
    }

    body { 
        background: var(--bg); 
        color: var(--text); 
        font-family: 'Inter', sans-serif; 
        margin: 0;
        transition: background 0.3s;
    }

    .container { max-width: 1100px; margin: 0 auto; padding: 2rem 1rem; }

    .page-header { text-align: center; margin-bottom: 3rem; }
    .page-title { 
        font-size: 2.8rem; 
        font-weight: 700; 
        background: linear-gradient(135deg, #7B61FF, #A78BFA); 
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent; 
        margin: 0; 
    }
    .page-subtitle { color: var(--text-light); font-size: 1.1rem; margin-top: 0.5rem; }

    /* Cards */
    .upload-card, .table-card {
        background: var(--card);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 2rem;
        border: 1px solid var(--border);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary), #A78BFA);
        color: white;
        padding: 1.5rem 2rem;
        font-size: 1.4rem;
        font-weight: 600;
    }

    .upload-card form { padding: 2rem; }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1rem;
    }

    .form-group.full-width { grid-column: 1 / -1; }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--text);
    }

    .required { color: var(--danger); }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.9rem 1.2rem;
        border: 2px solid var(--border);
        border-radius: 12px;
        background: var(--card);
        color: var(--text);
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(123,97,255,0.15);
    }

    .form-group small {
        color: var(--text-light);
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: block;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
        margin-top: 1.5rem;
        justify-content: flex-end;
    }

    .btn-primary-large, .btn-secondary-large {
        padding: 0.9rem 2rem;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1.05rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary-large {
        background: linear-gradient(135deg, var(--primary), #A78BFA);
        color: white;
        flex: 1;
    }

    .btn-primary-large:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 10px 20px rgba(123,97,255,0.3); 
    }

    .btn-secondary-large {
        background: #334155;
        color: #cbd5e1;
        flex: 1;
    }

    /* ==================== FIXED: This line was broken in your code ==================== */
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 2rem;
        background: var(--card);
        border-bottom: 1px solid var(--border);
        flex-wrap: wrap;
        gap: 1rem;
    }

    .table-header h2 { margin: 0; font-size: 1.4rem; font-weight: 600; }
    .count-badge { 
        background: rgba(123,97,255,0.2); 
        color: var(--primary); 
        padding: 0.5rem 1.2rem; 
        border-radius: 50px; 
        font-weight: 600; 
        font-size: 0.95rem;
    }

    .table-responsive { overflow-x: auto; }
    .exams-table { 
        width: 100%; 
        border-collapse: collapse; 
        font-size: 0.95rem; 
    }
    .exams-table th { 
        background: rgba(123,97,255,0.1); 
        color: var(--text-light);
        padding: 1.2rem 1rem;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid var(--border);
    }
    .exams-table td { 
        padding: 1.3rem 1rem; 
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .exams-table tr:hover { 
        background: rgba(123,97,255,0.08); 
    }

    .subject-tag { 
        background: rgba(123,97,255,0.2); 
        color: var(--primary); 
        padding: 0.4rem 0.9rem; 
        border-radius: 30px; 
        font-size: 0.85rem; 
        font-weight: 500;
    }

    .btn-open { 
        background: var(--primary); 
        color: white; 
        padding: 0.6rem 1.2rem; 
        border-radius: 10px; 
        text-decoration: none; 
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-block;
    }
    .btn-open:hover { background: var(--primary-dark); }

    .btn-delete-sm { 
        background: var(--danger); 
        color: white; 
        border: none; 
        padding: 0.6rem 1.2rem; 
        border-radius: 10px; 
        cursor: pointer;
        font-size: 0.85rem;
    }
    .btn-delete-sm:hover { background: #dc2626; }

    .empty-state { 
        text-align: center; 
        padding: 5rem 2rem; 
        color: var(--text-light); 
        font-size: 1.1rem;
    }

    .alert-danger { 
        background: #fee2e2; 
        color: #991b1b; 
        padding: 1rem 1.5rem; 
        border-radius: 12px; 
        border-left: 5px solid var(--danger);
        margin-bottom: 2rem;
        font-weight: 500;
    }

    /* Toast & Loading */
    #toast {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        padding: 1rem 1.8rem;
        border-radius: 12px;
        color: white;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        transform: translateX(400px);
        transition: transform 0.5s ease;
        z-index: 1000;
    }
    #toast.show { transform: translateX(0); }
    #toast.error { background: var(--danger); }

    .loading-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.7);
        backdrop-filter: blur(8px);
        align-items: center;
        justify-content: center;
        z-index: 999;
    }
    .spinner {
        width: 60px;
        height: 60px;
        border: 6px solid #334155;
        border-top: 6px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-actions { flex-direction: column; }
        .table-header { flex-direction: column; align-items: stretch; text-align: center; }
        .exams-table thead { display: none; }
        .exams-table tr { 
            display: block; 
            margin-bottom: 1.5rem; 
            background: var(--card);
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid var(--border);
        }
        .exams-table td { 
            display: block; 
            text-align: right; 
            padding: 0.6rem 0;
            position: relative;
        }
        .exams-table td::before { 
            content: attr(data-label) ": "; 
            position: absolute; 
            left: 0; 
            font-weight: 600; 
            color: var(--text-light);
        }
        .exams-table td:last-child { text-align: center; }
    }
</style>

<!-- Your original script (100% untouched) -->
<script>
    function showToast(msg, error = false) {
        const toast = document.getElementById('toast');
        toast.textContent = msg;
        toast.className = 'toast' + (error ? ' error' : '');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
    }

    document.getElementById('uploadForm').addEventListener('submit', () => {
        document.getElementById('loadingOverlay').style.display = 'flex';
    });

    function deleteExam(id) {
        if (!confirm('Delete this exam permanently? This action cannot be undone.')) return;
        
        document.getElementById('loadingOverlay').style.display = 'flex';
        
        fetch(`api/delete_exam.php?id=${id}`)
            .then(r => r.json())
            .then(res => {
                document.getElementById('loadingOverlay').style.display = 'none';
                if (res.success) {
                    document.querySelector(`tr td:nth-child(6) button[onclick="deleteExam(${id})"]`).closest('tr').remove();
                    showToast('Exam deleted successfully!');
                    location.reload();
                } else {
                    showToast(res.message || 'Failed to delete', true);
                }
            })
            .catch(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
                showToast('Network error', true);
            });
    }

    // URL messages
    if (location.search.includes('uploaded=1')) showToast('Exam uploaded successfully!');
    if (location.search.includes('deleted=1')) showToast('Exam deleted successfully!');
</script>

<?php include 'includes/footer.php'; ?>