<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

/* AUTO-CREATE uploaded_at COLUMN IF NOT EXISTS */
try {
    $pdo->query("SELECT uploaded_at FROM materials LIMIT 1");
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Unknown column 'uploaded_at'") !== false) {
        $pdo->exec("ALTER TABLE materials ADD COLUMN uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        $pdo->exec("UPDATE materials SET uploaded_at = NOW() WHERE uploaded_at IS NULL");
    }
}
?>
<?php include 'includes/header.php'; ?>

<style>
    :root {
        --primary: #7B61FF;
        --primary-light: #A78BFA;
        --danger: #EF4444;
        --success: #10B981;
        --text: #1f2937;
        --text-light: #6b7280;
        --bg: #ffffff;
        --card: #ffffff;
        --border: rgba(0,0,0,0.1);
        --shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .dark-mode {
        --text: #f3f4f6;
        --text-light: #94a3b8;
        --bg: #0f172a;
        --card: #1e293b;
        --border: #334155;
        --shadow: 0 10px 30px rgba(0,0,0,0.4);
    }

    body {
        background: var(--bg);
        color: var(--text);
        font-family: 'Inter', sans-serif;
        transition: background 0.3s, color 0.3s;
        margin: 0;
    }

    .container { max-width: 1100px; margin: 0 auto; padding: 2rem 1rem; }

    /* Headings - Beautiful gradient in both modes */
    h1, h2 {
        margin: 0 0 1.5rem 0;
        font-weight: 700;
    }

    h1 {
        font-size: 2.8rem;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    h2 {
        font-size: 1.9rem;
        color: var(--text);
        margin-top: 2.5rem;
    }

    /* Cards */
    .card {
        background: var(--card);
        border-radius: 16px;
        padding: 28px;
        margin: 24px 0;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
    }

    /* Form Styling */
    form.card label {
        display: block;
        margin: 16px 0 8px;
        font-weight: 600;
        color: var(--text);
        font-size: 0.95rem;
    }

    form.card select,
    form.card input[type="text"],
    form.card textarea,
    form.card input[type="file"] {
        width: 100%;
        padding: 12px 14px;
        border: 2px solid var(--border);
        border-radius: 12px;
        background: var(--card);
        color: var(--text);
        font-size: 1rem;
        box-sizing: border-box;
        transition: all 0.3s ease;
        margin-bottom: 16px;
    }

    form.card input:focus,
    form.card select:focus,
    form.card textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(123,97,255,0.15);
    }

    form.card textarea {
        min-height: 100px;
        resize: vertical;
    }

    /* Buttons */
    .btn, .btn-sm {
        display: inline-block;
        padding: 10px 20px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        text-align: center;
    }

    .btn {
        padding: 14px 32px;
        font-size: 1.05rem;
    }

    .btn-sm {
        padding: 8px 16px;
        font-size: 0.875rem;
    }

    .btn-view, .btn-sm.btn-view {
        background: var(--primary);
        color: white;
    }

    .btn-view:hover, .btn-sm.btn-view:hover {
        background: #6a51e6;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(123,97,255,0.3);
    }

    .btn-danger, .btn-sm.btn-danger {
        background: var(--danger);
        color: white;
    }

    .btn-danger:hover, .btn-sm.btn-danger:hover {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(239,68,68,0.3);
    }

    /* Table */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
        background: var(--card);
        border-radius: 12px;
        overflow: hidden;
    }

    table th {
        background: rgba(123,97,255,0.1);
        color: var(--text);
        padding: 16px 14px;
        text-align: left;
        font-weight: 600;
        font-size: 0.95rem;
    }

    table td {
        padding: 16px 14px;
        border-bottom: 1px solid var(--border);
        color: var(--text);
        vertical-align: middle;
    }

    table tr:hover {
        background: rgba(123,97,255,0.08);
    }

    table tr:last-child td {
        border-bottom: none;
    }

    /* Alerts */
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        padding: 16px 20px;
        border-radius: 12px;
        margin: 20px 0;
        font-weight: 500;
        border-left: 5px solid var(--success);
        box-shadow: 0 4px 12px rgba(16,185,129,0.15);
    }

    .dark-mode .alert-success {
        background: rgba(16,185,129,0.2);
        color: #86efac;
        border-left-color: var(--success);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .container { padding: 1rem; }
        h1 { font-size: 2.2rem; }
        table { font-size: 0.9rem; }
        table th, table td { padding: 12px 10px; }
        .btn, .btn-sm { width: 100%; margin: 8px 0; }
    }
