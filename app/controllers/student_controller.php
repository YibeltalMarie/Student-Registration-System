<?php
// app/controllers/StudentController.php

require_once __DIR__ . '/../models/student.php';
require_once __DIR__ . '/../../config/database.php';

function studentIndex() {
    global $db;
    $students = getAllStudents($db);
    require_once __DIR__ . '/../views/students/index.php';
}

function studentCreate() {
    global $db;
    $departments = getAllDepartments($db);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $dateOfBirth = $_POST['date_of_birth'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $address = $_POST['address'] ?? '';
        // Allow department_id to be NULL (not required)
        $departmentId = !empty($_POST['department_id']) ? $_POST['department_id'] : null;
        $enrollmentYear = $_POST['enrollment_year'] ?? date('Y');
        
        $result = createStudent($db, $firstName, $lastName, $email, $phone, $dateOfBirth, $gender, $address, $departmentId, $enrollmentYear);
        
        if ($result) {
            header('Location: index.php?url=students&success=created');
            exit;
        } else {
            $error = "Failed to add student. Email might already exist.";
        }
    }
    
    require_once __DIR__ . '/../views/students/create.php';
}

function studentEdit($id) {
    global $db;
    $student = getStudentById($db, $id);
    $departments = getAllDepartments($db);
    
    if (!$student) {
        header('Location: index.php?url=students');
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $dateOfBirth = $_POST['date_of_birth'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $address = $_POST['address'] ?? '';
        $departmentId = !empty($_POST['department_id']) ? $_POST['department_id'] : null;
        $enrollmentYear = $_POST['enrollment_year'] ?? date('Y');
        
        if (updateStudent($db, $id, $firstName, $lastName, $email, $phone, $dateOfBirth, $gender, $address, $departmentId, $enrollmentYear)) {
            header('Location: index.php?url=students&success=updated');
            exit;
        }
    }
    
    require_once __DIR__ . '/../views/students/edit.php';
}

function studentDelete($id) {
    global $db;
    deleteStudent($db, $id);
    header('Location: index.php?url=students&success=deleted');
    exit;
}
?>
