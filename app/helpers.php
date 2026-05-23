<?php

/**
 * helpers.php — Global helper functions
 *
 * Loaded via composer.json "files" autoload — no namespace needed.
 * These functions are available everywhere in the app.
 *
 * FIX: url() now reads APP_URL from .env so subfolder installs work correctly.
 *      e.g. APP_URL=http://localhost/myproject/student-registration-system/public
 *      url('students') => http://localhost/myproject/student-registration-system/public/students
 */

// XSS protection — escape all output (Week 5)
function e(?string $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// URL helper — reads APP_URL from .env
function url(string $path = ''): string
{
    $base = rtrim($_ENV['APP_URL'] ?? '', '/');
    if ($path === '') {
        return $base;
    }
    return $base . '/' . ltrim($path, '/');
}

// CSRF token generation and retrieval (Week 4)
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF hidden input field
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// Flash message setter
function flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

// Flash message getter — removes after reading (one-time display)
function get_flash(string $key): ?string
{
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

// Role check helper (Week 5)
function has_role(string ...$roles): bool
{
    $userRole = $_SESSION['user_role'] ?? '';
    return in_array($userRole, $roles, true);
}

// Check if user is logged in
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

// Redirect helper — uses url() so subfolder paths are correct
function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

// Pagination helper
function paginate(int $total, int $perPage, int $currentPage, string $baseUrl = ''): array
{
    $totalPages = (int)ceil($total / max(1, $perPage));
    return [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'base_url'     => $baseUrl,
        'has_prev'     => $currentPage > 1,
        'has_next'     => $currentPage < $totalPages,
        'offset'       => ($currentPage - 1) * $perPage,
    ];
}

// Format a date string
function format_date(?string $date, string $format = 'd M Y'): string
{
    if (!$date) return 'N/A';
    $ts = strtotime($date);
    return $ts ? date($format, $ts) : 'N/A';
}

// Convert numeric grade to letter grade
function letter_grade(float $grade): string
{
    return match (true) {
        $grade >= 90 => 'A',
        $grade >= 80 => 'B',
        $grade >= 70 => 'C',
        $grade >= 60 => 'D',
        default      => 'F',
    };
}

// Active nav link helper — compares current URI path to a route segment
function active(string $path): string
{
    $scriptDir  = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    $uriPath    = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

    // Strip the subfolder prefix to get the route portion
    if ($scriptDir !== '' && str_starts_with($uriPath, $scriptDir)) {
        $uriPath = substr($uriPath, strlen($scriptDir));
    }
    $uriPath = rtrim($uriPath, '/') ?: '/';

    if ($path === '/' || $path === '') {
        return $uriPath === '/' ? 'active' : '';
    }

    return str_starts_with($uriPath, '/' . ltrim($path, '/')) ? 'active' : '';
}

// Check if current user is a student
function is_student(): bool
{
    return ($_SESSION['user_role'] ?? '') === 'student';
}

// Check if current user is admin
function is_admin(): bool
{
    return ($_SESSION['user_role'] ?? '') === 'admin';
}

// Get the student record linked to the logged-in user (for student role)
function get_my_student_id(): ?int
{
    return $_SESSION['student_db_id'] ?? null;
}
