<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

class EmailService
{
    private bool $smtpConfigured;

    public function __construct()
    {
        $host = $_ENV['MAIL_HOST']     ?? '';
        $user = $_ENV['MAIL_USERNAME'] ?? '';
        $pass = $_ENV['MAIL_PASSWORD'] ?? '';

        // Only treat as configured when real (non-placeholder) credentials exist
        $this->smtpConfigured = (
            $host !== '' &&
            $user !== '' &&
            $user !== 'your@gmail.com' &&
            $pass !== '' &&
            $pass !== 'your-app-password'
        );
    }

    public static function isConfigured(): bool
    {
        $host = $_ENV['MAIL_HOST']     ?? '';
        $user = $_ENV['MAIL_USERNAME'] ?? '';
        $pass = $_ENV['MAIL_PASSWORD'] ?? '';
        return ($host !== '' && $user !== '' && $user !== 'your@gmail.com' && $pass !== '' && $pass !== 'your-app-password');
    }

    public function isSmtpConfigured(): bool
    {
        return $this->smtpConfigured;
    }

    // ── Core send ────────────────────────────────────────────────
    private function send(string $to, string $toName, string $subject, string $html): bool
    {
        if (!$this->smtpConfigured) {
            // Log to storage/exports/email_log.txt so admins can see what would have been sent
            $log = dirname(__DIR__, 2) . '/storage/exports/email_log.txt';
            @file_put_contents(
                $log,
                date('[Y-m-d H:i:s]') . " TO:{$to} | SUBJ:{$subject}\n",
                FILE_APPEND | LOCK_EX
            );
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST']     ?? '';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
            $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom(
                $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com',
                $_ENV['MAIL_FROM_NAME']    ?? 'Student Registration System'
            );
            $mail->addAddress($to, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html));
            $mail->send();
            return true;
        } catch (MailerException $e) {
            error_log('EmailService::send failed: ' . $e->getMessage());
            return false;
        }
    }

    // ── HTML email wrapper ───────────────────────────────────────
    private function wrap(string $title, string $body): string
    {
        $app = htmlspecialchars($_ENV['APP_NAME'] ?? 'Student Registration System');
        return "<!DOCTYPE html><html><head><meta charset='UTF-8'>
<style>
  body{margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif}
  .wrap{max-width:580px;margin:32px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.1)}
  .hdr{background:#1e293b;color:#fff;padding:28px 32px;text-align:center}
  .hdr h1{margin:0;font-size:20px;font-weight:700}
  .body{padding:32px}
  .body h2{color:#1e293b;margin-top:0}
  .body p{color:#475569;line-height:1.7;margin:0 0 16px}
  .cred-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px 24px;margin:20px 0}
  .cred-row{display:flex;align-items:center;margin-bottom:10px}
  .cred-label{color:#64748b;font-size:13px;width:120px;font-weight:600}
  .cred-val{font-family:Courier New,monospace;font-size:15px;font-weight:700;color:#1e293b;background:#e0e7ff;padding:4px 12px;border-radius:6px}
  .btn{display:inline-block;padding:14px 28px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-weight:700;font-size:15px;margin:8px 0}
  .warn{background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:14px 18px;color:#92400e;font-size:13px;margin-top:20px}
  .ftr{background:#f8fafc;padding:16px 32px;text-align:center;color:#94a3b8;font-size:12px;border-top:1px solid #e2e8f0}
</style>
</head>
<body>
<div class='wrap'>
  <div class='hdr'><h1>🎓 {$app}</h1></div>
  <div class='body'><h2>{$title}</h2>{$body}</div>
  <div class='ftr'>© " . date('Y') . " {$app} &nbsp;·&nbsp; Do not reply to this email.</div>
</div>
</body></html>";
    }

    // ── Public email methods ─────────────────────────────────────

    /**
     * Sends login credentials to a newly registered student.
     * Called by StudentController::store() after creating the user account.
     */
    public function sendStudentCredentialsEmail(
        string $to,
        string $name,
        string $username,
        string $defaultPassword
    ): bool {
        $loginUrl = url('login');
        $body = "
<p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>
<p>Your student account has been created in the Student Registration System. 
   Use the credentials below to log in.</p>
<div class='cred-box'>
  <div class='cred-row'>
    <span class='cred-label'>Username</span>
    <span class='cred-val'>" . htmlspecialchars($username) . "</span>
  </div>
  <div class='cred-row'>
    <span class='cred-label'>Password</span>
    <span class='cred-val'>" . htmlspecialchars($defaultPassword) . "</span>
  </div>
</div>
<p><a class='btn' href='" . htmlspecialchars($loginUrl) . "'>Login Now →</a></p>
<div class='warn'>
  ⚠️ <strong>You will be required to change your password immediately after your first login.</strong><br>
  Please keep your new password safe and do not share it with anyone.
</div>";
        return $this->send($to, $name, 'Your Student Account Credentials', $this->wrap('Welcome to SRS!', $body));
    }

    /**
     * Email verification link (for self-registered users when SMTP is on).
     */
    public function sendVerificationEmail(string $to, string $token): bool
    {
        $link = url('verify-email?token=' . urlencode($token));
        $body = "
<p>Thank you for registering. Click the button below to verify your email address:</p>
<p><a class='btn' href='" . htmlspecialchars($link) . "'>Verify Email →</a></p>
<p style='font-size:12px;color:#94a3b8;margin-top:16px'>
  Or copy this link into your browser:<br>
  <span style='word-break:break-all'>" . htmlspecialchars($link) . "</span>
</p>
<p style='font-size:12px;color:#94a3b8'>This link expires in 24 hours.</p>";
        return $this->send($to, $to, 'Verify Your Email Address', $this->wrap('Verify Your Email', $body));
    }

    /**
     * Password reset link.
     */
    public function sendPasswordResetEmail(string $to, string $token): bool
    {
        $link = url('reset-password?token=' . urlencode($token));
        $body = "
<p>We received a request to reset your password. Click below to proceed:</p>
<p><a class='btn' href='" . htmlspecialchars($link) . "'>Reset Password →</a></p>
<p style='font-size:12px;color:#94a3b8;margin-top:16px'>
  Or copy this link:<br>
  <span style='word-break:break-all'>" . htmlspecialchars($link) . "</span>
</p>
<div class='warn'>⚠️ This link expires in <strong>1 hour</strong>. If you did not request a reset, ignore this email.</div>";
        return $this->send($to, $to, 'Password Reset Request', $this->wrap('Reset Your Password', $body));
    }

    /**
     * Enrollment confirmation email.
     */
    public function sendEnrollmentEmail(
        string $to,
        string $name,
        string $courseName,
        string $courseCode
    ): bool {
        $body = "
<p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>
<p>You have been successfully enrolled in:</p>
<div class='cred-box'>
  <div class='cred-row'><span class='cred-label'>Course</span><span class='cred-val'>" . htmlspecialchars($courseName) . "</span></div>
  <div class='cred-row'><span class='cred-label'>Code</span><span class='cred-val'>" . htmlspecialchars($courseCode) . "</span></div>
</div>
<p>Good luck with your studies!</p>";
        return $this->send($to, $name, "Enrollment Confirmed: {$courseName}", $this->wrap('Enrollment Confirmed', $body));
    }

    /**
     * Grade release notification.
     */
    public function sendGradeEmail(
        string $to,
        string $name,
        string $courseName,
        float  $grade,
        string $letterGrade
    ): bool {
        $body = "
<p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>
<p>Your grade for <strong>" . htmlspecialchars($courseName) . "</strong> has been published:</p>
<div class='cred-box'>
  <div class='cred-row'><span class='cred-label'>Numeric</span><span class='cred-val'>" . number_format($grade, 1) . "</span></div>
  <div class='cred-row'><span class='cred-label'>Letter</span><span class='cred-val'>" . htmlspecialchars($letterGrade) . "</span></div>
</div>";
        return $this->send($to, $name, "Grade Released: {$courseName}", $this->wrap('Grade Published', $body));
    }

    /**
     * Bulk email to students.
     */
    public function sendBulkEmail(string $to, string $name, string $subject, string $message): bool
    {
        $body = "<p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>" .
                "<p>" . nl2br(htmlspecialchars($message)) . "</p>";
        return $this->send($to, $name, $subject, $this->wrap($subject, $body));
    }

    /**
     * Legacy welcome email (kept for backward compatibility).
     */
    public function sendWelcomeEmail(string $to, string $name, string $studentId): bool
    {
        return $this->sendStudentCredentialsEmail($to, $name, $studentId, $studentId);
    }

    /**
     * Notifies a student that their password has been restored to the system default.
     * Sent after system generates a new random password for the student.
     * Includes their username and the default password, and reminds them to change it.
     */
    public function sendDefaultPasswordRestoredEmail(
        string $to,
        string $name,
        string $username,
        string $newPassword
    ): bool {
        $loginUrl        = url('login');
        $defaultPassword = $newPassword;

        $body = "
<p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>
<p>We received a request to restore your password to the system default.
   Your password has been reset. Use the credentials below to log in:</p>

<div class='cred-box'>
  <div class='cred-row'>
    <span class='cred-label'>Username</span>
    <span class='cred-val'>" . htmlspecialchars($username) . "</span>
  </div>
  <div class='cred-row'>
    <span class='cred-label'>Password</span>
    <span class='cred-val'>" . htmlspecialchars($defaultPassword) . "</span>
  </div>
</div>

<p><a class='btn' href='" . htmlspecialchars($loginUrl) . "'>Login Now →</a></p>

<div class='warn'>
  ⚠️ <strong>You will be required to set a new password immediately after logging in.</strong><br>
  If you did not request this reset, please contact your administrator right away.
</div>";

        return $this->send(
            $to,
            $name,
            'Your Password Has Been Restored to Default',
            $this->wrap('Password Restored', $body)
        );
    }
}