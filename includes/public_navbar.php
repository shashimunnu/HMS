<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="/HMS/public/index.php">
            <i class="bi bi-hospital"></i> HMS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/HMS/public/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/HMS/public/services.php">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/HMS/public/facilities.php">Facilities</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/HMS/public/doctors.php">Doctors</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/HMS/public/appointments.php">Appointments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/HMS/public/contact.php">Contact</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> 
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <li>
                                    <a class="dropdown-item" href="/HMS/admin/dashboard.php">
                                        <i class="bi bi-speedometer2"></i> Admin Dashboard
                                    </a>
                                </li>
                            <?php elseif ($_SESSION['role'] === 'doctor'): ?>
                                <li>
                                    <a class="dropdown-item" href="/HMS/doctor/dashboard.php">
                                        <i class="bi bi-speedometer2"></i> Doctor Dashboard
                                    </a>
                                </li>
                            <?php else: ?>
                                <li>
                                    <a class="dropdown-item" href="/HMS/patient/dashboard.php">
                                        <i class="bi bi-speedometer2"></i> Patient Dashboard
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item" href="/HMS/profile.php">
                                    <i class="bi bi-person"></i> Profile
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
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/HMS/auth/login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
