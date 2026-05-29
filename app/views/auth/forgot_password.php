<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'SRS') ?></title>
<link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body class="auth-page">
<div class="auth-left">
    <div class="brand-logo">🔑</div>
    <h1>Password Reset</h1>
    <p>Enter your registered email and we'll send you a reset link.</p>
</div>
<div class="auth-right">
<div class="auth-card">
    <div class="auth-card-header">
        <h2>Forgot your password?</h2>
        <p>We'll help you get back into your account</p>
    </div>
    <?php foreach (['success','error','info','warning'] as $t):
        $m = get_flash($t); if (!$m) continue; ?>
    <div class="alert alert-<?= $t ?> mb-3">
        <span class="alert-icon"><?= $t==='success'?'✅':($t==='error'?'❌':($t==='warning'?'⚠️':'ℹ️')) ?></span>
        <span class="alert-text"><?= htmlspecialchars($m) ?></span>
        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    </div>
    <?php endforeach; ?>
    <form method="POST" action="<?= url('forgot-password') ?>">
        <?= csrf_field() ?>
        <div class="form-group mb-4">
            <label>Email Address</label>
            <div class="input-icon-wrap">
                <span class="input-icon">📧</span>
                <input type="email" name="email" class="form-control"
                       placeholder="Enter your registered email" required autofocus>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Send Reset Link</button>
    </form>
    <div class="auth-links"><a href="<?= url('login') ?>">← Back to Sign In</a></div>
</div>
</div>
</body>
</html>
