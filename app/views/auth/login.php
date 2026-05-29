<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'SRS') ?></title>
<link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body class="auth-page">

<!-- Left branding panel -->
<div class="auth-left">
  <div class="brand-logo">🎓</div>
  <h1><?= htmlspecialchars($_ENV['APP_NAME'] ?? 'Student Registration System') ?></h1>
  <p>A complete academic management platform for students, courses, and departments.</p>
  <ul class="feature-list">
    <li><span class="fi">👨‍🎓</span> Manage students &amp; enrollments</li>
    <li><span class="fi">📚</span> Course &amp; department management</li>
    <li><span class="fi">📊</span> Reports, rankings &amp; analytics</li>
    <li><span class="fi">🔐</span> Role-based access control</li>
  </ul>
</div>

<!-- Right form panel -->
<div class="auth-right">
<div class="auth-card">

  <div class="auth-card-header">
    <h2>Welcome back 👋</h2>
    <p>Sign in to your account to continue</p>
  </div>

  <?php foreach (['success','error','warning','info'] as $t):
    $m = get_flash($t); if (!$m) continue; ?>
  <div class="alert alert-<?= $t ?> mb-3">
    <span class="alert-icon"><?= $t==='success'?'✅':($t==='error'?'❌':($t==='warning'?'⚠️':'ℹ️')) ?></span>
    <span class="alert-text"><?= $m /* may contain safe HTML link */ ?></span>
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
  </div>
  <?php endforeach; ?>

  <form method="POST" action="<?= url('login') ?>">
    <?= csrf_field() ?>

    <div class="form-group mb-4">
      <label for="username">Username</label>
      <div class="input-icon-wrap">
        <span class="input-icon">👤</span>
        <input type="text" id="username" name="username"
               class="form-control" placeholder="Enter your username"
               required autofocus autocomplete="username">
      </div>
    </div>

    <div class="form-group mb-3">
      <label for="password">Password</label>
      <div class="input-icon-wrap">
        <span class="input-icon">🔒</span>
        <input type="password" id="password" name="password"
               class="form-control" placeholder="Enter your password"
               required autocomplete="current-password">
      </div>
    </div>

    <div class="flex justify-between items-center mb-4" style="font-size:13px">
      <div class="form-check">
        <input type="checkbox" id="remember" name="remember">
        <label for="remember" style="font-weight:400">Remember me</label>
      </div>
      <a href="<?= url('forgot-password') ?>"
         style="color:var(--accent);text-decoration:none;font-weight:600">
        Forgot password?
      </a>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In →</button>
  </form>

  <div class="auth-links">
    Don't have an account? <a href="<?= url('register') ?>">Create one</a>
  </div>

  <!-- Default admin credentials hint -->
  <div style="margin-top:20px;background:var(--bg);border-radius:8px;padding:14px 16px;border:1px solid var(--border)">
    <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:8px">
      Default Admin Account
    </div>
    <div style="font-family:monospace;font-size:13px;color:var(--text)">
      Username: <strong>admin</strong><br>
      Password: <strong>password</strong>
      <span style="font-size:11px;color:var(--danger);display:block;margin-top:4px">
        ⚠️ Run <code>fix_admin.php</code> first to set Admin@123
      </span>
    </div>
  </div>

</div>
</div>

</body>
</html>
