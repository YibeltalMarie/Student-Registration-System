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

     public function register(): void
    {
        $this->checkCsrf();

        $errors = $this->validate($_POST, [
            'username' => 'required|min:3|max:50',
            'email'    => 'required|email',
            'password' => 'required|min:8',
        ]);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = $_POST;
            redirect('register');
        }

        if ($this->userModel->usernameExists(trim($_POST['username']))) {
            flash('error', 'Username already taken.');
            redirect('register');
        }
        if ($this->userModel->emailExists(trim($_POST['email']))) {
            flash('error', 'Email already registered.');
            redirect('register');
        }

        $pepper    = $_ENV['PASSWORD_PEPPER'] ?? '';
        $smtpReady = $this->emailService->isSmtpConfigured();
        $token     = bin2hex(random_bytes(32));
        $autoVerify = !$smtpReady;

        $this->userModel->create([
            'username'                 => trim($_POST['username']),
            'email'                    => trim($_POST['email']),
            'password'                 => password_hash($_POST['password'] . $pepper, PASSWORD_BCRYPT),
            'role'                     => 'student',
            'must_change_password'     => 0,
            'email_verification_token' => $autoVerify ? null : $token,
            'email_verified_at'        => $autoVerify ? date('Y-m-d H:i:s') : null,
        ]);

        if ($autoVerify) {
            flash('success', 'Account created! You can now log in.');
        } else {
            $this->emailService->sendVerificationEmail(trim($_POST['email']), $token);
            flash('success', 'Registered! Check your email to verify your account.');
        }
        redirect('login');
    }

     // =============================VERIFY EMAIL ===============================
    public function verifyEmail(): void
    {
        $token = trim($_GET['token'] ?? '');
        if ($token && $this->userModel->verifyEmail($token)) {
            flash('success', 'Email verified! You can now log in.');
        } else {
            flash('error', 'Invalid or expired verification link.');
        }
        redirect('login');
    }

    // =============================CHANGE PASSWORD ===============================
    public function changePasswordForm(): void
    {
        if (empty($_SESSION['user_id'])) redirect('login');
        if (empty($_SESSION['must_change_password'])) redirect('');
        $this->view('auth.change_password');
    }

    public function changePassword(): void
    {
        $this->checkCsrf();
        if (empty($_SESSION['user_id'])) redirect('login');

        $userId = (int)$_SESSION['user_id'];
        $user   = $this->userModel->findById($userId);

        if (!$user) redirect('login');

        // =========== Require OLD password ===========
        $oldPass = $_POST['old_password'] ?? '';
        $pepper  = $_ENV['PASSWORD_PEPPER'] ?? '';
        $oldOk   = password_verify($oldPass . $pepper, $user['password']);
        if (!$oldOk) {
            // ========Fallback: try without pepper (legacy hash)---------
            $oldOk = password_verify($oldPass, $user['password']);
        }

        if (!$oldOk) {
            flash('error', 'Old password is incorrect.');
            redirect('change-password');
        }

        $errors = $this->validate($_POST, ['password' => 'required|min:8']);
        if (!empty($errors)) {
            flash('error', current($errors));
            redirect('change-password');
        }

        if (($_POST['password'] ?? '') !== ($_POST['password_confirm'] ?? '')) {
            flash('error', 'New passwords do not match.');
            redirect('change-password');
        }

        $newHash = password_hash($_POST['password'] . $pepper, PASSWORD_BCRYPT);
        $this->userModel->updatePassword($userId, $newHash, true); // true = clear must_change_password

        $_SESSION['must_change_password'] = false;
        flash('success', 'Password changed successfully. Welcome!');
        redirect('');
    }

      // =============================FORGOT PASSWORD ===============================
    public function forgotPasswordForm(): void
    {
        $this->view('auth.forgot_password');
    }

    public function forgotPassword(): void
    {
        $this->checkCsrf();

        $email = trim($_POST['email'] ?? '');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
            redirect('forgot-password');
        }

        $token = bin2hex(random_bytes(32));
        $found = $this->userModel->setResetToken($email, $token);

        if (!$this->emailService->isSmtpConfigured()) {
            // Email is not configured — cannot send reset link
            flash('error', 'Password reset requires email to be configured. Please contact your administrator to reset your password.');
            redirect('forgot-password');
        }

        if ($found) {
            $this->emailService->sendPasswordResetEmail($email, $token);
            flash('success', 'A password reset link has been sent to your email address.');
            redirect('login');
        } else {
            // Generic message — do not reveal whether the email exists
            flash('info', 'If that email exists in our system, a reset link has been sent.');
            redirect('login');
        }
    }

    public function resetPasswordForm(): void
    {
        $token = $_GET['token'] ?? '';
        $user  = $token ? $this->userModel->findByResetToken($token) : null;
        if (!$user) {
            flash('error', 'Invalid or expired reset link.');
            redirect('login');
        }
        $this->view('auth.reset_password', ['token' => $token]);
    }


}