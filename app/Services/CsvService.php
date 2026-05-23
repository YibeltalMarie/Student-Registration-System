<?php

namespace App\Services;

// Week 8: CSV export and import
class CsvService
{
    // Week 8: fputcsv export with UTF-8 BOM
    public function exportStudents(array $students): void
    {
        $filename = 'students_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, [
            'Student ID', 'First Name', 'Last Name', 'Email', 'Phone',
            'Date of Birth', 'Gender', 'Address', 'Department',
            'Enrollment Year', 'Status', 'Created At'
        ]);

        foreach ($students as $s) {
            fputcsv($output, [
                $s['student_id'], $s['first_name'], $s['last_name'],
                $s['email'], $s['phone'] ?? '', $s['date_of_birth'] ?? '',
                $s['gender'] ?? '', $s['address'] ?? '',
                $s['department_name'] ?? '', $s['enrollment_year'],
                $s['status'], $s['created_at'],
            ]);
        }
        fclose($output);
        exit;
    }

    public function exportEnrollments(array $enrollments): void
    {
        $filename = 'enrollments_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, [
            'Student ID', 'First Name', 'Last Name', 'Department',
            'Course Code', 'Course Name', 'Credits', 'Status', 'Grade', 'Enrolled At'
        ]);

        foreach ($enrollments as $e) {
            fputcsv($output, [
                $e['student_code'], $e['first_name'], $e['last_name'],
                $e['department_name'] ?? '', $e['course_code'], $e['course_name'],
                $e['credits'], $e['status'], $e['grade'] ?? '', $e['enrolled_at'] ?? '',
            ]);
        }
        fclose($output);
        exit;
    }

    // Week 8: fgetcsv bulk import
    public function importStudents(array $file, $studentModel, $deptModel): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'errors' => ['File upload error.']];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'], true)) {
            return ['success' => false, 'errors' => ['Invalid file type. Please upload a CSV file.']];
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            return ['success' => false, 'errors' => ['Cannot open file.']];
        }

        // Skip header row
        fgetcsv($handle);

        $errors = [];
        $count  = 0;
        $row    = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $row++;
            if (count($data) < 5) {
                $errors[] = "Row {$row}: Insufficient columns.";
                continue;
            }

            [$firstName, $lastName, $email, $enrollmentYear, $deptCode] = $data;

            if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row {$row}: Invalid email '{$email}'.";
                continue;
            }

            // Find dept by code
            $depts = $deptModel->all();
            $deptId = null;
            foreach ($depts as $d) {
                if (strtolower(trim($d['code'])) === strtolower(trim($deptCode))) {
                    $deptId = $d['id'];
                    break;
                }
            }

            if (!$deptId) {
                $errors[] = "Row {$row}: Department code '{$deptCode}' not found.";
                continue;
            }

            try {
                $studentModel->create([
                    'student_id'      => $studentModel->generateStudentId(),
                    'first_name'      => trim($firstName),
                    'last_name'       => trim($lastName),
                    'email'           => trim($email),
                    'enrollment_year' => (int)trim($enrollmentYear),
                    'department_id'   => $deptId,
                    'status'          => 'active',
                    'profile_image'   => null,
                ]);
                $count++;
            } catch (\Exception $e) {
                $errors[] = "Row {$row}: " . $e->getMessage();
            }
        }

        fclose($handle);
        return ['success' => true, 'count' => $count, 'errors' => $errors];
    }

    public function downloadTemplate(): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="student_import_template.csv"');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['first_name', 'last_name', 'email', 'enrollment_year', 'department_code']);
        fputcsv($output, ['John', 'Doe', 'john.doe@example.com', date('Y'), 'CS']);
        fclose($output);
        exit;
    }
}