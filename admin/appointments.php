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

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

try {
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Process form submission
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    // Add new appointment logic
                    $patient_id = $_POST['patient_id'];
                    $doctor_id = $_POST['doctor_id'];
                    $appointment_date = $_POST['appointment_date'];
                    $appointment_time = $_POST['appointment_time'];
                    $status = $_POST['status'];
                    
                    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("iisss", $patient_id, $doctor_id, $appointment_date, $appointment_time, $status);
                    
                    if ($stmt->execute()) {
                        $message = "Appointment added successfully";
                    } else {
                        $error = "Error adding appointment: " . $stmt->error;
                    }
                    break;

                case 'edit':
                    // Edit appointment logic
                    $id = $_POST['id'];
                    $patient_id = $_POST['patient_id'];
                    $doctor_id = $_POST['doctor_id'];
                    $appointment_date = $_POST['appointment_date'];
                    $appointment_time = $_POST['appointment_time'];
                    $status = $_POST['status'];
                    
                    $stmt = $conn->prepare("UPDATE appointments SET patient_id=?, doctor_id=?, appointment_date=?, appointment_time=?, status=? WHERE id=?");
                    $stmt->bind_param("iisssi", $patient_id, $doctor_id, $appointment_date, $appointment_time, $status, $id);
                    
                    if ($stmt->execute()) {
                        $message = "Appointment updated successfully";
                    } else {
                        $error = "Error updating appointment: " . $stmt->error;
                    }
                    break;

                case 'delete':
                    // Delete appointment logic
                    $id = $_POST['id'];
                    
                    $stmt = $conn->prepare("DELETE FROM appointments WHERE id=?");
                    $stmt->bind_param("i", $id);
                    
                    if ($stmt->execute()) {
                        $message = "Appointment deleted successfully";
                    } else {
                        $error = "Error deleting appointment: " . $stmt->error;
                    }
                    break;
            }
        }
    }

    // Get appointments data
    $appointments_query = "
        SELECT a.*, p.name as patient_name, d.name as doctor_name 
        FROM appointments a 
        LEFT JOIN patients p ON a.patient_id = p.id 
        LEFT JOIN doctors d ON a.doctor_id = d.id 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    $appointments_result = $conn->query($appointments_query);

    // Get patients list for form
    $patients_result = $conn->query("SELECT id, name FROM patients ORDER BY name");
    
    // Get doctors list for form
    $doctors_result = $conn->query("SELECT id, name FROM doctors ORDER BY name");

} catch (Exception $e) {
    error_log("Error in appointments.php: " . $e->getMessage());
    $error = "An error occurred while processing your request. Please try again later.";
}

// Include header
require_once __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/admin_navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <?php 
                        switch($action) {
                            case 'add':
                                echo 'Add New Appointment';
                                break;
                            case 'edit':
                                echo 'Edit Appointment';
                                break;
                            default:
                                echo 'Manage Appointments';
                        }
                        ?>
                    </h1>
                    <?php if ($action === 'list'): ?>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> New Appointment
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
                <?php endif; ?>

                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <!-- Appointment Form -->
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="appointments.php">
                                <?php if ($action === 'edit'): ?>
                                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <?php endif; ?>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="patient_id" class="form-label">Patient *</label>
                                        <select class="form-select" id="patient_id" name="patient_id" required>
                                            <option value="">Select Patient</option>
                                            <?php while ($patient = $patients_result->fetch_assoc()): ?>
                                                <option value="<?php echo $patient['id']; ?>" 
                                                    <?php echo (isset($appointment) && $appointment['patient_id'] == $patient['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($patient['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="doctor_id" class="form-label">Doctor *</label>
                                        <select class="form-select" id="doctor_id" name="doctor_id" required>
                                            <option value="">Select Doctor</option>
                                            <?php while ($doctor = $doctors_result->fetch_assoc()): ?>
                                                <option value="<?php echo $doctor['id']; ?>"
                                                    <?php echo (isset($appointment) && $appointment['doctor_id'] == $doctor['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($doctor['name']); ?> 
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="appointment_date" class="form-label">Date *</label>
                                        <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                               value="<?php echo isset($appointment) ? $appointment['appointment_date'] : ''; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="appointment_time" class="form-label">Time *</label>
                                        <input type="time" class="form-control" id="appointment_time" name="appointment_time" 
                                               value="<?php echo isset($appointment) ? $appointment['appointment_time'] : ''; ?>" required>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="appointments.php" class="btn btn-secondary me-md-2">Cancel</a>
                                    <button type="submit" name="<?php echo $action === 'edit' ? 'update_appointment' : 'add_appointment'; ?>" class="btn btn-primary">
                                        <?php echo $action === 'edit' ? 'Update' : 'Add'; ?> Appointment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Appointments List -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Patient</th>
                                            <th>Doctor</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($appointments_result && $appointments_result->num_rows > 0): ?>
                                            <?php while ($row = $appointments_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $row['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($row['patient_name'] ?? 'Unknown Patient'); ?></td>
                                                    <td><?php echo htmlspecialchars($row['doctor_name'] ?? 'Unknown Doctor'); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></td>
                                                    <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                                                    <td>
                                                        <a href="?action=edit&id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteAppointment(<?php echo $row['id']; ?>)">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No appointments found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Initialize modals
        var modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
            new bootstrap.Modal(modal);
        });
    </script>
</body>
</html>
