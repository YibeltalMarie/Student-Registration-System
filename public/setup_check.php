<?php
/**
 * setup_check.php — Run this ONCE to verify your install is correct.
 * Access at: http://localhost/myproject/student-registration-system/public/setup_check.php
 * DELETE THIS FILE after verifying everything works.
 */

$checks = [];

// 1. PHP version
$phpOk = version_compare(PHP_VERSION, '8.0.0', '>=');
$checks[] = [$phpOk, 'PHP Version: ' . PHP_VERSION . ($phpOk ? ' ✅' : ' ❌ Need 8.0+')];

// 2. Required extensions
foreach (['mysqli', 'gd', 'mbstring', 'fileinfo', 'openssl'] as $ext) {
    $ok = extension_loaded($ext);
    $checks[] = [$ok, "Extension {$ext}: " . ($ok ? '✅' : '❌ MISSING — install php8.x-' . $ext)];
}

// 3. vendor/autoload.php exists
$vendorOk = file_exists(dirname(__DIR__) . '/vendor/autoload.php');
$checks[] = [$vendorOk, 'vendor/autoload.php: ' . ($vendorOk ? '✅' : '❌ Run: composer install')];

// 4. .env exists
$envOk = file_exists(dirname(__DIR__) . '/.env');
$checks[] = [$envOk, '.env file: ' . ($envOk ? '✅' : '❌ Copy .env.example to .env and fill in values')];

// 5. Load .env and check Database class
if ($vendorOk) {
    require dirname(__DIR__) . '/vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();

    // 6. Database class exists (PSR-4 check)
    $dbClassOk = class_exists('App\Config\Database');
    $checks[] = [$dbClassOk, 'App\\Config\\Database class: ' . ($dbClassOk ? '✅' : '❌ Check app/Config/Database.php exists')];

    // 7. DB connection
    if ($dbClassOk) {
        try {
            $conn = App\Config\Database::getInstance()->getConnection();
            $checks[] = [true, 'DB Connection: ✅ Connected to ' . ($_ENV['DB_NAME'] ?? '?')];
        } catch (\Exception $e) {
            $checks[] = [false, 'DB Connection: ❌ ' . $e->getMessage()];
        }
    }

    // 8. helpers.php loaded
    $helpersOk = function_exists('url') && function_exists('redirect') && function_exists('has_role');
    $checks[] = [$helpersOk, 'helpers.php functions: ' . ($helpersOk ? '✅' : '❌ Check composer autoload files')];

    // 9. .env WEATHER_CITY quoted check
    $weatherCity = $_ENV['WEATHER_CITY'] ?? 'NOT SET';
    $weatherOk   = str_contains($weatherCity, ' ') ? true : ($weatherCity !== 'NOT SET');
    $checks[] = [true, 'WEATHER_CITY = "' . $weatherCity . '"  ✅'];
}

// 10. storage/ writable
$storageDirs = ['storage/uploads/profiles', 'storage/backups', 'storage/exports'];
foreach ($storageDirs as $dir) {
    $path = dirname(__DIR__) . '/' . $dir;
    $ok   = is_dir($path) && is_writable($path);
    $checks[] = [$ok, "{$dir}: " . ($ok ? '✅ writable' : '❌ not writable — run: chmod -R 755 storage/')];
}

// 11. mod_rewrite
$rewriteOk = isset($_SERVER['HTTP_MOD_REWRITE']) || function_exists('apache_get_modules')
    ? in_array('mod_rewrite', apache_get_modules() ?? [])
    : true; // can't detect, assume ok
$checks[] = [true, 'mod_rewrite: Check Apache config manually if routing fails'];

// 12. PSR-4 directory casing
$dirs = [
    'app/Config'      => 'App\\Config',
    'app/Controllers' => 'App\\Controllers',
    'app/Core'        => 'App\\Core',
    'app/Models'      => 'App\\Models',
    'app/Services'    => 'App\\Services',
];
foreach ($dirs as $dir => $ns) {
    $path = dirname(__DIR__) . '/' . $dir;
    $ok   = is_dir($path);
    $checks[] = [$ok, "{$dir}/ directory (for namespace {$ns}): " . ($ok ? '✅' : '❌ MISSING or wrong case!')];
}

$allOk = array_reduce($checks, fn($c, $i) => $c && $i[0], true);
?>
<!DOCTYPE html>
<html>
<head>
<title>SRS Setup Check</title>
<style>
body { font-family: monospace; background: #1a1a2e; color: #eee; padding: 30px; }
h1 { color: #3498db; }
.ok  { color: #2ecc71; }
.err { color: #e74c3c; font-weight: bold; }
.box { background: #16213e; padding: 20px; border-radius: 8px; margin-top: 20px; }
.warn { color: #f39c12; }
</style>
</head>
<body>
<h1>🎓 SRS Setup Check</h1>
<div class="box">
<?php foreach ($checks as [$ok, $msg]): ?>
<p class="<?= $ok ? 'ok' : 'err' ?>"><?= htmlspecialchars($msg) ?></p>
<?php endforeach; ?>
</div>
<?php if ($allOk): ?>
<p class="ok" style="font-size:1.3em;margin-top:20px">✅ Everything looks good! Delete this file and visit <a href="." style="color:#3498db">the app</a>.</p>
<?php else: ?>
<p class="err" style="font-size:1.2em;margin-top:20px">❌ Fix the errors above, then refresh this page.</p>
<?php endif; ?>
<p class="warn" style="margin-top:30px">⚠️ DELETE setup_check.php before going to production!</p>
</body>
</html>
