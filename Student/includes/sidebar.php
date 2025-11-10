<aside class="sidebar">
  <div class="logo">Accendo</div>
  <nav>
    <a href="index.php" class="<?=basename($_SERVER['PHP_SELF'])=='index.php'?'active':''?>">Dashboard</a>
    <a href="my_courses.php" class="<?=basename($_SERVER['PHP_SELF'])=='my_courses.php'?'active':''?>">My Courses</a>
    <a href="assignments.php" class="<?=basename($_SERVER['PHP_SELF'])=='assignments.php'?'active':''?>">Assignments</a>
    <a href="exams.php" class="<?=basename($_SERVER['PHP_SELF'])=='exams.php'?'active':''?>">Exams</a>
    <a href="profile.php" class="<?=basename($_SERVER['PHP_SELF'])=='profile.php'?'active':''?>">Profile</a>
    <a href="settings.php" class="<?=basename($_SERVER['PHP_SELF'])=='settings.php'?'active':''?>">Settings</a>
    <a href="../Auth/logout.php">Logout</a>
  </nav>
  <div style="margin-top:auto;padding-top:20px;">
    <button id="theme-toggle" class="btn" style="width:100%;margin:5px 0;">Dark Mode</button>
    <button id="font-increase" class="btn" style="width:48%;">A+</button>
    <button id="font-decrease" class="btn" style="width:48%;">A-</button>
  </div>
</aside>