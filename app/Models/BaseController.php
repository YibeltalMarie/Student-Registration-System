<?php

namespace App\Models;

use App\Config\Database;
use mysqli;
use mysqli_stmt;
use RuntimeException;

// Week 7: Abstract class — BaseModel
abstract class BaseModel
{
    protected mysqli $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Week 3: Prepared statements — generic query executor
    protected function query(string $sql, string $types = '', array $params = []): mysqli_stmt|false
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Prepare failed: ' . $this->db->error . ' | SQL: ' . $sql);
        }
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    // Find single record by primary key
    public function find(int $id): ?array
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?", 'i', [$id]);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    // Get all records
    public function all(string $orderBy = ''): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        $stmt = $this->query($sql);
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // Count all records
    public function count(): int
    {
        $stmt = $this->query("SELECT COUNT(*) AS cnt FROM {$this->table}");
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0);
    }

    // Delete by primary key
    public function delete(int $id): bool
    {
        $stmt = $this->query("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?", 'i', [$id]);
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected > 0;
    }

    // Week 3: Transaction helpers
    public function beginTransaction(): void
    {
        $this->db->begin_transaction();
    }

    public function commit(): void
    {
        $this->db->commit();
    }

    public function rollback(): void
    {
        $this->db->rollback();
    }

    public function lastInsertId(): int
    {
        return (int)$this->db->insert_id;
    }
}
