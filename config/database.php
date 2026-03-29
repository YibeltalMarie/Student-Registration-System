<?php
// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'yibeltal');
define('DB_PASS', 'mot94her');
define('DB_NAME', 'student_registration_system');

// Create connection
function dbConnect() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    echo "Connected successfully!";

    return $conn;
}
?>