<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Get database connection
    $conn = getDBConnection();

    // Admin user details
    $admin_username = 'admin';
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $admin_email = 'admin@hospital.com';
    $admin_role = 'admin';
    $status = 'active';

    // First check if users table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'users'");
    if ($table_check->num_rows == 0) {
        // Create users table if it doesn't exist
        $create_table_sql = "CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            role ENUM('admin', 'doctor', 'patient', 'receptionist') NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (!$conn->query($create_table_sql)) {
            throw new Exception("Error creating users table: " . $conn->error);
        }
    }

    // Check if admin user exists
    $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $admin_username, $admin_email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows == 0) {
        // Create admin user
        $insert_sql = "INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sssss", $admin_username, $admin_password, $admin_email, $admin_role, $status);
        
        if ($insert_stmt->execute()) {
            echo "Admin user created successfully!\n";
            echo "Username: " . $admin_username . "\n";
            echo "Password: admin123\n";
            echo "Email: " . $admin_email . "\n";
        } else {
            throw new Exception("Error creating admin user: " . $conn->error);
        }
        
        $insert_stmt->close();
    } else {
        echo "Admin user already exists!\n";
    }
    
    $check_stmt->close();

} catch (Exception $e) {
    error_log("Error in create_admin.php: " . $e->getMessage());
    die("Error: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
