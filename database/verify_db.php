<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hospital_db";

try {
    // Create connection without database first
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql)) {
        echo "Database checked/created successfully<br>";
    }

    // Select the database
    $conn->select_db($dbname);

    // Drop tables in correct order (reverse of creation order due to foreign keys)
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $tables_to_drop = ['appointments', 'doctors', 'patients', 'services', 'users'];
    foreach ($tables_to_drop as $table) {
        $conn->query("DROP TABLE IF EXISTS $table");
        echo "Dropped $table table<br>";
    }
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    // Create tables if they don't exist
    $tables = [
        "users" => "CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'doctor', 'nurse', 'receptionist') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "doctors" => "CREATE TABLE IF NOT EXISTS doctors (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            name VARCHAR(100) NOT NULL,
            specialization VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            email VARCHAR(100),
            consultation_fee DECIMAL(10,2) DEFAULT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            FOREIGN KEY (user_id) REFERENCES users(id)
        )",
        
        "services" => "CREATE TABLE IF NOT EXISTS services (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(50),
            image VARCHAR(255),
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "patients" => "CREATE TABLE IF NOT EXISTS patients (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            dob DATE,
            gender ENUM('Male', 'Female', 'Other'),
            phone VARCHAR(20),
            email VARCHAR(100),
            address TEXT,
            blood_group VARCHAR(5),
            status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "appointments" => "CREATE TABLE IF NOT EXISTS appointments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            patient_id INT,
            doctor_id INT,
            appointment_date DATE,
            appointment_time TIME,
            notes TEXT,
            status ENUM('Scheduled', 'Completed', 'Cancelled', 'No Show') DEFAULT 'Scheduled',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES patients(id),
            FOREIGN KEY (doctor_id) REFERENCES doctors(id)
        )"
    ];

    foreach ($tables as $table_name => $sql) {
        if ($conn->query($sql)) {
            echo "Table '$table_name' checked/created successfully<br>";
        } else {
            echo "Error with table '$table_name': " . $conn->error . "<br>";
        }
    }

    // Check if admin user exists, if not create one
    $check_admin = $conn->query("SELECT id FROM users WHERE username = 'admin'");
    if ($check_admin->num_rows == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, role) VALUES ('admin', '$admin_password', 'admin')";
        if ($conn->query($sql)) {
            echo "Admin user created successfully<br>";
            echo "Username: admin<br>";
            echo "Password: admin123<br>";
        } else {
            echo "Error creating admin user: " . $conn->error . "<br>";
        }
    } else {
        echo "Admin user already exists<br>";
    }

    // Add sample doctors if none exist
    $check_doctors = $conn->query("SELECT COUNT(*) as count FROM doctors");
    if ($check_doctors->fetch_assoc()['count'] == 0) {
        $default_doctors = [
            [
                'name' => 'Dr. John Smith',
                'specialization' => 'Cardiology',
                'phone' => '+1 (555) 123-4567',
                'email' => 'john.smith@hospital.com',
                'consultation_fee' => 150.00,
                'status' => 'active'
            ],
            [
                'name' => 'Dr. Sarah Johnson',
                'specialization' => 'Pediatrics',
                'phone' => '+1 (555) 234-5678',
                'email' => 'sarah.johnson@hospital.com',
                'consultation_fee' => 100.00,
                'status' => 'active'
            ],
            [
                'name' => 'Dr. Michael Chen',
                'specialization' => 'Neurology',
                'phone' => '+1 (555) 345-6789',
                'email' => 'michael.chen@hospital.com',
                'consultation_fee' => 200.00,
                'status' => 'active'
            ],
            [
                'name' => 'Dr. Emily Brown',
                'specialization' => 'Orthopedics',
                'phone' => '+1 (555) 456-7890',
                'email' => 'emily.brown@hospital.com',
                'consultation_fee' => 175.00,
                'status' => 'active'
            ]
        ];

        $insert_doctor = $conn->prepare("INSERT INTO doctors (name, specialization, phone, email, consultation_fee, status) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($default_doctors as $doctor) {
            $insert_doctor->bind_param("ssssds", 
                $doctor['name'], 
                $doctor['specialization'], 
                $doctor['phone'], 
                $doctor['email'],
                $doctor['consultation_fee'],
                $doctor['status']
            );
            $insert_doctor->execute();
        }
        echo "Added sample doctors<br>";
    }

    // Add default services if none exist
    $check_services = $conn->query("SELECT COUNT(*) as count FROM services");
    if ($check_services->fetch_assoc()['count'] == 0) {
        $default_services = [
            [
                'name' => 'Emergency Care',
                'description' => '24/7 emergency medical services with rapid response teams.',
                'icon' => 'bi-heart-pulse',
                'image' => 'emergency.jpg'
            ],
            [
                'name' => 'Cardiology',
                'description' => 'Comprehensive cardiac care with modern diagnostic facilities.',
                'icon' => 'bi-heart',
                'image' => 'cardiology.jpg'
            ],
            [
                'name' => 'Pediatrics',
                'description' => 'Specialized care for children in a child-friendly environment.',
                'icon' => 'bi-emoji-smile',
                'image' => 'pediatrics.jpg'
            ],
            [
                'name' => 'Laboratory Services',
                'description' => 'Advanced diagnostic laboratory with accurate and timely results.',
                'icon' => 'bi-flask',
                'image' => 'laboratory.jpg'
            ],
            [
                'name' => 'Neurology',
                'description' => 'Expert neurological care with advanced treatment options.',
                'icon' => 'bi-brain',
                'image' => 'neurology.jpg'
            ],
            [
                'name' => 'Orthopedics',
                'description' => 'Comprehensive care for bone and joint conditions.',
                'icon' => 'bi-universal-access',
                'image' => 'orthopedics.jpg'
            ]
        ];

        $insert_service = $conn->prepare("INSERT INTO services (name, description, icon, image) VALUES (?, ?, ?, ?)");
        foreach ($default_services as $service) {
            $insert_service->bind_param("ssss", 
                $service['name'], 
                $service['description'], 
                $service['icon'], 
                $service['image']
            );
            $insert_service->execute();
        }
        echo "Added default services<br>";
    }

    // Add sample patients if none exist
    $check_patients = $conn->query("SELECT COUNT(*) as count FROM patients");
    if ($check_patients->fetch_assoc()['count'] == 0) {
        $default_patients = [
            [
                'name' => 'James Wilson',
                'dob' => '1985-03-15',
                'gender' => 'Male',
                'phone' => '+1234567893',
                'email' => 'james.wilson@email.com',
                'address' => '123 Main St, City',
                'blood_group' => 'A+',
                'status' => 'active'
            ],
            [
                'name' => 'Emily Davis',
                'dob' => '1990-07-22',
                'gender' => 'Female',
                'phone' => '+1234567894',
                'email' => 'emily.davis@email.com',
                'address' => '456 Oak Ave, City',
                'blood_group' => 'O+',
                'status' => 'active'
            ],
            [
                'name' => 'Robert Taylor',
                'dob' => '1978-11-30',
                'gender' => 'Male',
                'phone' => '+1234567895',
                'email' => 'robert.taylor@email.com',
                'address' => '789 Pine St, City',
                'blood_group' => 'B-',
                'status' => 'active'
            ]
        ];

        foreach ($default_patients as $patient) {
            $stmt = $conn->prepare("INSERT INTO patients (name, dob, gender, phone, email, address, blood_group, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", 
                $patient['name'], 
                $patient['dob'], 
                $patient['gender'], 
                $patient['phone'], 
                $patient['email'], 
                $patient['address'], 
                $patient['blood_group'],
                $patient['status']
            );
            $stmt->execute();
        }
        echo "Sample patients added successfully<br>";
    }

    // Display table contents
    foreach ($tables as $table_name => $sql) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table_name");
        $count = $result->fetch_assoc()['count'];
        echo "$table_name table has $count records<br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
