<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

try {
    // Test database connection
    $conn = getDBConnection();
    echo "Database connection successful<br>";
    
    // Test query to get admin user
    $sql = "SELECT id, username, role FROM users WHERE username = 'admin'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "Found admin user:<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Role: " . $user['role'] . "<br>";
    } else {
        echo "Admin user not found<br>";
        
        // Try to create admin user
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, email, role) VALUES ('admin', ?, 'admin@hospital.com', 'admin')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $admin_password);
        
        if ($stmt->execute()) {
            echo "Created new admin user<br>";
            echo "Username: admin<br>";
            echo "Password: admin123<br>";
        } else {
            echo "Failed to create admin user: " . $conn->error . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
