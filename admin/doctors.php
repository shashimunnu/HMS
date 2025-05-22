<?php
// Database connection and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Get database connection
$conn = Database::getInstance()->getConnection();

// Get all doctors
try {
    $query = "SELECT * FROM doctors ORDER BY name";
    $result = $conn->query($query);
} catch (Exception $e) {
    error_log("Error fetching doctors: " . $e->getMessage());
    $result = null;
}

// Include header
require_once __DIR__ . '/../includes/header.php';
?>

<body>
    <?php require_once __DIR__ . '/../includes/admin_navbar.php'; ?>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Manage Doctors</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                            <i class="bi bi-plus-circle me-2"></i>Add Doctor
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Specialization</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['specialization']); ?></td>
                                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $row['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($row['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary edit-doctor" 
                                                            data-id="<?php echo $row['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                                            data-specialization="<?php echo htmlspecialchars($row['specialization']); ?>"
                                                            data-phone="<?php echo htmlspecialchars($row['phone']); ?>"
                                                            data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                            data-status="<?php echo htmlspecialchars($row['status']); ?>"
                                                            data-bs-toggle="tooltip" 
                                                            title="Edit Doctor">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-doctor" 
                                                            data-id="<?php echo $row['id']; ?>"
                                                            data-bs-toggle="tooltip" 
                                                            title="Delete Doctor">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No doctors found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Doctor Modal -->
    <div class="modal fade" id="addDoctorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Doctor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addDoctorForm" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Specialization</label>
                            <select class="form-select" name="specialization" required>
                                <option value="">Select Specialization</option>
                                <option value="Cardiology">Cardiology</option>
                                <option value="Dermatology">Dermatology</option>
                                <option value="Neurology">Neurology</option>
                                <option value="Orthopedics">Orthopedics</option>
                                <option value="Pediatrics">Pediatrics</option>
                                <option value="Psychiatry">Psychiatry</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Doctor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Doctor Modal -->
    <div class="modal fade" id="editDoctorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Doctor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editDoctorForm" method="POST">
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Specialization</label>
                            <select class="form-select" name="specialization" required>
                                <option value="">Select Specialization</option>
                                <option value="Cardiology">Cardiology</option>
                                <option value="Dermatology">Dermatology</option>
                                <option value="Neurology">Neurology</option>
                                <option value="Orthopedics">Orthopedics</option>
                                <option value="Pediatrics">Pediatrics</option>
                                <option value="Psychiatry">Psychiatry</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Doctor</button>
                    </div>
                </form>
            </div>
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

        // Handle edit doctor button clicks
        document.querySelectorAll('.edit-doctor').forEach(button => {
            button.addEventListener('click', function() {
                const modal = document.getElementById('editDoctorModal');
                const form = modal.querySelector('form');
                
                form.querySelector('[name="id"]').value = this.dataset.id;
                form.querySelector('[name="name"]').value = this.dataset.name;
                form.querySelector('[name="specialization"]').value = this.dataset.specialization;
                form.querySelector('[name="phone"]').value = this.dataset.phone;
                form.querySelector('[name="email"]').value = this.dataset.email;
                form.querySelector('[name="status"]').value = this.dataset.status;
                
                new bootstrap.Modal(modal).show();
            });
        });

        // Handle delete doctor button clicks
        document.querySelectorAll('.delete-doctor').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this doctor?')) {
                    const doctorId = this.dataset.id;
                    // Add your delete logic here
                }
            });
        });
    </script>
</body>
</html>
