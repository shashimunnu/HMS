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

// Get all users
try {
    $query = "SELECT id, username, role FROM users ORDER BY username";
    $result = $conn->query($query);
} catch (Exception $e) {
    error_log("Error fetching users: " . $e->getMessage());
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
                        <h5 class="mb-0">Manage Users</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-plus-circle me-2"></i>Add User
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $row['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                                        <?php echo ucfirst($row['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary edit-user" 
                                                            data-id="<?php echo $row['id']; ?>"
                                                            data-username="<?php echo htmlspecialchars($row['username']); ?>"
                                                            data-role="<?php echo htmlspecialchars($row['role']); ?>"
                                                            data-bs-toggle="tooltip" 
                                                            title="Edit User">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <?php if ($row['id'] !== $_SESSION['user_id']): ?>
                                                        <button class="btn btn-sm btn-danger delete-user" 
                                                                data-id="<?php echo $row['id']; ?>"
                                                                data-bs-toggle="tooltip" 
                                                                title="Delete User">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No users found</td>
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addUserForm" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="doctor">Doctor</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editUserForm" method="POST">
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Leave blank to keep current password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="doctor">Doctor</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
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

        // Handle edit user button clicks
        document.querySelectorAll('.edit-user').forEach(button => {
            button.addEventListener('click', function() {
                const modal = document.getElementById('editUserModal');
                const form = modal.querySelector('form');
                
                form.querySelector('[name="id"]').value = this.dataset.id;
                form.querySelector('[name="username"]').value = this.dataset.username;
                form.querySelector('[name="role"]').value = this.dataset.role;
                
                // Clear password fields
                form.querySelector('[name="password"]').value = '';
                form.querySelector('[name="confirm_password"]').value = '';
                
                new bootstrap.Modal(modal).show();
            });
        });

        // Handle delete user button clicks
        document.querySelectorAll('.delete-user').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this user?')) {
                    const userId = this.dataset.id;
                    // Add your delete logic here
                }
            });
        });
    </script>
</body>
</html>
