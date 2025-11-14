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

    <!-- Toast -->
    <div id="toast" class="toast" role="alert" aria-live="assertive"></div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay" aria-hidden="true">
        <div class="loading" id="loading"></div>
    </div>

    <!-- Error Alert -->
    <?php if (isset($_GET['error'])): 
        $msg = match ($_GET['error']) {
            'missing'      => 'All fields are required.',
            'invalid_link' => 'Please enter a valid Google Form link.',
            'time'         => 'End time must be after start time.',
            default        => 'An error occurred. Please try again.'
        };
    ?>
        <div class="alert alert-error" role="alert">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <!-- Upload Form -->
    <section class="card form-card">
        <form id="uploadForm" action="api/upload_exam.php" method="POST" novalidate>
            <fieldset>
                <legend class="visually-hidden">Upload New Exam</legend>

                <!-- Subject -->
                <div class="form-group">
                    <label for="subject_id">Subject <span class="required">*</span></label>
                    <select id="subject_id" name="subject_id" required aria-required="true">
                        <option value="">Select a subject</option>
                        <?php
                        $stmt = $pdo->prepare(
                            "SELECT s.id, s.name 
                             FROM teacher_subjects ts 
                             JOIN subjects s ON ts.subject_id = s.id 
                             WHERE ts.teacher_id = ? 
                             ORDER BY s.name"
                        );
                        $stmt->execute([$_SESSION['user_id']]);
                        while ($s = $stmt->fetch()) {
                            echo '<option value="'.htmlspecialchars($s['id']).'">'
                                .htmlspecialchars($s['name']).'</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Title -->
                <div class="form-group">
                    <label for="title">Exam Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title"
                           placeholder="e.g., Midterm Exam in Algebra"
                           required aria-required="true">
                </div>

                <!-- Google Form Link -->
                <div class="form-group">
                    <label for="google_form_link">Google Form Link <span class="required">*</span></label>
                    <input type="url" id="google_form_link" name="google_form_link"
                           placeholder="https://forms.gle/..."
                           pattern="^https://forms\.gle/.*"
                           title="Must be a valid forms.gle link"
                           required aria-required="true">
                    <small class="form-help">Only <code>forms.gle</code> short links are accepted.</small>
                </div>

                <!-- Start / End -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_time">Start Time <span class="required">*</span></label>
                        <input type="datetime-local" id="start_time" name="start_time"
                               required aria-required="true">
                    <div class="form-group">
                        <label for="end_time">End Time <span class="required">*</span></label>
                        <input type="datetime-local" id="end_time" name="end_time"
                               required aria-required="true">
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Upload Exam</button>
                    <button type="reset" class="btn btn-secondary">Clear Form</button>
                </div>
            </fieldset>
        </form>
    </section>

    <!-- Exams Table -->
    <section class="card table-card">
        <div class="table-header">
            <h2>Uploaded Exams</h2>
            <p class="table-count" id="tableCount">
                <?php
                $stmtCount = $pdo->prepare(
                    "SELECT COUNT(*) 
                     FROM exams e 
                     WHERE e.teacher_id = ?"
                );
                $stmtCount->execute([$_SESSION['user_id']]);
                echo $stmtCount->fetchColumn() . ' exam(s)';
                ?>
            </p>
        </div>

        <?php
        $stmt = $pdo->prepare(
            "SELECT e.id, e.title, e.google_form_link, e.start_time, e.end_time, e.created_at,
                    s.name AS subject_name
             FROM exams e
             JOIN subjects s ON e.subject_id = s.id
             WHERE e.teacher_id = ?
             ORDER BY e.created_at DESC"
        );
        $stmt->execute([$_SESSION['user_id']]);
        $exams = $stmt->fetchAll();
        ?>

        <?php if (empty($exams)): ?>
            <p class="empty-state">No exams uploaded yet. Use the form above to get started.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table id="examsTable" class="responsive-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Subject</th>
                            <th>Link</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Uploaded</th>
                            <th class="actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exams as $e):
                            $linkBtn = $e['google_form_link']
                                ? '<a href="'.htmlspecialchars($e['google_form_link']).'" target="_blank" rel="noopener" class="btn btn-open btn-sm">Open</a>'
                                : '<span class="text-muted">—</span>';
                        ?>
                            <tr data-id="<?= $e['id'] ?>">
                                <td data-label="Title"><strong><?= htmlspecialchars($e['title']) ?></strong></td>
                                <td data-label="Subject"><?= htmlspecialchars($e['subject_name']) ?></td>
                                <td data-label="Link"><?= $linkBtn ?></td>
                                <td data-label="Start"><?= date('M j, Y ⟨ g:i A', strtotime($e['start_time'])) ?></td>
                                <td data-label="End"><?= date('M j, Y ⟨ g:i A', strtotime($e['end_time'])) ?></td>
                                <td data-label="Uploaded"><?= date('M j, Y ⟨ g:i A', strtotime($e['created_at'])) ?></td>
                                <td data-label="Actions" class="actions">
                                    <button onclick="deleteExam(<?= $e['id'] ?>)" 
                                            class="btn btn-delete btn-sm" 
                                            aria-label="Delete exam">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<style>
    :root {
        --bg: #f9fafb;
        --card: #ffffff;
        --text: #1f2937;
        --text-muted: #6b7280;
        --border: #e5e7eb;
        --primary: #7b61ff;
        --primary-hover: #6a51e6;
        --success: #10b981;
        --error: #ef4444;
        --warning: #f59e0b;
        --radius: 12px;
        --shadow: 0 4px 6px -1px rgba(0,0,0,.1);
        --transition: all .2s ease;
        --font: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .dark-mode {
        --bg: #0f172a;
        --card: #1e293b;
        --text: #f1f5f9;
        --text-muted: #94a3b8;
        --border: #334155;
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
        margin: 0;
        background: var(--bg);
        color: var(--text);
        font-family: var(--font);
        line-height: 1.6;
    }

    .container {
        max-width: 1150px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    /* ---------- Header ---------- */
    .page-header { text-align: center; margin-bottom: 2rem; }
    .page-title { margin: 0 0 .5rem; font-size: 2rem; font-weight: 700; }
    .page-subtitle { margin: 0; color: var(--text-muted); font-size: 1rem; }

    /* ---------- Cards ---------- */
    .card {
        background: var(--card);
        border-radius: var(--radius);
        padding: 1.75rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
    }

    /* ---------- Form ---------- */
    .form-group { margin-bottom: 1.25rem; }
    .form-group label {
        display: block;
        margin-bottom: .5rem;
        font-weight: 600;
        color: var(--text);
    }
    .required { color: var(--error); }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: .75rem 1rem;
        border: 1px solid var(--border);
        border-radius: .5rem;
        background: var(--card);
        color: var(--text);
        font-size: 1rem;
        transition: var(--transition);
    }
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(123,97,255,.2);
    }

    .form-help {
        display: block;
        margin-top: .375rem;
        font-size: .875rem;
        color: var(--text-muted);
    }

    .form-row {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }

    .form-actions {
        display: flex;
        gap: .75rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    /* ---------- Buttons ---------- */
    .btn {
        padding: .625rem 1.25rem;
        border: none;
        border-radius: .5rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .9375rem;
    }
    .btn-primary { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: var(--primary-hover); }
    .btn-secondary { background: var(--text-muted); color: #fff; }
    .btn-secondary:hover { background: #475569; }
    .btn-sm { padding: .375rem .75rem; font-size: .8125rem; font-weight: 500; }
    .btn-open { background: var(--primary); color: #fff; }
    .btn-open:hover { background: var(--primary-hover); }
    .btn-delete { background: var(--error); color: #fff; }
    .btn-delete:hover { background: #dc2626; }

    /* ---------- Table ---------- */
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: .75rem;
    }
    .table-header h2 { margin: 0; font-size: 1.25rem; font-weight: 600; }
    .table-count { margin: 0; color: var(--text-muted); font-size: .925rem; }

    .table-wrapper {
        overflow-x: auto;
        border-radius: .5rem;
        border: 1px solid var(--border);
    }

    .responsive-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .925rem;
    }
    .responsive-table th {
        background: #f8fafc;
        color: #374151;
        font-weight: 600;
        text-align: left;
        padding: .875rem 1rem;
        border-bottom: 2px solid var(--border);
    }
    .dark-mode .responsive-table th { background: #1e293b; color: #e2e8f0; }

    .responsive-table td {
        padding: .875rem 1rem;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .responsive-table tr:hover { background: rgba(123,97,255,.05); }
    .actions { white-space: nowrap; }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--text-muted);
        font-style: italic;
    }

    /* ---------- Alerts ---------- */
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        padding: .875rem 1rem;
        border-radius: .5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--error);
        font-weight: 500;
    }

    /* ---------- Toast ---------- */
    .toast {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        min-width: 320px;
        padding: 1rem 1.25rem;
        border-radius: .5rem;
        color: #fff;
        font-weight: 600;
        box-shadow: 0 10px 20px rgba(0,0,0,.15);
        transform: translateX(120%);
        transition: transform .4s cubic-bezier(.175,.885,.32,1.275);
        z-index: 1000;
    }
    .toast.show { transform: translateX(0); }
    .toast.error { background: var(--error); }
    .toast.success { background: var(--success); }

    /* ---------- Loading ---------- */
    .loading-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,.7);
        backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
        z-index: 999;
    }
    .loading {
        width: 48px;
        height: 48px;
        border: 4px solid #e2e8f0;
        border-top: 4px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ---------- Responsive ---------- */
    @media (max-width: 768px) {
        .form-row { grid-template-columns: 1fr; }
        .form-actions { flex-direction: column; }
        .table-header { flex-direction: column; align-items: flex-start; }

        .responsive-table thead { display: none; }
        .responsive-table tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid var(--border);
            border-radius: .5rem;
            padding: .75rem;
        }
        .responsive-table td {
            display: block;
            text-align: right;
            padding: .5rem 0;
            border: none;
            position: relative;
        }
        .responsive-table td::before {
            content: attr(data-label);
            position: absolute;
            left: 0;
            font-weight: 600;
            color: var(--text-muted);
        }
        .responsive-table td.actions { text-align: center; }
    }

    .visually-hidden {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0,0,0,0);
        border: 0;
    }
