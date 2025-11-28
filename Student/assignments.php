<?php 
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../Auth/login.php"); 
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="assignments-page">
    <div class="container">
        <header class="page-header">
            <h1 class="page-title">My Assignments</h1>
            <p class="page-desc">Submit work • Track progress</p>
        </header>

        <div class="filters-bar">
            <div class="search-wrapper">
                <input type="text" id="searchInput" placeholder="Search..." aria-label="Search assignments">
                <svg class="search-icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" fill="none" stroke="currentColor" stroke-width="2"/><path d="m21 21-4.35-4.35" fill="none" stroke="currentColor" stroke-width="2"/></svg>
            </div>
            <select id="filterStatus" class="status-filter">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="submitted">Submitted</option>
                <option value="overdue">Overdue</option>
            </select>
        </div>

        <div class="assignments-grid" id="assignmentsList">
            <?php
            $stmt = $pdo->prepare("
                SELECT a.id, a.title, a.description, a.due_date, 
                       s.name AS subject,
                       sub.id AS submission_id, sub.file_path AS submitted_file, 
                       sub.grade, sub.submitted_at
                FROM assignments a
                JOIN subjects s ON a.subject_id = s.id
                JOIN student_subjects ss ON ss.subject_id = s.id
                LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
                WHERE ss.student_id = ?
                ORDER BY a.due_date ASC
            ");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
            $assignments = $stmt->fetchAll();

            if (empty($assignments)) {
                echo '<div class="empty-state"><div class="empty-icon">No assignments</div><p>All caught up!</p></div>';
            }

            foreach ($assignments as $a) {
                $dueDate = new DateTime($a['due_date']);
                $now = new DateTime();
                $isOverdue = $dueDate < $now && !$a['submission_id'];

                $status = $a['submission_id']
                    ? ($a['grade'] !== null ? 'graded' : 'submitted')
                    : ($isOverdue ? 'overdue' : 'pending');

                $filterStatus = $status === 'graded' ? 'submitted' : $status;

                $statusConfig = [
                    'pending'   => ['text' => 'Pending',   'color' => '#f59e0b'],
                    'submitted' => ['text' => 'Submitted', 'color' => '#3b82f6'],
                    'graded'    => ['text' => 'Graded',    'color' => '#10b981'],
                    'overdue'   => ['text' => 'Overdue',   'color' => '#ef4444'],
                ];

                $badge = $statusConfig[$status];
                $dueStr = $dueDate->format('M j, Y');

                // Fixed: Relative web path from /student/ to /uploads/
                $fileUrl = $a['submitted_file'] ? '../' . htmlspecialchars($a['submitted_file']) : '';

                echo "
                <article class='assignment-card' data-status='$filterStatus' data-title='" . htmlspecialchars($a['title']) . "' data-subject='" . htmlspecialchars($a['subject']) . "'>
                    <div class='card-header'>
                        <div class='info'>
                            <div class='subject'>" . htmlspecialchars($a['subject']) . "</div>
                            <h2 class='title'>" . htmlspecialchars($a['title']) . "</h2>
                            <div class='due'>Due $dueStr</div>
                        </div>
                        <span class='status-badge' style='background:{$badge['color']}'>
                            {$badge['text']}
                        </span>
                    </div>

                    <div class='card-body'>
                        <p class='desc'>" . (strlen($a['description'] ?? '') > 110 
                            ? htmlspecialchars(substr($a['description'], 0, 110)) . '...' 
                            : htmlspecialchars($a['description'] ?? 'No description')) . "</p>
                    </div>

                    <div class='card-footer'>
                        " . ($a['submission_id'] ? "
                            <div class='meta'>
                                <small>Submitted " . (new DateTime($a['submitted_at']))->format('M j') . "</small>
                                " . ($a['grade'] !== null ? "<strong class='grade'>{$a['grade']}/100</strong>" : "") . "
                            </div>
                            <button class='btn-view' onclick='openFileModal(\"$fileUrl\")'>View</button>
                        " : ($isOverdue ? 
                            "<span class='overdue'>Past Due</span>" : 
                            "<button class='btn-submit' onclick='openSubmitModal({$a['id']})'>Submit</button>"
                        )) . "
                    </div>
                </article>";
            }
            ?>
        </div>
    </div>
</div>

<!-- Submit Modal -->
<dialog id="submitModal" class="modal">
    <div class="modal-content">
        <header><h2>Submit Assignment</h2><button onclick="closeSubmitModal()" class="close">×</button></header>
        <form id="submitForm" enctype="multipart/form-data">
            <input type="hidden" name="assignment_id" id="assignmentId">
            <div class="form-group">
                <label>Upload File <span class="req">*</span></label>
                <input type="file" name="file" required accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.gif,.mp4,.webm,.ogg">
            </div>
            <div class="actions">
                <button type="submit" class="btn-primary">Submit</button>
                <button type="button" onclick="closeSubmitModal()" class="btn-cancel">Cancel</button>
            </div>
        </form>
    </div>
