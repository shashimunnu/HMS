<?php
require_once '../config/database.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

try {
    $conn = getDBConnection();
    $error = '';
    $success = '';

    // Get list of active doctors with their consultation fees
    $doctor_query = "SELECT d.id, d.name, d.specialization, d.consultation_fee, d.phone, d.email 
                     FROM doctors d 
                     WHERE d.status = 'active'
                     ORDER BY d.name";
    $doctors = $conn->query($doctor_query);

    if (!$doctors) {
        throw new Exception("Error loading doctors: " . $conn->error);
    }

    // Get existing appointments for the current user
    $user_id = $_SESSION['user_id'];
    $appointments_query = "SELECT a.*, d.name as doctor_name, p.name as patient_name 
                          FROM appointments a
                          INNER JOIN doctors d ON a.doctor_id = d.id
                          INNER JOIN patients p ON a.patient_id = p.id
                          WHERE p.id IN (
                              SELECT id FROM patients WHERE email = (
                                  SELECT username FROM users WHERE id = ?
                              )
                          )
                          AND a.status != 'cancelled' AND d.status = 'active'
                          ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    $stmt = $conn->prepare($appointments_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $appointments = $stmt->get_result();

    if (!$appointments) {
        throw new Exception("Error loading appointments: " . $conn->error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Get form data
            $doctor_id = filter_input(INPUT_POST, 'doctor_id', FILTER_SANITIZE_NUMBER_INT);
            $appointment_date = htmlspecialchars(trim($_POST['appointment_date'] ?? ''), ENT_QUOTES, 'UTF-8');
            $appointment_time = htmlspecialchars(trim($_POST['appointment_time'] ?? ''), ENT_QUOTES, 'UTF-8');
            $reason = htmlspecialchars(trim($_POST['reason'] ?? ''), ENT_QUOTES, 'UTF-8');
            $patient_name = htmlspecialchars(trim($_POST['patient_name'] ?? ''), ENT_QUOTES, 'UTF-8');

            // Validate inputs
            if (!$doctor_id || !$appointment_date || !$appointment_time || !$patient_name) {
                throw new Exception("Please fill in all required fields");
            }

            // Validate date format
            $date = DateTime::createFromFormat('Y-m-d', $appointment_date);
            if (!$date || $date->format('Y-m-d') !== $appointment_date) {
                throw new Exception("Invalid date format");
            }

            // Validate time format
            $time = DateTime::createFromFormat('H:i:s', $appointment_time);
            if (!$time || $time->format('H:i:s') !== $appointment_time) {
                throw new Exception("Invalid time format");
            }

            // Get user email (using username as email)
            $user_sql = "SELECT username FROM users WHERE id = ?";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            
            if (!$user_result || $user_result->num_rows === 0) {
                throw new Exception("User not found");
            }
            
            $user_data = $user_result->fetch_assoc();
            $user_email = $user_data['username']; // Using username as email
            
            // Check if patient record exists
            $check_patient_sql = "SELECT id FROM patients WHERE email = ?";
            $check_patient_stmt = $conn->prepare($check_patient_sql);
            $check_patient_stmt->bind_param("s", $user_email);
            $check_patient_stmt->execute();
            $patient_result = $check_patient_stmt->get_result();
            
            if ($patient_result->num_rows > 0) {
                $patient_id = $patient_result->fetch_assoc()['id'];
                // Update patient name if it has changed
                $update_patient_sql = "UPDATE patients SET name = ? WHERE id = ?";
                $update_patient_stmt = $conn->prepare($update_patient_sql);
                $update_patient_stmt->bind_param("si", $patient_name, $patient_id);
                if (!$update_patient_stmt->execute()) {
                    throw new Exception("Error updating patient record: " . $update_patient_stmt->error);
                }
            } else {
                // Create new patient record
                $create_patient_sql = "INSERT INTO patients (name, email, dob, gender, phone) 
                                     VALUES (?, ?, CURDATE(), 'other', '0000000000')";
                $create_patient_stmt = $conn->prepare($create_patient_sql);
                $create_patient_stmt->bind_param("ss", $patient_name, $user_email);
                if (!$create_patient_stmt->execute()) {
                    throw new Exception("Error creating patient record: " . $create_patient_stmt->error);
                }
                $patient_id = $conn->insert_id;
            }
            
            // Check if appointment slot is available
            $check_sql = "SELECT COUNT(*) as count FROM appointments 
                         WHERE doctor_id = ? AND appointment_date = ? 
                         AND appointment_time = ? AND status != 'cancelled'";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("iss", $doctor_id, $appointment_date, $appointment_time);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            
            if ($count > 0) {
                throw new Exception("This time slot is already booked. Please choose another time.");
            }
            
            // Create appointment
            $sql = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiss", $patient_id, $doctor_id, $appointment_date, $appointment_time);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create appointment: " . $stmt->error);
            }
            
            // Commit transaction
            $conn->commit();
            $success = "Appointment booked successfully!";
            
            // Refresh the appointments list
            $stmt = $conn->prepare($appointments_query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $appointments = $stmt->get_result();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
} catch (Exception $e) {
    error_log("Appointment booking error: " . $e->getMessage());
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .consultation-fee {
            color: #198754;
            font-weight: bold;
        }
        .alert {
            margin-top: 1rem;
        }
        .form-label.required:after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <?php include_once '../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Book an Appointment</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="appointmentForm">
                            <div class="mb-3">
                                <label for="doctor_id" class="form-label required">Select Doctor</label>
                                <select class="form-select" id="doctor_id" name="doctor_id" required>
                                    <option value="">Choose a doctor...</option>
                                    <?php while ($doctor = $doctors->fetch_assoc()): ?>
                                        <option value="<?php echo $doctor['id']; ?>" 
                                                data-fee="<?php echo $doctor['consultation_fee'] ? number_format($doctor['consultation_fee'], 2) : 'N/A'; ?>">
                                            <?php echo htmlspecialchars($doctor['name']); ?> 
                                            (<?php echo htmlspecialchars($doctor['specialization']); ?>)
                                            <?php 
                                            if ($doctor['consultation_fee']) {
                                                echo ' - $' . number_format($doctor['consultation_fee'], 2);
                                            }
                                            ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="patient_name" class="form-label required">Your Name</label>
                                <input type="text" class="form-control" id="patient_name" name="patient_name" required>
                            </div>

                            <div class="mb-3">
                                <label for="appointment_date" class="form-label required">Appointment Date</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="appointment_time" class="form-label required">Appointment Time</label>
                                <select class="form-select" id="appointment_time" name="appointment_time" required>
                                    <option value="">Select time...</option>
                                    <?php
                                    $start = strtotime('09:00');
                                    $end = strtotime('17:00');
                                    for ($time = $start; $time <= $end; $time += 1800) {
                                        printf(
                                            '<option value="%s">%s</option>',
                                            date('H:i:s', $time),
                                            date('h:i A', $time)
                                        );
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for Visit</label>
                                <textarea class="form-control" id="reason" name="reason" rows="3"></textarea>
                            </div>

                            <div id="consultation_fee_display" class="alert alert-info" style="display: none;"></div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Book Appointment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Your Appointments</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($appointments->num_rows === 0): ?>
                            <p class="text-muted">You have no upcoming appointments.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Doctor</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($appointment = $appointments->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $appointment['status'] === 'pending' ? 'warning' : 
                                                            ($appointment['status'] === 'completed' ? 'success' : 'primary'); 
                                                    ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Display consultation fee when doctor is selected
        document.getElementById('doctor_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const fee = selectedOption.getAttribute('data-fee');
            const feeDisplay = document.getElementById('consultation_fee_display');
            
            if (feeDisplay) {
                if (fee && fee !== 'N/A') {
                    feeDisplay.textContent = `Consultation Fee: {fee}`;
                    feeDisplay.style.display = 'block';
                } else {
                    feeDisplay.style.display = 'none';
                }
            }
        });

        // Form validation
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            const date = document.getElementById('appointment_date').value;
            const time = document.getElementById('appointment_time').value;
            const now = new Date();
            const appointmentDate = new Date(date + ' ' + time);

            if (appointmentDate < now) {
                e.preventDefault();
                alert('Please select a future date and time for your appointment.');
            }
        });
    </script>
</body>
</html>
