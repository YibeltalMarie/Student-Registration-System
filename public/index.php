
<?php
/**
 * Front Controller - Entry point for Student Registration System
 * 
 * This file handles all incoming requests and routes them to the appropriate
 * controller actions via the router.
 */

// Define application constants
define('APP_NAME', 'Student Registration System');
define('BASE_PATH', __DIR__ . '/..');

// Load the router - this handles all the routing logic
require_once BASE_PATH . '/routes/web.php';


?>
