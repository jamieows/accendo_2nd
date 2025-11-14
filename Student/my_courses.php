
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
        <input type="text" id="issiearchInput" placeholder="Search..." autocomplete="off">
        <button id="clearSearch">Clear</button>
    </div>

    <div class="grid" id="materialsGrid">
        <?php
        $stmt = $pdo->prepare("
            SELECT m.id, m.title, m.file_path, s.name AS subj
            FROM materials m
            JOIN subjects s ON m.subject_id = s.id
            JOIN student_subjects ss ON ss.subject_id = s.id
            WHERE ss.student_id = ?
            ORDER BY s.name, m.title
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $materials = $stmt->fetchAll();

        if (empty($materials)) {
            echo "<p class='empty'>No materials available.</p>";
        } else {
            foreach ($materials as $m) {
                $id   = (int)$m['id'];
                $subj = htmlspecialchars($m['subj']);
                $title= htmlspecialchars($m['title']);
                $ext  = strtolower(pathinfo($m['file_path'], PATHINFO_EXTENSION));

                echo "<div class='card' data-id='$id' data-ext='$ext' data-subject='$subj' data-title='$title'>
                    <h3>$subj – $title</h3>
                    <button class='open-btn' data-id='$id'>Open</button>
                </div>";
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
            <button id="closeModal" class="close">x</button>
        </div>
        <div class="modal-body">
            <div id="loading">Loading...</div>

            <iframe id="pdf" class="viewer" style="display:none;"></iframe>
            <img    id="img"  class="viewer" style="display:none;" alt="">
            <video  id="vid"  class="viewer" style="display:none;" controls></video>
            <div    id="office" class="viewer" style="display:none;">
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

    * { margin:0; padding:0; box-sizing:border-box; }
    body { background:var(--bg); color:var(--text); font-family:Inter,system-ui,sans-serif; line-height:1.5; }

    .container { max-width:1200px; margin:0 auto; padding:2rem 1.5rem; }
    h1 { text-align:center; font-size:2rem; margin-bottom:1.8rem; background:linear-gradient(90deg,#7c3aed,#3b82f6); -webkit-background-clip:text; color:transparent; font-weight:700; }

    .search { display:flex; gap:.5rem; max-width:600px; margin:0 auto 2rem; }
    .search input { flex:1; padding:.75rem 1rem; border:none; border-radius:var(--radius); background:#111827; color:var(--text); font-size:1rem; }
    .search input::placeholder { color:#64748b; }
    .search input:focus { outline:none; background:#1e293b; }
    .search button { padding:.75rem 1.2rem; border:none; border-radius:var(--radius); background:#2a3552; color:var(--text); cursor:pointer; font-size:.9rem; }
    .search button:hover { background:#3c4a70; }

    .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:1.5rem; }
    .card { background:var(--card); padding:1.5rem; border-radius:var(--radius); box-shadow:var(--shadow); transition:transform .3s ease, box-shadow .3s ease; }
    .card:hover { transform:translateY(-6px); box-shadow:0 12px 30px rgba(0,0,0,.5); }
    .card h3 { margin-bottom:.75rem; font-size:1.2rem; font-weight:600; word-break:break-word; }
    .open-btn { background:var(--success); color:#fff; border:none; padding:.6rem 1.2rem; border-radius:12px; cursor:pointer; font-weight:600; font-size:.95rem; }
    .open-btn:hover { background:#059669; }

    .empty { text-align:center; color:var(--muted); font-size:1.1rem; padding:3rem; background:var(--card); border-radius:var(--radius); grid-column:1/-1; }

    /* MODAL */
    .modal { position:fixed; inset:0; background:rgba(0,0,0,.92); display:flex; align-items:center; justify-content:center; z-index:9999; padding:1rem; }
    .modal-content { background:var(--card); width:90vw; max-width:1400px; height:92vh; border-radius:var(--radius); overflow:hidden; box-shadow:0 30px 80px rgba(0,0,0,.7); display:flex; flex-direction:column; }
    .modal-header { padding:1rem 1.5rem; background:#1e293b; border-bottom:1px solid #2f354d; display:flex; justify-content:space-between; align-items:center; }
    .modal-header h2 { margin:0; font-size:1.5rem; font-weight:600; }
    .close { background:none; border:none; font-size:2rem; color:var(--muted); cursor:pointer; line-height:1; }
    .close:hover { color:var(--text); }

    .modal-body { flex:1; overflow:auto; padding:1.5rem; background:var(--bg); }
    .viewer { width:100%; height:100%; border:none; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,.3); }
    .viewer:not(iframe) { max-height:80vh; margin:0 auto; display:block; }

    #loading { text-align:center; padding:3rem; color:var(--muted); font-size:1.1rem; }

    #fallback, #error { text-align:center; padding:1.5rem; font-size:1rem; }
    #fallback p { color:var(--muted); margin-bottom:.75rem; }
    #download { color:var(--accent); text-decoration:underline; font-weight:500; }
    #download:hover { color:#60a5fa; }
    #error { color:#ef4444; }

    @media (max-width:768px) {
        .modal-content { width:96vw; height:94vh; }
        .search { flex-direction:column; }
        .search input, .search button { border-radius:12px; margin-bottom:.5rem; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const close = document.getElementById('closeModal');
    const title = document.getElementById('modalTitle');
    const loading = document.getElementById('loading');
    const pdf = document.getElementById('pdf');
    const img = document.getElementById('img');
    const vid = document.getElementById('vid');
    const office = document.getElementById('office');
    const officeFrame = office?.querySelector('iframe');
    const fallback = document.getElementById('fallback');
    const download = document.getElementById('download');
    const error = document.getElementById('error');

    const hideAll = () => [loading, pdf, img, vid, office, fallback, error].forEach(el => el && (el.style.display = 'none'));
    const closeModal = () => { modal.style.display = 'none'; hideAll(); document.body.style.overflow = ''; };

    close?.addEventListener('click', closeModal);
    modal.addEventListener('click', e => e.target === modal && closeModal());
    document.addEventListener('keydown', e => e.key === 'Escape' && closeModal());

    document.querySelectorAll('.open-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const card = btn.closest('.card');
            const ext = card.dataset.ext;
            const fileTitle = card.querySelector('h3').textContent.trim();

            title.textContent = fileTitle;
            hideAll();
            loading.style.display = 'block';
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Log view
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

    // Search
    const input = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    const cards = document.querySelectorAll('.card');

    const filter = () => {
        const q = input.value.trim().toLowerCase();
        let visible = 0;
        cards.forEach(c => {
            const match = c.dataset.subject.toLowerCase().includes(q) || c.dataset.title.toLowerCase().includes(q);
            c.style.display = match ? 'flex' : 'none';
            if (match) visible++;
        });
        const noRes = document.getElementById('noResults');
        if (noRes) noRes.style.display = (visible === 0 && q !== '') ? 'block' : 'none';
    };

    input.addEventListener('input', filter);
    clearBtn.addEventListener('click', () => { input.value = ''; input.focus(); filter(); });

    if (cards.length && !document.getElementById('noResults')) {
        const p = document.createElement('p');
        p.id = 'noResults'; p.className = 'empty';
        p.textContent = 'No results found.';
        p.style.display = 'none';
        document.querySelector('.grid').parentNode.insertBefore(p, document.querySelector('.grid'));
    }
});
</script>

<?php include 'includes/footer.php'; ?>
