<?php
// Database connection and functions
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    $_SESSION['error'] = "Please login as a patient to book an appointment.";
    header("Location: ../../auth/login.php");
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../../patient/book-appointment.php");
    exit;
}

// Get database connection
$conn = Database::getInstance()->getConnection();

// Get form data
$patient_id = $_SESSION['user_id'];
$doctor_id = $_POST['doctor_id'] ?? '';
$appointment_date = $_POST['appointment_date'] ?? '';
$appointment_time = $_POST['appointment_time'] ?? '';
$reason = $_POST['reason'] ?? '';
$notes = $_POST['notes'] ?? '';

// Validate required fields
if (empty($doctor_id) || empty($appointment_date) || empty($appointment_time) || empty($reason)) {
    $_SESSION['error'] = "All required fields must be filled out.";
    header("Location: ../../patient/book-appointment.php");
    exit;
}

// Validate appointment date (must be in the future)
if (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
    $_SESSION['error'] = "Appointment date must be in the future.";
    header("Location: ../../patient/book-appointment.php");
    exit;
}

try {
    // Check if the doctor exists and is active
    $stmt = $conn->prepare("SELECT id FROM doctors WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Selected doctor is not available.";
        header("Location: ../../patient/book-appointment.php");
        exit;
    }

    // Check if the time slot is available
    $stmt = $conn->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'");
    $stmt->bind_param("iss", $doctor_id, $appointment_date, $appointment_time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "This time slot is already booked. Please select another time.";
        header("Location: ../../patient/book-appointment.php");
        exit;
    }

    // Insert the appointment
    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("iissss", $patient_id, $doctor_id, $appointment_date, $appointment_time, $reason, $notes);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Appointment booked successfully! Please wait for confirmation.";
    } else {
        $_SESSION['error'] = "Error booking appointment. Please try again.";
    }

} catch (Exception $e) {
    error_log("Error booking appointment: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while booking the appointment. Please try again.";
}

header("Location: ../../patient/book-appointment.php");
exit;
