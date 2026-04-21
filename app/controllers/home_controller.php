

<?php
// app/controllers/home_controller.php

require_once __DIR__ . '/../../config/database.php';

function homePage() {
    global $db;
    
    // Get total student count
    $countResult = mysqli_query($db, "SELECT COUNT(*) as total FROM students");
    $studentCount = mysqli_fetch_assoc($countResult)['total'];
    
    // Get active students count (where status is 'active')
    $activeResult = mysqli_query($db, "SELECT COUNT(*) as total FROM students WHERE status = 'active' OR status IS NULL");
    $activeStudents = mysqli_fetch_assoc($activeResult)['total'];
    
    // Get recent students (last 5)
    $recentStudents = mysqli_query($db, "SELECT s.*, d.department_name 
                                         FROM students s 
                                         LEFT JOIN departments d ON s.department_id = d.department_id 
                                         ORDER BY s.student_id DESC 
                                         LIMIT 5");
    
    // Load the home view
    require_once __DIR__ . '/../views/home.php';
}
?>

