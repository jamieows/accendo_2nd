<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
// Handle settings POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Theme preference (light | dark | system)
  if (isset($_POST['theme'])) {
    $theme = $_POST['theme'];
    $_SESSION['admin_theme'] = in_array($theme, ['light','dark','system']) ? $theme : 'system';
  }
  // Note: primary color removed. Theme only (light | dark | system).
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
  /* Modal confirmation styles */
  .modal-overlay { position: fixed; inset: 0; display: none; align-items: center; justify-content: center; background: rgba(0,0,0,0.35); z-index: 1200; }
  .modal-overlay.show { display: flex; }
  .modal-wrapper { position: relative; }
  .modal { background: var(--card); color: var(--text); border-radius: 10px; padding: 16px 18px; box-shadow: 0 12px 30px rgba(2,6,23,0.2); min-width: 260px; max-width: 90%; }
  .modal h3 { margin:0 0 6px 0; font-size:1.05rem; }
  .modal p { margin:0; color: #6B7280; }
  .modal .close-btn { background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:var(--text);position:absolute;right:8px;top:6px; }
  /* Highlight for newly arrived activity rows */
  .new-activity { background: rgba(124, 58, 237, 0.08); transition: background-color 0.6s ease; }
</style>

<div class="card">
  <form method="post" id="settings-form">
    <h2 style="margin-bottom:8px;">Appearance</h2>
    <p class="muted" style="margin-bottom:12px;">Choose your preferred theme for the admin interface.</p>

    <div class="form-row" style="margin-bottom:14px;">
      <label for="theme" style="display:block;margin-bottom:6px;">Theme</label>
      <?php $curTheme = $_SESSION['admin_theme'] ?? 'system'; ?>
      <select id="theme" name="theme" class="input" style="max-width:320px;"> 
        <option value="system" <?= $curTheme==='system'?'selected':'' ?>>System (follow OS)</option>
        <option value="light" <?= $curTheme==='light'?'selected':'' ?>>Light</option>
        <option value="dark" <?= $curTheme==='dark'?'selected':'' ?>>Dark</option>
      </select>
    </div>

    <!-- Primary color removed: theme only (light | dark | system) -->
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
  <h2>Activity History</h2>
  <p class="muted">Recent actions (Create / Update / Delete). Use the CUD filter above to restrict results.</p>

  <?php
  // Render activity rows: prefer a dedicated activity_logs table when present
  $hf = $_SESSION['history_filter'] ?? 'all';

  // Check if activity_logs table exists in this database
  $tblStmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'activity_logs'");
  $tblStmt->execute();
  $hasActivityTable = (bool) $tblStmt->fetchColumn();

  $rows = [];
  if ($hasActivityTable) {
      $params = [];
      $where = '';
      if ($hf !== 'all') { $where = 'WHERE action = ?'; $params[] = $hf; }
      $q = "SELECT actor_name, action, target, details, created_at FROM activity_logs $where ORDER BY created_at DESC LIMIT 100";
      $s = $pdo->prepare($q);
      $s->execute($params);
      $rows = $s->fetchAll();
  } else {
      // Fallback: show recent user registrations as 'create' events
      if ($hf === 'all' || $hf === 'create') {
          $s = $pdo->query("SELECT first_name, last_name, username, created_at FROM users ORDER BY created_at DESC LIMIT 100");
          while ($u = $s->fetch()) {
              $rows[] = [
                  'actor_name' => $u['first_name'] . ' ' . $u['last_name'],
                  'action' => 'create',
                  'target' => 'user_account',
                  'details' => $u['username'],
                  'created_at' => $u['created_at']
              ];
          }
      }
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
            $time = new DateTime($r['created_at'], new DateTimeZone('UTC'));
            $time->setTimezone(new DateTimeZone('Asia/Manila'));
          ?>
            <tr>
              <td><?= $time->format('M j, Y g:i A') ?></td>
              <td><?= htmlspecialchars($r['actor_name'] ?? 'System') ?></td>
              <td><?= htmlspecialchars(ucfirst($r['action'])) ?></td>
              <td><?= htmlspecialchars(ucfirst(str_replace('_',' ',$r['target'] ?? '')) ) ?></td>
              <td><?= htmlspecialchars($r['details'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" class="muted">No activity found for the selected filter.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- System info moved to bottom -->
<div class="card">
  <h2>System Info</h2>
  <p><strong>System Time (PH):</strong> <?= date('F j, Y g:i A') ?> (Asia/Manila)</p>
  <p><strong>Database:</strong> accendo_db</p>
  <p><strong>Version:</strong> Accendo LMS v2.0</p>
</div>

<script>
  (function(){
    const themeSelect = document.getElementById('theme');
    const mql = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
    function applyTheme(t) {
      const isDark = (t === 'dark') || (t === 'system' && mql && mql.matches);
      document.documentElement.classList.toggle('dark-mode', !!isDark);
      try { localStorage.setItem('admin_theme', t); } catch(e){}
    }

    // On load, apply saved theme from session (server-side) or fallback to 'system'
    const initial = '<?= addslashes($_SESSION['admin_theme'] ?? 'system') ?>';
    if (initial) {
      applyTheme(initial);
      if (themeSelect) themeSelect.value = initial;
    }

    // When user changes selection, apply and persist
    if (themeSelect) {
      themeSelect.addEventListener('change', (e)=> applyTheme(e.target.value));
    }

    // If following system, update theme when OS preference changes
    if (mql) {
      const listener = function(){
        const cur = (themeSelect && themeSelect.value) || localStorage.getItem('admin_theme') || 'system';
        if (cur === 'system') applyTheme('system');
      };
      if (mql.addEventListener) mql.addEventListener('change', listener);
      else if (mql.addListener) mql.addListener(listener);
    }

      // Modal show/hide for saved confirmation
      function hideSavedModal() {
        const modal = document.getElementById('saved-modal');
        if (!modal) return;
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        setTimeout(()=> { modal.style.display = 'none'; }, 200);
      }
      function showSavedModal() {
        const modal = document.getElementById('saved-modal');
        if (!modal) return;
        modal.style.display = 'flex';
        // small timeout to allow display before adding class for transition
        setTimeout(()=> { modal.classList.add('show'); modal.setAttribute('aria-hidden','false'); }, 10);
        // auto-hide after 2.5s
        setTimeout(hideSavedModal, 2500);
      }
      // wire close button and escape key
      document.getElementById('saved-modal-close')?.addEventListener('click', hideSavedModal);
      document.addEventListener('keydown', (e)=> { if (e.key === 'Escape') hideSavedModal(); });
      // if server indicated saved, show modal on load
      if (window.__settingsSaved) {
        // delay until after DOM is painted
        setTimeout(showSavedModal, 80);
        // also remove inline-notice if present
        const inl = document.getElementById('inline-notice'); if (inl) inl.style.display = 'none';
      }

    // Start realtime activity polling with new-item detection and highlight
    const activityTableBody = document.querySelector('#activity-table-body');
    const seenIds = new Set();

    function createRow(r, markNew = false) {
      const tr = document.createElement('tr');
      if (r.id) tr.dataset.id = r.id;
      const tdTime = document.createElement('td'); tdTime.textContent = r.created_at || '';
      const tdActor = document.createElement('td'); tdActor.textContent = r.actor_name || 'System';
      const tdAction = document.createElement('td'); tdAction.textContent = r.action || '';
      const tdTarget = document.createElement('td'); tdTarget.textContent = r.target || '';
      const tdDetails = document.createElement('td'); tdDetails.textContent = r.details || '';
      tr.appendChild(tdTime);
      tr.appendChild(tdActor);
      tr.appendChild(tdAction);
      tr.appendChild(tdTarget);
      tr.appendChild(tdDetails);
      if (markNew) {
        tr.classList.add('new-activity');
        // remove highlight after 3s
        setTimeout(()=> tr.classList.remove('new-activity'), 3000);
      }
      return tr;
    }

    let initialLoad = true;
    async function fetchActivity() {
      try {
        const res = await fetch('api/activity_logs.php');
        if (!res.ok) throw new Error('Network response was not ok');
        const json = await res.json();
        if (!activityTableBody) return;

        if (initialLoad) {
          // render full list and mark seen ids
          activityTableBody.innerHTML = '';
          json.forEach(r => {
            if (r.id) seenIds.add(r.id);
            activityTableBody.appendChild(createRow(r, false));
          });
          initialLoad = false;
        } else {
          // detect new items (those with ids not seen before)
          const newItems = [];
          for (const r of json) {
            if (r.id && !seenIds.has(r.id)) {
              newItems.push(r);
            }
          }
          if (newItems.length) {
            // prepend in chronological order (newest first in feed)
            newItems.reverse().forEach(r => {
              const tr = createRow(r, true);
              activityTableBody.insertBefore(tr, activityTableBody.firstChild);
              if (r.id) seenIds.add(r.id);
            });
            // keep table length reasonable
            while (activityTableBody.children.length > 200) activityTableBody.removeChild(activityTableBody.lastChild);
          }
        }
      } catch (e) {
        console.error('Failed to fetch activity', e);
      }
    }
    fetchActivity();
    // poll faster for near-real-time updates
    setInterval(fetchActivity, 4000);
  })();
</script>

<?php include 'includes/footer.php'; ?>