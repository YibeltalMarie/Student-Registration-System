<?php

namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\DepartmentModel;
use App\Models\EnrollmentModel;
use App\Models\UserModel;
use App\Services\ImageService;
use App\Services\CsvService;
use App\Services\PdfService;
use App\Services\EmailService;

class StudentController extends BaseController
{
    private StudentModel    $studentModel;
    private DepartmentModel $deptModel;
    private EnrollmentModel $enrollmentModel;
    private UserModel       $userModel;
    private ImageService    $imageService;
    private CsvService      $csvService;
    private PdfService      $pdfService;
    private EmailService    $emailService;

    public function __construct()
    {
        parent::__construct();
        $this->studentModel    = new StudentModel();
        $this->deptModel       = new DepartmentModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->userModel       = new UserModel();
        $this->imageService    = new ImageService();
        $this->csvService      = new CsvService();
        $this->pdfService      = new PdfService();
        $this->emailService    = new EmailService();
    }

    // ── INDEX: admin sees all, student sees only themselves ────
    public function index(): void
    {
        $this->requireAuth();

        // Student role: redirect to their own profile
        if (is_student()) {
            $studentId = get_my_student_id();
            if ($studentId) {
                redirect('students/show/' . $studentId);
            } else {
                flash('error', 'Student profile not found.');
                redirect('');
            }
        }

        // Admin view
        $search     = trim($_GET['search'] ?? '');
        $status     = $_GET['status'] ?? '';
        $department = $_GET['department'] ?? '';
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $perPage    = 15;

        $total       = $this->studentModel->countFiltered($search, $status, $department);
        $paging      = paginate($total, $perPage, $page, '');
        $students    = $this->studentModel->getAllWithDepartment($search, $status, $department, $perPage, $paging['offset']);
        $departments = $this->deptModel->all();

        $this->view('students.index', compact('students', 'departments', 'search', 'status', 'department', 'paging'));
    }

    public function create(): void
    {
        $this->requireAdmin();
        $departments = $this->deptModel->all();
        $this->view('students.create', compact('departments'));
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->checkCsrf();

        $errors = $this->validate($_POST, [
            'first_name'      => 'required|max:100',
            'last_name'       => 'required|max:100',
            'email'           => 'required|email|max:150',
            'department_id'   => 'required|integer',
            'enrollment_year' => 'required|integer',
            'date_of_birth'   => 'date|min_age:16',
            'gender'          => 'in:male,female,other',
            'status'          => 'in:active,inactive,graduated,suspended',
        ]);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = $_POST;
            redirect('students/create');
        }

        // Image upload
        $profileImage = null;
        if (!empty($_FILES['profile_image']['name'])) {
            $result = $this->imageService->upload($_FILES['profile_image']);
            if ($result['success']) {
                $profileImage = $result['filename'];
            } else {
                $_SESSION['errors'] = ['profile_image' => $result['error']];
                $_SESSION['old']    = $_POST;
                redirect('students/create');
            }
        }

        $studentId = $this->studentModel->generateStudentId();
        $data = array_merge($_POST, [
            'student_id'    => $studentId,
            'profile_image' => $profileImage,
        ]);

        $id = $this->studentModel->create($data);
        $this->logActivity('create', 'student', $id, null, $data);

        // ── Auto-create user account ──────────────────────────
        // Username = student_id, Default password = random 8-char (letters + digits)
        $email           = trim($_POST['email']);
        $defaultPassword = $this->generateRandomPassword();
        $pepper          = $_ENV['PASSWORD_PEPPER'] ?? '';
        $hashedPassword  = password_hash($defaultPassword . $pepper, PASSWORD_BCRYPT);

        if (!$this->userModel->emailExists($email) && !$this->userModel->usernameExists($studentId)) {
            $this->userModel->create([
                'username'                 => $studentId,
                'email'                    => $email,
                'password'                 => $hashedPassword,
                'role'                     => 'student',
                'must_change_password'     => 1,       // Force change on first login
                'email_verification_token' => null,
                'email_verified_at'        => date('Y-m-d H:i:s'), // Auto-verified
            ]);
        }

        // ── Send credentials email to student ─────────────────
        $fullName = trim($_POST['first_name']) . ' ' . trim($_POST['last_name']);
        $emailSent = $this->emailService->sendStudentCredentialsEmail(
            $email, $fullName, $studentId, $defaultPassword
        );

        $emailNote = $emailSent
            ? 'Credentials emailed to student.'
            : 'Email not configured — share credentials manually.';

