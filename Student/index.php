<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../Auth/login.php");
    exit();
}

// Fetch student's name
$stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();
$studentName = $student ? htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) : 'Student';

$userId = $_SESSION['user_id'];
$now = date('Y-m-d H:i:s');

// Total Assignments & Exams
$stmt = $pdo->prepare("SELECT COUNT(*) FROM assignments a JOIN subjects s ON a.subject_id = s.id JOIN student_subjects ss ON ss.subject_id = s.id WHERE ss.student_id = ?");
$stmt->execute([$userId]);
$total_assignments = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM exams e JOIN subjects s ON e.subject_id = s.id JOIN student_subjects ss ON ss.subject_id = s.id WHERE ss.student_id = ?");
$stmt->execute([$userId]);
$total_exams = $stmt->fetchColumn();

// Missing Submissions: Overdue assignments + active/started exams not attempted
$missing_assignments = $pdo->prepare("
    SELECT COUNT(*) FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
    WHERE ss.student_id = ? AND sub.id IS NULL AND a.due_date < ?
");
$missing_assignments->execute([$userId, $userId, $now]);
$missing_assignments = $missing_assignments->fetchColumn();

$missing_exams = $pdo->prepare("
    SELECT COUNT(*) FROM exams e
    JOIN subjects s ON e.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    LEFT JOIN exam_attempts ea ON ea.exam_id = e.id AND ea.student_id = ?
    WHERE ss.student_id = ? AND e.start_time <= ? AND e.end_time >= ?
");
$missing_exams->execute([$userId, $userId, $now, $now]);
$missing_exams = $missing_exams->fetchColumn();

$missing_submissions = $missing_assignments + $missing_exams;

// Upcoming Deadlines: Includes overdue assignments and upcoming exams
$stmt = $pdo->prepare("
    -- Pending + Overdue Assignments
    SELECT a.title, a.due_date AS deadline, s.name AS subject, 'assignment' AS type, 'Due' AS label
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
    WHERE ss.student_id = ? AND sub.id IS NULL

    UNION ALL

    -- All exams that haven't ended yet (use start_time as the deadline)
    SELECT e.title, e.start_time AS deadline, s.name AS subject, 'exam' AS type, 'Starts' AS label
    FROM exams e
    JOIN subjects s ON e.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    WHERE ss.student_id = ? AND e.end_time >= ?

    ORDER BY deadline ASC
    LIMIT 6
");
$stmt->execute([$userId, $userId, $userId, $now]);
$upcoming = $stmt->fetchAll();

// To-Do List: Overdue, active exams, due soon
$stmt = $pdo->prepare("
    -- Overdue Assignments
    SELECT a.title, a.due_date AS deadline, s.name AS subject,
           'overdue' AS type, 'assignment' AS item_type
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
    WHERE ss.student_id = ? AND sub.id IS NULL AND a.due_date < ?

    UNION ALL

    -- Exams starting within 24 hours
    SELECT e.title, e.start_time AS deadline, s.name AS subject,
           'due_soon' AS type, 'exam' AS item_type
    FROM exams e
    JOIN subjects s ON e.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    WHERE ss.student_id = ?
      AND e.start_time >= ? AND e.start_time <= DATE_ADD(?, INTERVAL 24 HOUR)
      AND e.end_time >= ?

    UNION ALL

    -- Currently active exams
    SELECT e.title, e.start_time AS deadline, s.name AS subject,
           'active' AS type, 'exam' AS item_type
    FROM exams e
    JOIN subjects s ON e.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    WHERE ss.student_id = ? AND ? BETWEEN e.start_time AND e.end_time

    ORDER BY 
        CASE type WHEN 'overdue' THEN 1 WHEN 'active' THEN 2 ELSE 3 END,
        deadline ASC
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Student Dashboard | Accendo LMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" 
          integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

<style>
    :root {
        --primary: #3B82F6;
        --primary-hover: #2563EB;
        --primary-light: rgba(59,130,246,0.1);
        --primary-gradient: linear-gradient(135deg, #3B82F6, #60A5FA);
        --danger: #EF4444;
        --success: #10B981;
        --text: #1e293b;
        --text-light: #64748b;
        --bg: #f8fafc;
        --card-bg: #ffffff;
        --border: #e2e8f0;
        --shadow: 0 10px 30px rgba(0,0,0,0.08);
        --shadow-hover: 0 20px 50px rgba(59,130,246,0.2);
        --radius: 18px;
    }

    .dark-mode {
        --text: #f1f5f9;
        --text-light: #94a3b8;
        --bg: #0f172a;
        --card-bg: #1e293b;
        --border: #334155;
        --shadow: 0 15px 35px rgba(0,0,0,0.5);
        --shadow-hover: 0 25px 60px rgba(59,130,246,0.3);
        --primary-light: rgba(59,130,246,0.15);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        background: var(--bg);
        color: var(--text);
        font-family: 'Inter', sans-serif;
        line-height: 1.6;
        transition: background 0.4s ease;
    }

    .dashboard {
        max-width: 1280px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }

    /* Header */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 3rem;
        flex-wrap: wrap;
        gap: 2rem;
    }

    .greeting h1 {
        font-size: 2.6rem;
        font-weight: 800;
        margin: 0;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .greeting p {
        margin-top: 0.6rem;
        color: var(--text-light);
        font-size: 1.1rem;
        font-weight: 500;
    }

    .date-time {
        text-align: right;
        font-size: 1rem;
        color: var(--text-light);
    }

    .date-time strong {
        color: var(--text);
        font-weight: 700;
        font-size: 1.15rem;
        display: block;
        margin-bottom: 4px;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.8rem;
        margin-bottom: 3.5rem;
    }

    .stat-card {
        background: var(--card-bg);
        padding: 2rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        text-align: center;
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
        transition: all 0.4s ease;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 6px;
        background: var(--primary-gradient);
    }

    .stat-card:hover {
        transform: translateY(-12px);
        box-shadow: var(--shadow-hover);
    }

    .stat-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 1.2rem;
        background: var(--primary-gradient);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        box-shadow: 0 10px 30px rgba(59,130,246,0.4);
    }

    .stat-value {
        font-size: 2.8rem;
        font-weight: 800;
        margin: 0.6rem 0;
        color: var(--text);
    }

    .stat-label {
        color: var(--text-light);
        font-size: 1.05rem;
        font-weight: 600;
        letter-spacing: 0.8px;
    }

    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 2rem;
        margin-bottom: 3.5rem;
    }

    .action-card {
        background: var(--card-bg);
        padding: 2.2rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 1.8rem;
        text-decoration: none;
        color: var(--text);
        border: 1px solid var(--border);
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }

    .action-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 6px;
        height: 100%;
        background: var(--primary-gradient);
        transition: width 0.4s ease;
    }

    .action-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-hover);
        background: var(--primary-light);
    }

    .action-card:hover::before {
        width: 100%;
        opacity: 0.15;
    }

    .action-icon {
        width: 80px;
        height: 80px;
        background: var(--primary-gradient);
        color: white;
        border-radius: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.4rem;
        flex-shrink: 0;
        box-shadow: 0 12px 30px rgba(59,130,246,0.4);
        transition: transform 0.4s ease;
    }

    .action-card:hover .action-icon {
        transform: scale(1.12) rotate(8deg);
    }

    .action-content h3 {
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--text);
    }

    .action-content p {
        color: var(--text-light);
        font-size: 1rem;
        font-weight: 500;
    }

    /* Recent Activity */
    .activity-card {
        background: var(--card-bg);
        padding: 2.2rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
    }

    .activity-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.8rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border);
    }

    .activity-header h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text);
        margin: 0;
    }

    .activity-header a {
        color: var(--primary);
        font-weight: 600;
        text-decoration: none;
        font-size: 0.95rem;
    }

    .activity-list {
        list-style: none;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1.2rem;
        padding: 1.2rem 0;
        border-bottom: 1px dashed var(--border);
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        background: var(--primary-light);
        border-radius: 14px;
        margin: 0 -1.2rem;
        padding-left: 1.2rem;
        padding-right: 1.2rem;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 48px;
        height: 48px;
        background: var(--primary-gradient);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
        box-shadow: 0 8px 20px rgba(59,130,246,0.35);
    }

    .activity-time {
        margin-left: auto;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--primary);
        background: var(--primary-light);
        padding: 0.4rem 0.9rem;
        border-radius: 30px;
    }

    .empty-activity {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-light);
        font-style: italic;
        font-size: 1.1rem;
    }

    @media (max-width: 768px) {
        .dashboard-header { flex-direction: column; text-align: center; }
        .date-time { text-align: center; }
        .stats-grid, .quick-actions { grid-template-columns: 1fr; }
        .action-card { padding: 1.8rem; }
        .action-icon { width: 70px; height: 70px; font-size: 2rem; }
    }
