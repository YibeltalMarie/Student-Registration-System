<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'SRS') ?></title>
<link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body class="auth-page">

<div class="auth-left">
  <div class="brand-logo">🔐</div>
  <h1>Password Change Required</h1>
  <p>For your security, you must change your default password before accessing the system.</p>
  <ul class="feature-list">
    <li><span class="fi">✅</span> Minimum 8 characters</li>
    <li><span class="fi">✅</span> Mix letters and numbers</li>
    <li><span class="fi">✅</span> Keep it confidential</li>
  </ul>
</div>

<div class="auth-right">
<div class="auth-card">
  <div class="auth-card-header">
    <h2>Set Your New Password</h2>
    <p>Logged in as <strong><?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong></p>
  </div>

  <?php $err = get_flash('error'); if ($err): ?>
  <div class="alert alert-error mb-4">
    <span class="alert-icon">❌</span>
    <span class="alert-text"><?= htmlspecialchars($err) ?></span>
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
  </div>
  <?php endif; ?>

  <div class="alert alert-warning mb-4">
    <span class="alert-icon">⚠️</span>
    <span class="alert-text">You are using a temporary password. You must set a new one to continue.</span>
  </div>

  <form method="POST" action="<?= url('change-password') ?>">
    <?= csrf_field() ?>

    <div class="form-group mb-4">
      <label>Current (Old) Password <span class="req">*</span></label>
      <div class="input-icon-wrap">
        <span class="input-icon">🔑</span>
        <input type="password" name="old_password" class="form-control"
               placeholder="Enter your current password" required autofocus>
      </div>
    </div>

    <div class="form-group mb-4">
      <label>New Password <span class="req">*</span>
        <span class="form-hint">(min 8 characters)</span>
      </label>
      <div class="input-icon-wrap">
        <span class="input-icon">🔒</span>
        <input type="password" name="password" id="pwd" class="form-control"
               placeholder="Enter new password" required minlength="8"
               oninput="checkStrength(this.value)">
      </div>
      <div id="strengthBar" style="height:4px;border-radius:2px;margin-top:6px;background:#e2e8f0;overflow:hidden">
        <div id="strengthFill" style="height:100%;width:0;transition:width .3s,background .3s"></div>
      </div>
      <span id="strengthLabel" class="form-hint mt-1" style="display:block"></span>
    </div>

    <div class="form-group mb-4">
      <label>Confirm New Password <span class="req">*</span></label>
      <div class="input-icon-wrap">
        <span class="input-icon">🔒</span>
        <input type="password" name="password_confirm" class="form-control"
               placeholder="Repeat new password" required>
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg">Save New Password →</button>
  </form>
</div>
</div>

<script>
function checkStrength(val) {
  const fill  = document.getElementById('strengthFill');
  const label = document.getElementById('strengthLabel');
  let score = 0;
  if (val.length >= 8)  score++;
  if (val.length >= 12) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const pct   = (score / 5) * 100;
  const color = score <= 1 ? '#ef4444' : score <= 3 ? '#f59e0b' : '#10b981';
  const text  = score <= 1 ? 'Weak' : score <= 3 ? 'Fair' : 'Strong';
  fill.style.width      = pct + '%';
  fill.style.background = color;
  label.style.color     = color;
  label.textContent     = val.length ? text : '';
}
</script>
</body>
</html>
