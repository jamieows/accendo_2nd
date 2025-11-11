<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<style>
  /* Import a clean professional font */
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');

  :root{
    --bg:#f5f7fb;
    --card:#ffffff;
    --muted:#6b7280;
    --accent:#2563eb;
    --shadow: 0 6px 18px rgba(16,24,40,0.06);
  }

  body{ font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; background:var(--bg); color:#0f172a; margin:0; padding:0; }

  main.container{ max-width:1100px; margin:28px auto; padding:20px; box-sizing:border-box; }

  .welcome{
    display:flex; align-items:center; justify-content:space-between; gap:20px; margin-bottom:18px;
  }
  .welcome h1{ font-size:1.6rem; margin:0; font-weight:600; letter-spacing:-0.2px; }
  .welcome p{ margin:0; color:var(--muted); }

  .card{
    background:var(--card);
    border-radius:12px;
    padding:20px;
    box-shadow:var(--shadow);
    border:1px solid rgba(15,23,42,0.04);
  }

  .card h2{
    margin:0 0 12px 0; font-size:1.05rem; color:#0b1220; font-weight:600;
  }

  .subject-grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap:12px;
    margin-top:8px;
  }

  .material-card{
    display:flex; align-items:center; gap:12px;
    padding:12px; background:linear-gradient(180deg, rgba(37,99,235,0.03), rgba(37,99,235,0.01));
    border-radius:10px; border:1px solid rgba(37,99,235,0.08);
  }

  .material-icon{ width:40px; height:40px; display:flex; align-items:center; justify-content:center; background:#fff; border-radius:8px; box-shadow: 0 2px 6px rgba(15,23,42,0.04); flex:0 0 40px; }
  .material-icon svg{ width:22px; height:22px; fill:var(--accent); }

  .material-card strong{ display:block; font-size:0.98rem; color:#071133; }

  .material-meta{ margin-left:auto; display:flex; gap:8px; align-items:center; }

  .voice-btn{
    background:transparent; border:1px solid rgba(7,17,51,0.06); padding:6px 10px; border-radius:8px; font-size:0.9rem; color:var(--accent); cursor:pointer;
  }
  .voice-btn:hover{ background:rgba(37,99,235,0.06); }

  .empty-note{ color:var(--muted); padding:12px 0; }

  @media (max-width:520px){
    .welcome{ flex-direction:column; align-items:flex-start; gap:8px; }
  }
</style>

<main class="container">
  <header class="welcome">
    <div>
      <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>
      <p>Student dashboard — quick access to your subjects and materials</p>
    </div>
    <div aria-hidden="true" style="color:var(--muted);font-size:0.9rem">Student</div>
  </header>

  <section class="card" aria-labelledby="enrolled-subjects">
    <h2 id="enrolled-subjects">My Enrolled Subjects</h2>

    <?php
    $stmt = $pdo->prepare("SELECT s.name FROM student_subjects ss JOIN subjects s ON ss.subject_id = s.id WHERE ss.student_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $subjects = $stmt->fetchAll();

    if ($subjects) {
        echo '<div class="subject-grid">';
        foreach ($subjects as $s) {
            $name = htmlspecialchars($s['name']);
            echo '<div class="material-card">
                    <span class="material-icon" aria-hidden="true">
                      <!-- book icon -->
                      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M18 2H6c-1.1 0-2 .9-2 2v16l7-3 7 3V4c0-1.1-.9-2-2-2z"/></svg>
                    </span>
                    <div>
                      <strong>'. $name .'</strong>
                      <div style="font-size:0.85rem;color:var(--muted);margin-top:4px;">Course material & resources</div>
                    </div>
                    <div class="material-meta">
                      <button class="voice-btn speak-btn" aria-label="Read subject name">🔊 Speak</button>
                    </div>
                  </div>';
        }
        echo '</div>';
    } else {
        echo '<p class="empty-note">No subjects enrolled.</p>';
    }
    ?>
  </section>
</main>

<?php include 'includes/footer.php'; ?>