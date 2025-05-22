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

try {
    // Fetch all users except the current user
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id != ? ORDER BY username");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'id' => $row['id'],
            'username' => htmlspecialchars($row['username']),
            'role' => $row['role']
        ];
    }
    
    echo json_encode(['success' => true, 'users' => $users]);
} catch (Exception $e) {
    error_log("Error fetching users: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching users']);
}
?>
