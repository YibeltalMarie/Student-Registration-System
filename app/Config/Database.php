<?php

namespace App\Config;

use mysqli;
use RuntimeException;

/**
 * Database Singleton
 *
 * PSR-4: namespace App\Config  =>  file must be at app/Config/Database.php
 * composer.json autoload: "App\\" => "app/"
 * Therefore App\Config\Database  =>  app/Config/Database.php  ✅
 *
 * The old config/database.php (outside app/) was the cause of
 * "Class App\Config\Database not found" errors.
 */
class Database
{
    private static ?Database $instance = null;
    private mysqli $connection;

    private function __construct()
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $name = $_ENV['DB_NAME'] ?? 'student_registration_system';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';

        $this->connection = new mysqli($host, $user, $pass, $name);

        if ($this->connection->connect_error) {
            throw new RuntimeException(
                'Database connection failed: ' . $this->connection->connect_error
            );
        }

        $this->connection->set_charset('utf8mb4');
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    // Prevent cloning of the singleton
    private function __clone() {}
}
