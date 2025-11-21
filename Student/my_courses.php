<?php 
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../Auth/login.php"); 
    exit();
} 
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <h1>Learning Materials</h1>

    <div class="search">
        <input type="text" id="searchInput" placeholder="Search materials or subjects..." autocomplete="off">
        <button id="clearSearch">Clear</button>
    </div>

    <div class="courses-grid" id="coursesGrid">
        <?php
        $stmt = $pdo->prepare("
            SELECT s.id AS subject_id, s.name AS subject_name,
                   m.id AS material_id, m.title, m.file_path
            FROM materials m
            JOIN subjects s ON m.subject_id = s.id
            JOIN student_subjects ss ON ss.subject_id = s.id
            WHERE ss.student_id = ?
            ORDER BY s.name, m.title
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $all_materials = $stmt->fetchAll();

        if (empty($all_materials)) {
            echo "<p class='empty'>No materials available.</p>";
        } else {
            $grouped = [];
            foreach ($all_materials as $row) {
                $grouped[$row['subject_id']]['name'] = $row['subject_name'];
                $grouped[$row['subject_id']]['materials'][] = [
                    'id' => $row['material_id'],
                    'title' => $row['title'],
                    'file_path' => $row['file_path']
                ];
            }

            foreach ($grouped as $subject_id => $data) {
                $subject_name = htmlspecialchars($data['name']);
                echo "<div class='course-card'>";
                echo "  <div class='course-header'>";
                echo "    <h2 class='course-title'>$subject_name</h2>";
                echo "    <span class='material-count'>" . count($data['materials']) . " material" . (count($data['materials']) > 1 ? 's' : '') . "</span>";
                echo "  </div>";
                echo "  <div class='materials-list'>";

                foreach ($data['materials'] as $m) {
                    $id    = (int)$m['id'];
                    $title = htmlspecialchars($m['title']);
                    $ext   = strtolower(pathinfo($m['file_path'], PATHINFO_EXTENSION));

                    echo "<div class='material-item' data-id='$id' data-ext='$ext' data-subject='$subject_name' data-title='$title'>";
                    echo "  <span class='material-title'>$title</span>";
                    echo "  <button class='open-btn' data-id='$id'>Open →</button>";
                    echo "</div>";
                }

                echo "  </div>";
                echo "</div>";
            }
        }
        ?>
    </div>
</div>

<!-- LARGE MODAL -->
<div id="modal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle"></h2>
            <button id="closeModal" class="close">×</button>
        </div>
        <div class="modal-body">
            <div id="loading">Loading...</div>
            <iframe id="pdf" class="viewer" style="display:none;"></iframe>
            <img id="img" class="viewer" style="display:none;" alt="">
            <video id="vid" class="viewer" style="display:none;" controls></video>
            <div id="office" class="viewer" style="display:none;">
                <iframe style="width:100%;height:100%;border:none;"></iframe>
            </div>
            <div id="fallback" style="display:none;">
                <p>Cannot preview this file.</p>
                <a id="download" href="#" target="_blank">Download</a>
            </div>
            <div id="error" style="display:none;">Failed to load file.</div>
        </div>
    </div>
</div>

<style>
    :root {
        --bg: #0b1220;
        --card: #1b243a;
        --text: #e6eef8;
        --muted: #9ca3af;
        --accent: #3b82f6;
        --success: #10b981;
        --radius: 16px;
        --shadow: 0 4px 20px rgba(0,0,0,.4);
    }

    /* Light mode overrides when .dark-mode is NOT present */
    body:not(.dark-mode) {
        --bg: #f8fafc;
        --card: #ffffff;
        --text: #1e293b;
        --muted: #64748b;
        --accent: #3b82f6;
        --success: #10b981;
    }

    body { 
        background: var(--bg); 
        color: var(--text); 
        font-family: Inter, system-ui, sans-serif; 
        transition: background 0.3s ease, color 0.3s ease;
    }

    .container { max-width:1200px; margin:0 auto; padding:2rem 1.5rem; }
    h1 { text-align:center; font-size:2rem; margin-bottom:1.8rem; background:linear-gradient(90deg,#7c3aed,#3b82f6); -webkit-background-clip:text; color:transparent; font-weight:700; }

    .search { display:flex; gap:.5rem; max-width:600px; margin:0 auto 2rem; }
    .search input { 
        flex:1; padding:.75rem 1rem; border:none; border-radius:var(--radius); 
        background: var(--card); color: var(--text); 
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .search button { padding:.75rem 1.2rem; background:#2a3552; color:#fff; border:none; border-radius:var(--radius); cursor:pointer; }

    .courses-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(340px,1fr)); gap:1.8rem; }

    .course-card {
        background:var(--card); border-radius:var(--radius); overflow:hidden; 
        box-shadow:var(--shadow); transition:all .3s ease; border: 1px solid rgba(255,255,255,0.05);
    }
    .course-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(0,0,0,.6); }

    .course-header {
        padding:1.4rem 1.6rem;
        background:linear-gradient(135deg,#1e293b,#162447);
        display:flex; justify-content:space-between; align-items:center;
        border-bottom:1px solid #2f354d;
    }
    .course-title {
        font-size:1.35rem; font-weight:700; margin:0;
        background:linear-gradient(90deg,#a78bfa,#60a5fa); -webkit-background-clip:text; color:transparent;
    }
    .material-count {
        font-size:0.9rem; color:var(--muted); background:#2d3748; padding:0.35rem 0.8rem; border-radius:20px;
    }

    .materials-list { padding:0.8rem 0; }
    .material-item {
        display:flex; justify-content:space-between; align-items:center;
        padding:0.9rem 1.6rem; transition:background .2s;
        border-bottom:1px solid rgba(255,255,255,0.05);
    }
    .material-item:last-child { border-bottom:none; }
    .material-item:hover { background:rgba(255,255,255,0.08); }

    .material-title { font-size:1rem; flex:1; word-break:break-word; padding-right:1rem; }
    .open-btn {
        background:var(--success); color:#fff; border:none; padding:0.55rem 1.1rem;
        border-radius:12px; cursor:pointer; font-weight:600; font-size:0.9rem;
    }
    .open-btn:hover { background:#059669; }

    .empty { text-align:center; grid-column:1/-1; padding:3rem; background:var(--card); border-radius:var(--radius); color:var(--muted); }

    .modal { position:fixed; inset:0; background:rgba(0,0,0,.92); display:flex; align-items:center; justify-content:center; z-index:9999; }
    .modal-content { background:var(--card); width:90vw; max-width:1400px; height:92vh; border-radius:var(--radius); overflow:hidden; display:flex; flex-direction:column; }
    .modal-header { padding:1rem 1.5rem; background:#1e293b; border-bottom:1px solid #2f354d; display:flex; justify-content:space-between; align-items:center; }
    .close { background:none; border:none; font-size:2rem; color:var(--muted); cursor:pointer; }
    .modal-body { flex:1; overflow:auto; padding:1.5rem; background:var(--bg); }
    .viewer { width:100%; height:100%; border:none; border-radius:12px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // === DARK MODE INITIALIZATION ===
    const savedTheme = localStorage.getItem('accendo_theme');
    const isDark = savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches);

    if (isDark) {
        document.documentElement.classList.add('dark-mode');
        document.body.classList.add('dark-mode');
    } else {
        document.documentElement.classList.remove('dark-mode');
        document.body.classList.remove('dark-mode');
    }

    // === MODAL & SEARCH (unchanged logic below) ===
    const modal = document.getElementById('modal');
    const close = document.getElementById('closeModal');
    const titleEl = document.getElementById('modalTitle');
    const loading = document.getElementById('loading');
    const pdf = document.getElementById('pdf');
    const img = document.getElementById('img');
    const vid = document.getElementById('vid');
    const office = document.getElementById('office');
    const officeFrame = office.querySelector('iframe');
    const fallback = document.getElementById('fallback');
    const download = document.getElementById('download');
    const error = document.getElementById('error');

    const hideAll = () => [loading, pdf, img, vid, office, fallback, error].forEach(el => el.style.display = 'none');
    const closeModal = () => { modal.style.display = 'none'; hideAll(); document.body.style.overflow = ''; };

    close.addEventListener('click', closeModal);
    modal.addEventListener('click', e => e.target === modal && closeModal());
    document.addEventListener('keydown', e => e.key === 'Escape' && closeModal());

    document.querySelectorAll('.open-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const item = btn.closest('.material-item');
            const id = item.dataset.id;
            const ext = item.dataset.ext;
            const subject = item.dataset.subject;
            const materialTitle = item.dataset.title;

            titleEl.textContent = `${subject} – ${materialTitle}`;
            hideAll();
            loading.style.display = 'block';
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            try {
                await fetch('api/view_materials.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `material_id=${id}`
                });
            } catch (_) {}

            const url = `api/view_file.php?id=${id}`;

            try {
                if (ext === 'pdf') {
                    pdf.src = url; pdf.style.display = 'block';
                } else if (['jpg','jpeg','png','gif','webp'].includes(ext)) {
                    img.src = url; img.style.display = 'block';
                } else if (['mp4','webm','ogg'].includes(ext)) {
                    vid.src = url; vid.style.display = 'block';
                } else if (['doc','docx','xls','xlsx','ppt','pptx'].includes(ext)) {
                    officeFrame.src = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(url)}`;
                    office.style.display = 'block';
                } else {
                    download.href = url;
                    fallback.style.display = 'block';
                }
            } catch (e) {
                error.style.display = 'block';
            } finally {
                loading.style.display = 'none';
            }
        });
    });

    // Search functionality
    const input = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    const courseCards = document.querySelectorAll('.course-card');

    const filter = () => {
        const q = input.value.trim().toLowerCase();
        courseCards.forEach(card => {
            const subject = card.querySelector('.course-title').textContent.toLowerCase();
            const items = card.querySelectorAll('.material-item');
            let visibleItems = 0;

            items.forEach(item => {
                const title = item.dataset.title.toLowerCase();
                const matches = subject.includes(q) || title.includes(q);
                item.style.display = matches ? 'flex' : 'none';
                if (matches) visibleItems++;
            });

            card.style.display = (visibleItems > 0 || q === '') ? 'block' : 'none';
        });
    };

    input.addEventListener('input', filter);
    clearBtn.addEventListener('click', () => { input.value = ''; filter(); });
});
</script>

<?php include 'includes/footer.php'; ?>