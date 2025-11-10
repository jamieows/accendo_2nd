<aside class="sidebar" aria-label="Admin Navigation">
  <div class="logo">Accendo</div>
  <nav>
    <a href="index.php" class="<?=basename($_SERVER['PHP_SELF'])=='index.php'?'active':''?>">Dashboard</a>
    <a href="manage_users.php" class="<?=basename($_SERVER['PHP_SELF'])=='manage_users.php'?'active':''?>">Manage Users</a>
    <a href="manage_courses.php" class="<?=basename($_SERVER['PHP_SELF'])=='manage，也可以.php'?'active':''?>">Manage Courses</a>
    <a href="settings.php" class="<?=basename($_SERVER['PHP_SELF'])=='settings.php'?'active':''?>">Settings</a>
    <a href="../Auth/logout.php">Logout</a>
  </nav>
  <div style="margin-top:auto;padding-top:20px;">
    <button id="theme-toggle" class="btn" style="width:100%;margin:5px 0;">Dark Mode</button>
    <button id="font-increase" class="btn" style="width:48%;">A+</button>
    <button id="font-decrease" class="btn" style="width:48%;">A-</button>
  </div>
</aside>