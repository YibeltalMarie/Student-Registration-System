<?php

namespace App\Controllers;

use App\Models\StudentModel;

// Week 6: RESTful API
class ApiController extends BaseController
{
    private StudentModel $studentModel;

    public function __construct()
    {
        parent::__construct();
        $this->studentModel = new StudentModel();
        $this->setCorsHeaders();
    }

    // Week 1 & 6: CORS headers
    private function setCorsHeaders(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    // Week 6: API key authentication
    private function authenticate(): void
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $apiKey     = $_ENV['API_KEY'] ?? '';

        if (!str_starts_with($authHeader, 'Bearer ')) {
            $this->json(['error' => 'Authorization header missing'], 401);
        }

        $providedKey = substr($authHeader, 7);
        if (!hash_equals($apiKey, $providedKey)) {
            $this->json(['error' => 'Invalid API key'], 401);
        }
    }

    public function students(): void
    {
        $this->authenticate();

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int)($_GET['limit'] ?? 10)));
        $search  = trim($_GET['search'] ?? '');
        $status  = $_GET['status'] ?? '';
        $offset  = ($page - 1) * $perPage;

        $total    = $this->studentModel->countFiltered($search, $status);
        $students = $this->studentModel->getAllForExport($search, $status);

        $this->json([
            'success' => true,
            'data'    => $students,
            'meta'    => [
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => (int)ceil($total / $perPage),
            ],
            'status' => 200,
        ]);
    }

    public function getStudent(string $id): void
    {
        $this->authenticate();
        $student = $this->studentModel->findWithDepartment((int)$id);
        if (!$student) {
            $this->json(['success' => false, 'error' => 'Student not found'], 404);
        }
        $this->json(['success' => true, 'data' => $student, 'status' => 200]);
    }

    public function createStudent(): void
    {
        $this->authenticate();
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $errors = $this->validate($body, [
            'first_name'      => 'required|max:100',
            'last_name'       => 'required|max:100',
            'email'           => 'required|email',
            'enrollment_year' => 'required|integer',
            'department_id'   => 'required|integer',
        ]);

        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }

        $body['student_id'] = $this->studentModel->generateStudentId();
        $id = $this->studentModel->create($body);
        $student = $this->studentModel->findWithDepartment($id);

        $this->json(['success' => true, 'data' => $student, 'status' => 201], 201);
    }

    public function updateStudent(string $id): void
    {
        $this->authenticate();
        $student = $this->studentModel->find((int)$id);
        if (!$student) {
            $this->json(['success' => false, 'error' => 'Student not found'], 404);
        }

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $data = array_merge($student, $body);
        $this->studentModel->update((int)$id, $data);

        $updated = $this->studentModel->findWithDepartment((int)$id);
        $this->json(['success' => true, 'data' => $updated, 'status' => 200]);
    }

    public function deleteStudent(string $id): void
    {
        $this->authenticate();
        $student = $this->studentModel->find((int)$id);
        if (!$student) {
            $this->json(['success' => false, 'error' => 'Student not found'], 404);
        }
        $this->studentModel->delete((int)$id);
        $this->json(['success' => true, 'message' => 'Student deleted', 'status' => 200]);
    }
}