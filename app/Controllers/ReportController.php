<?php

namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\EnrollmentModel;
use App\Models\DepartmentModel;
use App\Services\CsvService;
use App\Services\PdfService;
use App\Services\EmailService;

class ReportController extends BaseController
{
    private StudentModel    $studentModel;
    private EnrollmentModel $enrollmentModel;
    private DepartmentModel $deptModel;
    private CsvService      $csvService;
    private PdfService      $pdfService;
    private EmailService    $emailService;

    public function __construct()
    {
        parent::__construct();
        $this->studentModel    = new StudentModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->deptModel       = new DepartmentModel();
        $this->csvService      = new CsvService();
        $this->pdfService      = new PdfService();
        $this->emailService    = new EmailService();
    }

    public function index(): void
    {
        $this->requireAuth();

        // Student: view-only personal report (no export)
        if (is_student()) {
            $studentDbId = get_my_student_id();
            $student     = $studentDbId ? $this->studentModel->findWithDepartment($studentDbId) : null;
            $enrollments = $studentDbId ? $this->enrollmentModel->getStudentEnrollments($studentDbId) : [];
            $this->view('reports.student_report', compact('student', 'enrollments'));
            return;
        }

        // Admin view
        $departments = $this->deptModel->all();
        $this->view('reports.index', compact('departments'));
    }

    public function exportPdf(): void
    {
        $this->requireAdmin();
        $enrollments = $this->enrollmentModel->getAllForReport();
        $this->pdfService->exportEnrollmentReport($enrollments);
    }

    public function exportCsv(): void
    {
        $this->requireAdmin();
        $enrollments = $this->enrollmentModel->getAllForReport();
        $this->csvService->exportEnrollments($enrollments);
    }

    // Bulk email: all students OR filtered by department
    public function bulkEmail(): void
    {
        $this->requireAdmin();
        $this->checkCsrf();

        $subject    = trim($_POST['subject'] ?? '');
        $message    = trim($_POST['message'] ?? '');
        $department = trim($_POST['department_id'] ?? '');

        if (!$subject || !$message) {
            flash('error', 'Subject and message are required.');
            redirect('reports');
        }

        $students = $this->studentModel->getAllForExport('', 'active', $department);
        $sent     = 0;
        foreach ($students as $s) {
            $this->emailService->sendBulkEmail(
                $s['email'],
                $s['first_name'] . ' ' . $s['last_name'],
                $subject,
                $message
            );
            $sent++;
        }

        flash('success', "Bulk email queued for {$sent} student(s)." .
            (!$this->emailService->isSmtpConfigured() ? ' (SMTP not configured — emails logged to file.)' : '')
        );
        redirect('reports');
    }

    public function backup(): void
    {
        $this->requireAdmin();
        $this->checkCsrf();

        $dbName = $_ENV['DB_NAME'] ?? 'student_registration_system';
        $dbUser = $_ENV['DB_USER'] ?? 'root';
        $dbPass = $_ENV['DB_PASS'] ?? '';
        $dbHost = $_ENV['DB_HOST'] ?? 'localhost';

        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $path     = __DIR__ . '/../../storage/backups/' . $filename;
        $passArg  = $dbPass ? '-p' . escapeshellarg($dbPass) : '';
        $cmd      = "mysqldump -h " . escapeshellarg($dbHost)
                  . " -u " . escapeshellarg($dbUser)
                  . " {$passArg} " . escapeshellarg($dbName)
                  . " > " . escapeshellarg($path) . " 2>&1";

        exec($cmd, $output, $code);
        if ($code === 0 && file_exists($path)) {
            flash('success', "Backup created: {$filename}");
        } else {
            flash('error', 'Backup failed. Check server permissions and mysqldump availability.');
        }
        redirect('reports');
    }

    public function downloadBackup(string $file): void
    {
        $this->requireAdmin();
        $file = basename($file);
        $path = __DIR__ . '/../../storage/backups/' . $file;
        if (!file_exists($path) || !str_ends_with($file, '.sql')) {
            flash('error', 'Backup file not found.');
            redirect('reports');
        }
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function activityLogs(): void
    {
        $this->requireAdmin();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        $stmt = $this->db->prepare(
            "SELECT l.*, u.username FROM activity_logs l
             LEFT JOIN users u ON l.user_id = u.id
             ORDER BY l.created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->bind_param('ii', $perPage, $offset);
        $stmt->execute();
        $logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stmt2 = $this->db->prepare("SELECT COUNT(*) AS cnt FROM activity_logs");
        $stmt2->execute();
        $total = (int)$stmt2->get_result()->fetch_assoc()['cnt'];
        $stmt2->close();

        $paging = paginate($total, $perPage, $page, '');
        $this->view('reports.activity_logs', compact('logs', 'paging'));
    }
}