<?php declare(strict_types=1);
/**
 * Front Controller — public/index.php
 *
 * FIXES APPLIED:
 * 1. declare(strict_types=1) is on line 1 — no preceding whitespace/newlines
 * 2. Subfolder base-path stripped from REQUEST_URI before routing
 *    e.g. /myproject/student-registration-system/public/students -> /students
 * 3. Image serving path also uses the stripped URI
 */

define('ROOT_PATH', dirname(__DIR__));

// Load Composer autoloader (vendor/autoload.php built by: composer install)
require ROOT_PATH . '/vendor/autoload.php';

// Load .env  — safeLoad() won't throw if .env is missing
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->safeLoad();

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// ---------------------------------------------------------------
// Detect the subfolder base path so the router only sees the part
// AFTER the public/ directory, regardless of where XAMPP is installed.
//
// Example install at:  /myproject/student-registration-system/public/
// REQUEST_URI will be: /myproject/student-registration-system/public/students
// SCRIPT_NAME will be: /myproject/student-registration-system/public/index.php
// base path becomes:   /myproject/student-registration-system/public
// routeUri becomes:    /students   <-- what the router needs
// ---------------------------------------------------------------
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$uriPath    = parse_url($requestUri, PHP_URL_PATH);

// Strip the base directory prefix from the URI path
if ($scriptDir !== '' && str_starts_with($uriPath, $scriptDir)) {
    $routeUri = substr($uriPath, strlen($scriptDir));
} else {
    $routeUri = $uriPath;
}
$routeUri = rtrim($routeUri, '/') ?: '/';

// Remember-me auto-login
if (empty($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $userModel = new App\Models\UserModel();
    $user      = $userModel->findByRememberToken($_COOKIE['remember_token']);
    if ($user && $user['email_verified_at']) {
        session_regenerate_id(true);
        $_SESSION['user_id']       = $user['id'];
        $_SESSION['username']      = $user['username'];
        $_SESSION['user_role']     = $user['role'];
        $_SESSION['last_activity'] = time();
    }
}

// Serve profile images stored outside public/
if (str_starts_with($routeUri, '/storage/uploads/profiles/')) {
    $filename = basename(urldecode(substr($routeUri, strlen('/storage/uploads/profiles/'))));
    $filePath = ROOT_PATH . '/storage/uploads/profiles/' . $filename;
    if (file_exists($filePath) && is_file($filePath)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        if (in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
            header('Content-Type: ' . $mime);
            header('Cache-Control: public, max-age=3600');
            readfile($filePath);
            exit;
        }
    }
    http_response_code(404);
    exit;
}

// Boot router and dispatch
$router = new App\Core\Router();
require ROOT_PATH . '/routes/web.php';
$router->dispatch($routeUri, $_SERVER['REQUEST_METHOD'] ?? 'GET');
