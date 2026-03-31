<?php
// app/models/student.php

/**
 * Get all students with department names
 */
function getAllStudents($db) {
    $query = "SELECT s.*, d.department_name 
              FROM students s 
              LEFT JOIN departments d ON s.department_id = d.department_id 
              ORDER BY s.student_id DESC";
    $result = mysqli_query($db, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Get single student by ID
 */
function getStudentById($db, $id) {
    $stmt = mysqli_prepare($db, "SELECT s.*, d.department_name 
                                 FROM students s 
                                 LEFT JOIN departments d ON s.department_id = d.department_id 
                                 WHERE s.student_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

/**
 * Create new student
 */
function createStudent($db, $firstName, $lastName, $email, $phone, $dateOfBirth, $gender, $address, $departmentId, $enrollmentYear) {
    $stmt = mysqli_prepare($db, "INSERT INTO students 
        (first_name, last_name, email, phone, date_of_birth, gender, address, department_id, enrollment_year) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssssssis", $firstName, $lastName, $email, $phone, $dateOfBirth, $gender, $address, $departmentId, $enrollmentYear);
    return mysqli_stmt_execute($stmt);
}

/**
 * Update existing student
 */
function updateStudent($db, $id, $firstName, $lastName, $email, $phone, $dateOfBirth, $gender, $address, $departmentId, $enrollmentYear) {
    $stmt = mysqli_prepare($db, "UPDATE students 
        SET first_name = ?, last_name = ?, email = ?, phone = ?, date_of_birth = ?, 
            gender = ?, address = ?, department_id = ?, enrollment_year = ? 
        WHERE student_id = ?");
    mysqli_stmt_bind_param($stmt, "sssssssisi", $firstName, $lastName, $email, $phone, $dateOfBirth, $gender, $address, $departmentId, $enrollmentYear, $id);
    return mysqli_stmt_execute($stmt);
}

/**
 * Delete student
 */
function deleteStudent($db, $id) {
    $stmt = mysqli_prepare($db, "DELETE FROM students WHERE student_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

/**
 * Get all departments for dropdown
 */
function getAllDepartments($db) {
    $result = mysqli_query($db, "SELECT * FROM departments ORDER BY department_name");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>

