<?php

namespace App\Models;

class DepartmentModel extends BaseModel
{
    protected string $table = 'departments';

    public function all(string $orderBy = 'name'): array
    {
        $stmt = $this->query("SELECT d.*, p.name AS parent_name FROM departments d LEFT JOIN departments p ON d.parent_id = p.id ORDER BY d.name");
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function create(array $data): int
    {
        $stmt = $this->query(
            "INSERT INTO departments (name, code, description, parent_id, created_at) VALUES (?, ?, ?, ?, NOW())",
            'sssi',
            [
                $data['name'],
                $data['code'],
                $data['description'] ?? null,
                $data['parent_id'] ?: null,
            ]
        );
        $id = $this->lastInsertId();
        $stmt->close();
        return $id;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->query(
            "UPDATE departments SET name=?, code=?, description=?, parent_id=?, updated_at=NOW() WHERE id=?",
            'sssii',
            [
                $data['name'],
                $data['code'],
                $data['description'] ?? null,
                $data['parent_id'] ?: null,
                $id,
            ]
        );
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected >= 0;
    }

    // Week 2: Recursion — buildTree()
    public function buildTree(array $departments, ?int $parentId = null): array
    {
        $tree = [];
        foreach ($departments as $dept) {
            $deptParent = $dept['parent_id'] === null ? null : (int)$dept['parent_id'];
            if ($deptParent === $parentId) {
                $children = $this->buildTree($departments, (int)$dept['id']);
                if ($children) {
                    $dept['children'] = $children;
                }
                $tree[] = $dept;
            }
        }
        return $tree;
    }

    public function getTree(): array
    {
        $all = $this->all();
        return $this->buildTree($all);
    }

    public function hasStudents(int $id): bool
    {
        $stmt = $this->query("SELECT COUNT(*) AS cnt FROM students WHERE department_id = ?", 'i', [$id]);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0) > 0;
    }

    public function codeExists(string $code, int $excludeId = 0): bool
    {
        $stmt = $this->query(
            "SELECT COUNT(*) AS cnt FROM departments WHERE code = ? AND id != ?",
            'si', [$code, $excludeId]
        );
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0) > 0;
    }
}
