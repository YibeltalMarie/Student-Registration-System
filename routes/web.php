<?php
// Load controllers
require_once __DIR__ . '/../app/controllers/student_controller.php';
require_once __DIR__ . '/../app/controllers/home_controller.php';

// Get URL parameter
$url = $_GET['url'] ?? 'home';  // Default is now 'home' instead of 'students'
$id = $_GET['id'] ?? null;

// Route to appropriate function
switch($url) {
    // Home page (what users see first)
    case 'home':
        homePage();
        break;
    
    // Student routes
    case 'students':
        studentIndex();
        break;
    case 'students/create':
        studentCreate();
        break;
    case 'students/edit':
        if ($id) studentEdit($id);
        else header('Location: index.php?url=students');
        break;
    case 'students/delete':
        if ($id) studentDelete($id);
        else header('Location: index.php?url=students');
        break;
    
    // 404 - Page not found
    default:
        echo "<h1>404 - Page Not Found</h1>";
        echo "<p>The page you requested does not exist.</p>";
        echo "<a href='index.php'>Go to Home</a>";
        break;
}
?>




