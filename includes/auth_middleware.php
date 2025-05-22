<?php
function checkAuth() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }

    return true;
}

function requireAuth($allowed_roles = []) {
    if (!checkAuth()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: " . BASE_URL . "/login");
        exit;
    }

    // If specific roles are required, check user's role
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        header("HTTP/1.0 403 Forbidden");
        include BASE_PATH . '/includes/403.php';
        exit;
    }

    return true;
}

function redirectIfLoggedIn() {
    if (checkAuth()) {
        switch ($_SESSION['role']) {
            case 'admin':
                header("Location: " . BASE_URL . "/admin/dashboard");
                break;
            case 'doctor':
                header("Location: " . BASE_URL . "/doctor/dashboard");
                break;
            case 'patient':
                header("Location: " . BASE_URL . "/patient/dashboard");
                break;
            default:
                header("Location: " . BASE_URL . "/");
        }
        exit;
    }
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function isAdmin() {
    return getUserRole() === 'admin';
}

function isDoctor() {
    return getUserRole() === 'doctor';
}

function isPatient() {
    return getUserRole() === 'patient';
}
?>
