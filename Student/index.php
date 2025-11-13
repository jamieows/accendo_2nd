<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>


<div class="dashboard-container">
  <!-- Professional Welcome -->
  <header class="welcome-header" aria-labelledby="welcome-title">
    <h1 id="welcome-title" class="welcome-title">
      Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!
    </h1>
    <p class="welcome-subtitle">Your dashboard — accessible, voice-enabled, and ready.</p>
  </header>

  <!-- Dashboard Grid -->
  <main class="dashboard-grid">
    
    <!-- Enrolled Subjects -->
    <section class="card subjects-card" aria-labelledby="subjects-heading">
      <header class="card-header">
        <h2 id="subjects-heading" class="card-title">My Enrolled Subjects</h2>
        <button class="voice-btn speak-all-btn" aria-label="Speak all subject names">
          Speak All
        </button>
      </header>

      <?php
      $stmt = $pdo->prepare("
        SELECT s.name, s.id 
        FROM student_subjects ss 
        JOIN subjects s ON ss.subject_id = s.id 
        WHERE ss.student_id = ? 
        ORDER BY s.name
      ");
      $stmt->execute([$_SESSION['user_id']]);
      $subjects = $stmt->fetchAll();

      if ($subjects) {
        echo '<ul class="subjects-list" role="list">';
        foreach ($subjects as $s) {
          $subjectName = htmlspecialchars($s['name']);
          $subjectId = $s['id'];
          $color = match($subjectId % 5) {
            0 => '#dc2626', // red-600
            1 => '#2563eb', // blue-600
            2 => '#16a34a', // green-600
            3 => '#d97706', // amber-600
            4 => '#9333ea', // purple-600
          };
          echo "<li class='subject-item' role='listitem'>
                  <div class='subject-icon' style='background:$color;' aria-hidden='true'>
                    <svg viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                      <path d='M4 19.5A2.5 2.5 0 0 1 6.5 17H20'></path>
                      <path d='M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z'></path>
                    </svg>
                  </div>
                  <div class='subject-info'>
                    <strong class='subject-name'>$subjectName</strong>
                    <p class='subject-desc'>View materials, assignments, and exams</p>
                  </div>
                  <button class='voice-btn speak-btn' 
                          data-text='$subjectName' 
                          aria-label='Speak $subjectName'>Speak</button>
                </li>";
        }
        echo '</ul>';
      } else {
        echo '<div class="empty-state" role="status" aria-live="polite">
                <p>No enrolled subjects. Contact your teacher to begin.</p>
              </div>';
      }
      ?>
    </section>

    <!-- Quick Stats -->
    <aside class="card stats-card" aria-labelledby="stats-heading">
      <h2 id="stats-heading" class="card-title">At a Glance</h2>
      <div class="stats-grid">
        <div class="stat-item">
          <div class="stat-value" id="subject-count"><?= count($subjects) ?></div>
          <div class="stat-label">Subjects</div>
        </div>
        <div class="stat-item">
          <div class="stat-value" id="assignment-count">—</div>
          <div class="stat-label">Pending</div>
        </div>
        <div class="stat-item">
          <div class="stat-value" id="exam-count">—</div>
          <div class="stat-label">Upcoming Exam</div>
        </div>
      </div>
    </aside>
  </main>
</div>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

  :root {
    --bg: #f8fafc;
    --card: #ffffff;
    --text: #0f172a;
    --text-muted: #64748b;
    --accent: #2563eb;
    --accent-hover: #1d4ed8;
    --border: #e2e8f0;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
    --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    --radius: 12px;
    --transition: all 0.2s ease;
    --focus: 0 0 0 3px rgba(37, 99, 235, 0.3);
  }

  @media (prefers-color-scheme: dark) {
    :root {
      --bg: #0f172a;
      --card: #1e293b;
      --text: #f1f5f9;
      --text-muted: #94a3b8;
      --border: #334155;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.3);
      --shadow-md: 0 4px 12px rgba(0,0,0,0.4);
    }
  }

  body {
    font-family: 'Inter', system-ui, sans-serif;
    background: var(--bg);
    color: var(--text);
    line-height: 1.6;
    margin: 0;
  }

  .dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 28px 20px;
  }

  /* Header */
  .welcome-header {
    margin-bottom: 32px;
  }

  .welcome-title {
    font-size: 1.875rem;
    font-weight: 700;
    margin: 0 0 8px 0;
    color: var(--text);
  }

  .welcome-subtitle {
    margin: 0;
    color: var(--text-muted);
    font-size: 1rem;
  }

  /* Grid */
  .dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 24px;
  }

  @media (max-width: 992px) {
    .dashboard-grid { grid-template-columns: 1fr; }
  }

  /* Cards */
  .card {
    background: var(--card);
    border-radius: var(--radius);
    padding: 24px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border);
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
  }

  .card-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0;
    color: var(--text);
  }

  /* Subjects List */
  .subjects-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .subject-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: rgba(37, 99, 235, 0.02);
    border: 1px solid rgba(37, 99, 235, 0.1);
    border-radius: 10px;
    transition: var(--transition);
  }

  .subject-item:hover,
  .subject-item:focus-within {
    background: rgba(37, 99, 235, 0.05);
    border-color: rgba(37, 99, 235, 0.25);
    box-shadow: var(--focus);
  }

  .subject-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .subject-icon svg {
    width: 24px;
    height: 24px;
  }

  .subject-info {
    flex: 1;
    min-width: 0;
  }

  .subject-name {
    display: block;
    font-weight: 600;
    font-size: 1.05rem;
    color: var(--text);
  }

  .subject-desc {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 4px 0 0;
  }

  /* Voice Button */
  .voice-btn {
    background: transparent;
    border: 1.5px solid var(--accent);
    color: var(--accent);
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .voice-btn::before {
    content: "Speak";
    font-size: 1.1em;
  }

  .voice-btn:hover,
  .voice-btn:focus {
    background: var(--accent);
    color: white;
    outline: none;
    box-shadow: var(--focus);
  }

  .speak-all-btn {
    font-size: 0.875rem;
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 32px 16px;
    color: var(--text-muted);
  }

  .empty-state p {
    margin: 0;
    font-size: 1rem;
  }

  /* Stats */
  .stats-grid {
    display: grid;
    gap: 16px;
  }

  .stat-item {
    text-align: center;
  }

  .stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--accent);
    margin-bottom: 4px;
  }

  .stat-label {
    font-size: 0.875rem;
    color: var(--text-muted);
  }

  /* Responsive */
  @media (max-width: 640px) {
    .welcome-title { font-size: 1.6rem; }
    .card-header { flex-direction: column; align-items: stretch; }
    .subject-item { flex-direction: column; text-align: center; }
    .subject-icon { margin-bottom: 8px; }
  }

  /* High Contrast */
  @media (prefers-contrast: high) {
    .card, .subject-item { border-width: 2px; }
  }

  /* Reduced Motion */
  @media (prefers-reduced-motion: reduce) {
    * { transition: none !important; }
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const speakButtons = document.querySelectorAll('.speak-btn');
  const speakAllBtn = document.querySelector('.speak-all-btn');
  let utterance = null;

  if ('speechSynthesis' in window) {
    const voices = speechSynthesis.getVoices();
    const preferred = voices.find(v => v.lang === 'fil-PH') || 
                      voices.find(v => v.lang.startsWith('en')) || 
                      voices[0];

    function speak(text) {
      if (utterance) speechSynthesis.cancel();
      utterance = new SpeechSynthesisUtterance(text);
      utterance.rate = 0.9;
      utterance.pitch = 1;
      utterance.volume = 1;
      if (preferred) utterance.voice = preferred;
      speechSynthesis.speak(utterance);
    }

    speakButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const text = btn.dataset.text || btn.closest('.subject-item').querySelector('.subject-name').textContent;
        speak(text);
      });
    });

    if (speakAllBtn) {
      speakAllBtn.addEventListener('click', () => {
        const names = Array.from(document.querySelectorAll('.subject-name'))
          .map(el => el.textContent).join(', ');
        speak(`You are enrolled in: ${names}`);
      });
    }
  }

  // Load dynamic stats
  fetch('api/student_stats.php')
    .then(r => r.json())
    .then(d => {
      if (d.assignments !== undefined) document.getElementById('assignment-count').textContent = d.assignments;
      if (d.next_exam) document.getElementById('exam-count').textContent = d.next_exam;
    })
    .catch(() => {
      document.getElementById('assignment-count').textContent = '—';
      document.getElementById('exam-count').textContent = '—';
    });
});
</script>

<?php include 'includes/footer.php'; ?>