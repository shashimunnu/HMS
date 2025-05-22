<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Define navigation structure
$nav_items = [
    'public' => [
        'Home' => '/HMS/index.php',
        'Doctors' => '/HMS/doctors.php',
        'Services' => '/HMS/services.php',
        'Contact' => '/HMS/contact.php'
    ],
    'admin' => [
        'Dashboard' => '/HMS/admin/dashboard.php',
        'Doctors' => '/HMS/admin/doctors.php',
        'Patients' => '/HMS/admin/patients.php',
        'Appointments' => '/HMS/admin/appointments.php',
        'Services' => '/HMS/admin/services.php',
        'Users' => '/HMS/admin/users.php'
    ],
    'doctor' => [
        'Dashboard' => '/HMS/doctor/dashboard.php',
        'My Appointments' => '/HMS/doctor/appointments.php',
        'My Schedule' => '/HMS/doctor/schedule.php',
        'My Patients' => '/HMS/doctor/patients.php'
    ],
    'patient' => [
        'Dashboard' => '/HMS/patient/dashboard.php',
        'Book Appointment' => '/HMS/patient/book-appointment.php',
        'My Appointments' => '/HMS/patient/appointments.php',
        'Medical History' => '/HMS/patient/medical-history.php'
    ]
];

// Get navigation based on user role
function get_nav_menu() {
    global $nav_items;
    
    if (!isset($_SESSION['user_id'])) {
        return $nav_items['public'] ?? [];
    }
    
    $role = $_SESSION['role'] ?? 'public';
    return $nav_items[$role] ?? $nav_items['public'] ?? [];
}

// Function to check if menu item is active
function is_menu_active($url) {
    $current_url = $_SERVER['REQUEST_URI'] ?? '';
    return strpos($current_url, $url) !== false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/HMS/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/HMS/index.php">
                <i class="bi bi-hospital me-2"></i>HMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php 
                    $menu_items = get_nav_menu();
                    if (is_array($menu_items) || is_object($menu_items)):
                        foreach ($menu_items as $label => $url): 
                    ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_menu_active($url) ? 'active' : ''; ?>" 
                               href="<?php echo htmlspecialchars($url); ?>">
                                <?php echo htmlspecialchars($label); ?>
                            </a>
                        </li>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </ul>

                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" 
                               data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i>
                                <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="/HMS/profile.php">
                                        <i class="bi bi-person me-2"></i>Profile
                                    </a>
                                </li>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <li>
                                        <a class="dropdown-item" href="/HMS/admin/settings.php">
                                            <i class="bi bi-gear me-2"></i>Settings
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/HMS/auth/logout.php">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/HMS/auth/login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show">
                <?php 
                echo htmlspecialchars($_SESSION['flash_message']);
                unset($_SESSION['flash_message']);
                unset($_SESSION['flash_type']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
