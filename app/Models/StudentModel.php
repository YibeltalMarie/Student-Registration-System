<?php

namespace App\Models;

class StudentModel extends BaseModel
{
    protected string $table = 'students';

    // Week 3: JOIN query
    public function getAllWithDepartment(string $search = '', string $status = '', string $department = '', int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT s.*, d.name AS department_name
                FROM students s
                LEFT JOIN departments d ON s.department_id = d.id
                WHERE 1=1";
        $types = '';
        $params = [];

        if ($search) {
            $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id LIKE ? OR s.email LIKE ?)";
            $like = "%{$search}%";
            $types .= 'ssss';
            $params = array_merge($params, [$like, $like, $like, $like]);
        }
        if ($status) {
            $sql .= " AND s.status = ?";
            $types .= 's';
            $params[] = $status;
        }
        if ($department) {
            $sql .= " AND s.department_id = ?";
            $types .= 'i';
            $params[] = (int)$department;
        }

        $sql .= " ORDER BY s.created_at DESC LIMIT ? OFFSET ?";
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->query($sql, $types, $params);
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function countFiltered(string $search = '', string $status = '', string $department = ''): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM students s WHERE 1=1";
        $types = '';
        $params = [];

        if ($search) {
            $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id LIKE ? OR s.email LIKE ?)";
            $like = "%{$search}%";
            $types .= 'ssss';
            $params = array_merge($params, [$like, $like, $like, $like]);
        }
        if ($status) {
            $sql .= " AND s.status = ?";
            $types .= 's';
            $params[] = $status;
        }
        if ($department) {
            $sql .= " AND s.department_id = ?";
            $types .= 'i';
            $params[] = (int)$department;
        }

        $stmt = $this->query($sql, $types, $params);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0);
    }

    public function findWithDepartment(int $id): ?array
    {
        $stmt = $this->query(
            "SELECT s.*, d.name AS department_name
             FROM students s LEFT JOIN departments d ON s.department_id = d.id
             WHERE s.id = ?",
            'i', [$id]
        );
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->query(
            "INSERT INTO students (student_id, first_name, last_name, email, phone, date_of_birth,
             gender, address, department_id, enrollment_year, status, profile_image, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            'ssssssssisss',
            [
                $data['student_id'],
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'] ?? null,
                $data['date_of_birth'] ?? null,
                $data['gender'] ?? null,
                $data['address'] ?? null,
                $data['department_id'],
                $data['enrollment_year'],
                $data['status'] ?? 'active',
                $data['profile_image'] ?? null,
            ]
        );
        $id = $this->lastInsertId();
        $stmt->close();
        return $id;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->query(
            "UPDATE students SET first_name=?, last_name=?, email=?, phone=?, date_of_birth=?,
             gender=?, address=?, department_id=?, enrollment_year=?, status=?, profile_image=?,
             updated_at=NOW() WHERE id=?",
            'ssssssssissi',
            [
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'] ?? null,
                $data['date_of_birth'] ?? null,
                $data['gender'] ?? null,
                $data['address'] ?? null,
                $data['department_id'],
                $data['enrollment_year'],
                $data['status'] ?? 'active',
                $data['profile_image'] ?? null,
                $id,
            ]
        );
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected >= 0;
    }

    // Week 3: Window function — RANK() OVER (PARTITION BY)
    public function getRankedStudents(): array
    {
        $sql = "SELECT s.id, s.student_id, s.first_name, s.last_name, d.name AS department_name,
                       AVG(e.grade) AS avg_grade,
                       RANK() OVER (PARTITION BY s.department_id ORDER BY AVG(e.grade) DESC) AS dept_rank,
                       RANK() OVER (ORDER BY AVG(e.grade) DESC) AS overall_rank
                FROM students s
                LEFT JOIN departments d ON s.department_id = d.id
                LEFT JOIN enrollments e ON s.id = e.student_id AND e.grade IS NOT NULL
                GROUP BY s.id, s.student_id, s.first_name, s.last_name, d.name, s.department_id
                HAVING avg_grade IS NOT NULL
                ORDER BY overall_rank";
        $stmt = $this->query($sql);
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function getStats(): array
    {
        $stmt = $this->query(
            "SELECT
               COUNT(*) AS total,
               SUM(status = 'active') AS active,
               SUM(status = 'graduated') AS graduated,
               SUM(status = 'suspended') AS suspended,
               SUM(status = 'inactive') AS inactive
             FROM students"
        );
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?? [];
    }

    // Week 3: GROUP BY aggregates
    public function getByDepartmentStats(): array
    {
        $stmt = $this->query(
            "SELECT d.name, COUNT(s.id) AS total, AVG(e.grade) AS avg_grade
             FROM departments d
             LEFT JOIN students s ON s.department_id = d.id
             LEFT JOIN enrollments e ON e.student_id = s.id
             GROUP BY d.id, d.name
             ORDER BY total DESC"
        );
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function getAllForExport(string $search = '', string $status = '', string $department = ''): array
    {
        $sql = "SELECT s.student_id, s.first_name, s.last_name, s.email, s.phone,
                       s.date_of_birth, s.gender, s.address, d.name AS department_name,
                       s.enrollment_year, s.status, s.created_at
                FROM students s LEFT JOIN departments d ON s.department_id = d.id
                WHERE 1=1";
        $types = '';
        $params = [];

        if ($search) {
            $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id LIKE ?)";
            $like = "%{$search}%";
            $types .= 'sss';
            $params = array_merge($params, [$like, $like, $like]);
        }
        if ($status) {
            $sql .= " AND s.status = ?";
            $types .= 's';
            $params[] = $status;
        }
        if ($department) {
            $sql .= " AND s.department_id = ?";
            $types .= 'i';
            $params[] = (int)$department;
        }
        $sql .= " ORDER BY s.last_name, s.first_name";

        $stmt = $this->query($sql, $types, $params);
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Generate a unique student ID
     * Format: STU + YEAR + 4-digit sequence (e.g., STU20260001)
     * Uses MAX to get the highest existing number, not COUNT
     */
    public function generateStudentId(): string
    {
        $year = date('Y');
        
        // Get the MAX sequence number for this year (not COUNT)
        $stmt = $this->query(
            "SELECT MAX(CAST(SUBSTRING(student_id, -4) AS UNSIGNED)) AS max_seq 
            FROM students 
            WHERE student_id LIKE ?",
            's', ["STU{$year}%"]
        );
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $maxSeq = (int)($row['max_seq'] ?? 0);
        $stmt->close();
        
        // Next sequence number
        $nextSeq = $maxSeq + 1;
        $paddedSeq = str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        
        $newStudentId = "STU{$year}{$paddedSeq}";
        
        // Safety: Verify it's actually unique (prevent race conditions)
        $checkStmt = $this->query(
            "SELECT 1 FROM students WHERE student_id = ?",
            's', [$newStudentId]
        );
        $exists = $checkStmt->get_result()->num_rows > 0;
        $checkStmt->close();
        
        if ($exists) {
            // If somehow exists, recursively generate next one
            return $this->generateStudentId();
        }
        
        return $newStudentId;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->query("SELECT * FROM students WHERE email = ?", 's', [$email]);
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function getCoursesByDepartment(int $departmentId): array
    {
        $stmt = $this->query(
            "SELECT c.id, c.code, c.name, c.credits, c.max_students,
                    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND status = 'enrolled') AS enrolled_count
             FROM courses c
             WHERE c.department_id = ?
             ORDER BY c.name",
            'i', [$departmentId]
        );
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}
