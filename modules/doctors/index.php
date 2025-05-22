<?php
session_start();
include '../../config/db_connect.php';
include '../../includes/functions.php';

checkLogin();

// Handle doctor deletion
if (isset($_POST['delete_doctor'])) {
    $doctor_id = sanitize($_POST['doctor_id']);
    $sql = "DELETE FROM doctors WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
}

// Fetch all doctors
$sql = "SELECT d.*, u.username, u.role FROM doctors d 
        LEFT JOIN users u ON d.user_id = u.id 
        ORDER BY d.name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Management - HMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Doctor Management</h2>
            <a href="add.php" class="btn btn-primary">Add New Doctor</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Specialization</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id'] ?? ''; ?></td>
                        <td><?php echo htmlspecialchars($row['name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['specialization'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['username'] ?? ''); ?></td>
                        <td>
                            <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this doctor?');">
                                <input type="hidden" name="doctor_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_doctor" class="btn btn-danger btn-sm">
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
