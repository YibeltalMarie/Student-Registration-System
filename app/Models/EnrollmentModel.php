<?php

namespace App\Models;

use RuntimeException;

class EnrollmentModel extends BaseModel
{
    protected string $table = 'enrollments';

    public function getAllWithDetails(string $search = '', string $status = '', int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT e.*, s.first_name, s.last_name, s.student_id AS student_code,
                       c.name AS course_name, c.code AS course_code, c.credits
                FROM enrollments e
                JOIN students s ON e.student_id = s.id
                JOIN courses c ON e.course_id = c.id
                WHERE 1=1";
        $types = '';
        $params = [];

        if ($search) {
            $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR c.name LIKE ? OR s.student_id LIKE ?)";
            $like = "%{$search}%";
            $types .= 'ssss';
            $params = [$like, $like, $like, $like];
        }
        if ($status) {
            $sql .= " AND e.status = ?";
            $types .= 's';
            $params[] = $status;
        }
        $sql .= " ORDER BY e.created_at DESC LIMIT ? OFFSET ?";
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->query($sql, $types, $params);
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function countFiltered(string $search = '', string $status = ''): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM enrollments e
                JOIN students s ON e.student_id = s.id
                JOIN courses c ON e.course_id = c.id WHERE 1=1";
        $types = '';
        $params = [];
        if ($search) {
            $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR c.name LIKE ?)";
            $like = "%{$search}%";
            $types .= 'sss';
            $params = [$like, $like, $like];
        }
        if ($status) {
            $sql .= " AND e.status = ?";
            $types .= 's';
            $params[] = $status;
        }
        $stmt = $this->query($sql, $types, $params);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0);
    }

    public function findWithDetails(int $id): ?array
    {
        $stmt = $this->query(
            "SELECT e.*, s.first_name, s.last_name, s.student_id AS student_code, s.email AS student_email,
                    c.name AS course_name, c.code AS course_code, c.credits
             FROM enrollments e
             JOIN students s ON e.student_id = s.id
             JOIN courses c ON e.course_id = c.id
             WHERE e.id = ?",
            'i', [$id]
        );
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    // Week 3: Transaction — enrollment creation
    public function createWithTransaction(array $data): int
    {
        $this->beginTransaction();
        try {
            // Check seat availability
            $stmt = $this->query(
                "SELECT c.max_students,
                        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND status = 'enrolled') AS enrolled
                 FROM courses c WHERE c.id = ? FOR UPDATE",
                'i', [$data['course_id']]
            );
            $result = $stmt->get_result();
            $course = $result->fetch_assoc();
            $stmt->close();

            if (!$course) {
                throw new RuntimeException('Course not found.');
            }
            if ((int)$course['enrolled'] >= (int)$course['max_students']) {
                throw new RuntimeException('Course is full. No available seats.');
            }

            // Check duplicate enrollment
            $stmt2 = $this->query(
                "SELECT id FROM enrollments WHERE student_id = ? AND course_id = ? AND status != 'dropped'",
                'ii', [$data['student_id'], $data['course_id']]
            );
            $result2 = $stmt2->get_result();
            $exists = $result2->fetch_assoc();
            $stmt2->close();

            if ($exists) {
                throw new RuntimeException('Student is already enrolled in this course.');
            }

            $stmt3 = $this->query(
                "INSERT INTO enrollments (student_id, course_id, status, enrolled_at, created_at) VALUES (?, ?, 'enrolled', NOW(), NOW())",
                'ii', [$data['student_id'], $data['course_id']]
            );
            $id = $this->lastInsertId();
            $stmt3->close();

            $this->commit();
            return $id;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function updateGrade(int $id, float $grade): bool
    {
        $stmt = $this->query(
            "UPDATE enrollments SET grade = ?, updated_at = NOW() WHERE id = ?",
            'di', [$grade, $id]
        );
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected > 0;
    }

    public function getStudentEnrollments(int $studentId): array
    {
        $stmt = $this->query(
            "SELECT e.*, c.name AS course_name, c.code AS course_code, c.credits
             FROM enrollments e JOIN courses c ON e.course_id = c.id
             WHERE e.student_id = ? ORDER BY e.enrolled_at DESC",
            'i', [$studentId]
        );
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function getRecentEnrollments(int $limit = 5): array
    {
        $stmt = $this->query(
            "SELECT e.*, s.first_name, s.last_name, c.name AS course_name
             FROM enrollments e
             JOIN students s ON e.student_id = s.id
             JOIN courses c ON e.course_id = c.id
             ORDER BY e.created_at DESC LIMIT ?",
            'i', [$limit]
        );
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function getAllForReport(): array
    {
        $stmt = $this->query(
            "SELECT s.student_id AS student_code, s.first_name, s.last_name, d.name AS department_name,
                    c.code AS course_code, c.name AS course_name, c.credits,
                    e.status, e.grade, e.enrolled_at
             FROM enrollments e
             JOIN students s ON e.student_id = s.id
             JOIN courses c ON e.course_id = c.id
             LEFT JOIN departments d ON s.department_id = d.id
             ORDER BY s.last_name, c.name"
        );
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}
