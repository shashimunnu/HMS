<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /HMS/auth/login.php');
    exit();
}

// Get the current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/HMS/admin/dashboard">HMS Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" 
                       href="/HMS/admin/dashboard">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'doctors.php' ? 'active' : ''; ?>" 
                       href="/HMS/admin/doctors">
                        <i class="bi bi-person-badge"></i> Doctors
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'patients.php' ? 'active' : ''; ?>" 
                       href="/HMS/admin/patients">
                        <i class="bi bi-people"></i> Patients
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'appointments.php' ? 'active' : ''; ?>" 
                       href="/HMS/admin/appointments">
                        <i class="bi bi-calendar-check"></i> Appointments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'services.php' ? 'active' : ''; ?>" 
                       href="/HMS/admin/services">
                        <i class="bi bi-gear"></i> Services
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>" 
                       href="/HMS/admin/users">
                        <i class="bi bi-people-fill"></i> Users
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> 
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li>
                            <a class="dropdown-item" href="/HMS/admin/profile">
                                <i class="bi bi-person"></i> Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/HMS/admin/settings">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="/HMS/auth/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
