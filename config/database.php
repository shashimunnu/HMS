<?php
// Database configuration constants
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'hospital_db');

// Database connection class
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            // First try to connect without database
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }

            // Create database if it doesn't exist
            if (!$this->conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME)) {
                throw new Exception("Failed to create database: " . $this->conn->error);
            }

            // Select the database
            if (!$this->conn->select_db(DB_NAME)) {
                throw new Exception("Failed to select database: " . $this->conn->error);
            }
            
            // Set charset
            if (!$this->conn->set_charset("utf8mb4")) {
                throw new Exception("Failed to set charset: " . $this->conn->error);
            }
            
        } catch (Exception $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("An error occurred while connecting to the database. Please try again later.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        if ($this->conn !== null) {
            $this->conn->close();
            $this->conn = null;
        }
    }

    // Prevent cloning of the instance
    private function __clone() {}

    public function __wakeup() {
        $this->__construct();
    }
}

// Function to get database connection
function getDBConnection() {
    try {
        $db = Database::getInstance();
        return $db->getConnection();
    } catch (Exception $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        die("Database connection failed. Please check the logs.");
    }
}

// Register shutdown function to close connection
register_shutdown_function(function() {
    if (Database::getInstance() !== null) {
        Database::getInstance()->closeConnection();
    }
});
?>
