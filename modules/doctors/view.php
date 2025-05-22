<?php
session_start();
include '../../config/db_connect.php';
include '../../includes/functions.php';

checkLogin();

$doctor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch doctor data with user information
$stmt = $conn->prepare("
    SELECT d.*, u.username, u.role, 
           (SELECT COUNT(*) FROM appointments WHERE doctor_id = d.id) as total_appointments
    FROM doctors d 
    LEFT JOIN users u ON d.user_id = u.id 
    WHERE d.id = ?
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if (!$doctor) {
    header('Location: index.php');
    exit();
}

// Fetch recent appointments
$stmt = $conn->prepare("
    SELECT a.*, p.name as patient_name 
    FROM appointments a
    LEFT JOIN patients p ON a.patient_id = p.id
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT 5
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Doctor - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-person-badge"></i> Doctor Details</h4>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="edit.php?id=<?php echo $doctor_id; ?>" class="btn btn-light">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($doctor['name']); ?></p>
                                <p><strong>Specialization:</strong> <?php echo htmlspecialchars($doctor['specialization']); ?></p>
                                <p><strong>Username:</strong> <?php echo htmlspecialchars($doctor['username']); ?></p>
                                <p><strong>Role:</strong> <?php echo ucfirst($doctor['role']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($doctor['phone']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></p>
                                <p><strong>Total Appointments:</strong> <?php echo $doctor['total_appointments']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Recent Appointments</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($appointments->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Patient</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($appointment = $appointments->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $appointment['status'] === 'completed' ? 'success' : 
                                                            ($appointment['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                                    ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No recent appointments found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Statistics</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Fetch appointment statistics
                        $stmt = $conn->prepare("
                            SELECT 
                                COUNT(*) as total,
                                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                            FROM appointments 
                            WHERE doctor_id = ?
                        ");
                        $stmt->bind_param("i", $doctor_id);
                        $stmt->execute();
                        $stats = $stmt->get_result()->fetch_assoc();
                        ?>
                        <div class="mb-3">
                            <h6>Total Appointments</h6>
                            <h2 class="text-primary"><?php echo $stats['total']; ?></h2>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Completed</span>
                                <span class="text-success"><?php echo $stats['completed']; ?></span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $stats['total'] > 0 ? ($stats['completed']/$stats['total']*100) : 0; ?>%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Pending</span>
                                <span class="text-warning"><?php echo $stats['pending']; ?></span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-warning" style="width: <?php echo $stats['total'] > 0 ? ($stats['pending']/$stats['total']*100) : 0; ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Cancelled</span>
                                <span class="text-danger"><?php echo $stats['cancelled']; ?></span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-danger" style="width: <?php echo $stats['total'] > 0 ? ($stats['cancelled']/$stats['total']*100) : 0; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-calendar-week"></i> Schedule</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Fetch upcoming appointments
                        $stmt = $conn->prepare("
                            SELECT appointment_date, COUNT(*) as count
                            FROM appointments 
                            WHERE doctor_id = ? 
                            AND appointment_date >= CURDATE()
                            AND status = 'pending'
                            GROUP BY appointment_date
                            ORDER BY appointment_date
                            LIMIT 5
                        ");
                        $stmt->bind_param("i", $doctor_id);
                        $stmt->execute();
                        $schedule = $stmt->get_result();
                        
                        if ($schedule->num_rows > 0):
                            while ($day = $schedule->fetch_assoc()):
                        ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo date('M d, Y', strtotime($day['appointment_date'])); ?></span>
                                <span class="badge bg-primary"><?php echo $day['count']; ?> appointments</span>
                            </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <p class="text-muted mb-0">No upcoming appointments scheduled.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
