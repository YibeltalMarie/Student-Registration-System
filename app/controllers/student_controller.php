<?php
// app/controllers/StudentController.php

require_once __DIR__ . '/../models/student.php';
require_once __DIR__ . '/../../config/database.php';

/**
 * Helper function to sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);                // Remove whitespace
    $data = stripslashes($data);        // Remove backslashes
    $data = htmlspecialchars($data);    // Convert HTML special characters
    return $data;
}

/**
 * Helper function to validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Helper function to validate phone number (Ethiopian format)
 */
function validatePhone($phone) {
    // Allows: +251XXXXXXXXX, 09XXXXXXXX, 07XXXXXXXX
    return preg_match('/^(\+251|0)[97]\d{8}$/', $phone);
}

/**
 * Helper function to validate date format
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Helper function to validate required fields
 */
function validateRequired($field, $fieldName, &$errors) {
    if (empty($field)) {
        $errors[] = "$fieldName is required";
        return false;
    }
    return true;
}

function studentIndex() {
    global $db;
    $students = getAllStudents($db);
    require_once __DIR__ . '/../views/students/index.php';
}

function studentCreate() {
    global $db;
    $departments = getAllDepartments($db);
    $errors = [];  // Array to collect validation errors
    $oldInput = []; // Store old input for repopulating form
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // SANITIZATION: Clean all input data
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $dateOfBirth = sanitizeInput($_POST['date_of_birth'] ?? '');
        $gender = sanitizeInput($_POST['gender'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $departmentId = !empty($_POST['department_id']) ? (int)sanitizeInput($_POST['department_id']) : null;
        $enrollmentYear = sanitizeInput($_POST['enrollment_year'] ?? date('Y'));
        
        // Store sanitized input for repopulating form on error
        $oldInput = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'date_of_birth' => $dateOfBirth,
            'gender' => $gender,
            'address' => $address,
            'department_id' => $departmentId,
            'enrollment_year' => $enrollmentYear
        ];
        
        // VALIDATION: Check all required fields
        validateRequired($firstName, 'First name', $errors);
        validateRequired($lastName, 'Last name', $errors);
        validateRequired($email, 'Email', $errors);
        validateRequired($enrollmentYear, 'Enrollment year', $errors);
        
        // Email format validation
        if (!empty($email) && !validateEmail($email)) {
            $errors[] = "Please enter a valid email address (e.g., name@example.com)";
        }
        
        // Phone number validation (if provided)
        if (!empty($phone) && !validatePhone($phone)) {
            $errors[] = "Please enter a valid phone number (e.g., +251912345678 or 0912345678)";
        }
        
        // Date of birth validation (if provided)
        if (!empty($dateOfBirth) && !validateDate($dateOfBirth)) {
            $errors[] = "Please enter a valid date of birth (YYYY-MM-DD)";
        }
        
        // Gender validation (if provided)
        if (!empty($gender) && !in_array($gender, ['Male', 'Female'])) {
            $errors[] = "Gender must be either 'Male' or 'Female'";
        }
        
        // Enrollment year validation
        $currentYear = date('Y');
        if (!empty($enrollmentYear) && ($enrollmentYear < 2000 || $enrollmentYear > $currentYear + 5)) {
            $errors[] = "Enrollment year must be between 2000 and " . ($currentYear + 5);
        }
        
        // Department ID validation (if provided, must be numeric)
        if (!empty($departmentId) && !is_numeric($departmentId)) {
            $errors[] = "Invalid department selection";
        }
        
        // IF NO ERRORS, proceed with creation
        if (empty($errors)) {
            $result = createStudent($db, $firstName, $lastName, $email, $phone, $dateOfBirth, $gender, $address, $departmentId, $enrollmentYear);
            
            if ($result) {
                header('Location: index.php?url=students&success=created');
                exit;
            } else {
                $errors[] = "Failed to add student. Email might already exist or database error.";
            }
        }
    }
    
    // Pass errors and old input to the view
    require_once __DIR__ . '/../views/students/create.php';
}

function studentEdit($id) {
    global $db;
    
    // Sanitize the ID parameter
    $id = (int)sanitizeInput($id);
    
    $student = getStudentById($db, $id);
    $departments = getAllDepartments($db);
    $errors = [];
    $oldInput = [];
    
    if (!$student) {
        header('Location: index.php?url=students');
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // SANITIZATION: Clean all input data
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $dateOfBirth = sanitizeInput($_POST['date_of_birth'] ?? '');
        $gender = sanitizeInput($_POST['gender'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $departmentId = !empty($_POST['department_id']) ? (int)sanitizeInput($_POST['department_id']) : null;
        $enrollmentYear = sanitizeInput($_POST['enrollment_year'] ?? date('Y'));
        
        // Store sanitized input for repopulating form on error
        $oldInput = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'date_of_birth' => $dateOfBirth,
            'gender' => $gender,
            'address' => $address,
            'department_id' => $departmentId,
            'enrollment_year' => $enrollmentYear
        ];
        
        // VALIDATION: Check all required fields
        validateRequired($firstName, 'First name', $errors);
        validateRequired($lastName, 'Last name', $errors);
        validateRequired($email, 'Email', $errors);
        validateRequired($enrollmentYear, 'Enrollment year', $errors);
        
        // Email format validation
        if (!empty($email) && !validateEmail($email)) {
            $errors[] = "Please enter a valid email address (e.g., name@example.com)";
        }
        
        // Phone number validation (if provided)
        if (!empty($phone) && !validatePhone($phone)) {
            $errors[] = "Please enter a valid phone number (e.g., +251912345678 or 0912345678)";
        }
        
        // Date of birth validation (if provided)
        if (!empty($dateOfBirth) && !validateDate($dateOfBirth)) {
            $errors[] = "Please enter a valid date of birth (YYYY-MM-DD)";
        }
        
        // Gender validation (if provided)
        if (!empty($gender) && !in_array($gender, ['Male', 'Female'])) {
            $errors[] = "Gender must be either 'Male' or 'Female'";
        }
        
        // Enrollment year validation
        $currentYear = date('Y');
        if (!empty($enrollmentYear) && ($enrollmentYear < 2000 || $enrollmentYear > $currentYear + 5)) {
            $errors[] = "Enrollment year must be between 2000 and " . ($currentYear + 5);
        }
        
        // Department ID validation (if provided, must be numeric)
        if (!empty($departmentId) && !is_numeric($departmentId)) {
            $errors[] = "Invalid department selection";
        }
        
        // IF NO ERRORS, proceed with update
        if (empty($errors)) {
            if (updateStudent($db, $id, $firstName, $lastName, $email, $phone, $dateOfBirth, $gender, $address, $departmentId, $enrollmentYear)) {
                header('Location: index.php?url=students&success=updated');
                exit;
            } else {
                $errors[] = "Failed to update student. Email might already exist or database error.";
            }
        }
    }
    
    // Pass errors and old input to the view
    require_once __DIR__ . '/../views/students/edit.php';
}

function studentDelete($id) {
    global $db;
    
    // Sanitize the ID parameter
    $id = (int)sanitizeInput($id);
    
    // Optional: Add validation to prevent deleting if student has enrollments
    // You could add a check here before deleting
    
    deleteStudent($db, $id);
    header('Location: index.php?url=students&success=deleted');
    exit;
}
?>
