<?php

namespace App\Models;

class UserModel extends BaseModel
{
    protected string $table = 'users';

    // ----------------------------------------------------------------
    // The KEY FIX: MySQLi bind_param() cannot handle PHP null via 's'.
    // We INSERT only non-nullable columns, then UPDATE nullable ones
    // in a second query so null values are sent as SQL NULL correctly.
    // ----------------------------------------------------------------
    public function create(array $data): int
    {
        // ========Step 1: insert the required columns (all non-null)=========
        $stmt = $this->query(
            "INSERT INTO users (username, email, password, role, must_change_password, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            'ssssi',
            [
                $data['username'],
                $data['email'],
                $data['password'],
                $data['role'] ?? 'viewer',
                $data['must_change_password'] ?? 0,
            ]
        );
        $id = $this->lastInsertId();
        $stmt->close();

         // =========Step 2: set nullable fields using direct SQL NULL where needed ===========
        $verifiedAt = $data['email_verified_at'] ?? null;
        $token      = $data['email_verification_token'] ?? null;

        // =========Use a raw query to safely set NULL values============
        $verifiedSql = $verifiedAt ? "'" . $this->db->real_escape_string($verifiedAt) . "'" : 'NULL';
        $tokenSql    = $token      ? "'" . $this->db->real_escape_string($token) . "'"      : 'NULL';

        $this->db->query(
            "UPDATE users
             SET email_verified_at = {$verifiedSql},
                 email_verification_token = {$tokenSql}
             WHERE id = {$id}"
        );

        return $id;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->query("SELECT * FROM users WHERE username = ?", 's', [$username]);
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }
 
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->query("SELECT * FROM users WHERE email = ?", 's', [$email]);
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->query("SELECT * FROM users WHERE id = ?", 'i', [$id]);
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function verifyEmail(string $token): bool
    {
        $stmt = $this->query(
            "UPDATE users
             SET email_verified_at = NOW(), email_verification_token = NULL
             WHERE email_verification_token = ?",
            's', [$token]
        );
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected > 0;
    }

    public function setVerificationToken(int $id, string $token): void
    {
        $this->query(
            "UPDATE users SET email_verification_token = ? WHERE id = ?",
            'si', [$token, $id]
        )->close();
    }

    public function incrementFailedAttempts(int $id): void
    {
        $max     = (int)($_ENV['LOGIN_MAX_ATTEMPTS']    ?? 5);
        $minutes = (int)($_ENV['LOGIN_LOCKOUT_MINUTES'] ?? 15);
        $this->query(
            "UPDATE users
             SET failed_attempts = failed_attempts + 1,
                 locked_until = IF(failed_attempts + 1 >= ?,
                                   DATE_ADD(NOW(), INTERVAL ? MINUTE),
                                   locked_until)
             WHERE id = ?",
            'iii', [$max, $minutes, $id]
        )->close();
    }

    public function resetFailedAttempts(int $id): void
    {
        $this->query(
            "UPDATE users SET failed_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?",
            'i', [$id]
        )->close();
    }

    public function isLocked(array $user): bool
    {
        if (empty($user['locked_until'])) return false;
        return strtotime($user['locked_until']) > time();
    }

     public function setResetToken(string $email, string $token): bool
    {
        $stmt = $this->query(
            "UPDATE users
             SET reset_token = ?, reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR)
             WHERE email = ?",
            'ss', [$token, $email]
        );
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected > 0;
    }

     public function findByResetToken(string $token): ?array
    {
        $stmt = $this->query(
            "SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()",
            's', [$token]
        );
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }
    
    // Updates password and clears reset token and must_change_password flag
    public function updatePassword(int $id, string $hashedPassword, bool $clearForceChange = false): void
    {
        if ($clearForceChange) {
            $this->query(
                "UPDATE users
                 SET password = ?, reset_token = NULL, reset_token_expires = NULL,
                     must_change_password = 0
                 WHERE id = ?",
                'si', [$hashedPassword, $id]
            )->close();
        } else {
            $this->query(
                "UPDATE users
                 SET password = ?, reset_token = NULL, reset_token_expires = NULL
                 WHERE id = ?",
                'si', [$hashedPassword, $id]
            )->close();
        }
    }

    public function setRememberToken(int $id, string $token): void
    {
        $this->query("UPDATE users SET remember_token = ? WHERE id = ?", 'si', [$token, $id])->close();
    }

    public function findByRememberToken(string $token): ?array
    {
        $stmt = $this->query("SELECT * FROM users WHERE remember_token = ?", 's', [$token]);
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function clearRememberToken(int $id): void
    {
        $this->query("UPDATE users SET remember_token = NULL WHERE id = ?", 'i', [$id])->close();
    }
}