        flash('success',
            "Student registered successfully! " .
            "Login: Username = <strong>{$studentId}</strong> / Password = <strong>{$defaultPassword}</strong>. " .
            $emailNote
        );
        redirect('students');
    }

    public function show(string $id): void
    {
        $this->requireAuth();

        // Student can only view their own profile
        if (is_student()) {
            $myId = get_my_student_id();
            if ((int)$id !== $myId) {
                flash('error', 'You can only view your own profile.');
                redirect('students/show/' . $myId);
            }
        }

        $student = $this->studentModel->findWithDepartment((int)$id);
        if (!$student) { http_response_code(404); $this->view('errors.404'); return; }
        $enrollments = $this->enrollmentModel->getStudentEnrollments((int)$id);
        $this->view('students.show', compact('student', 'enrollments'));
    }

    public function edit(string $id): void
    {
        $this->requireAdmin();
        $student     = $this->studentModel->findWithDepartment((int)$id);
        if (!$student) { http_response_code(404); $this->view('errors.404'); return; }
        $departments = $this->deptModel->all();
        $this->view('students.edit', compact('student', 'departments'));
    }

    public function update(string $id): void
    {
        $this->requireAdmin();
        $this->checkCsrf();

        $student = $this->studentModel->find((int)$id);
        if (!$student) { http_response_code(404); $this->view('errors.404'); return; }

        $errors = $this->validate($_POST, [
            'first_name'      => 'required|max:100',
            'last_name'       => 'required|max:100',
            'email'           => 'required|email',
            'department_id'   => 'required|integer',
            'enrollment_year' => 'required|integer',
            'date_of_birth'   => 'date|min_age:16',
        ]);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = $_POST;
            redirect('students/edit/' . $id);
        }

        $profileImage = $student['profile_image'];
        if (!empty($_FILES['profile_image']['name'])) {
            $result = $this->imageService->upload($_FILES['profile_image']);
            if ($result['success']) {
                if ($profileImage) $this->imageService->delete($profileImage);
                $profileImage = $result['filename'];
            }
        }

        $data = array_merge($_POST, ['profile_image' => $profileImage]);
        $this->studentModel->update((int)$id, $data);
        $this->logActivity('update', 'student', (int)$id, $student, $data);

        flash('success', 'Student updated successfully.');
        redirect('students');
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $this->checkCsrf();

        $student = $this->studentModel->find((int)$id);
        if ($student) {
            if ($student['profile_image']) $this->imageService->delete($student['profile_image']);
            $this->studentModel->delete((int)$id);
            $this->logActivity('delete', 'student', (int)$id, $student, null);
        }
        flash('success', 'Student deleted successfully.');
        redirect('students');
    }

    public function ranking(): void
    {
        $this->requireAuth();
        $students = $this->studentModel->getRankedStudents();
        $this->view('students.ranking', compact('students'));
    }

    public function exportCsv(): void
    {
        $this->requireAdmin();
        $students = $this->studentModel->getAllForExport($_GET['search'] ?? '', $_GET['status'] ?? '', $_GET['department'] ?? '');
        $this->csvService->exportStudents($students);
    }

    public function exportPdf(): void
    {
        $this->requireAdmin();
        $students = $this->studentModel->getAllForExport($_GET['search'] ?? '', $_GET['status'] ?? '', $_GET['department'] ?? '');
        $this->pdfService->exportStudentList($students);
    }

    public function importCsv(): void
    {
        $this->requireAdmin();
        $this->checkCsrf();

        if (empty($_FILES['csv_file']['name'])) {
            flash('error', 'Please select a CSV file.');
            redirect('students');
        }

        $result = $this->csvService->importStudents($_FILES['csv_file'], $this->studentModel, $this->deptModel);
        flash($result['success'] ? 'success' : 'error',
            $result['success'] ? "Imported {$result['count']} students." : implode(', ', $result['errors'])
        );
        redirect('students');
    }

    public function csvTemplate(): void
    {
        $this->requireAdmin();
        $this->csvService->downloadTemplate();
    }

    /**
     * Generates a cryptographically random 8-character password.
     * Uses uppercase, lowercase, and digits for readability while remaining secure.
     * Example output: "Kx7mR2pQ"
     */
    private function generateRandomPassword(): string
    {
        $chars    = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $password = '';
        $max      = strlen($chars) - 1;
        for ($i = 0; $i < 8; $i++) {
            $password .= $chars[random_int(0, $max)];
        }
        return $password;
    }
}