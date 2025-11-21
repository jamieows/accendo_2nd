<?php 
session_start();
require_once '../config/db.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}

date_default_timezone_set('Asia/Manila');
$now = date('Y-m-d H:i:s');
$student_id = $_SESSION['user_id'];
?>
<?php include 'includes/header.php'; ?>

<div class="exams-container">
    <header class="page-header" aria-labelledby="page-title">
        <h1 id="page-title" class="page-title">My Exams & Quizzes</h1>
        <p class="page-subtitle">View upcoming and active assessments from your subjects</p>
    </header>

    <section class="exams-grid" aria-live="polite">
        <?php
        $stmt = $pdo->prepare("
            SELECT e.id, e.title, e.google_form_link, e.start_time, e.end_time, 
                   s.name AS subject,
                   u.first_name, u.last_name
            FROM exams e
            JOIN subjects s ON e.subject_id = s.id
            JOIN users u ON e.teacher_id = u.id
            WHERE e.subject_id IN (
                SELECT subject_id FROM student_subjects WHERE student_id = ?
            )
            ORDER BY e.start_time DESC
        ");
        $stmt->execute([$student_id]);
        $exams = $stmt->fetchAll();

        if (empty($exams)) {
            echo '
            <div class="empty-state">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <p><strong>No exams available yet</strong></p>
                <small>Your teachers haven\'t uploaded any exams, or you\'re not enrolled in any subjects with exams.</small>
            </div>';
        } else {
            foreach ($exams as $e) {
                $start = new DateTime($e['start_time']);
                $end   = new DateTime($e['end_time']);
                $now_dt = new DateTime();

                $is_upcoming = $now_dt < $start;
                $is_active   = $now_dt >= $start && $now_dt <= $end;
                $is_closed   = $now_dt > $end;

                $status_text  = $is_upcoming ? 'Not Started' : ($is_active ? 'Take Now' : 'Closed');
                $status_class = $is_upcoming ? 'warning' : ($is_active ? 'success' : 'expired');
                $can_take     = $is_active && !empty($e['google_form_link']);

                $display_time = $is_upcoming 
                    ? 'Starts ' . $start->format('M j, Y \a\t g:i A')
                    : 'Ends ' . $end->format('M j, Y \a\t g:i A');

                $title   = htmlspecialchars($e['title']);
                $subject = htmlspecialchars($e['subject']);
                $teacher = htmlspecialchars(trim($e['first_name'] . ' ' . $e['last_name']));
                $link    = htmlspecialchars($e['google_form_link']);
                ?>
                <article class="exam-card <?= $status_class ?>">
                    <header class="card-header">
                        <div class="card-icon">
                            <?php if ($is_active): ?>
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            <?php else: ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="card-meta">
                            <h3 class="card-title"><?= $title ?></h3>
                            <p class="card-course"><?= $subject ?> • <?= $teacher ?></p>
                        </div>
                    </header>

                    <div class="card-body">
                        <p class="exam-schedule"><strong><?= $display_time ?></strong></p>
                        <span class="status-badge badge-<?= $status_class ?>"><?= $status_text ?></span>
                    </div>

                    <footer class="card-footer">
                        <?php if ($can_take): ?>
                            <a href="<?= $link ?>" target="_blank" rel="noopener noreferrer" class="btn-take-exam active">
                                Take Exam Now
                            </a>
                        <?php elseif ($is_upcoming): ?>
                            <button class="btn-take-exam" disabled>Starts Soon</button>
                        <?php else: ?>
                            <button class="btn-take-exam" disabled>Exam Closed</button>
                        <?php endif; ?>
                    </footer>
                </article>
                <?php
            }
        }
        ?>
    </section>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    :root {
        --bg: #f8fafc; --card: #ffffff; --text: #0f172a; --text-muted: #64748b;
        --success: #10b981; --warning: #f59e0b; --expired: #ef4444;
        --border: #e2e8f0; --shadow: 0 4px 12px rgba(0,0,0,0.08);
        --radius: 16px; --primary: #7c3aed;
    }
    @media (prefers-color-scheme: dark) {
        :root { --bg:#0f172a; --card:#1e293b; --text:#f1f5f9; --text-muted:#94a3b8; --border:#334155; }
    }

    body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); margin: 0; line-height: 1.6; }
    .exams-container { max-width: 1100px; margin: 0 auto; padding: 2rem 1rem; }

    .page-header { text-align: center; margin-bottom: 3rem; }
    .page-title { font-size: 2.2rem; font-weight: 700; margin: 0 0 .5rem; }
    .page-subtitle { color: var(--text-muted); font-size: 1.1rem; }

    .exams-grid { 
        display: grid; 
        gap: 1.75rem; 
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); 
    }

    .exam-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    .exam-card:hover { transform: translateY(-6px); box-shadow: 0 12px 30px rgba(0,0,0,0.12); }

    .card-header {
        padding: 1.5rem 1.5rem 1rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        background: linear-gradient(135deg, rgba(124,58,237,.05), transparent);
    }
    .card-icon {
        background: rgba(139,92,246,.15);
        color: #8b5cf6;
        padding: .75rem;
        border-radius: 14px;
        flex-shrink: 0;
    }
    .card-meta { flex: 1; }
    .card-title { margin: 0 0 .4rem; font-size: 1.2rem; font-weight: 600; }
    .card-course { margin: 0; font-size: .95rem; color: var(--text-muted); }

    .card-body { padding: 0 1.5rem 1rem; flex-grow: 1; }
    .exam-schedule { margin: 0 0 .75rem; font-size: 1rem; }
    .status-badge {
        display: inline-block;
        padding: .4rem .9rem;
        border-radius: 50px;
        font-size: .85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-warning { background: #fffbeb; color: #92400e; }
    .badge-expired { background: #fee2e2; color: #991b1b; }

    .card-footer {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid var(--border);
        background: rgba(249,250,251,.6);
        text-align: right;
    }
    .btn-take-exam {
        padding: .75rem 1.6rem;
        font-weight: 600;
        font-size: 1rem;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-take-exam.active {
        background: var(--primary);
        color: white;
        box-shadow: 0 4px 12px rgba(124,58,237,.3);
    }
    .btn-take-exam.active:hover {
        background: #6d28d9;
        transform: translateY(-2px);
    }
    .btn-take-exam[disabled] {
        background: #94a3b8;
        color: #cbd5e1;
        cursor: not-allowed;
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
    }
    .empty-state svg { margin-bottom: 1.5rem; }

    @media (max-width: 640px) {
        .exams-grid { grid-template-columns: 1fr; }
        .card-header { flex-direction: column; align-items: flex-start; }
        .card-icon { margin-bottom: .5rem; }
    }
</style>

<?php include 'includes/footer.php'; ?>