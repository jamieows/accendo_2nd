<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../Auth/login.php");
    exit();
}

// Fetch teacher's name
$stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$teacher = $stmt->fetch();
$teacherName = $teacher ? htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) : 'Teacher';

/* --------------------------------------------------------------
   AUTO-CREATE `uploaded_at` IN ALL RELEVANT TABLES
   -------------------------------------------------------------- */
$tables = ['materials', 'assignments', 'exams'];
foreach ($tables as $table) {
    try {
        $pdo->query("SELECT uploaded_at FROM $table LIMIT 1");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Unknown column 'uploaded_at'") !== false) {
            $pdo->exec("ALTER TABLE $table ADD COLUMN uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            $pdo->exec("UPDATE $table SET uploaded_at = NOW() WHERE uploaded_at IS NULL");
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Teacher Dashboard | Accendo LMS</title>
    <!-- Font Awesome 6 (Free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" 
          integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

<style>
    :root {
        --primary: #7B61FF;
        --primary-hover: #6a51e6;
        --danger: #EF4444;
        --danger-hover: #dc2626;
        --success: #10B981;
        --warning: #F59E0B;
        --text: #1f2937;
        --text-light: #6b7280;
        --bg: #f9fafb;
        --card-bg: #ffffff;
        --border: #e5e7eb;
        --shadow: 0 10px 25px rgba(0,0,0,0.06);
        --radius: 1rem;
    }
    .dark-mode {
        --text: #f3f4f6;
        --text-light: #9ca3af;
        --bg: #0f172a;
        --card-bg: #1e293b;
        --border: #334155;
        --shadow: 0 10px 25px rgba(0,0,0,0.3);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { 
        background: var(--bg); 
        color: var(--text); 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        transition: all 0.3s ease;
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
        margin-bottom: 2.5rem;
        flex-wrap: wrap;
        gap: 1.5rem;
    }
    .greeting h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        background: linear-gradient(135deg, var(--primary), #5b4ed4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .greeting p {
        margin: 0.4rem 0 0;
        color: var(--text-light);
        font-size: 1rem;
    }
    .date-time {
        text-align: right;
        font-size: 0.95rem;
        color: var(--text-light);
    }
    .date-time strong { color: var(--text); font-weight: 600; }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    .stat-card {
        background: var(--card-bg);
        padding: 1.75rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid var(--border);
    }
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(123,97,255,0.15);
    }
    .stat-icon {
        width: 60px; height: 60px;
        margin: 0 auto 1rem;
        background: linear-gradient(135deg, var(--primary), #5b4ed4);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        box-shadow: 0 6px 15px rgba(123,97,255,0.3);
    }
    .stat-value {
        font-size: 2.4rem;
        font-weight: 800;
        margin: 0.5rem 0;
        color: var(--text);
    }
    .stat-label {
        color: var(--text-light);
        font-size: 0.95rem;
        font-weight: 500;
    }

    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    .action-card {
        background: var(--card-bg);
        padding: 2rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 1.25rem;
        text-decoration: none;
        color: var(--text);
        transition: all 0.3s ease;
        border: 1px solid var(--border);
    }
    .action-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 15px 30px rgba(123,97,255,0.2);
        background: linear-gradient(135deg, #7B61FF08, transparent);
    }
    .action-icon {
        width: 70px; height: 70px;
        background: linear-gradient(135deg, var(--primary), #5b4ed4);
        color: white;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        flex-shrink: 0;
        box-shadow: 0 8px 20px rgba(123,97,255,0.3);
    }
    .action-content h3 {
        margin: 0 0 0.4rem;
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text);
    }
    .action-content p {
        margin: 0;
        font-size: 0.95rem;
        color: var(--text-light);
    }

    /* Recent Activity */
    .activity-card {
        background: var(--card-bg);
        padding: 2rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
    }
    .activity-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border);
    }
    .activity-header h3 {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--text);
    }
    .activity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px dashed var(--border);
        font-size: 0.98rem;
    }
    .activity-item:last-child { border-bottom: none; }
    .activity-icon {
        width: 42px; height: 42px;
        background: linear-gradient(135deg, var(--primary), #5b4ed4);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(123,97,255,0.25);
    }
    .activity-time {
        margin-left: auto;
        font-size: 0.85rem;
        color: var(--text-light);
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .dashboard-header { flex-direction: column; text-align: center; }
        .date-time { text-align: center; }
        .stats-grid, .quick-actions { grid-template-columns: 1fr; }
    }
</style>

<div class="dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>Welcome back, <?= $teacherName ?>!</h1>
            <p>Here's what's happening with your classes today.</p>
        </div>
        <div class="date-time">
            <strong><?= date('l, F j, Y') ?></strong><br>
            <?= date('g:i A') ?> (Philippine Time)
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <?php
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM teacher_subjects WHERE teacher_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $totalSubjects = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM materials WHERE teacher_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $totalMaterials = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM assignments WHERE teacher_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $totalAssignments = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM exams WHERE teacher_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $totalExams = $stmt->fetchColumn();
        ?>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-book"></i></div>
            <div class="stat-value"><?= $totalSubjects ?></div>
            <div class="stat-label">Active Subjects</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            <div class="stat-value"><?= $totalMaterials ?></div>
            <div class="stat-label">Materials Uploaded</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-tasks"></i></div>
            <div class="stat-value"><?= $totalAssignments ?></div>
            <div class="stat-label">Assignments Created</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clipboard-check"></i></div>
            <div class="stat-value"><?= $totalExams ?></div>
            <div class="stat-label">Exams Uploaded</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="my_courses.php" class="action-card">
            <div class="action-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div class="action-content">
                <h3>Upload Material</h3>
                <p>Share lecture notes, videos, or PDFs</p>
            </div>
        </a>
        <a href="assignment.php" class="action-card">
            <div class="action-icon"><i class="fas fa-edit"></i></div>
            <div class="action-content">
                <h3>Create Assignment</h3>
                <p>Set tasks with due dates</p>
            </div>
        </a>
        <a href="exams.php" class="action-card">
            <div class="action-icon"><i class="fas fa-file-alt"></i></div>
            <div class="action-content">
                <h3>Upload Exam</h3>
                <p>Add quizzes or exams</p>
            </div>
        </a>
    </div>

    <!-- Recent Activity -->
    <div class="activity-card">
        <div class="activity-header">
            <h3>Recent Activity</h3>
            <a href="#" style="font-size:0.95rem;color:var(--primary);font-weight:600;">View all</a>
        </div>
        <ul class="activity-list">
            <?php
            $activities = [];
            $tables = [
                ['table' => 'materials',   'icon' => '<i class="fas fa-file-alt"></i>',       'action' => 'uploaded material'],
                ['table' => 'assignments', 'icon' => '<i class="fas fa-tasks"></i>',         'action' => 'created assignment'],
                ['table' => 'exams',       'icon' => '<i class="fas fa-clipboard-check"></i>', 'action' => 'uploaded exam']
            ];

            foreach ($tables as $t) {
                $stmt = $pdo->prepare("
                    SELECT title, uploaded_at 
                    FROM {$t['table']} 
                    WHERE teacher_id = ? AND uploaded_at IS NOT NULL
                    ORDER BY uploaded_at DESC 
                    LIMIT 3
                ");
                $stmt->execute([$_SESSION['user_id']]);
                while ($row = $stmt->fetch()) {
                    $activities[] = [
                        'icon' => $t['icon'],
                        'text' => "You {$t['action']}: " . htmlspecialchars($row['title']),
                        'time' => $row['uploaded_at']
                    ];
                }
            }

            usort($activities, fn($a, $b) => strtotime($b['time']) - strtotime($a['time']));
            $activities = array_slice($activities, 0, 5);

            foreach ($activities as $act) {
                $timeAgo = time_elapsed_string($act['time']);
                echo "<li class='activity-item'>
                        <div class='activity-icon'>{$act['icon']}</div>
                        <div>{$act['text']}</div>
                        <div class='activity-time'>$timeAgo</div>
                      </li>";
            }

            if (empty($activities)) {
                echo "<li class='activity-item' style='justify-content:center;color:var(--text-light);font-style:italic;'>
                        No recent activity yet
                      </li>";
            }

            function time_elapsed_string($datetime) {
                $now = new DateTime;
                $ago = new DateTime($datetime);
                $diff = $now->diff($ago);
                if ($diff->y) return $diff->y . 'y ago';
                if ($diff->m) return $diff->m . 'mo ago';
                if ($diff->d) return $diff->d . 'd ago';
                if ($diff->h) return $diff->h . 'h ago';
                if ($diff->i) return $diff->i . 'm ago';
                return 'just now';
            }
            ?>
        </ul>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="../assets/js/global.js"></script>
</body>
</html>