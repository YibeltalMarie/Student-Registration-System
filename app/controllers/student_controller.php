<?php

function students_index($db) {
    $students  = student_get_all($db);
    $pageTitle = 'Students';
    require_once ROOT_PATH . '/app/views/students/index.php';
}

function students_create($db) {
    $departments = department_get_all($db);
    $pageTitle   = 'Add Student';
    require_once ROOT_PATH . '/app/views/students/create.php';
}

function students_store($db) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?page=students');
        exit;
    }

    $result = student_create($db, $_POST);

    if ($result) {
        header('Location: index.php?page=students&success=Student+added+successfully');
    } else {
        header('Location: index.php?page=students&error=' . urlencode(mysqli_error($db)));
    }
    exit;
}

function students_edit($db) {
    $id      = $_GET['id'] ?? 0;
    $student = student_get_by_id($db, $id);

    if (!$student) {
        header('Location: index.php?page=students&error=Student+not+found');
        exit;
    }

    $departments = department_get_all($db);
    $pageTitle   = 'Edit Student';
    require_once ROOT_PATH . '/app/views/students/edit.php';
}

function students_update($db) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?page=students');
        exit;
    }

    $id     = $_POST['student_id'] ?? 0;
    $result = student_update($db, $id, $_POST);

    if ($result) {
        header('Location: index.php?page=students&success=Student+updated+successfully');
    } else {
        header('Location: index.php?page=students&error=' . urlencode(mysqli_error($db)));
    }
    exit;
}

function students_delete($db) {
    $id     = $_GET['id'] ?? 0;
    $result = student_delete($db, $id);

    if ($result) {
        header('Location: index.php?page=students&success=Student+deleted+successfully');
    } else {
        header('Location: index.php?page=students&error=' . urlencode(mysqli_error($db)));
    }
    exit;
}

