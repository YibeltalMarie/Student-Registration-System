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
            return ['success' => false, 'errors' => ['File upload error code: ' . $file['error']]];
        }

        // Check extension instead of MIME (MIME is unreliable for CSV across OS/editors)
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            return ['success' => false, 'errors' => ['Invalid file type. Please upload a .csv file.']];
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            return ['success' => false, 'errors' => ['Cannot open uploaded file.']];
        }

        // Strip UTF-8 BOM if present (Excel adds this)
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle); // no BOM, go back to start
        }

        // Read header row and build a column index map (case-insensitive)
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return ['success' => false, 'errors' => ['CSV file appears to be empty.']];
        }
        $colMap = [];
        foreach ($headers as $i => $h) {
            $colMap[strtolower(trim($h))] = $i;
        }

        // Resolve column index by multiple possible header names
        $col = function(array $candidates) use ($colMap): ?int {
            foreach ($candidates as $name) {
                if (isset($colMap[$name])) return $colMap[$name];
            }
            return null;
        };


        $iFirstName      = $col(['first_name', 'first name', 'firstname']);
        $iLastName       = $col(['last_name', 'last name', 'lastname']);
        $iEmail          = $col(['email']);
        $iEnrollmentYear = $col(['enrollment_year', 'enrollment year', 'enrollmentyear', 'year']);
        $iDeptCode       = $col(['department', 'department_code', 'dept', 'dept_code']);
        $iPhone          = $col(['phone', 'phone_number', 'phone number']);
        $iDob            = $col(['date_of_birth', 'date of birth', 'dob', 'dateofbirth']);
        $iGender         = $col(['gender']);
        $iAddress        = $col(['address']);
        $iStatus         = $col(['status']);

        // Required columns check
        $missing = [];
        if ($iFirstName === null)      $missing[] = 'First Name';
        if ($iLastName === null)       $missing[] = 'Last Name';
        if ($iEmail === null)          $missing[] = 'Email';
        if ($iEnrollmentYear === null) $missing[] = 'Enrollment Year';
        if ($iDeptCode === null)       $missing[] = 'Department';
        if (!empty($missing)) {
            fclose($handle);
            return ['success' => false, 'errors' => ['Missing required columns: ' . implode(', ', $missing)]];
        }

        $errors = [];
        $count  = 0;
        $row    = 0;

        // Cache departments once
        $depts = $deptModel->all();
        $deptMap = [];
        foreach ($depts as $d) {
            $deptMap[strtolower(trim($d['code']))] = $d['id'];
        }

        // Valid gender and status values
        $validGenders  = ['male', 'female', 'other'];
        $validStatuses = ['active', 'inactive', 'graduated', 'suspended'];

        while (($data = fgetcsv($handle)) !== false) {
            $row++;

            // Skip completely empty rows
            if (count(array_filter($data, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }

            $get = fn(int $i) => isset($data[$i]) ? trim($data[$i]) : '';

            $firstName      = $get($iFirstName);
            $lastName       = $get($iLastName);
            $email          = $get($iEmail);
            $enrollmentYear = $get($iEnrollmentYear);
            $deptCode       = $get($iDeptCode);
            $phone          = $iPhone !== null   ? $get($iPhone)   : null;
            $dob            = $iDob !== null     ? $get($iDob)     : null;
            $gender         = $iGender !== null  ? strtolower($get($iGender))  : null;
            $address        = $iAddress !== null ? $get($iAddress) : null;
            $status         = $iStatus !== null  ? strtolower($get($iStatus))  : 'active';

            // Validate required fields
            if ($firstName === '' || $lastName === '') {
                $errors[] = "Row {$row}: First name and last name are required.";
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row {$row}: Invalid email '{$email}'.";
                continue;
            }

            $deptId = $deptMap[strtolower($deptCode)] ?? null;
            if (!$deptId) {
                $available = strtoupper(implode(', ', array_keys($deptMap)));
                $errors[] = "Row {$row}: Department code '{$deptCode}' not found. Available: {$available}";
                continue;
            }

            $year = (int)$enrollmentYear;
            if ($year < 2000 || $year > (int)date('Y') + 1) {
                $errors[] = "Row {$row}: Invalid enrollment year '{$enrollmentYear}'.";
                continue;
            }

            // Normalize date of birth (accepts M/D/YYYY or YYYY-MM-DD)
            $dobFormatted = null;
            if ($dob && $dob !== '') {
                $parsed = date_create($dob);
                $dobFormatted = $parsed ? date_format($parsed, 'Y-m-d') : null;
            }

            // Normalize gender
            if ($gender && !in_array($gender, $validGenders, true)) {
                $gender = null;
            }


            // Normalize status
            if (!in_array($status, $validStatuses, true)) {
                $status = 'active';
            }

            try {
                $studentModel->create([
                    'student_id'      => $studentModel->generateStudentId(),
                    'first_name'      => $firstName,
                    'last_name'       => $lastName,
                    'email'           => $email,
                    'phone'           => $phone ?: null,
                    'date_of_birth'   => $dobFormatted,
                    'gender'          => $gender,
                    'address'         => $address ?: null,
                    'enrollment_year' => $year,
                    'department_id'   => $deptId,
                    'status'          => $status,
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
        fputcsv($output, [
            'First Name', 'Last Name', 'Email', 'Phone',
            'Date of Birth', 'Gender', 'Enrollment Year',
            'Department', 'Status', 'Address', 'Profile Photo'
        ]);
        fputcsv($output, [
            'Abebe', 'Girma', 'abebe.girma@example.com', '+251911000001',
            '2000-03-15', 'Male', date('Y'),
            'CS', 'Active', 'Addis Ababa', ''
        ]);
        fputcsv($output, [
            'Tigist', 'Haile', 'tigist.haile@example.com', '+251922000002',
            '2001-06-20', 'Female', date('Y'),
            'EE', 'Active', 'Bahir Dar', ''
        ]);
        fclose($output);
        exit;
    }
}
