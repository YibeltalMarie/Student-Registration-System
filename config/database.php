<?php
// Function to load environment variables
function loadEnv() {
    $envFile = __DIR__ . '/../.env';
    
    // Check if .env file exists
    if (!file_exists($envFile)) {
        die('.env file not found! Please copy .env.example to .env and configure your database settings.');
    }
    
    // Read .env file line by line
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments (lines starting with #)
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Split by equals sign
        $parts = explode('=', $line, 2);
        
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Set environment variable
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Load environment variables
loadEnv();

// Define constants from environment
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'student_registration_system');
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'development');

// Database connection function
function dbConnect() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        // Log the error
        error_log("Database connection failed: " . mysqli_connect_error());
        
        // Show different error in development vs production
        if (ENVIRONMENT === 'development') {
            die("Database connection failed: " . mysqli_connect_error() . 
                "<br>Check your .env file settings.");
        } else {
            die("Database connection error. Please try again later.");
        }
    }
    
    // Set charset for proper encoding
    mysqli_set_charset($conn, "utf8mb4");
    
    return $conn;
}

// Create connection
$db = dbConnect();
?>