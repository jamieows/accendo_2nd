<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}

// FIXED: Force PH timezone for ALL times (LINE 15 FIXED!)
date_default_timezone_set('Asia/Manila');

// Handle settings POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['theme'])) {
    $theme = $_POST['theme'];
    $_SESSION['admin_theme'] = in_array($theme, ['light','dark','system']) ? $theme : 'system';
  }
  $saved = true;
}
?>
<?php include 'includes/header.php'; ?>

<?php if (!empty($_SESSION['flash_message'])): ?>
  <div style="position:fixed; top:20px; right:20px; padding:16px 24px; border-radius:12px; color:white; font-weight:600; z-index:9999; box-shadow:0 10px 30px rgba(0,0,0,0.3); background: <?= $_SESSION['flash_type'] === 'warning' ? '#f39c12' : '#27ae60' ?>;">
    <?= htmlspecialchars($_SESSION['flash_message']) ?>
    <span style="float:right; cursor:pointer; margin-left:20px;" onclick="this.parentElement.remove()">Close</span>
  </div>
  <?php 
  unset($_SESSION['flash_message']); 
  unset($_SESSION['flash_type']); 
  ?>
  <script>setTimeout(() => document.querySelector('[style*="position:fixed"]')?.remove(), 5000);</script>
<?php endif; ?>

<div class="page-header">
  <h1>Admin Settings</h1>
  <p class="subtitle">Manage admin settings • <?= date('l, F j, Y') ?> • PH Time: <span id="ph-clock"></span></p>
</div>

<?php if (!empty($saved)): ?>
  <div class="notice" id="inline-notice" style="display:none;">Settings saved.</div>
  <script>window.__settingsSaved = true;</script>
<?php endif; ?>

