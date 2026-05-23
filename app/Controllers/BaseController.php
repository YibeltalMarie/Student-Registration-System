<?php

namespace App\Controllers;

use App\Config\Database;
use mysqli;

abstract class BaseController
{
    protected mysqli $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    protected function view(string $view, array $data = []): void
    {
        $viewPath = __DIR__ . '/../views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo '<h1>View not found: ' . htmlspecialchars($view) . '</h1>';
            exit;
        }

        $noLayout = [
            'auth.login',
            'auth.register',
            'auth.forgot_password',
            'auth.reset_password',
            'auth.change_password',
            'auth.restore_default',
            'errors.404',
        ];

        extract($data, EXTR_SKIP);

        if (in_array($view, $noLayout, true)) {
            include $viewPath;
            return;
        }

        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        include __DIR__ . '/../views/layout/main.php';
    }

    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function checkCsrf(): void
    {
        $submitted = $_POST['csrf_token'] ?? '';
        $stored    = $_SESSION['csrf_token'] ?? '';

        if (!hash_equals($stored, $submitted)) {
            flash('error', 'Invalid security token. Please try again.');
            unset($_SESSION['csrf_token']);
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            if ($referer) {
                header('Location: ' . $referer);
            } else {
                redirect('');
            }
            exit;
        }
        unset($_SESSION['csrf_token']);
    }

    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            flash('error', 'Please log in to continue.');
            redirect('login');
        }

        // Session timeout
        $lifetime = (int)($_ENV['SESSION_LIFETIME'] ?? 3600);
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $lifetime) {
            session_unset();
            session_destroy();
            redirect('login');
        }
        $_SESSION['last_activity'] = time();

        // Force password change — redirect everywhere except change-password itself
        if (!empty($_SESSION['must_change_password'])) {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
            if ($scriptDir && str_starts_with($uri, $scriptDir)) {
                $uri = substr($uri, strlen($scriptDir));
            }
            $uri = rtrim($uri, '/') ?: '/';
            if ($uri !== '/change-password') {
                redirect('change-password');
            }
        }
    }

    // Only admin can access — replaces old requireRole('admin','staff')
    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (!is_admin()) {
            flash('error', 'Access denied. Admin only.');
            redirect('');
        }
    }

    // Legacy alias — kept so existing controllers still compile
    // Accepts 'admin' or 'student'; if caller passes 'staff' it maps to 'admin'
    protected function requireRole(string ...$roles): void
    {
        $this->requireAuth();
        $userRole = $_SESSION['user_role'] ?? '';
        // Map legacy 'staff'/'viewer' to 'admin' for backward compatibility
        $normalised = array_map(fn($r) => in_array($r, ['staff','viewer']) ? 'admin' : $r, $roles);
        if (!in_array($userRole, $normalised, true)) {
            flash('error', 'Access denied. Insufficient permissions.');
            redirect('');
        }
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $ruleList = explode('|', $ruleString);
            $value    = $data[$field] ?? null;
            $label    = ucfirst(str_replace('_', ' ', $field));

            foreach ($ruleList as $rule) {
                if (isset($errors[$field])) break;

                if ($rule === 'required') {
                    if ($value === null || trim((string)$value) === '') {
                        $errors[$field] = "{$label} is required.";
                    }
                } elseif ($rule === 'email') {
                    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = "{$label} must be a valid email address.";
                    }
                } elseif (str_starts_with($rule, 'min:')) {
                    $min = (int)substr($rule, 4);
                    if ($value !== null && strlen((string)$value) < $min) {
                        $errors[$field] = "{$label} must be at least {$min} characters.";
                    }
                } elseif (str_starts_with($rule, 'max:')) {
                    $max = (int)substr($rule, 4);
                    if ($value !== null && strlen((string)$value) > $max) {
                        $errors[$field] = "{$label} must not exceed {$max} characters.";
                    }
                } elseif ($rule === 'numeric') {
                    if ($value !== null && $value !== '' && !is_numeric($value)) {
                        $errors[$field] = "{$label} must be a number.";
                    }
                } elseif ($rule === 'integer') {
                    if ($value !== null && $value !== '' && !ctype_digit((string)$value)) {
                        $errors[$field] = "{$label} must be an integer.";
                    }
                } elseif ($rule === 'date') {
                    if ($value && strtotime($value) === false) {
                        $errors[$field] = "{$label} must be a valid date.";
                    }
                } elseif (str_starts_with($rule, 'in:')) {
                    $allowed = explode(',', substr($rule, 3));
                    if ($value && !in_array($value, $allowed, true)) {
                        $errors[$field] = "{$label} must be one of: " . implode(', ', $allowed) . '.';
                    }
                } elseif (str_starts_with($rule, 'min_age:')) {
                    $minAge = (int)substr($rule, 8);
                    if ($value) {
                        try {
                            $age = (new \DateTime())->diff(new \DateTime($value))->y;
                            if ($age < $minAge) {
                                $errors[$field] = "{$label} — must be at least {$minAge} years old.";
                            }
                        } catch (\Exception) {
                            $errors[$field] = "{$label} must be a valid date.";
                        }
                    }
                } elseif (str_starts_with($rule, 'regex:')) {
                    $pattern = substr($rule, 6);
                    if ($value && !preg_match($pattern, $value)) {
                        $errors[$field] = "{$label} format is invalid.";
                    }
                }
            }
        }

        return $errors;
    }

    protected function logActivity(string $action, string $entity, int $entityId, ?array $old = null, ?array $new = null): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $ip     = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $oldJ   = $old ? json_encode($old) : null;
        $newJ   = $new ? json_encode($new) : null;

        $stmt = $this->db->prepare(
            "INSERT INTO activity_logs (user_id,action,entity_type,entity_id,old_data,new_data,ip_address,created_at)
             VALUES (?,?,?,?,?,?,?,NOW())"
        );
        if ($stmt) {
            $stmt->bind_param('ississs', $userId, $action, $entity, $entityId, $oldJ, $newJ, $ip);
            $stmt->execute();
            $stmt->close();
        }
    }
}
