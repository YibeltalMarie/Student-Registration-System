<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'SRS') ?></title>
<link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body>
<div class="layout" id="appLayout">

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon-wrap">🎓</div>
      <div>
        <div class="brand-name">SRS</div>
        <div class="brand-tagline"><?= is_admin() ? 'Admin Portal' : 'Student Portal' ?></div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>
      <a href="<?= url('') ?>" class="nav-item <?= active('') ?>" data-tooltip="Dashboard">
        <span class="nav-icon">📊</span><span class="nav-label">Dashboard</span>
      </a>

      <?php if (is_admin()): ?>
      <!-- ── ADMIN NAV ── -->
      <div class="nav-section-label">Academic</div>
      <a href="<?= url('students') ?>" class="nav-item <?= active('students') ?>" data-tooltip="Students">
        <span class="nav-icon">👨‍🎓</span><span class="nav-label">Students</span>
      </a>
      <a href="<?= url('departments') ?>" class="nav-item <?= active('departments') ?>" data-tooltip="Departments">
        <span class="nav-icon">🏛️</span><span class="nav-label">Departments</span>
      </a>
      <a href="<?= url('courses') ?>" class="nav-item <?= active('courses') ?>" data-tooltip="Courses">
        <span class="nav-icon">📚</span><span class="nav-label">Courses</span>
      </a>
      <a href="<?= url('enrollments') ?>" class="nav-item <?= active('enrollments') ?>" data-tooltip="Enrollments">
        <span class="nav-icon">📋</span><span class="nav-label">Enrollments</span>
      </a>
      <div class="nav-section-label">Reports</div>
      <a href="<?= url('reports') ?>" class="nav-item <?= active('reports') ?>" data-tooltip="Reports">
        <span class="nav-icon">📈</span><span class="nav-label">Reports</span>
      </a>
      <a href="<?= url('activity-logs') ?>" class="nav-item <?= active('activity-logs') ?>" data-tooltip="Activity Logs">
        <span class="nav-icon">🔍</span><span class="nav-label">Activity Logs</span>
      </a>

      <?php else: ?>
      <!-- ── STUDENT NAV ── -->
      <div class="nav-section-label">My Account</div>
      <?php $myId = get_my_student_id(); ?>
      <a href="<?= url('students/show/' . ($myId ?? 0)) ?>" class="nav-item <?= active('students/show') ?>" data-tooltip="My Profile">
        <span class="nav-icon">👤</span><span class="nav-label">My Profile</span>
      </a>
      <a href="<?= url('courses') ?>" class="nav-item <?= active('courses') ?>" data-tooltip="My Courses">
        <span class="nav-icon">📚</span><span class="nav-label">My Courses</span>
      </a>
      <a href="<?= url('enrollments') ?>" class="nav-item <?= active('enrollments') ?>" data-tooltip="My Enrollments">
        <span class="nav-icon">📋</span><span class="nav-label">My Enrollments</span>
      </a>
      <div class="nav-section-label">Reports</div>
      <a href="<?= url('reports') ?>" class="nav-item <?= active('reports') ?>" data-tooltip="My Report">
        <span class="nav-icon">📈</span><span class="nav-label">My Report</span>
      </a>
      <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?></div>
        <div class="user-info">
          <div class="user-name"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></div>
          <div class="user-role"><?= is_admin() ? 'Administrator' : 'Student' ?></div>
        </div>
      </div>
    </div>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <button class="topbar-toggle" id="sidebarToggle" title="Toggle sidebar">☰</button>
      <div class="topbar-breadcrumb">
        <span>SRS</span>
        <?php if (isset($pageTitle)): ?>
        <span class="bc-sep">›</span>
        <span class="bc-current"><?= htmlspecialchars($pageTitle) ?></span>
        <?php endif; ?>
      </div>
      <div class="topbar-right">
        <div class="topbar-user">
          <span>👤</span> <?= htmlspecialchars($_SESSION['username'] ?? '') ?>
          <span class="badge <?= is_admin() ? 'badge-admin' : 'badge-enrolled' ?>" style="margin-left:6px">
            <?= is_admin() ? 'Admin' : 'Student' ?>
          </span>
        </div>
        <form method="POST" action="<?= url('logout') ?>" style="display:inline">
          <?= csrf_field() ?>
          <button type="submit" class="topbar-btn" title="Sign out">🚪</button>
        </form>
      </div>
    </header>

    <div class="page-body">
      <?php
      $flashTypes = ['success','error','warning','info'];
      $hasFlash   = false;
      foreach ($flashTypes as $t) {
          if (!empty($_SESSION['flash'][$t])) { $hasFlash = true; break; }
      }
      ?>
      <?php if ($hasFlash): ?>
      <div class="flash-container">
        <?php foreach ($flashTypes as $t): $m = get_flash($t); if (!$m) continue; ?>
        <div class="alert alert-<?= $t ?>">
          <span class="alert-icon"><?= $t==='success'?'✅':($t==='error'?'❌':($t==='warning'?'⚠️':'ℹ️')) ?></span>
          <span class="alert-text"><?= $m ?></span>
          <button class="alert-close" onclick="this.parentElement.remove()">×</button>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="page-content">
        <?= $content ?? '' ?>
      </div>

      <footer class="page-footer">
        © <?= date('Y') ?> <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'SRS') ?> &nbsp;·&nbsp; PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?>
      </footer>
    </div>
  </div>
</div>
<script src="<?= url('js/app.js') ?>"></script>
</body>
</html>
