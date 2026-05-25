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


        return $id;
    }

}