<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\StudentModel;
use App\Services\EmailService;

class AuthController extends BaseController
{
    private UserModel    $userModel;
    private StudentModel $studentModel;
    private EmailService $emailService;

    public function __construct()
    {
        parent::__construct();
        $this->userModel    = new UserModel();
        $this->studentModel = new StudentModel();
        $this->emailService = new EmailService();
    }

// =======================LOGIN =================================
    public function loginForm(): void
    {
        if (!empty($_SESSION['user_id'])) redirect('');
        $this->view('auth.login');
    }

public function login(): void
    {
        $this->checkCsrf();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        $user = $this->userModel->findByUsername($username);
        if (!$user) {
            flash('error', 'Invalid username or password.');
            redirect('login');
        }

        if ($this->userModel->isLocked($user)) {
            flash('error', 'Account locked. Try again in 15 minutes.');
            redirect('login');
        }

        // ====== Verify password: try with pepper, fall back to without (upgrades on match) =======
        $pepper   = $_ENV['PASSWORD_PEPPER'] ?? '';
        $verified = false;

        if ($pepper !== '') {
            $verified = password_verify($password . $pepper, $user['password']);
        }
        if (!$verified) {
            if (password_verify($password, $user['password'])) {
                $verified = true;
                if ($pepper !== '') {
                    $this->userModel->updatePassword(
                        (int)$user['id'],
                        password_hash($password . $pepper, PASSWORD_BCRYPT)
                    );
                }
            }
        }

        if (!$verified) {
            $this->userModel->incrementFailedAttempts((int)$user['id']);
            flash('error', 'Invalid username or password.');
            redirect('login');
        }

        // ====== Email verification — skip for admin =======
        if (empty($user['email_verified_at']) && $user['role'] !== 'admin') {
            if (!$this->emailService->isSmtpConfigured()) {
                $token = $user['email_verification_token'] ?? '';
                if (!$token) {
                    $token = bin2hex(random_bytes(32));
                    $this->userModel->setVerificationToken((int)$user['id'], $token);
                }
                $link = url('verify-email?token=' . urlencode($token));
                flash('warning',
                    'Email not verified. <a href="' . htmlspecialchars($link) .
                    '" style="font-weight:700;text-decoration:underline">Click here to verify instantly</a>.'
                );
            } else {
                flash('error', 'Please verify your email before logging in.');
            }
            redirect('login');
        }

        // ======  Successful login =======
        session_regenerate_id(true);
        $this->userModel->resetFailedAttempts((int)$user['id']);

        $_SESSION['user_id']              = $user['id'];
        $_SESSION['username']             = $user['username'];
        $_SESSION['user_role']            = $user['role'];
        $_SESSION['last_activity']        = time();
        $_SESSION['must_change_password'] = (bool)($user['must_change_password'] ?? false);

        // ====== If student role, load their student record id into session =======
        if ($user['role'] === 'student') {
            $student = $this->studentModel->findByEmail($user['email']);
            if ($student) {
                $_SESSION['student_db_id']     = $student['id'];
                $_SESSION['student_id_code']   = $student['student_id'];
                $_SESSION['student_dept_id']   = $student['department_id'];
            }
        }

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->userModel->setRememberToken((int)$user['id'], $token);
            setcookie('remember_token', $token, [
                'expires'  => time() + 30 * 24 * 3600,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        if (!empty($_SESSION['must_change_password'])) {
            redirect('change-password');
        }

        redirect('');
    }

       // =============================LOGOUT ===============================
    public function logout(): void
    {
        $this->checkCsrf();
        if (!empty($_SESSION['user_id'])) {
            $this->userModel->clearRememberToken((int)$_SESSION['user_id']);
        }
        session_unset();
        session_destroy();
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        redirect('login');
    }

    // =============================REGISTER ===============================
    public function registerForm(): void
    {
        if (!empty($_SESSION['user_id'])) redirect('');
        $this->view('auth.register');
    }


}