<?php
session_start();
include '../config/db_connect.php';
include '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get monthly appointments statistics
$monthly_stats = $conn->query("
    SELECT 
        DATE_FORMAT(appointment_date, '%Y-%m') as month,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN status = 'Scheduled' THEN 1 ELSE 0 END) as scheduled
    FROM appointments
    WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(appointment_date, '%Y-%m')
    ORDER BY month ASC
")->fetch_all(MYSQLI_ASSOC);

// Get department statistics for active doctors
$department_stats = $conn->query("
    SELECT 
        specialization,
        COUNT(*) as doctor_count,
        (SELECT COUNT(*) 
         FROM appointments a 
         JOIN doctors d2 ON a.doctor_id = d2.id 
         WHERE d2.specialization = doctors.specialization 
         AND d2.status = 'active') as appointment_count
    FROM doctors
    WHERE status = 'active'
    GROUP BY specialization
    ORDER BY doctor_count DESC
")->fetch_all(MYSQLI_ASSOC);

// Get daily appointment distribution for active doctors
$daily_distribution = $conn->query("
    SELECT 
        DAYNAME(appointment_date) as day_name,
        COUNT(*) as appointment_count
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    AND d.status = 'active'
    GROUP BY DAYNAME(appointment_date), DAYOFWEEK(appointment_date)
    ORDER BY DAYOFWEEK(appointment_date)
")->fetch_all(MYSQLI_ASSOC);

// Get gender distribution of patients
$gender_distribution = $conn->query("
    SELECT 
        gender,
        COUNT(*) as count
    FROM patients
    GROUP BY gender
")->fetch_all(MYSQLI_ASSOC);

// Prepare response
$response = [
    'monthly_stats' => $monthly_stats,
    'department_stats' => $department_stats,
    'daily_distribution' => $daily_distribution,
    'gender_distribution' => $gender_distribution
];

echo json_encode($response);
?>
