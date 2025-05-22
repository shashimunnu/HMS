<?php
// Database connection and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    // If not logged in, redirect to login page with a message
    $_SESSION['error'] = "Please login as a patient to book an appointment.";
    header("Location: ../auth/login.php");
    exit;
}

// Get database connection
$conn = Database::getInstance()->getConnection();

// Get all active doctors
try {
    $query = "SELECT id, name, specialization FROM doctors WHERE status = 'active' ORDER BY name";
    $doctors = $conn->query($query);
} catch (Exception $e) {
    error_log("Error fetching doctors: " . $e->getMessage());
    $doctors = null;
}

// Get available time slots (you may want to customize this based on your requirements)
$timeSlots = [
    '09:00:00' => '09:00 AM',
    '09:30:00' => '09:30 AM',
    '10:00:00' => '10:00 AM',
    '10:30:00' => '10:30 AM',
    '11:00:00' => '11:00 AM',
    '11:30:00' => '11:30 AM',
    '12:00:00' => '12:00 PM',
    '12:30:00' => '12:30 PM',
    '14:00:00' => '02:00 PM',
    '14:30:00' => '02:30 PM',
    '15:00:00' => '03:00 PM',
    '15:30:00' => '03:30 PM',
    '16:00:00' => '04:00 PM',
    '16:30:00' => '04:30 PM',
    '17:00:00' => '05:00 PM'
];

// Include header
require_once __DIR__ . '/../includes/header.php';
?>

<body>
    <?php require_once __DIR__ . '/../includes/patient_navbar.php'; ?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Book an Appointment</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form id="appointmentForm" method="POST" action="../api/appointments/book.php">
                            <div class="mb-3">
                                <label class="form-label">Select Doctor</label>
                                <select class="form-select" name="doctor_id" required>
                                    <option value="">Choose a doctor</option>
                                    <?php if ($doctors && $doctors->num_rows > 0): ?>
                                        <?php while ($doctor = $doctors->fetch_assoc()): ?>
                                            <option value="<?php echo $doctor['id']; ?>">
                                                Dr. <?php echo htmlspecialchars($doctor['name']); ?> 
                                                (<?php echo htmlspecialchars($doctor['specialization']); ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Appointment Date</label>
                                <input type="date" class="form-control" name="appointment_date" 
                                       min="<?php echo date('Y-m-d'); ?>" 
                                       max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Preferred Time</label>
                                <select class="form-select" name="appointment_time" required>
                                    <option value="">Select time slot</option>
                                    <?php foreach ($timeSlots as $value => $label): ?>
                                        <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reason for Visit</label>
                                <textarea class="form-control" name="reason" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Additional Notes</label>
                                <textarea class="form-control" name="notes" rows="2"></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Book Appointment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get today's date in YYYY-MM-DD format
            const today = new Date().toISOString().split('T')[0];
            
            // Set minimum date to today
            document.querySelector('input[name="appointment_date"]').min = today;
            
            // Calculate date 30 days from now
            const maxDate = new Date();
            maxDate.setDate(maxDate.getDate() + 30);
            const maxDateStr = maxDate.toISOString().split('T')[0];
            
            // Set maximum date to 30 days from now
            document.querySelector('input[name="appointment_date"]').max = maxDateStr;
        });
    </script>
</body>
</html>
