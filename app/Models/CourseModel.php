<?php

namespace App\Models;

class CourseModel extends BaseModel
{
    protected string $table = 'courses';

    public function getAllWithDepartment(string $search = '', string $department = '', int $limit = 20, int $offset = 0): array
    {
        $sql    = "SELECT c.*, d.name AS department_name FROM courses c
                   LEFT JOIN departments d ON c.department_id = d.id WHERE 1=1";
        $types  = '';
        $params = [];

        if ($search) {
            $sql   .= " AND (c.name LIKE ? OR c.code LIKE ?)";
            $like   = "%{$search}%";
            $types .= 'ss';
            $params = [$like, $like];
        }
        if ($department) {
            $sql   .= " AND c.department_id = ?";
            $types .= 'i';
            $params[] = (int)$department;
        }
        $sql   .= " ORDER BY c.name LIMIT ? OFFSET ?";
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->query($sql, $types, $params);
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function countFiltered(string $search = '', string $department = ''): int
    {
        $sql    = "SELECT COUNT(*) AS cnt FROM courses WHERE 1=1";
        $types  = '';
        $params = [];
        if ($search) {
            $sql   .= " AND (name LIKE ? OR code LIKE ?)";
            $like   = "%{$search}%";
            $types .= 'ss';
            $params = [$like, $like];
        }
        if ($department) {
            $sql   .= " AND department_id = ?";
            $types .= 'i';
            $params[] = (int)$department;
        }
        $stmt = $this->query($sql, $types, $params);
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0);
    }

    public function create(array $data): int
    {
        $stmt = $this->query(
            "INSERT INTO courses (code, name, description, credits, department_id, max_students, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            'sssiii',
            [
                $data['code'],
                $data['name'],
                $data['description'] ?? null,
                (int)($data['credits']      ?? 3),
                (int)($data['department_id']),
                (int)($data['max_students'] ?? 30),
            ]
        );
        $id = $this->lastInsertId();
        $stmt->close();
        return $id;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->query(
            "UPDATE courses SET code=?, name=?, description=?, credits=?,
             department_id=?, max_students=?, updated_at=NOW() WHERE id=?",
            'sssiiii',
            [
                $data['code'],
                $data['name'],
                $data['description'] ?? null,
                (int)($data['credits']      ?? 3),
                (int)($data['department_id']),
                (int)($data['max_students'] ?? 30),
                $id,
            ]
        );
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected >= 0;
    }

    public function codeExists(string $code, int $excludeId = 0): bool
    {
        $stmt = $this->query(
            "SELECT COUNT(*) AS cnt FROM courses WHERE code = ? AND id != ?",
            'si', [$code, $excludeId]
        );
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0) > 0;
    }

    // ── ENROLLMENT AJAX: all available for a student (no dept filter) ──────
    public function getAvailableForStudent(int $studentId): array
    {
        $stmt = $this->query(
            "SELECT c.id, c.code, c.name, c.credits, c.max_students,
                    (SELECT COUNT(*) FROM enrollments
                     WHERE course_id = c.id AND status = 'enrolled') AS enrolled_count
             FROM courses c
             WHERE c.id NOT IN (
                 SELECT course_id FROM enrollments
                 WHERE student_id = ? AND status != 'dropped'
             )
             ORDER BY c.name",
            'i', [$studentId]
        );
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // ── ENROLLMENT AJAX: only courses from student's department ─────────────
    // This is what the admin enrollment form calls.
    // Returns courses that:
    //   1. Belong to the student's department
    //   2. Student is NOT already enrolled in
    // No seat-full filter — admin can override; seats shown as info only.
    public function getAvailableForStudentByDept(int $studentId, int $deptId): array
    {
        // No department set → fall back to all courses
        if ($deptId <= 0) {
            return $this->getAvailableForStudent($studentId);
        }

        $stmt = $this->query(
            "SELECT c.id, c.code, c.name, c.credits, c.max_students,
                    (SELECT COUNT(*) FROM enrollments
                     WHERE course_id = c.id AND status = 'enrolled') AS enrolled_count
             FROM courses c
             WHERE c.department_id = ?
               AND c.id NOT IN (
                   SELECT course_id FROM enrollments
                   WHERE student_id = ? AND status != 'dropped'
               )
             ORDER BY c.name",
            'ii', [$deptId, $studentId]
        );
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}
