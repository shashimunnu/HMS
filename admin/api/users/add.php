<?php
session_start();
require_once '../../../config/db_connect.php';
require_once '../../../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log the received data
    error_log("Received POST data: " . print_r($_POST, true));
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Debug: Log the processed data
    error_log("Processed data - Username: $username, Role: $role");

    // Validate input
    if (empty($username) || empty($password) || empty($role)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }

    try {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            exit;
        }

        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $username, $password_hash, $role);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User added successfully']);
        } else {
            error_log("MySQL Error: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Error adding user: ' . $conn->error]);
        }
    } catch (Exception $e) {
        error_log("Exception: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
