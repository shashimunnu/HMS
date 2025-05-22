<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

try {
    $conn = getDBConnection();
    
    // Check if admin exists
    $sql = "SELECT id, username, role FROM users WHERE username = 'admin'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "Admin user exists. Creating new password...<br>";
        
        // Update admin password
        $new_password = password_hash('admin123', PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = ? WHERE username = 'admin'";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("s", $new_password);
        
        if ($stmt->execute()) {
            echo "Admin password updated successfully<br>";
            echo "Username: admin<br>";
            echo "Password: admin123<br>";
        } else {
            echo "Failed to update admin password: " . $conn->error . "<br>";
        }
    } else {
        echo "Admin user not found. Creating new admin user...<br>";
        
        // Create admin user
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, role) VALUES ('admin', ?, 'admin')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $password);
        
        if ($stmt->execute()) {
            echo "Admin user created successfully<br>";
            echo "Username: admin<br>";
            echo "Password: admin123<br>";
        } else {
            echo "Failed to create admin user: " . $conn->error . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
