<?php
require_once __DIR__ . '/../config/database.php';

function initializeDatabase() {
    try {
        $conn = getDBConnection();
        
        // Read and execute SQL file
        $sql = file_get_contents(__DIR__ . '/hospital_db.sql');
        
        // Split SQL file into individual queries
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        
        // Execute each query
        foreach ($queries as $query) {
            if (!empty($query)) {
                if (!$conn->query($query)) {
                    throw new Exception("Error executing query: " . $conn->error);
                }
            }
        }
        
        echo "Database initialized successfully!";
        
    } catch (Exception $e) {
        error_log("Database Initialization Error: " . $e->getMessage());
        die("Failed to initialize database: " . $e->getMessage());
    }
}

// Run the initialization
initializeDatabase();
?>