</style>

<div class="dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>Welcome back, <?= $studentName ?>!</h1>
            <p>Here's your learning overview for today.</p>
        </div>
        <div class="date-time">
            <strong><?= date('l, F j, Y') ?></strong>
            <?= date('g:i A') ?> (Philippine Time)
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-tasks"></i></div>
            <div class="stat-value"><?= $total_assignments ?></div>
            <div class="stat-label">Total Assignments</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clipboard-check"></i></div>
            <div class="stat-value"><?= $total_exams ?></div>
            <div class="stat-label">Available Exams</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-value"><?= $missing_submissions ?></div>
            <div class="stat-label">Missing Submissions</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="courses.php" class="action-card">
            <div class="action-icon"><i class="fas fa-book"></i></div>
            <div class="action-content">
                <h3>View Materials</h3>
                <p>Access lecture notes and resources</p>
            </div>
        </a>
        <a href="assignments.php" class="action-card">
            <div class="action-icon"><i class="fas fa-edit"></i></div>
            <div class="action-content">
                <h3>Submit Assignment</h3>
                <p>Upload your work before deadlines</p>
            </div>
        </a>
        <a href="exams.php" class="action-card">
            <div class="action-icon"><i class="fas fa-file-alt"></i></div>
            <div class="action-content">
                <h3>Take Exam</h3>
                <p>Start quizzes or tests when ready</p>
            </div>
        </a>
    </div>

    <!-- Recent Activity -->
    <div class="activity-card">
        <div class="activity-header">
            <h3>Recent Activity</h3>
            <a href="#">View all</a>
        </div>
        <ul class="activity-list">
            <?php
            $activities = [];
            $tables = [
                ['table' => 'assignment_submissions', 'icon' => '<i class="fas fa-tasks"></i>',       'action' => 'submitted assignment'],
                ['table' => 'exam_attempts',          'icon' => '<i class="fas fa-clipboard-check"></i>', 'action' => 'attempted exam']
            ];

            foreach ($tables as $t) {
                $stmt = $pdo->prepare("
                    SELECT a.title, {$t['table']}.submitted_at AS time
                    FROM {$t['table']}
                    JOIN " . ($t['table'] === 'assignment_submissions' ? 'assignments' : 'exams') . " a ON {$t['table']}." . ($t['table'] === 'assignment_submissions' ? 'assignment_id' : 'exam_id') . " = a.id
                    WHERE {$t['table']}.student_id = ?
                    ORDER BY {$t['table']}.submitted_at DESC
                    LIMIT 5
                ");
                $stmt->execute([$_SESSION['user_id']]);
                while ($row = $stmt->fetch()) {
                    $activities[] = [
                        'icon' => $t['icon'],
                        'text' => "You {$t['action']}: " . htmlspecialchars($row['title']),
                        'time' => $row['time']
                    ];
                }
            }

            usort($activities, fn($a, $b) => strtotime($b['time']) - strtotime($a['time']));
            $activities = array_slice($activities, 0, 6);

            if (empty($activities)) {
                echo '<li class="empty-activity">No recent activity yet. Get started!</li>';
            } else {
                foreach ($activities as $act) {
                    $timeAgo = time_elapsed_string($act['time']);
                    echo "<li class='activity-item'>
                            <div class='activity-icon'>{$act['icon']}</div>
                            <div>{$act['text']}</div>
                            <div class='activity-time'>$timeAgo</div>
                          </li>";
                }
            }

            function time_elapsed_string($datetime) {
                $now = new DateTime;
                $ago = new DateTime($datetime);
                $diff = $now->diff($ago);

                $diff->w = floor($diff->d / 7);
                $diff->d -= $diff->w * 7;

                $string = ['y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'i' => 'minute'];
                foreach ($string as $k => &$v) {
                    if ($diff->$k) {
                        $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                    } else {
                        unset($string[$k]);
                    }
                }

                if (!$string) return 'just now';
                return implode(', ', $string) . ' ago';
            }
            ?>
        </ul>
    </div>
</div>

<!-- GLOBAL THEME SYNC -->
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

    // Real-time sync when user changes theme in Settings
    window.addEventListener('storage', (e) => {
        if (e.key === 'theme') {
            applyTheme();
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>