</style>

<script>
    /* ---------- Toast ---------- */
    function showToast(message, isError = false) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = 'toast' + (isError ? ' error' : ' success');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3500);
    }

    /* ---------- Loading ---------- */
    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }
    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    /* ---------- Form Submit ---------- */
    document.getElementById('uploadForm').addEventListener('submit', () => showLoading());

    /* ---------- Delete Exam ---------- */
    function deleteExam(id) {
        if (!confirm('Are you sure you want to delete this exam? This cannot be undone.')) return;

        showLoading();
        fetch(`api/delete_exam.php?id=${id}`)
            .then(r => r.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    document.querySelector(`tr[data-id="${id}"]`).remove();
                    showToast('Exam deleted successfully!');
                    updateTableCount();
                } else {
                    showToast(data.message || 'Delete failed.', true);
                }
            })
            .catch(() => {
                hideLoading();
                showToast('Network error. Please try again.', true);
            });
    }

    function updateTableCount() {
        const count = document.querySelectorAll('#examsTable tbody tr').length;
        document.getElementById('tableCount').textContent = `${count} exam(s)`;
    }

    /* ---------- URL Feedback ---------- */
    window.addEventListener('load', () => {
        const params = new URLSearchParams(location.search);
        if (params.get('uploaded')) showToast('Exam uploaded successfully!');
        if (params.get('deleted')) showToast('Exam deleted successfully!');
        if (params.get('error')) {
            const msgs = {
                missing: 'All fields are required.',
                invalid_link: 'Invalid Google Form link.',
                time: 'End time must be after start time.'
            };
            const msg = msgs[params.get('error')] || 'Operation failed.';
            showToast(msg, true);
        }
        if (params.toString()) {
            history.replaceState({}, '', location.pathname);
        }
    });
</script>

<?php include 'includes/footer.php'; ?>