<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<div class="courses-container">
  <h1 class="page-title">📚 Learning Materials</h1>
  
  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="Search materials by title or subject...">
    <button class="btn btn-search">🔍 Search</button>
  </div>

  <div class="materials-grid">
    <?php
    $stmt = $pdo->prepare("SELECT m.title, m.file_path, m.file_type, s.name AS subj
                           FROM materials m
                           JOIN subjects s ON m.subject_id = s.id
                           JOIN student_subjects ss ON ss.subject_id = s.id
                           WHERE ss.student_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->rowCount() === 0) {
      echo "<p class='no-materials'>No learning materials available yet. Check back soon!</p>";
    } else {
      while($m = $stmt->fetch()){
        $icon = $m['file_type']=='pdf' ? 'PDF' : ($m['file_type']=='doc' ? 'DOC' : 'Video');
        $iconClass = strtolower($m['file_type']);
        echo "<div class='material-card'>
                <div class='material-header'>
                  <span class='material-icon $iconClass'>$icon</span>
                  <h3 class='material-title'>{$m['subj']} – {$m['title']}</h3>
                </div>
                <div class='material-actions'>
                  <a href='../{$m['file_path']}' target='_blank' class='btn btn-open'>Open</a>
                  <button class='btn btn-speak voice-btn speak-btn'>Speak</button>
                </div>
              </div>";
      }
    }
    ?>
  </div>
</div>

<style>
  /* Root variables for consistent theming */
  :root {
    --color-bg: #0b1220;
    --color-card: #1b243a;
    --color-text: #e6eef8;
    --color-accent: #3b82f6;
    --color-muted: #9ca3af;
    --color-btn: #2a3552;
    --color-btn-hover: #3c4a70;
    --color-success: #10b981;
    --color-border: #2f354d;
    --transition: 0.2s ease;
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.3);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.4);
  }

  body {
    background: var(--color-bg);
    color: var(--color-text);
    font-family: "Inter", system-ui, sans-serif;
  }

  .courses-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
  }

  .page-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-align: center;
    color: var(--color-text);
  }

  /* Search Bar */
  .search-bar {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
  }

  .search-bar input {
    flex: 1;
    padding: 12px;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    background: #111827;
    color: var(--color-text);
    font-size: 1rem;
  }

  .btn-search {
    background: var(--color-btn);
    color: var(--color-text);
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
  }

  .btn-search:hover {
    background: var(--color-btn-hover);
  }

  /* Materials Grid */
  .materials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
  }

  .material-card {
    background: var(--color-card);
    border-radius: 12px;
    padding: 20px;
    box-shadow: var(--shadow-sm);
    transition: transform var(--transition), box-shadow var(--transition);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .material-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
  }

  .material-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
  }

  .material-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    color: #fff;
  }

  .material-icon.pdf { background: #ef4444; } /* Red for PDF */
  .material-icon.doc { background: #3b82f6; } /* Blue for DOC */
  .material-icon.video { background: #8b5cf6; } /* Purple for Video */

  .material-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--color-text);
    margin: 0;
    flex: 1;
  }

  .material-actions {
    display: flex;
    gap: 10px;
    margin-top: auto;
  }

  .btn {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: var(--transition);
    text-align: center;
    text-decoration: none;
  }

  .btn-open {
    background: var(--color-success);
    color: #fff;
    flex: 1;
  }

  .btn-open:hover {
    background: #059669;
  }

  .btn-speak {
    background: var(--color-accent);
    color: #fff;
    flex: 1;
  }

  .btn-speak:hover {
    background: #2563eb;
  }

  .no-materials {
    text-align: center;
    color: var(--color-muted);
    font-size: 1.1rem;
    padding: 40px;
    background: var(--color-card);
    border-radius: 12px;
    margin: 0 auto;
    max-width: 600px;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .materials-grid {
      grid-template-columns: 1fr;
    }
    .search-bar {
      flex-direction: column;
    }
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('searchInput');
  const materialCards = document.querySelectorAll('.material-card');

  searchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    materialCards.forEach(card => {
      const title = card.querySelector('.material-title').textContent.toLowerCase();
      card.style.display = title.includes(query) ? 'flex' : 'none';
    });
  });
});
</script>

<?php include 'includes/footer.php'; ?>