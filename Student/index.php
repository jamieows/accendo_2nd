<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php");
    exit(); 
}

$userId = $_SESSION['user_id'];
$now = date('Y-m-d H:i:s');

// Total Assignments
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    WHERE ss.student_id = ?
");
$stmt->execute([$userId]);
$total_assignments = $stmt->fetchColumn();

// Total Exams
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM exams e
    JOIN subjects s ON e.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    WHERE ss.student_id = ?
");
$stmt->execute([$userId]);
$total_exams = $stmt->fetchColumn();

// MISSING SUBMISSIONS: Unsubmitted assignments + Untaken active/upcoming exams
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
    WHERE ss.student_id = ? AND sub.id IS NULL
");
$stmt->execute([$userId, $userId]);
$missing_assignments = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM exams e
    JOIN subjects s ON e.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    WHERE ss.student_id = ? 
      AND e.end_time >= ?  -- Exam not closed yet
");
$stmt->execute([$userId, $now]);
$active_or_upcoming_exams = $stmt->fetchColumn();

$missing_submissions = $missing_assignments + $active_or_upcoming_exams;

// UPCOMING DEADLINES (Next 6): Assignments (due soon) + Exams (starting soon)
$stmt = $pdo->prepare("
    -- Pending Assignments
    SELECT a.title, a.due_date AS deadline, s.name AS subject, 
           'assignment' AS type, 'Due' AS label
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
    WHERE ss.student_id = ? AND sub.id IS NULL
      AND a.due_date >= ?
    UNION ALL
    -- Upcoming or Active Exams
    SELECT e.title, e.start_time AS deadline, s.name AS subject,
           'exam' AS type, 'Starts' AS label
    FROM exams e
    JOIN subjects s ON e.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    WHERE ss.student_id = ?
      AND e.end_time >= ?
    ORDER BY deadline ASC
    LIMIT 6
");
$stmt->execute([$userId, $userId, $now, $userId, $now]);
$upcoming = $stmt->fetchAll();

// TO-DO LIST: Overdue Assignments + Exams Starting Soon + Active Exams
$stmt = $pdo->prepare("
    -- Overdue Assignments
    SELECT a.title, a.due_date AS deadline, s.name AS subject,
           'overdue' AS type, 'assignment' AS item_type
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
    WHERE ss.student_id = ? AND sub.id IS NULL AND a.due_date < ?
    
    UNION ALL
    -- Exams Starting Within 24 Hours
    SELECT e.title, e.start_time AS deadline, s.name AS subject,
           'due_soon' AS type, 'exam' AS item_type
    FROM exams e
    JOIN subjects s ON e.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    WHERE ss.student_id = ?
      AND e.start_time >= ? 
      AND e.start_time <= DATE_ADD(?, INTERVAL 24 HOUR)
      AND e.end_time >= ?

    UNION ALL
    -- Currently Active Exams
    SELECT e.title, e.end_time AS deadline, s.name AS subject,
           'active' AS type, 'exam' AS item_type
    FROM exams e
    JOIN subjects s ON e.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    WHERE ss.student_id = ?
      AND ? BETWEEN e.start_time AND e.end_time

    ORDER BY CASE WHEN type = 'overdue' THEN 1 ELSE 2 END DESC, deadline ASC
    LIMIT 8
");
$stmt->execute([
    $userId, $userId, $now,
    $userId, $now, $now, $now,
    $userId, $now
]);
$todo_list = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Welcome back, <?= htmlspecialchars($_SESSION['name'] ?? 'Student') ?>!</h1>
        <p>Your learning overview at a glance</p>
        <span class="date"><?= date('l, F j, Y') ?></span>
    </header>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon assignments">📝</div>
            <div class="stat-value"><?= $total_assignments ?></div>
            <div class="stat-label">Total Assignments</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon exams">📚</div>
            <div class="stat-value"><?= $total_exams ?></div>
            <div class="stat-label">Available Exams</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon missing">⚠️</div>
            <div class="stat-value"><?= $missing_submissions ?></div>
            <div class="stat-label">Missing Submissions</div>
        </div>
    </div>

    <div class="dashboard-content">
        <section class="card deadlines-card">
            <h2>Upcoming Deadlines</h2>
            <?php if ($upcoming): ?>
                <ul class="deadline-list">
                    <?php foreach ($upcoming as $item): 
                        $deadlineDt = new DateTime($item['deadline']);
                        $nowDt = new DateTime();
                        $isOverdue = $deadlineDt < $nowDt;
                        $icon = $item['type'] === 'exam' ? '📚 Exam' : '📝 Assignment';
                    ?>
                        <li class="<?= $isOverdue ? 'overdue' : '' ?>">
                            <div class="deadline-info">
                                <strong><?= htmlspecialchars($item['title']) ?></strong>
                                <span class="subject"><?= htmlspecialchars($item['subject']) ?> • <?= $icon ?></span>
                            </div>
                            <div class="deadline-date <?= $isOverdue ? 'text-danger' : '' ?>">
                                <?= $item['label'] ?> <?= $deadlineDt->format('M j, g:i A') ?>
                                <?php if ($isOverdue): ?> (Overdue)<?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="empty">No upcoming deadlines</p>
            <?php endif; ?>
            <a href="assignments.php" class="view-all">View All Assignments</a>
            <a href="exams.php" class="view-all" style="margin-top:0.5rem;display:block;">View All Exams</a>
        </section>

        <section class="card todo-card">
            <h2>To-Do List</h2>
            <?php if ($todo_list): ?>
                <ul class="todo-list">
                    <?php foreach ($todo_list as $task): 
                        $badgeClass = $task['type'] === 'overdue' ? 'danger' : ($task['type'] === 'active' ? 'success' : 'warning');
                        $label = $task['type'] === 'overdue' ? 'Overdue' : ($task['type'] === 'active' ? 'Active Now' : 'Due Soon');
                        $icon = $task['item_type'] === 'exam' ? '📚 Exam' : '📝 Assignment';
                        $taskDt = new DateTime($task['deadline']);
                    ?>
                        <li class="todo-item <?= $task['type'] ?>">
                            <div class="todo-check"><?= $task['type'] === 'active' ? '⏰' : '⚠️' ?></div>
                            <div class="todo-content">
                                <strong><?= htmlspecialchars($task['title']) ?></strong>
                                <span class="subject"><?= htmlspecialchars($task['subject']) ?> • <?= $icon ?></span>
                            </div>
                            <div class="todo-due">
                                <span class="badge <?= $badgeClass ?>"><?= $label ?> • <?= $taskDt->format('M j, g:i A') ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="empty">Nothing to do — great job!</p>
            <?php endif; ?>
            <a href="assignments.php" class="view-all">Go to Assignments</a>
            <a href="exams.php" class="view-all" style="margin-top:0.5rem;display:block;">Go to Exams</a>
        </section>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    :root {
        --bg: #f8fafc;
        --card: #ffffff;
        --text: #0f172a;
        --text-muted: #64748b;
        --accent: #7c3aed;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --border: #e2e8f0;
        --radius: 14px;
        --shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        --transition: all 0.2s ease;
    }

    body {
        font-family: 'Inter', system-ui, sans-serif;
        background: var(--bg);
        color: var(--text);
        margin: 0;
        line-height: 1.6;
    }

    .dashboard-container { 
        max-width: 1200px; 
        margin: 0 auto; 
        padding: 2rem; 
    }

    .dashboard-header {
        margin-bottom: 2rem;
        text-align: center;
    }
    .dashboard-header h1 { 
        font-size: 2rem; 
        margin: 0 0 0.25rem; 
        font-weight: 700; 
    }
    .dashboard-header p { 
        color: var(--text-muted); 
        margin: 0; 
    }
    .date { 
        color: var(--text-muted); 
        font-size: 0.95rem; 
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .stat-card {
        background: var(--card);
        padding: 1.5rem;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        text-align: center;
        transition: var(--transition);
    }
    .stat-card:hover { 
        transform: translateY(-4px); 
    }
    .stat-card.warning .stat-value { color: var(--warning); }
    .stat-icon {
        font-size: 2rem; 
        margin-bottom: 1rem;
    }
    .stat-value { 
        font-size: 2rem; 
        font-weight: 700; 
        margin: 0.5rem 0; 
    }
    .stat-label { 
        color: var(--text-muted); 
        font-size: 0.95rem; 
    }

    .dashboard-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    @media (max-width: 992px) { 
        .dashboard-content { grid-template-columns: 1fr; } 
    }

    .card {
        background: var(--card);
        border-radius: var(--radius);
        padding: 1.5rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
    }
    .card h2 {
        font-size: 1.2rem;
        font-weight: 600;
        margin: 0 0 1rem;
        color: var(--accent);
    }

    .deadline-list, .todo-list { 
        list-style: none; 
        margin: 0; padding: 0; 
    }
    .deadline-list li, .todo-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 0.85rem 0;
        border-bottom: 1px solid var(--border);
        gap: 1rem;
    }
    .deadline-list li:last-child, .todo-item:last-child { border-bottom: none; }
    .deadline-list li.overdue { color: var(--danger); font-weight: 600; }
    .deadline-info strong, .todo-content strong { display: block; font-size: 0.98rem; }
    .deadline-info .subject, .todo-content .subject { 
        font-size: 0.82rem; 
        color: var(--text-muted); 
        margin-top: 0.2rem;
    }
    .deadline-date, .todo-due { font-weight: 600; font-size: 0.9rem; text-align: right; flex-shrink: 0; }

    .todo-check { font-size: 1.6rem; margin-right: 0.75rem; flex-shrink: 0; }
    .todo-content { flex: 1; }
    .badge {
        font-size: 0.75rem;
        padding: 0.3rem 0.6rem;
        border-radius: 1rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .badge.danger { background: #fee2e2; color: var(--danger); }
    .badge.warning { background: #fffbeb; color: #92400e; }
    .badge.success { background: #d1fae5; color: #065f46; }

    .view-all {
        display: block;
        margin-top: 1rem;
        text-align: center;
        color: var(--accent);
        font-weight: 500;
        text-decoration: none;
        font-size: 0.9rem;
    }
    .view-all:hover { text-decoration: underline; }

    .empty {
        color: var(--text-muted);
        font-style: italic;
        text-align: center;
        padding: 2rem;
        margin: 0;
    }

    @media (max-width: 768px) {
        .dashboard-container { padding: 1rem; }
        .stats-grid { grid-template-columns: 1fr; }
        .deadline-list li, .todo-item { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
        .deadline-date, .todo-due { text-align: left; }
    }
</style>

<?php include 'includes/footer.php'; ?>