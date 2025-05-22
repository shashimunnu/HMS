<?php
session_start();
include '../../config/db_connect.php';
include '../../includes/functions.php';

checkLogin();

// Handle appointment deletion
if (isset($_POST['delete_appointment'])) {
    $appointment_id = sanitize($_POST['appointment_id']);
    $sql = "DELETE FROM appointments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
}

// Fetch all appointments with patient and doctor details
$sql = "SELECT a.*, p.name as patient_name, d.name as doctor_name 
        FROM appointments a
        LEFT JOIN patients p ON a.patient_id = p.id
        LEFT JOIN doctors d ON a.doctor_id = d.id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Management - HMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Appointment Management</h2>
            <a href="add.php" class="btn btn-primary">Schedule New Appointment</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></td>
                        <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $row['status'] == 'Completed' ? 'success' : 
                                    ($row['status'] == 'Cancelled' ? 'danger' : 'primary'); 
                            ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this appointment?');">
                                <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_appointment" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
