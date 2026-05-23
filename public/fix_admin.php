<?php
/**
 * fix_admin.php — Run ONCE after first install.
 * Sets correct password hashes using your .env pepper.
 * DELETE this file after running it.
 *
 * Access: http://localhost/myproject/student-registration-system/public/fix_admin.php
 */
define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->safeLoad();

$pepper = $_ENV['PASSWORD_PEPPER'] ?? '';
$host   = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'student_registration_system';
$user   = $_ENV['DB_USER'] ?? 'root';
$pass   = $_ENV['DB_PASS'] ?? '';

$db = new mysqli($host, $user, $pass, $dbname);
if ($db->connect_error) {
    die('<h2 style="color:red">DB Error: ' . htmlspecialchars($db->connect_error) . '</h2>');
}

// Ensure must_change_password column exists
$db->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS must_change_password TINYINT(1) NOT NULL DEFAULT 0");

// Update role ENUM to support admin/student (run even if already correct)
$db->query("ALTER TABLE users MODIFY COLUMN role ENUM('admin','student') NOT NULL DEFAULT 'student'");

$results = [];

// 1. Fix admin password = "Admin@123" + pepper
$adminHash = password_hash('Admin@123' . $pepper, PASSWORD_BCRYPT);
$stmt = $db->prepare("UPDATE users SET password=?, must_change_password=0, email_verified_at=NOW(), role='admin' WHERE username='admin'");
$stmt->bind_param('s', $adminHash);
$stmt->execute();
if ($stmt->affected_rows === 0) {
    // Insert admin if missing
    $stmt2 = $db->prepare("INSERT IGNORE INTO users (username,email,password,role,must_change_password,email_verified_at) VALUES ('admin','admin@example.com',?,'admin',0,NOW())");
    $stmt2->bind_param('s', $adminHash);
    $stmt2->execute();
    $stmt2->close();
    $results[] = ['Admin account created', 'Username: admin / Password: Admin@123'];
} else {
    $results[] = ['Admin password updated', 'Username: admin / Password: Admin@123'];
}
$stmt->close();

// 2. Fix sample student passwords = "12345678" + pepper  (must_change_password=1)
$studentHash = password_hash('12345678' . $pepper, PASSWORD_BCRYPT);
$stmt3 = $db->prepare("UPDATE users SET password=?, must_change_password=1, role='student' WHERE role='student' OR username LIKE 'STU%'");
$stmt3->bind_param('s', $studentHash);
$stmt3->execute();
$studentCount = $stmt3->affected_rows;
$stmt3->close();
$results[] = ["Student passwords updated ({$studentCount} accounts)", 'Password: 12345678 (must change on first login)'];

$db->close();
?>
<!DOCTYPE html>
<html>
<head>
<title>Fix Passwords</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0;
       display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
.box { background: #1e293b; padding: 40px 48px; border-radius: 16px;
       max-width: 540px; width: 100%; border: 1px solid #334155; }
h1 { color: #6366f1; font-size: 22px; margin-bottom: 24px; }
.result { margin-bottom: 16px; padding: 14px 18px; background: #0f172a;
          border-radius: 8px; border-left: 4px solid #10b981; }
.result strong { color: #10b981; display: block; margin-bottom: 4px; }
.result span { font-family: monospace; font-size: 13px; color: #94a3b8; }
.warn { background: #451a03; border: 1px solid #92400e; border-radius: 8px;
        padding: 14px 18px; color: #fcd34d; font-size: 13px; margin-top: 24px; }
.warn code { background: #7c2d12; padding: 2px 8px; border-radius: 4px; color: #fed7aa; }
a { display: inline-block; margin-top: 20px; padding: 12px 28px;
    background: #6366f1; color: #fff; text-decoration: none;
    border-radius: 8px; font-weight: 700; font-size: 15px; }
a:hover { background: #4f46e5; }
</style>
</head>
<body>
<div class="box">
  <h1>🔐 Passwords Fixed!</h1>
  <?php foreach ($results as [$label, $detail]): ?>
  <div class="result">
    <strong>✅ <?= htmlspecialchars($label) ?></strong>
    <span><?= htmlspecialchars($detail) ?></span>
  </div>
  <?php endforeach; ?>
  <div class="warn">
    ⚠️ <strong>Delete this file immediately after use!</strong><br><br>
    Run: <code>rm /opt/lampp/htdocs/myproject/student-registration-system/public/fix_admin.php</code>
  </div>
  <a href="/">→ Go to Login</a>
</div>
</body>
</html>
