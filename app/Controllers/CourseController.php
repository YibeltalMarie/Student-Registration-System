<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\DepartmentModel;

class CourseController extends BaseController
{
    private CourseModel     $courseModel;
    private DepartmentModel $deptModel;

    public function __construct()
    {
        parent::__construct();
        $this->courseModel = new CourseModel();
        $this->deptModel   = new DepartmentModel();
    }

    // Student sees only their department's courses; admin sees all
    public function index(): void
    {
        $this->requireAuth();

        if (is_student()) {
            $deptId  = $_SESSION['student_dept_id'] ?? 0;
            $courses = $deptId
                ? $this->courseModel->getAllWithDepartment('', (string)$deptId, 100, 0)
                : [];
            $departments = [];
            $search      = '';
            $department  = (string)$deptId;
            $paging      = paginate(count($courses), 100, 1, '');
            $this->view('courses.index', compact('courses', 'departments', 'search', 'department', 'paging'));
            return;
        }

        // Admin view
        $search     = trim($_GET['search'] ?? '');
        $department = $_GET['department'] ?? '';
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $perPage    = 15;

        $total       = $this->courseModel->countFiltered($search, $department);
        $paging      = paginate($total, $perPage, $page, '');
        $courses     = $this->courseModel->getAllWithDepartment($search, $department, $perPage, $paging['offset']);
        $departments = $this->deptModel->all();

        $this->view('courses.index', compact('courses', 'departments', 'search', 'department', 'paging'));
    }

    public function create(): void
    {
        $this->requireAdmin();
        $departments = $this->deptModel->all();
        $this->view('courses.create', compact('departments'));
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->checkCsrf();

        $errors = $this->validate($_POST, [
            'code'          => 'required|max:20',
            'name'          => 'required|max:150',
            'department_id' => 'required|integer',
            'credits'       => 'required|integer',
            'max_students'  => 'required|integer',
        ]);

        if ($this->courseModel->codeExists($_POST['code'] ?? '')) {
            $errors['code'] = 'Course code already exists.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = $_POST;
            redirect('courses/create');
        }

        $id = $this->courseModel->create($_POST);
        $this->logActivity('create', 'course', $id, null, $_POST);
        flash('success', 'Course created successfully.');
        redirect('courses');
    }

    public function edit(string $id): void
    {
        $this->requireAdmin();
        $course      = $this->courseModel->find((int)$id);
        if (!$course) { http_response_code(404); $this->view('errors.404'); return; }
        $departments = $this->deptModel->all();
        $this->view('courses.edit', compact('course', 'departments'));
    }

    public function update(string $id): void
    {
        $this->requireAdmin();
        $this->checkCsrf();

        $errors = $this->validate($_POST, [
            'code' => 'required|max:20',
            'name' => 'required|max:150',
            'department_id' => 'required|integer',
            'credits'       => 'required|integer',
        ]);

        if ($this->courseModel->codeExists($_POST['code'] ?? '', (int)$id)) {
            $errors['code'] = 'Course code already exists.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            redirect('courses/edit/' . $id);
        }

        $old = $this->courseModel->find((int)$id);
        $this->courseModel->update((int)$id, $_POST);
        $this->logActivity('update', 'course', (int)$id, $old, $_POST);
        flash('success', 'Course updated successfully.');
        redirect('courses');
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $this->checkCsrf();
        $old = $this->courseModel->find((int)$id);
        $this->courseModel->delete((int)$id);
        $this->logActivity('delete', 'course', (int)$id, $old, null);
        flash('success', 'Course deleted successfully.');
        redirect('courses');
    }
}