<style>
  .modal-overlay { position: fixed; inset: 0; display: none; align-items: center; justify-content: center; background: rgba(0,0,0,0.35); z-index: 1200; }
  .modal-overlay.show { display: flex; }
  .modal-wrapper { position: relative; }
  .modal { background: var(--card); color: var(--text); border-radius: 10px; padding: 16px 18px; box-shadow: 0 12px 30px rgba(2,6,23,0.2); min-width: 260px; max-width: 90%; }
  .modal h3 { margin:0 0 6px 0; font-size:1.05rem; }
  .modal p { margin:0; color: #6B7280; }
  .modal .close-btn { background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:var(--text);position:absolute;right:8px;top:6px; }
  .new-activity { background: rgba(124, 58, 237, 0.08); transition: background-color 0.6s ease; }
</style>

<div class="card">
  <form method="post" id="settings-form">
    <h2 style="margin-bottom:8px;">Appearance</h2>
    <p class="muted" style="margin-bottom:12px; color: #6B7280">Choose your preferred theme for the admin interface.</p>

    <div class="form-row" style="margin-bottom:14px;">
      <label for="theme" style="display:block;margin-bottom:6px;">Theme</label>
      <?php $curTheme = $_SESSION['admin_theme'] ?? 'system'; ?>
      <select id="theme" name="theme" class="input" style="max-width:500px;"> 
        <option value="system" <?= $curTheme==='system'?'selected':'' ?>>System (follow OS)</option>
        <option value="light" <?= $curTheme==='light'?'selected':'' ?>>Light</option>
        <option value="dark" <?= $curTheme==='dark'?'selected':'' ?>>Dark</option>
      </select>
    </div>

    <div style="margin-top:6px;">
      <button type="submit" class="btn">Save settings</button>
    </div>
  </form>
</div>

<!-- Saved confirmation modal -->
<div id="saved-modal" class="modal-overlay" role="dialog" aria-hidden="true">
  <div class="modal-wrapper">
    <div class="modal">
      <button class="close-btn" id="saved-modal-close" aria-label="Close">&times;</button>
      <h3>Settings saved</h3>
      <p>Your preferences were saved successfully.</p>
    </div>
  </div>
</div>

<div class="card">
  <h2>Activity History</h2><br>
  <p class="muted" style="margin-bottom:12px; color: #6B7280">Recent actions (Create / Update / Delete).</p>

  <?php
  $hf = $_SESSION['history_filter'] ?? 'all';

  $tblStmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'activity_logs'");
  $tblStmt->execute();
  $hasActivityTable = (bool) $tblStmt->fetchColumn();

  $rows = [];
  if ($hasActivityTable) {
      $params = [];
      $where = '';
      if ($hf !== 'all') { $where = 'WHERE action = ?'; $params[] = $hf; }
      $q = "SELECT id, actor_name, action, target, details, created_at FROM activity_logs $where ORDER BY created_at DESC LIMIT 100";
      $s = $pdo->prepare($q);
      $s->execute($params);
      $rows = $s->fetchAll();
  }
  ?>

  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>Time</th>
          <th>Actor</th>
          <th>Action</th>
          <th>Target</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody id="activity-table-body">
        <?php if (!empty($rows)): ?>
          <?php foreach ($rows as $r): 
            $time = new DateTime($r['created_at']);
            $time->setTimezone(new DateTimeZone('Asia/Manila'));
          ?>
            <tr data-id="<?= $r['id'] ?>">
              <td><?= $time->format('M j, Y g:i A') ?> PHT</td>
              <td><?= htmlspecialchars($r['actor_name'] ?? 'System') ?></td>
              <td><?= htmlspecialchars(ucfirst($r['action'])) ?></td>
              <td><?= htmlspecialchars(ucfirst(str_replace('_',' ',$r['target'] ?? ''))) ?></td>
              <td><?= htmlspecialchars($r['details'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" class="muted">No activity found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <h2>System Info</h2>
  <p><strong>System Time (PH):</strong> <?= date('F j, Y g:i A') ?> PHT (Asia/Manila)</p>
  <p><strong>Database:</strong> accendo_db</p>
  <p><strong>Version:</strong> Accendo LMS v2.0</p>
</div>

<script>
  // LIVE PH CLOCK
  function updatePHClock() {
    const now = new Date();
    const phTime = now.toLocaleString("en-PH", { 
      timeZone: "Asia/Manila", 
      hour12: true, 
      hour: 'numeric', 
      minute: '2-digit', 
      second: '2-digit' 
    });
    document.getElementById("ph-clock").textContent = phTime;
  }
  updatePHClock();
  setInterval(updatePHClock, 1000);

  // REAL-TIME ACTIVITY POLLING (PH TIME)
  const activityTableBody = document.querySelector('#activity-table-body');
  const seenIds = new Set();
  <?php foreach ($rows as $r): ?>
    seenIds.add(<?= $r['id'] ?>);
  <?php endforeach; ?>

  async function fetchActivity() {
    try {
      const res = await fetch('api/activity_logs.php');
      if (!res.ok) return;
      const json = await res.json();

      const newItems = json.filter(r => r.id && !seenIds.has(r.id));
      if (newItems.length === 0) return;

      newItems.reverse().forEach(r => {
        const tr = document.createElement('tr');
        tr.dataset.id = r.id;
        seenIds.add(r.id);

        const time = new Date(r.created_at);
        const phTime = time.toLocaleString("en-PH", { 
          timeZone: "Asia/Manila", 
          month: 'short', day: 'numeric', year: 'numeric', 
          hour: 'numeric', minute: '2-digit', hour12: true 
        }) + ' PHT';

        tr.innerHTML = `
          <td>${phTime}</td>
          <td>${r.actor_name || 'System'}</td>
          <td>${r.action || ''}</td>
          <td>${(r.target || '').replace(/_/g, ' ')}</td>
          <td>${r.details || ''}</td>
        `;
        tr.classList.add('new-activity');
        setTimeout(() => tr.classList.remove('new-activity'), 3000);
        activityTableBody.insertBefore(tr, activityTableBody.firstChild);
      });

      while (activityTableBody.children.length > 200) {
        activityTableBody.removeChild(activityTableBody.lastChild);
      }
    } catch (e) { console.error(e); }
  }
  setInterval(fetchActivity, 4000);
</script>

<?php include 'includes/footer.php'; ?>