</style>

<div class="container">

    <h1>My Courses</h1>

    <form action="api/upload_material.php" method="POST" class="card" enctype="multipart/form-data">
        <label>Subject *</label>
        <select name="subject_id" required>
            <option value="">Select a subject</option>
            <?php
            $stmt = $pdo->prepare("SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ? ORDER BY s.name");
            $stmt->execute([$_SESSION['user_id']]);
            while($s = $stmt->fetch()) {
                echo "<option value='".htmlspecialchars($s['id'])."'>".htmlspecialchars($s['name'])."</option>";
            }
            ?>
        </select>

        <label>Title *</label>
        <input type="text" name="title" placeholder="e.g., Chapter 1: Introduction to Biology" required>

        <label>Description (Optional)</label>
        <textarea name="description" rows="4" placeholder="Brief description of the material..."></textarea>

        <label>File * (PDF, DOC, Video)</label>
        <input type="file" name="file" 
               accept=".pdf,.doc,.docx,.ppt,.pptx,video/mp4,video/webm,video/ogg" required>

        <button type="submit" class="btn btn-view">Upload Material</button>
    </form>

    <h2>Uploaded Materials</h2>

    <?php if (isset($_GET['uploaded'])): ?>
        <div class="alert-success">
            Material uploaded successfully!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert-success">
            Material deleted successfully.
        </div>
    <?php endif; ?>

    <div class="card">
        <?php
        $stmt = $pdo->prepare("
            SELECT m.id, m.title, m.description, s.name AS subject, m.file_path, m.uploaded_at
            FROM materials m
            JOIN subjects s ON m.subject_id = s.id
            WHERE m.teacher_id = ?
            ORDER BY m.uploaded_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $materials = $stmt->fetchAll();

        if (empty($materials)): ?>
            <p style="text-align:center; color:var(--text-light); padding:60px 20px; font-size:1.1rem;">
                No materials uploaded yet.<br>
                Start sharing resources with your students!
            </p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Subject</th>
                        <th>File</th>
                        <th>Uploaded</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $m):
                        $fileLink = $m['file_path']
                            ? '<a href="/accendo_2nd/'.htmlspecialchars($m['file_path']).'" target="_blank" class="btn-sm btn-view">View File</a>'
                            : '<span style="color:var(--text-light)">—</span>';

                        $description = $m['description'] ? htmlspecialchars($m['description']) : '<em style="color:var(--text-light)">No description</em>';

                        $uploaded = $m['uploaded_at']
                            ? date('M j, Y <br> g:i A', strtotime($m['uploaded_at']))
                            : '—';
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($m['title']) ?></strong></td>
                            <td><?= $description ?></td>
                            <td><span style="background:rgba(123,97,255,0.2); color:var(--primary); padding:4px 10px; border-radius:20px; font-size:0.85rem; font-weight:500;">
                                <?= htmlspecialchars($m['subject']) ?>
                            </span></td>
                            <td><?= $fileLink ?></td>
                            <td><small><?= $uploaded ?></small></td>
                            <td>
                                <a href="api/delete_material.php?delete=<?= urlencode($m['id']) ?>" 
                                   class="btn-sm btn-danger"
                                   onclick="return confirm('Delete this material permanently? This cannot be undone.')">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<!-- GLOBAL THEME SYNC - Same as exams.php & assignments.php -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const applyTheme = () => {
        const theme = localStorage.getItem('theme');
        if (theme === 'light') {
            document.body.classList.remove('dark-mode');
        } else {
            document.body.classList.add('dark-mode');
        }
    };

    applyTheme();

    // Listen for real-time theme changes from Settings page
    window.addEventListener('storage', (e) => {
        if (e.key === 'theme') applyTheme();
    });
});
</script>

<?php include 'includes/footer.php'; ?>