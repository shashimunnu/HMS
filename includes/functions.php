<?php
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /HMS/auth/login.php");
        exit();
    }
}

function hasPermission($requiredRole) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] != $requiredRole) {
        return false;
    }
    return true;
}

function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

function getFullName($userId) {
    global $conn;
    $sql = "SELECT name FROM doctors WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['name'];
    }
    return "";
}
?>
