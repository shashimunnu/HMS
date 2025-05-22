<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";

// Create connection without database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS hospital_db";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db("hospital_db");

// Read and execute SQL file
$sql = file_get_contents(__DIR__ . '/hospital_db.sql');
if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "Database schema created successfully<br>";
} else {
    echo "Error creating schema: " . $conn->error . "<br>";
}

// Create admin user
$admin_username = 'admin';
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_role = 'admin';

// Check if admin exists
$check_sql = "SELECT id FROM users WHERE username = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $admin_username);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    // Create admin user if not exists
    $insert_sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sss", $admin_username, $admin_password, $admin_role);
    
    if ($insert_stmt->execute()) {
        echo "Admin user created successfully<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Error creating admin user: " . $insert_stmt->error . "<br>";
    }
} else {
    echo "Admin user already exists<br>";
}

$conn->close();
echo "Setup completed!<br>";
?>
