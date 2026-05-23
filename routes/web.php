<?php

use App\Core\Router;

/** @var Router $router */

// Auth
$router->get('/login',           'AuthController@loginForm');
$router->post('/login',          'AuthController@login');
$router->get('/register',        'AuthController@registerForm');
$router->post('/register',       'AuthController@register');
$router->get('/verify-email',    'AuthController@verifyEmail');
$router->get('/forgot-password', 'AuthController@forgotPasswordForm');
$router->post('/forgot-password','AuthController@forgotPassword');
$router->get('/reset-password',  'AuthController@resetPasswordForm');
$router->post('/reset-password', 'AuthController@resetPassword');
$router->get('/change-password',  'AuthController@changePasswordForm');
$router->post('/change-password', 'AuthController@changePassword');

// Restore default password (student forgot changed password)
$router->get('/restore-default',  'AuthController@restoreDefaultForm');
$router->post('/restore-default', 'AuthController@restoreDefault');
$router->post('/logout',         'AuthController@logout');

// Dashboard
$router->get('/',                'DashboardController@index');

// Students
$router->get('/students',               'StudentController@index');
$router->get('/students/create',        'StudentController@create');
$router->post('/students/store',        'StudentController@store');
$router->get('/students/ranking',       'StudentController@ranking');
$router->get('/students/export-csv',    'StudentController@exportCsv');
$router->get('/students/export-pdf',    'StudentController@exportPdf');
$router->post('/students/import',       'StudentController@importCsv');
$router->get('/students/csv-template',  'StudentController@csvTemplate');
$router->get('/students/show/{id}',     'StudentController@show');
$router->get('/students/edit/{id}',     'StudentController@edit');
$router->post('/students/update/{id}',  'StudentController@update');
$router->post('/students/delete/{id}',  'StudentController@destroy');

// Departments
$router->get('/departments',                'DepartmentController@index');
$router->get('/departments/create',         'DepartmentController@create');
$router->post('/departments/store',         'DepartmentController@store');
$router->get('/departments/edit/{id}',      'DepartmentController@edit');
$router->post('/departments/update/{id}',   'DepartmentController@update');
$router->post('/departments/delete/{id}',   'DepartmentController@destroy');

// Courses
$router->get('/courses',               'CourseController@index');
$router->get('/courses/create',        'CourseController@create');
$router->post('/courses/store',        'CourseController@store');
$router->get('/courses/edit/{id}',     'CourseController@edit');
$router->post('/courses/update/{id}',  'CourseController@update');
$router->post('/courses/delete/{id}',  'CourseController@destroy');

// Enrollments
$router->get('/enrollments',                     'EnrollmentController@index');
$router->get('/enrollments/create',              'EnrollmentController@create');
$router->post('/enrollments/store',              'EnrollmentController@store');
$router->get('/enrollments/edit/{id}',           'EnrollmentController@edit');
$router->post('/enrollments/grade/{id}',         'EnrollmentController@updateGrade');
$router->get('/enrollments/available-courses',   'EnrollmentController@availableCourses');

// Reports
$router->get('/reports',                        'ReportController@index');
$router->get('/reports/export/pdf',             'ReportController@exportPdf');
$router->get('/reports/export/csv',             'ReportController@exportCsv');
$router->post('/bulk-email',                    'ReportController@bulkEmail');
$router->post('/reports/backup',                'ReportController@backup');
$router->get('/reports/download-backup/{file}', 'ReportController@downloadBackup');
$router->get('/activity-logs',                  'ReportController@activityLogs');

// REST API
$router->get('/api/students',          'ApiController@students');
$router->get('/api/students/{id}',     'ApiController@getStudent');
$router->post('/api/students',         'ApiController@createStudent');
$router->put('/api/students/{id}',     'ApiController@updateStudent');
$router->delete('/api/students/{id}',  'ApiController@deleteStudent');
