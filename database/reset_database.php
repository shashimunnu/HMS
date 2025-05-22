<?php
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getDBConnection();
    
    // Drop existing database
    $conn->query("DROP DATABASE IF EXISTS hospital_db");
    echo "Existing database dropped successfully.\n";
    
    // Initialize database with new structure
    require_once __DIR__ . '/init_database.php';
    
    // Create admin user
    require_once __DIR__ . '/create_admin.php';
    
    echo "\nDatabase reset and initialized successfully!";
    
} catch (Exception $e) {
    error_log("Database Reset Error: " . $e->getMessage());
    die("Error resetting database: " . $e->getMessage());
}
?>
