<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Get database connection
$conn = Database::getInstance()->getConnection();

// Function to check if a table exists
function tableExists($tableName) {
    global $conn;
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result && $result->num_rows > 0;
}

// Check if essential tables exist
if (!tableExists('users') || !tableExists('doctors') || !tableExists('services')) {
    // Tables don't exist, run initialization script
    require_once __DIR__ . '/config/init_db.php';
}

// Get statistics for public view
try {
    $stats = [
        'doctors' => $conn->query("SELECT COUNT(*) as count FROM doctors WHERE status = 'active'")->fetch_assoc()['count'],
        'services' => $conn->query("SELECT COUNT(*) as count FROM services WHERE status = 'active'")->fetch_assoc()['count']
    ];
} catch (Exception $e) {
    error_log("Error getting statistics: " . $e->getMessage());
    $stats = ['doctors' => 0, 'services' => 0];
}

// Include header
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4">Welcome to HMS</h1>
                <p class="lead">Your Health, Our Priority</p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="mt-4">
                        <a href="/HMS/auth/login.php" class="btn btn-light btn-lg me-3">Login</a>
                        <a href="/HMS/auth/register.php" class="btn btn-outline-light btn-lg">Register</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <img src="/HMS/assets/images/Hospital.jpg" alt="Hospital" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</div>

<!-- Quick Access Section -->
<?php if (isset($_SESSION['user_id'])): ?>
    <div class="container mt-5">
        <div class="row">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <!-- Admin Quick Links -->
                <div class="col-md-3">
                    <div class="card text-center mb-4">
                        <div class="card-body">
                            <i class="bi bi-speedometer2 display-4 text-primary"></i>
                            <h5 class="card-title mt-3">Admin Dashboard</h5>
                            <a href="/HMS/admin/dashboard.php" class="btn btn-primary">Access</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center mb-4">
                        <div class="card-body">
                            <i class="bi bi-people display-4 text-primary"></i>
                            <h5 class="card-title mt-3">Manage Users</h5>
                            <a href="/HMS/admin/users.php" class="btn btn-primary">Access</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center mb-4">
                        <div class="card-body">
                            <i class="bi bi-calendar-check display-4 text-primary"></i>
                            <h5 class="card-title mt-3">Appointments</h5>
                            <a href="/HMS/admin/appointments.php" class="btn btn-primary">Access</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center mb-4">
                        <div class="card-body">
                            <i class="bi bi-gear display-4 text-primary"></i>
                            <h5 class="card-title mt-3">Settings</h5>
                            <a href="/HMS/admin/settings.php" class="btn btn-primary">Access</a>
                        </div>
                    </div>
                </div>
            <?php elseif ($_SESSION['role'] === 'doctor'): ?>
                <!-- Doctor Quick Links -->
                <div class="col-md-3">
                    <div class="card text-center mb-4">
                        <div class="card-body">
                            <i class="bi bi-speedometer2 display-4 text-primary"></i>
                            <h5 class="card-title mt-3">Doctor Dashboard</h5>
                            <a href="/HMS/doctor/dashboard.php" class="btn btn-primary">Access</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center mb-4">
                        <div class="card-body">
                            <i class="bi bi-calendar-check display-4 text-primary"></i>
                            <h5 class="card-title mt-3">My Appointments</h5>
                            <a href="/HMS/doctor/appointments.php" class="btn btn-primary">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center mb-4">
                        <div class="card-body">
                            <i class="bi bi-clock display-4 text-primary"></i>
                            <h5 class="card-title mt-3">My Schedule</h5>
                            <a href="/HMS/doctor/schedule.php" class="btn btn-primary">Manage</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center mb-4">
                        <div class="card-body">
                            <i class="bi bi-gear display-4 text-primary"></i>
                            <h5 class="card-title mt-3">Settings</h5>
                            <a href="/HMS/doctor/settings.php" class="btn btn-primary">Access</a>
                        </div>
                    </div>
                </div>
            <?php elseif ($_SESSION['role'] === 'patient'): ?>
                <!-- Patient Quick Links -->
                <div class="col-md-3">
                    <div class="card text-center mb-4">
                        <div class="card-body">
                            <i class="bi bi-speedometer2 display-4 text-primary"></i>
                            <h5 class="card-title mt-3">Patient Dashboard</h5>
                            <a href="/HMS/patient/dashboard.php" class="btn btn-primary">Access</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center mb-4">
                        <div class="card-body">
                            <i class="bi bi-calendar-plus display-4 text-primary"></i>
                            <h5 class="card-title mt-3">Book Appointment</h5>
                            <a href="/HMS/patient/book-appointment.php" class="btn btn-primary">Book Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center mb-4">
                        <div class="card-body">
                            <i class="bi bi-journal-text display-4 text-primary"></i>
                            <h5 class="card-title mt-3">Medical History</h5>
                            <a href="/HMS/patient/medical-history.php" class="btn btn-primary">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center mb-4">
                        <div class="card-body">
                            <i class="bi bi-gear display-4 text-primary"></i>
                            <h5 class="card-title mt-3">Settings</h5>
                            <a href="/HMS/patient/settings.php" class="btn btn-primary">Access</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Services Section -->
<div class="container mt-5">
    <h2 class="text-center mb-4">Our Services</h2>
    <div class="row">
        <?php
        try {
            $services_query = "SELECT * FROM services WHERE status = 'active' LIMIT 6";
            $services_result = $conn->query($services_query);
            
            if ($services_result && $services_result->num_rows > 0) {
                while ($service = $services_result->fetch_assoc()) {
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($service['description']); ?></p>
                                <p class="card-text text-primary">₹<?php echo number_format($service['cost'], 2); ?></p>
                                <?php if (!isset($_SESSION['user_id'])): ?>
                                    <a href="/HMS/auth/login.php" class="btn btn-outline-primary">Book Now</a>
                                <?php elseif ($_SESSION['role'] === 'patient'): ?>
                                    <a href="/HMS/patient/book-appointment.php" class="btn btn-outline-primary">Book Now</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12 text-center"><p>No services available at the moment.</p></div>';
            }
        } catch (Exception $e) {
            error_log("Error fetching services: " . $e->getMessage());
            echo '<div class="col-12 text-center"><p>Error loading services. Please try again later.</p></div>';
        }
        ?>
    </div>
</div>

<!-- Statistics Section -->
<div class="bg-light py-5 mt-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4 mb-md-0">
                <i class="bi bi-people display-4 text-primary"></i>
                <h2 class="mt-3"><?php echo $stats['doctors']; ?>+</h2>
                <p class="text-muted">Expert Doctors</p>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <i class="bi bi-heart-pulse display-4 text-primary"></i>
                <h2 class="mt-3"><?php echo $stats['services']; ?>+</h2>
                <p class="text-muted">Medical Services</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-emoji-smile display-4 text-primary"></i>
                <h2 class="mt-3">1000+</h2>
                <p class="text-muted">Happy Patients</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>