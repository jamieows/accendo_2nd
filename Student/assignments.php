    <?php require_once '../config/db.php';
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
        header("Location: ../Auth/login.php"); exit();
    } ?>
    <?php include 'includes/header.php'; ?>

    <div class="assignments-container">
        <!-- Page Header -->
        <header class="page-header" aria-labelledby="page-title">
            <h1 id="page-title" class="page-title">Assignments</h1>
            <p class="page-subtitle">View and submit your coursework — accessible and voice-enabled.</p>
        </header>

        <!-- Search & Filter Bar -->
        <div class="controls-bar">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by title or subject..." aria-label="Search assignments">
                <button class="btn-search" aria-label="Search">Search</button>
            </div>
            <select id="filterStatus" class="filter-select" aria-label="Filter by status">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="submitted">Submitted</option>
                <option value="graded">Graded</option>
            </select>
        </div>

        <!-- Assignments List -->
        <section class="assignments-list" aria-live="polite">
            <?php
            $stmt = $pdo->prepare("
                SELECT a.id, a.title, a.description, a.due_date, a.file_required, s.name AS subject,
                    sub.id AS submission_id, sub.file_path AS submitted_file, sub.grade, sub.feedback, sub.submitted_at
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
                echo '<div class="empty-state"><p>No assignments found. Check back later or contact your teacher.</p></div>';
            } else {
                foreach ($assignments as $a) {
                    $isPastDue = new DateTime($a['due_date']) < new DateTime();
                    $status = $a['submission_id']
                        ? ($a['grade'] !== null ? 'graded' : 'submitted')
                        : ($isPastDue ? 'overdue' : 'pending');
                    $statusLabel = ucfirst($status);
                    $statusColor = match($status) {
                        'pending' => '#f59e0b',
                        'submitted' => '#3b82f6',
                        'graded' => '#10b981',
                        'overdue' => '#ef4444',
                    };
                    $dueDate = (new DateTime($a['due_date']))->format('M j, Y \a\t g:i A');
                    $subject = htmlspecialchars($a['subject']);
                    $title = htmlspecialchars($a['title']);
                    $desc = htmlspecialchars($a['description'] ?? 'No description provided.');

                    echo "<article class='assignment-card' data-status='$status' data-subject='$subject' data-title='$title'>
                        <header class='card-header'>
                            <div class='assignment-info'>
                                <h3 class='assignment-title'>$title</h3>
                                <p class='assignment-meta'>
                                    <span class='subject'>$subject</span> • <span class='due-date'>Due: $dueDate</span>
                                </p>
                            </div>
                            <span class='status-badge' style='background:$statusColor;' aria-label='Status: $statusLabel'>$statusLabel</span>
                        </header>
                        <div class='assignment-body'>
                            <p class='assignment-desc'>$desc</p>
                            <p class='file-requirement'>File required: " . ($a['file_required'] ? 'Yes' : 'No') . "</p>
                        </div>
                        <footer class='card-footer'>"
                            . ($a['submission_id']
                                ? ($a['grade'] !== null
                                    ? "<div class='grade-display'>Grade: <strong>{$a['grade']}/100</strong></div>"
                                    : "<div class='submitted-info'>Submitted on " . (new DateTime($a['submitted_at']))->format('M j, Y \a\t g:i A') . "</div>")
                                : ($isPastDue
                                    ? "<span class='overdue-notice'>Past due</span>"
                                    : "<button class='btn-submit' onclick='openSubmitModal({$a['id']})'>Submit</button>"))
                        . "</footer>
                    </article>";
                }
            }
            ?>
        </section>
    </div>

    <!-- Submit Modal -->
    <dialog id="submitModal" class="modal">
        <div class="modal-content">
            <header class="modal-header">
                <h3>Submit Assignment</h3>
                <button class="btn-close" onclick="closeSubmitModal()" aria-label="Close">Close</button>
            </header>
            <form id="submitForm" enctype="multipart/form-data" method="POST" action="api/submit_assignment.php">
                <input type="hidden" name="assignment_id" id="assignmentId">
                <div class="form-group">
                    <label for="fileUpload">Upload File (PDF, DOC, Image)</label>
                    <input type="file" id="fileUpload" name="file" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Submit</button>
                    <button type="button" class="btn-secondary" onclick="closeSubmitModal()">Cancel</button>
                </div>
            </form>
        </div>
    </dialog>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        :root { --bg: #f8fafc; --card: #ffffff; --text: #0f172a; --text-muted: #64748b; --accent: #2563eb; --accent-hover: #1d4ed8; --border: #e2e8f0; --shadow-sm: 0 1px 3px rgba(0,0,0,0.1); --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1); --radius: 12px; --transition: all 0.2s ease; --focus: 0 0 0 3px rgba(37, 99, 235, 0.3); }
        @media (prefers-color-scheme: dark) { :root { --bg: #0f172a; --card: #1e293b; --text: #f1f5f9; --text-muted: #94a3b8; --border: #334155; } }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); line-height: 1.6; margin: 0; }
        .assignments-container { max-width: 1000px; margin: 0 auto; padding: 28px 20px; }
        .page-header { margin-bottom: 32px; text-align: center; }
        .page-title { font-size: 1.875rem; font-weight: 700; margin: 0 0 8px 0; color: var(--text); }
        .page-subtitle { margin: 0; color: var(--text-muted); font-size: 1rem; }
        .controls-bar { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
        .search-box { flex: 1; min-width: 250px; display: flex; gap: 8px; }
        .search-box input { flex: 1; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; background: var(--card); color: var(--text); font-size: 1rem; }
        .btn-search, .filter-select { padding: 10px 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--card); color: var(--text); font-size: 0.925rem; cursor: pointer; }
        .btn-search { background: var(--accent); color: white; border: none; }
        .btn-search:hover { background: var(--accent-hover); }
        .assignment-card { background: var(--card); border-radius: var(--radius); padding: 20px; margin-bottom: 16px; box-shadow: var(--shadow-md); border: 1px solid var(--border); transition: var(--transition); }
        .assignment-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
        .card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; flex-wrap: wrap; gap: 12px; }
        .assignment-title { font-size: 1.2rem; font-weight: 600; margin: 0; color: var(--text); }
        .assignment-meta { margin: 4px 0 0; font-size: 0.875rem; color: var(--text-muted); }
        .status-badge { color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .assignment-body { margin: 16px 0; }
        .assignment-desc { margin: 0 0 8px 0; color: var(--text); }
        .file-requirement { font-size: 0.875rem; color: var(--text-muted); margin: 0; }
        .card-footer { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-top: 16px; }
        .btn-submit { background: var(--accent); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; }
        .btn-submit:hover { background: var(--accent-hover); }
        .grade-display, .submitted-info { font-size: 0.925rem; color: var(--text-muted); }
        .grade-display strong { color: #10b981; font-weight: 600; }
        .overdue-notice { color: #ef4444; font-weight: 600; font-size: 0.925rem; }
        .empty-state { text-align: center; padding: 48px 20px; color: var(--text-muted); background: var(--card); border-radius: var(--radius); border: 1px dashed var(--border); }
        .modal { border: none; border-radius: var(--radius); padding: 0; max-width: 500px; width: 90%; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .modal::backdrop { background: rgba(0,0,0,0.5); }
        .modal-content { padding: 24px; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h3 { margin: 0; font-size: 1.25rem; font-weight: 600; }
        .btn-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input[type="file"] { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--card); }
        .form-actions { display: flex; gap: 12px; justify-content: flex-end; }
        .btn-primary { background: var(--accent); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; }
        .btn-primary:hover { background: var(--accent-hover); }
        .btn-secondary { background: var(--text-muted); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        @media (max-width: 768px) { .controls-bar { flex-direction: column; } .card-header, .card-footer { flex-direction: column; align-items: stretch; } .status-badge { align-self: flex-start; } }
        @media (prefers-contrast: high) { .assignment-card { border-width: 2px; } }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            const filterStatus = document.getElementById('filterStatus');
            const cards = document.querySelectorAll('.assignment-card');
            const modal = document.getElementById('submitModal');
            const form = document.getElementById('submitForm');

            function filterAssignments() {
                const query = searchInput.value.toLowerCase();
                const status = filterStatus.value;
                cards.forEach(card => {
                    const title = card.dataset.title.toLowerCase();
                    const subject = card.dataset.subject.toLowerCase();
                    const cardStatus = card.dataset.status;
                    const matchesSearch = title.includes(query) || subject.includes(query);
                    const matchesStatus = !status || cardStatus === status;
                    card.style.display = matchesSearch && matchesStatus ? 'block' : 'none';
                });
            }
            searchInput.addEventListener('input', filterAssignments);
            filterStatus.addEventListener('change', filterAssignments);

            window.openSubmitModal = function(id) {
                document.getElementById('assignmentId').value = id;
                modal.showModal();
            };
            window.closeSubmitModal = function() {
                modal.close();
                form.reset();
            };

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch(this.action, { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                        else alert(data.message || 'Submission failed.');
                    })
                    .catch(() => alert('Error submitting assignment.'));
            });
        });
    </script>

    <?php include 'includes/footer.php'; ?>