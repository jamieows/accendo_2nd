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

// Missing Submissions
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
    WHERE ss.student_id = ? AND sub.id IS NULL
");
$stmt->execute([$userId, $userId]);
$missing_submissions = $stmt->fetchColumn();

// Upcoming Deadlines
$stmt = $pdo->prepare("
    SELECT a.title, a.due_date, s.name AS subject, 
           (a.due_date < ?) AS is_overdue
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
    WHERE ss.student_id = ? AND sub.id IS NULL
    ORDER BY a.due_date ASC
    LIMIT 5
");
$stmt->execute([$now, $userId, $userId]);
$upcoming = $stmt->fetchAll();

// To-Do List: Overdue + Pending
$stmt = $pdo->prepare("
    SELECT a.title, a.due_date, s.name AS subject,
           'overdue' AS type
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
    WHERE ss.student_id = ? AND sub.id IS NULL AND a.due_date < ?
    UNION ALL
    SELECT a.title, a.due_date, s.name AS subject,
           'due_soon' AS type
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
    WHERE ss.student_id = ? AND sub.id IS NULL 
      AND a.due_date >= ? AND a.due_date <= DATE_ADD(?, INTERVAL 3 DAY)
    ORDER BY due_date ASC
    LIMIT 8
");
$stmt->execute([$userId, $userId, $now, $userId, $userId, $now, $now]);
$todo_list = $stmt->fetchAll();

?>
<?php include 'includes/header.php'; ?>

<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>
        <p>Your learning overview at a glance</p>
        <span class="date"><?= date('l, F j, Y') ?></span>
    </header>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon assignments">🔖</div>
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
                    <?php foreach ($upcoming as $item): ?>
                        <li class="<?= $item['is_overdue'] ? 'overdue' : '' ?>">
                            <div class="deadline-info">
                                <strong><?= htmlspecialchars($item['title']) ?></strong>
                                <span class="subject"><?= htmlspecialchars($item['subject']) ?></span>
                            </div>
                            <div class="deadline-date <?= $item['is_overdue'] ? 'text-danger' : '' ?>">
                                <?= $item['is_overdue'] ? 'Overdue' : date('M j, g:i A', strtotime($item['due_date'])) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="empty">No pending deadlines</p>
            <?php endif; ?>
            <a href="assignments.php" class="view-all">View All Assignments</a>
        </section>

        <section class="card todo-card">
            <h2>To-Do List</h2>
            <?php if ($todo_list): ?>
                <ul class="todo-list">
                    <?php foreach ($todo_list as $task): ?>
                        <li class="todo-item <?= $task['type'] ?>">
                            <div class="todo-check">✅</div>
                            <div class="todo-content">
                                <strong><?= htmlspecialchars($task['title']) ?></strong>
                                <span class="subject"><?= htmlspecialchars($task['subject']) ?></span>
                            </div>
                            <div class="todo-due">
                                <?php if ($task['type'] === 'overdue'): ?>
                                    <span class="badge danger">Overdue</span>
                                <?php else: ?>
                                    <span class="badge warning">Due Soon</span>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="empty">Nothing to do — great job!</p>
            <?php endif; ?>
            <a href="assignments.php" class="view-all">Go to Assignments</a>
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
        --accent: #2563eb;
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

    /* HEADER */
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

    /* STAT CARDS */
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

    /* CONTENT GRID */
    .dashboard-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    @media (max-width: 992px) { 
        .dashboard-content { grid-template-columns: 1fr; } 
    }

    /* CARD BASE */
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
    }

    /* DEADLINES */
    .deadline-list { 
        list-style: none; 
        margin: 0; padding: 0; 
    }
    .deadline-list li {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border);
    }
    .deadline-list li:last-child { border-bottom: none; }
    .deadline-list li.overdue { color: var(--danger); }
    .deadline-info strong { display: block; font-size: 0.95rem; }
    .deadline-info .subject { font-size: 0.8rem; color: var(--text-muted); }
    .deadline-date { font-weight: 600; font-size: 0.9rem; }

    /* TO-DO LIST */
    .todo-list { 
        list-style: none; 
        margin: 0; padding: 0; 
    }
    .todo-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border);
    }
    .todo-item:last-child { border-bottom: none; }
    .todo-check {
        flex-shrink: 0;
        font-size: 1.5rem;
    }
    .todo-content strong { display: block; font-size: 0.95rem; }
    .todo-content .subject { font-size: 0.8rem; color: var(--text-muted); }
    .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        font-weight: 600;
    }
    .badge.danger { background: #fee2e2; color: var(--danger); }
    .badge.warning { background: #fef3c7; color: #d97706; }

    /* LINKS */
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

    /* EMPTY STATE */
    .empty {
        color: var(--text-muted);
        font-style: italic;
        text-align: center;
        padding: 1.5rem;
        margin: 0;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .dashboard-container { padding: 1rem; }
        .stats-grid { grid-template-columns: 1fr; }
        .dashboard-header { text-align: center; }
    }
</style>

<?php include 'includes/footer.php'; ?>