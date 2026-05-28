<?php

namespace App\Controllers;

use App\Models\DepartmentModel;

class DepartmentController extends BaseController
{
    private DepartmentModel $deptModel;

    public function __construct()
    {
        parent::__construct();
        $this->deptModel = new DepartmentModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $tree        = $this->deptModel->getTree();
        $departments = $this->deptModel->all();
        $this->view('departments.index', compact('tree', 'departments'));
    }

    public function create(): void
    {
        $this->requireAdmin();
        $departments = $this->deptModel->all();
        $this->view('departments.create', compact('departments'));
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->checkCsrf();

        $errors = $this->validate($_POST, [
            'name' => 'required|max:100',
            'code' => 'required|max:20',
        ]);
        if ($this->deptModel->codeExists($_POST['code'] ?? '')) {
            $errors['code'] = 'Department code already exists.';
        }
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = $_POST;
            redirect('departments/create');
        }
        $id = $this->deptModel->create($_POST);
        $this->logActivity('create', 'department', $id, null, $_POST);
        flash('success', 'Department created successfully.');
        redirect('departments');
    }

    public function edit(string $id): void
    {
        $this->requireAdmin();
        $department  = $this->deptModel->find((int)$id);
        if (!$department) { http_response_code(404); $this->view('errors.404'); return; }
        $departments = $this->deptModel->all();
        $this->view('departments.edit', compact('department', 'departments'));
    }

    public function update(string $id): void
    {
        $this->requireAdmin();
        $this->checkCsrf();

        $errors = $this->validate($_POST, [
            'name' => 'required|max:100',
            'code' => 'required|max:20',
        ]);
        if ($this->deptModel->codeExists($_POST['code'] ?? '', (int)$id)) {
            $errors['code'] = 'Department code already exists.';
        }
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            redirect('departments/edit/' . $id);
        }
        $old = $this->deptModel->find((int)$id);
        $this->deptModel->update((int)$id, $_POST);
        $this->logActivity('update', 'department', (int)$id, $old, $_POST);
        flash('success', 'Department updated successfully.');
        redirect('departments');
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $this->checkCsrf();
        if ($this->deptModel->hasStudents((int)$id)) {
            flash('error', 'Cannot delete department with enrolled students.');
            redirect('departments');
        }
        $old = $this->deptModel->find((int)$id);
        $this->deptModel->delete((int)$id);
        $this->logActivity('delete', 'department', (int)$id, $old, null);
        flash('success', 'Department deleted successfully.');
        redirect('departments');
    }
}
