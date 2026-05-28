<?php

namespace App\Controllers;

use App\Models\EnrollmentModel;
use App\Models\StudentModel;
use App\Models\CourseModel;
use App\Services\EmailService;

class EnrollmentController extends BaseController
{
    private EnrollmentModel $enrollmentModel;
    private StudentModel    $studentModel;
    private CourseModel     $courseModel;
    private EmailService    $emailService;

    public function __construct()
    {
        parent::__construct();
        $this->enrollmentModel = new EnrollmentModel();
        $this->studentModel    = new StudentModel();
        $this->courseModel     = new CourseModel();
        $this->emailService    = new EmailService();
    }

    // ── INDEX ──────────────────────────────────────────────────
    // Student sees only their own enrollments; admin sees all
    public function index(): void
    {
        $this->requireAuth();

        if (is_student()) {
            $studentDbId = get_my_student_id();
            $enrollments = $studentDbId
                ? $this->enrollmentModel->getStudentEnrollments($studentDbId)
                : [];
            $this->view('enrollments.student_index', compact('enrollments'));
            return;
        }

        $search  = trim($_GET['search'] ?? '');
        $status  = $_GET['status'] ?? '';
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;

        $total       = $this->enrollmentModel->countFiltered($search, $status);
        $paging      = paginate($total, $perPage, $page, '');
        $enrollments = $this->enrollmentModel->getAllWithDetails($search, $status, $perPage, $paging['offset']);

        $this->view('enrollments.index', compact('enrollments', 'search', 'status', 'paging'));
    }

    // ── CREATE FORM ────────────────────────────────────────────
    public function create(): void
    {
        $this->requireAuth(); // Use requireAuth instead of requireAdmin to allow searching by ID if needed, but normally restricted to Admin or the student
        $students = $this->studentModel->getAllWithDepartment('', 'active', '', 500, 0);
        $this->view('enrollments.create', compact('students'));
    }

    // ── STORE: enroll one student in one or more courses ───────
    public function store(): void
    {
        $this->requireAuth();
        $this->checkCsrf();

        $studentId = (int)($_POST['student_id'] ?? 0);

        // Accept course_ids[] (multi-select) or single course_id
        $courseIds = [];
        if (!empty($_POST['course_ids']) && is_array($_POST['course_ids'])) {
            $courseIds = array_filter(array_map('intval', $_POST['course_ids']));
        } elseif (!empty($_POST['course_id'])) {
            $courseIds = [(int)$_POST['course_id']];
        }

        if (!$studentId || empty($courseIds)) {
            flash('error', 'Please select a student and at least one course.');
            redirect('enrollments/create');
        }

        $succeeded = 0;
        $failures  = [];

        foreach ($courseIds as $courseId) {
            try {
                $id = $this->enrollmentModel->createWithTransaction([
                    'student_id' => $studentId,
                    'course_id'  => $courseId,
                ]);
                $this->logActivity('create', 'enrollment', $id, null, [
                    'student_id' => $studentId,
                    'course_id'  => $courseId,
                ]);

                // Send confirmation email
                $detail = $this->enrollmentModel->findWithDetails($id);
                if ($detail) {
                    $this->emailService->sendEnrollmentEmail(
                        $detail['student_email'],
                        $detail['first_name'],
                        $detail['course_name'],
                        $detail['course_code']
                    );
                }
                $succeeded++;
            } catch (\Exception $e) {
                $failures[] = $e->getMessage();
            }
        }

        if ($succeeded > 0) {
            $msg = "Successfully enrolled in {$succeeded} course(s).";
            if (!empty($failures)) {
                $msg .= ' Skipped: ' . implode('; ', $failures);
            }
            flash('success', $msg);
        } else {
            flash('error', 'Enrollment failed: ' . implode('; ', $failures));
        }

        redirect('enrollments');
    }

    // ── EDIT / GRADE ───────────────────────────────────────────
    public function edit(string $id): void
    {
        $this->requireAdmin();
        $enrollment = $this->enrollmentModel->findWithDetails((int)$id);
        if (!$enrollment) { http_response_code(404); $this->view('errors.404'); return; }
        $this->view('enrollments.edit', compact('enrollment'));
    }

    public function updateGrade(string $id): void
    {
        $this->requireAdmin();
        $this->checkCsrf();

        $errors = $this->validate($_POST, ['grade' => 'required|numeric']);
        $grade  = (float)($_POST['grade'] ?? 0);

        if ($grade < 0 || $grade > 100) {
            $errors['grade'] = 'Grade must be between 0 and 100.';
        }
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            redirect('enrollments/edit/' . $id);
        }

        $old = $this->enrollmentModel->findWithDetails((int)$id);
        $this->enrollmentModel->updateGrade((int)$id, $grade);
        $this->logActivity('update_grade', 'enrollment', (int)$id, $old, ['grade' => $grade]);

        if ($old) {
            $this->emailService->sendGradeEmail(
                $old['student_email'],
                $old['first_name'],
                $old['course_name'],
                $grade,
                letter_grade($grade)
            );
        }

        flash('success', 'Grade updated successfully.');
        redirect('enrollments');
    }

    // ── AJAX: available courses filtered by student's department ───
    // Called by the enrollment create form via fetch()
    public function availableCourses(): void
    {
        $this->requireAuth();

        $studentId = (int)($_GET['student_id'] ?? 0);
        if ($studentId <= 0) {
            $this->json(['error' => 'Student ID is required', 'courses' => []], 422);
        }

        // Load the student to get their department
        $student = $this->studentModel->find($studentId);
        if (!$student) {
            $this->json(['error' => 'Student not found', 'courses' => []], 404);
        }

        $deptId = (int)($student['department_id'] ?? 0);

        // Get courses from this student's department that they aren't enrolled in
        $courses = $this->courseModel->getAvailableForStudentByDept($studentId, $deptId);

        $this->json([
            'courses'    => $courses,
            'dept_id'    => $deptId,
            'student_id' => $studentId,
            'count'      => count($courses),
        ]);
    }
}
