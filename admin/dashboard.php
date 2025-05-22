<?php
// Database connection and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Get database connection
$conn = Database::getInstance()->getConnection();

// Initialize stats with default values
$stats = [
    'doctors' => 0,
    'patients' => 0,
    'appointments' => 0,
    'services' => 0
];

// Safely fetch statistics
try {
    $stats['doctors'] = $conn->query("SELECT COUNT(*) as count FROM doctors")->fetch_assoc()['count'] ?? 0;
    $stats['patients'] = $conn->query("SELECT COUNT(*) as count FROM patients")->fetch_assoc()['count'] ?? 0;
    $stats['appointments'] = $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'] ?? 0;
    $stats['services'] = $conn->query("SELECT COUNT(*) as count FROM services")->fetch_assoc()['count'] ?? 0;
} catch (Exception $e) {
    error_log("Error fetching dashboard statistics: " . $e->getMessage());
}

// Get recent appointments
$recent_appointments_query = "
    SELECT a.*, p.name as patient_name, d.name as doctor_name 
    FROM appointments a 
    LEFT JOIN patients p ON a.patient_id = p.id 
    LEFT JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.appointment_date >= CURDATE() 
    ORDER BY a.appointment_date ASC 
    LIMIT 5";

try {
    $recent_appointments = $conn->query($recent_appointments_query);
} catch (Exception $e) {
    error_log("Error fetching recent appointments: " . $e->getMessage());
    $recent_appointments = null;
}

// Safely fetch upcoming appointments
try {
    $upcoming_appointments = $conn->query("
        SELECT a.*, p.name as patient_name, d.name as doctor_name 
        FROM appointments a 
        LEFT JOIN patients p ON a.patient_id = p.id 
        LEFT JOIN doctors d ON a.doctor_id = d.id 
        WHERE a.appointment_date >= CURDATE() 
        AND a.status = 'Scheduled'
        ORDER BY a.appointment_date, a.appointment_time 
        LIMIT 5
    ");
} catch (Exception $e) {
    $upcoming_appointments = null;
    error_log("Error fetching upcoming appointments: " . $e->getMessage());
}

// Include header
require_once __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .stat-card {
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            color: #0d6efd;
        }
        .quick-action {
            transition: all 0.3s ease;
        }
        .quick-action:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        main {
            padding-top: 48px;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../includes/admin_navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="doctors.php">
                                <i class="bi bi-people"></i> Doctors
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="patients.php">
                                <i class="bi bi-person"></i> Patients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="appointments.php">
                                <i class="bi bi-calendar-check"></i> Appointments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="services.php">
                                <i class="bi bi-gear"></i> Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="bi bi-person-badge"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="bi bi-gear-fill"></i> Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Print</button>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2 text-muted">Total Doctors</h6>
                                        <h2 class="card-title mb-0"><?php echo $stats['doctors']; ?></h2>
                                    </div>
                                    <i class="bi bi-people stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2 text-muted">Total Patients</h6>
                                        <h2 class="card-title mb-0"><?php echo $stats['patients']; ?></h2>
                                    </div>
                                    <i class="bi bi-person stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2 text-muted">Appointments</h6>
                                        <h2 class="card-title mb-0"><?php echo $stats['appointments']; ?></h2>
                                    </div>
                                    <i class="bi bi-calendar-check stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2 text-muted">Services</h6>
                                        <h2 class="card-title mb-0"><?php echo $stats['services']; ?></h2>
                                    </div>
                                    <i class="bi bi-gear stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="appointments.php?action=add" class="list-group-item list-group-item-action quick-action">
                                        <i class="bi bi-plus-circle me-2"></i> New Appointment
                                    </a>
                                    <a href="patients.php?action=add" class="list-group-item list-group-item-action quick-action">
                                        <i class="bi bi-person-plus me-2"></i> Add Patient
                                    </a>
                                    <a href="doctors.php?action=add" class="list-group-item list-group-item-action quick-action">
                                        <i class="bi bi-person-plus-fill me-2"></i> Add Doctor
                                    </a>
                                    <a href="services.php?action=add" class="list-group-item list-group-item-action quick-action">
                                        <i class="bi bi-plus-square me-2"></i> Add Service
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Activity</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php if ($recent_appointments && $recent_appointments->num_rows > 0): ?>
                                        <?php while ($appointment = $recent_appointments->fetch_assoc()): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($appointment['patient_name'] ?? 'Unknown Patient'); ?></h6>
                                                    <small><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></small>
                                                </div>
                                                <p class="mb-1">Appointment with <?php echo htmlspecialchars($appointment['doctor_name'] ?? 'Unknown Doctor'); ?></p>
                                                <small class="text-muted">Status: <?php echo $appointment['status']; ?></small>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="list-group-item">No recent appointments</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Appointments -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Upcoming Appointments</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($upcoming_appointments && $upcoming_appointments->num_rows > 0): ?>
                                        <?php while ($appointment = $upcoming_appointments->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($appointment['patient_name'] ?? 'Unknown Patient'); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['doctor_name'] ?? 'Unknown Doctor'); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $appointment['status']; ?></span>
                                                </td>
                                                <td>
                                                    <a href="appointments.php?action=view&id=<?php echo $appointment['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="appointments.php?action=edit&id=<?php echo $appointment['id']; ?>" 
                                                       class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No upcoming appointments</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
