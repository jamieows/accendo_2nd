<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
$now = date('Y-m-d H:i:s');
?>
<?php include 'includes/header.php'; ?>

<div class="exams-container">
    <!-- Page Header -->
    <header class="page-header" aria-labelledby="page-title">
        <h1 id="page-title" class="page-title">Available Exams</h1>
        <p class="page-subtitle">Take your exams during the scheduled time window.</p>
    </header>

    <!-- Exams Grid -->
    <section class="exams-grid" aria-live="polite" id="examsList">
        <?php
        $stmt = $pdo->prepare("
            SELECT e.id, e.title, e.file_path, e.start_time, e.end_time, s.name AS subject
            FROM exams e
            JOIN subjects s ON e.subject_id = s.id
            JOIN student_subjects ss ON ss.subject_id = s.id
            WHERE ss.student_id = ? 
              AND ? BETWEEN e.start_time AND e.end_time
            ORDER BY e.end_time ASC
        ");
        $stmt->execute([$_SESSION['user_id'], $now]);
        $exams = $stmt->fetchAll();

        if (empty($exams)) {
            echo '
            <div class="empty-state" role="status" aria-live="polite">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>No exams are currently available.</p>
                <small>Check back during your exam schedule.</small>
            </div>';
        } else {
            foreach ($exams as $e) {
                $end = new DateTime($e['end_time']);
                $nowObj = new DateTime();
                $diff = $nowObj->diff($end);

                $timeLeft = $diff->days > 0
                    ? $diff->days . 'd ' . $diff->format('%hh %im')
                    : $diff->format('%h' . ($diff->h == 1 ? ' hour' : ' hours') . ' %i min');

                $title   = htmlspecialchars($e['title']);
                $subject = htmlspecialchars($e['subject']);
                $endFmt  = $end->format('M j, Y \a\t g:i A');
                $fileUrl = htmlspecialchars('../' . $e['file_path']);

                echo "
                <article class='exam-card' role='article'>
                    <header class='card-header'>
                        <div class='card-icon'>
                            <svg width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'>
                                <path d='M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.668 5.477 15.254 5 17 5s3.332.477 4.5 1.253v13C20.332 18.477 18.746 18 17 18s-3.332.477-4.5 1.253'/>
                            </svg>
                        </div>
                        <div class='card-meta'>
                            <h3 class='card-title'>$title</h3>
                            <p class='card-course'>$subject</p>
                        </div>
                    </header>

                    <div class='card-body'>
                        <p class='exam-schedule'><strong>Available until:</strong> $endFmt</p>
                        <p class='time-remaining' aria-label='Time remaining for exam'>
                            <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'>
                                <circle cx='12' cy='12' r='10'/>
                                <polyline points='12 6 12 12 16 14'/>
                            </svg>
                            <strong>$timeLeft</strong> left
                        </p>
                    </div>

                    <footer class='card-footer'>
                        <button 
                            class='btn-take-exam' 
                            data-exam-id='{$e['id']}' 
                            data-file-url='$fileUrl'
                            aria-label='Take exam: $title'>
                            Take Exam
                        </button>
                    </footer>
                </article>";
            }
        }
        ?>
    </section>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    :root {
        --bg: #f8fafc; --card: #ffffff; --text: #0f172a; --text-muted: #64748b;
        --accent: #059669; --accent-hover: #047857; --border: #e2e8f0;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.1); --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
        --radius: 12px; --transition: all 0.2s ease; --focus-ring: 0 0 0 3px rgba(5,150,105,0.3);
    }
    @media (prefers-color-scheme: dark) {
        :root { --bg:#0f172a; --card:#1e293b; --text:#f1f5f9; --text-muted:#94a3b8; --border:#334155; --accent:#10b981; --accent-hover:#059669; }
    }

    *,*::before,*::after{box-sizing:border-box;}
    body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);margin:0;line-height:1.6;}
    .exams-container{max-width:1000px;margin:0 auto;padding:2rem 1rem;}

    .page-header{text-align:center;margin-bottom:2.5rem;}
    .page-title{font-size:2rem;font-weight:700;margin:0 0 .5rem;}
    .page-subtitle{color:var(--text-muted);font-size:1rem;margin:0;}

    .exams-grid{display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));}

    .exam-card{
        background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
        overflow:hidden;box-shadow:var(--shadow-sm);transition:var(--transition);display:flex;flex-direction:column;
    }
    .exam-card:hover{box-shadow:var(--shadow-md);transform:translateY(-2px);}

    .card-header{padding:1.25rem 1.25rem .75rem;display:flex;align-items:flex-start;gap:1rem;}
    .card-icon{background:rgba(16,185,129,.1);color:var(--accent);padding:.5rem;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .card-icon svg{width:20px;height:20px;}
    .card-meta{flex:1;}
    .card-title{margin:0 0 .25rem;font-size:1.1rem;font-weight:600;}
    .card-course{margin:0;font-size:.9rem;color:var(--text-muted);font-weight:500;}

    .card-body{padding:0 1.25rem 1rem;flex-grow:1;}
    .exam-schedule{margin:0 0 .75rem;font-size:.925rem;color:var(--text);}
    .time-remaining{margin:0;font-size:.875rem;color:rgb(34,197,94);font-weight:600;display:flex;align-items:center;gap:.35rem;}
    .time-remaining svg{flex-shrink:0;}

    .card-footer{padding:1rem 1.25rem;border-top:1px solid var(--border);text-align:right;}
    .btn-take-exam{
        display:inline-block;padding:.65rem 1.25rem;background:var(--accent);color:#fff;
        text-decoration:none;font-weight:600;font-size:.925rem;border-radius:8px;
        transition:var(--transition);box-shadow:0 1px 2px rgba(0,0,0,.1);border:none;cursor:pointer;
    }
    .btn-take-exam:hover{background:var(--accent-hover);transform:translateY(-1px);}
    .btn-take-exam[disabled]{background:#94a3b8;cursor:not-allowed;opacity:.6;}
    .btn-take-exam:focus-visible{outline:2px solid var(--accent);outline-offset:2px;}

    .empty-state{grid-column:1/-1;text-align:center;padding:3rem 1rem;color:var(--text-muted);}
    .empty-state svg{margin-bottom:1rem;opacity:.5;}
    .empty-state small{display:block;margin-top:.5rem;font-size:.9rem;}

    .visually-hidden{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;}
    :focus-visible{outline:2px solid var(--accent);outline-offset:2px;}

    @media(max-width:640px){
        .exams-grid{grid-template-columns:1fr;}
        .card-header{flex-direction:column;align-items:flex-start;}
        .card-icon{margin-bottom:.5rem;}
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.btn-take-exam');

    buttons.forEach(btn => {
        btn.addEventListener('click', async () => {
            const examId   = btn.dataset.examId;
            const fileUrl  = btn.dataset.fileUrl;
            const original = btn.innerHTML;

            // UI: show loading
            btn.disabled = true;
            btn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 38 38" stroke="currentColor" style="animation:spin 1s linear infinite;">
                    <g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="2">
                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"/>
                        <path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"/></path>
                    </g></g>
                </svg>
                <span style="margin-left:.4rem;">Opening…</span>
            `;

            try {
                const res = await fetch('api/take_exam.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ exam_id: examId })
                });

                const data = await res.json();

                if (data.success) {
                    // Open the exam file in a new tab
                    window.open(fileUrl, '_blank', 'noopener,noreferrer');
                } else {
                    alert(data.message || 'Could not start the exam.');
                }
            } catch (err) {
                console.error(err);
                alert('Network error. Please try again.');
            } finally {
                // Restore button
                btn.disabled = false;
                btn.innerHTML = original;
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>