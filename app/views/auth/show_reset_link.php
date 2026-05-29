<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Link — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'SRS') ?></title>
<link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body class="auth-page">
<div class="auth-left">
    <div class="brand-logo">🔑</div>
    <h1>Password Reset</h1>
    <p>SMTP is not configured. Use the link below to reset your password.</p>
</div>
<div class="auth-right">
<div class="auth-card">
    <div class="auth-card-header">
        <h2>Your Reset Link</h2>
        <p>SMTP not configured — use this link directly</p>
    </div>
    <div class="alert alert-warning mb-4">
        <span class="alert-icon">⚠️</span>
        <span class="alert-text">Email sending is not configured. Copy the link below and open it in your browser to reset your password.</span>
    </div>
    <div style="background:var(--bg);border-radius:8px;padding:16px;word-break:break-all;font-size:12px;font-family:monospace;border:1px solid var(--border);margin-bottom:16px;">
        <?= htmlspecialchars($resetLink ?? '') ?>
    </div>
    <a href="<?= htmlspecialchars($resetLink ?? url('login')) ?>" class="btn btn-primary btn-block">
        → Open Reset Link
    </a>
    <div class="auth-links"><a href="<?= url('login') ?>">← Back to Sign In</a></div>
</div>
</div>
</body>
</html>
