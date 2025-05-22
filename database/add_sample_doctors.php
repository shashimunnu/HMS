<?php
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getDBConnection();
    
    // Clear existing doctors and their user accounts
    $conn->query("DELETE FROM doctors");
    $conn->query("DELETE FROM users WHERE role = 'doctor'");
    
    // Sample doctors data
    $doctors = [
        [
            'username' => 'dr.smith',
            'password' => password_hash('doctor123', PASSWORD_DEFAULT),
            'email' => 'dr.smith@hospital.com',
            'name' => 'Dr. John Smith',
            'specialization' => 'Cardiologist',
            'phone' => '+1 (555) 123-4567',
            'address' => '123 Medical Center Drive',
            'consultation_fee' => 150.00
        ],
        [
            'username' => 'dr.jones',
            'password' => password_hash('doctor123', PASSWORD_DEFAULT),
            'email' => 'dr.jones@hospital.com',
            'name' => 'Dr. Sarah Jones',
            'specialization' => 'Pediatrician',
            'phone' => '+1 (555) 234-5678',
            'address' => '456 Children\'s Way',
            'consultation_fee' => 100.00
        ],
        [
            'username' => 'dr.wilson',
            'password' => password_hash('doctor123', PASSWORD_DEFAULT),
            'email' => 'dr.wilson@hospital.com',
            'name' => 'Dr. David Wilson',
            'specialization' => 'Orthopedic Surgeon',
            'phone' => '+1 (555) 345-6789',
            'address' => '789 Bone & Joint Ave',
            'consultation_fee' => 200.00
        ],
        [
            'username' => 'dr.patel',
            'password' => password_hash('doctor123', PASSWORD_DEFAULT),
            'email' => 'dr.patel@hospital.com',
            'name' => 'Dr. Priya Patel',
            'specialization' => 'Dentist',
            'phone' => '+1 (555) 456-7890',
            'address' => '321 Dental Square',
            'consultation_fee' => 120.00
        ],
        [
            'username' => 'dr.chen',
            'password' => password_hash('doctor123', PASSWORD_DEFAULT),
            'email' => 'dr.chen@hospital.com',
            'name' => 'Dr. Wei Chen',
            'specialization' => 'Neurologist',
            'phone' => '+1 (555) 567-8901',
            'address' => '654 Brain Center Rd',
            'consultation_fee' => 180.00
        ],
        [
            'username' => 'dr.rodriguez',
            'password' => password_hash('doctor123', PASSWORD_DEFAULT),
            'email' => 'dr.rodriguez@hospital.com',
            'name' => 'Dr. Maria Rodriguez',
            'specialization' => 'General Physician',
            'phone' => '+1 (555) 678-9012',
            'address' => '987 Family Medicine Blvd',
            'consultation_fee' => 90.00
        ]
    ];
    
    // Add each doctor
    foreach ($doctors as $doctor) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // First create user account
            $user_sql = "INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, 'doctor', 'active')";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("sss", $doctor['username'], $doctor['password'], $doctor['email']);
            $user_stmt->execute();
            
            $user_id = $conn->insert_id;
            
            // Then create doctor profile
            $doctor_sql = "INSERT INTO doctors (user_id, name, specialization, phone, email, address, consultation_fee, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";
            $doctor_stmt = $conn->prepare($doctor_sql);
            $doctor_stmt->bind_param("isssssd", $user_id, $doctor['name'], $doctor['specialization'], $doctor['phone'], $doctor['email'], $doctor['address'], $doctor['consultation_fee']);
            $doctor_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            echo "Added doctor: " . $doctor['name'] . "\n";
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            echo "Error adding doctor " . $doctor['name'] . ": " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nSample doctors added successfully!\n";
    
} catch (Exception $e) {
    error_log("Error in add_sample_doctors.php: " . $e->getMessage());
    die("Failed to add sample doctors: " . $e->getMessage() . "\n");
}