</dialog>

<!-- File Viewer Modal -->
<dialog id="fileModal" class="modal modal-large">
    <div class="modal-content">
        <header>
            <h2>View Submitted File</h2>
            <button onclick="closeFileModal()" class="close">×</button>
        </header>
        <div id="fileViewer" class="viewer">
            <p style="text-align:center; padding:3rem; color:#94a3b8;">Loading file...</p>
        </div>
    </div>
</dialog>

<style>
    :root {
        --bg: #f8fafc; --card: #ffffff; --text: #1e293b; --muted: #64748b;
        --primary: #3b82f6; --success: #10b981; --warning: #f59e0b; --danger: #ef4444;
        --border: #e2e8f0; --radius: 14px; --shadow: 0 6px 16px rgba(0,0,0,0.09);
    }
    @media (prefers-color-scheme: dark) {
        :root { --bg: #0f172a; --card: #1e293b; --text: #f1f5f9; --muted: #94a3b8; --border: #334155; }
    }

    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); line-height:1.5; }

    .container { max-width: 1240px; margin: 0 auto; padding: 1.5rem 1rem; }
    .page-header { text-align: center; margin-bottom: 2rem; }
    .page-title { font-size: 2.2rem; font-weight: 700; }
    .page-desc { font-size: 1rem; color: var(--muted); }

    .filters-bar { display: flex; gap: 1rem; margin-bottom: 1.8rem; flex-wrap: wrap; align-items: center; }
    .search-wrapper { position: relative; flex: 1; min-width: 240px; }
    .search-wrapper input {
        width: 100%; padding: 0.75rem 0.75rem 0.75rem 2.5rem; border: 1px solid var(--border);
        border-radius: 12px; background: var(--card); font-size: 0.95rem;
    }
    .search-wrapper input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
    .search-icon { position: absolute; left: 0.8rem; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--muted); }

    .status-filter { padding: 0.75rem 1.2rem; border: 1px solid var(--border); border-radius: 12px; background: var(--card); font-size: 0.95rem; }

    .assignments-grid { display: grid; gap: 1.2rem; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); }

    .assignment-card {
        background: var(--card); border-radius: var(--radius); overflow: hidden;
        box-shadow: var(--shadow); border: 1px solid var(--border);
        transition: all 0.2s;
    }
    .assignment-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.12); }

    .card-header { padding: 1.1rem 1.3rem 0.8rem; display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
    .subject { font-size: 0.85rem; font-weight: 600; color: var(--primary); text-transform: uppercase; letter-spacing: 0.8px; }
    .title { font-size: 1.25rem; font-weight: 600; margin: 0.3rem 0; line-height: 1.3; }
    .due { font-size: 0.85rem; color: var(--muted); }

    .status-badge {
        padding: 0.45rem 0.9rem; border-radius: 20px; color: white;
        font-size: 0.78rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
    }

    .card-body { padding: 0 1.3rem 1rem; }
    .desc { font-size: 0.9rem; color: var(--muted); line-height: 1.5; }

    .card-footer {
        padding: 1rem 1.3rem; background: rgba(0,0,0,0.025); border-top: 1px solid var(--border);
        display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem;
    }
    .grade { color: var(--success); font-weight: 700; font-size: 1rem; margin-left: 0.5rem; }
    .overdue { color: var(--danger); font-weight: 600; }

    .btn-submit, .btn-view {
        padding: 0.55rem 1.1rem; border: none; border-radius: 10px; font-weight: 600; font-size: 0.9rem; cursor: pointer;
    }
    .btn-submit { background: var(--primary); color: white; }
    .btn-view { background: #dbeafe; color: var(--primary); }

    .empty-state { grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--muted); }
    .empty-icon { font-size: 3.5rem; margin-bottom: 0.5rem; }

    dialog { border: 0; border-radius: var(--radius); max-width: 1000px; width: 95%; max-height: 90vh; box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
    dialog::backdrop { background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); }
    .modal-large { max-width: 1000px; }
    .modal-content { background: var(--card); border-radius: var(--radius); overflow: hidden; }
    header { padding: 1.3rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    header h2 { font-size: 1.4rem; font-weight: 600; }
    .close { background:none; border:none; font-size:2.2rem; cursor:pointer; color:var(--muted); opacity: 0.7; }
    .close:hover { opacity: 1; }

    .viewer { height: 75vh; background: #000; position: relative; overflow: auto; }
    .viewer iframe, .viewer img, .viewer video { width: 100%; height: 100%; border: none; object-fit: contain; }

    .form-group { padding: 1.5rem; }
    .form-group label { font-weight: 600; margin-bottom: 0.5rem; display: block; }
    .req { color: var(--danger); }
    input[type=file] { width:100%; padding:1rem; border:2px dashed var(--border); border-radius:12px; background:var(--bg); }

    .actions { padding: 1.2rem 1.5rem; border-top: 1px solid var(--border); display: flex; gap: 1rem; justify-content: flex-end; background: rgba(0,0,0,0.02); }
    .btn-primary { background: var(--primary); color: white; padding: 0.7rem 1.5rem; border: none; border-radius: 10px; font-weight: 600; }
    .btn-cancel { background: transparent; color: var(--muted); border: 1px solid var(--border); padding: 0.7rem 1.3rem; border-radius: 10px; }

    @media (max-width: 768px) {
        .filters-bar { flex-direction: column; align-items: stretch; }
        .assignments-grid { grid-template-columns: 1fr; }
        .viewer { height: 60vh; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('searchInput');
    const filter = document.getElementById('filterStatus');
    const cards = document.querySelectorAll('.assignment-card');

    const updateFilter = () => {
        const q = search.value.toLowerCase().trim();
        const s = filter.value;
        cards.forEach(card => {
            const matchSearch = card.dataset.title.toLowerCase().includes(q) || card.dataset.subject.toLowerCase().includes(q);
            const matchStatus = !s || card.dataset.status === s;
            card.style.display = matchSearch && matchStatus ? '' : 'none';
        });
    };

    search.addEventListener('input', updateFilter);
    filter.addEventListener('change', updateFilter);

    window.openSubmitModal = id => {
        document.getElementById('assignmentId').value = id;
        document.getElementById('submitModal').showModal();
    };

    window.closeSubmitModal = () => {
        document.getElementById('submitModal').close();
        document.getElementById('submitForm').reset();
    };

    // Fixed: Robust file viewer with Google Docs fallback (handles DOCX better, no CORS issues)
    window.openFileModal = async (relativePath) => {
        if (!relativePath) {
            alert("No file found.");
            return;
        }

        // Test if direct access works (for images/PDF/video)
        const testUrl = relativePath;
        let directLoad = false;
        try {
            const response = await fetch(testUrl, { method: 'HEAD' });
            directLoad = response.ok;
            console.log('Direct file access:', testUrl, 'Status:', response.status); // Debug
        } catch (e) {
            console.error('Direct access failed:', e); // Debug
        }

        const ext = relativePath.split('.').pop().toLowerCase();
        let content = '<p style="text-align:center; padding:3rem; color:#94a3b8;">Loading preview...</p>';

        if (directLoad && (ext === 'pdf' || ['png', 'jpg', 'jpeg', 'gif', 'webp'].includes(ext))) {
            // Direct embed for PDF/images (fastest)
            if (ext === 'pdf') {
                content = `<iframe src="${testUrl}" frameborder="0" style="width:100%; height:100%;"></iframe>`;
            } else {
                content = `<img src="${testUrl}" alt="Submitted file" style="max-width:100%; height:auto;">`;
            }
        } else if (['doc', 'docx', 'pdf'].includes(ext) || !directLoad) {
            // Google Docs Viewer for DOCX/PDF (reliable embed, handles all)
            const absoluteUrl = new URL(testUrl, window.location.origin).href;
            const viewerUrl = `https://docs.google.com/gview?url=${encodeURIComponent(absoluteUrl)}&embedded=true`;
            content = `<iframe src="${viewerUrl}" frameborder="0" style="width:100%; height:100%;"></iframe>`;
            console.log('Using Google Viewer for:', absoluteUrl); // Debug
        } else if (['mp4', 'webm', 'ogg'].includes(ext)) {
            content = `<video src="${testUrl}" controls style="width:100%; height:100%;"></video>`;
        } else {
            // Fallback: Download link
            content = `
                <div style="padding:4rem; text-align:center; color:#94a3b8;">
                    <p>Preview not supported for this type (${ext.toUpperCase()}).</p>
                    <a href="${testUrl}" download style="color:#60a5fa; font-size:1.2rem; text-decoration:underline;">Download File</a>
                </div>`;
        }

        document.getElementById('fileViewer').innerHTML = content;
        document.getElementById('fileModal').showModal();
    };

    window.closeFileModal = () => {
        document.getElementById('fileModal').close();
        document.getElementById('fileViewer').innerHTML = '<p style="text-align:center;padding:3rem;color:#94a3b8;">Loading file...</p>';
    };

    // Submit handler (unchanged, but added debug)
    document.getElementById('submitForm').onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const res = await fetch('api/submit_assignment.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        console.log('Submit response:', data); // Debug
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Upload failed');
        }
    };

    // Close on backdrop click
    document.querySelectorAll('dialog').forEach(dlg => {
        dlg.addEventListener('click', e => {
            if (e.target === dlg) dlg.close();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>