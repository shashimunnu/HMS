<?php
require_once __DIR__ . '/config.php';

// Global connection variable
$conn = null;

/**
 * Creates a new database connection
 * @return mysqli Database connection object
 * @throws Exception if connection fails
 */
function createConnection() {
    global $conn;
    
    try {
        // Create connection with database selected
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Create database if it doesn't exist
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
        if (!$conn->query($sql)) {
            throw new Exception("Error creating database: " . $conn->error);
        }
        
        // Set charset to utf8mb4
        if (!$conn->set_charset("utf8mb4")) {
            throw new Exception("Error setting charset utf8mb4: " . $conn->error);
        }
        
        return $conn;
        
    } catch (Exception $e) {
        // Log the error
        error_log("Database Connection Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Gets the database connection, creating it if necessary
 * @return mysqli Database connection object
 */
function getConnection() {
    global $conn;
    
    if ($conn === null) {
        $conn = createConnection();
    }
    
    // Check if connection is still alive
    if (!$conn->ping()) {
        $conn = createConnection();
    }
    
    return $conn;
}

/**
 * Safely closes the database connection
 */
function closeConnection() {
    global $conn;
    
    if ($conn !== null) {
        $conn->close();
        $conn = null;
    }
}

// Create initial connection
try {
    $conn = getConnection();
    
    // Check if essential tables exist
    $required_tables = ['users', 'doctors', 'patients', 'appointments', 'services'];
    $tables_exist = true;
    
    foreach ($required_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if (!$result || $result->num_rows === 0) {
            $tables_exist = false;
            break;
        }
    }
    
    // If tables don't exist, run the database setup
    if (!$tables_exist) {
        require_once __DIR__ . '/../database/database_setup.php';
    }
    
} catch (Exception $e) {
    // Log the error with timestamp and details
    $error_message = date('Y-m-d H:i:s') . " - Database Error: " . $e->getMessage() . "\n";
    error_log($error_message, 3, __DIR__ . '/../logs/db_errors.log');
    
    // Display user-friendly message
    die("Database connection error. Please check the system logs for details.");
}

// Register shutdown function to close connection
register_shutdown_function('closeConnection');
?>
