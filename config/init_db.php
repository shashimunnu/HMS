<?php
require_once 'config.php';
require_once 'db_connect.php';

function createTables() {
    global $conn;
    
    try {
        // Users table
        $conn->query("CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            role ENUM('admin', 'doctor', 'patient', 'receptionist') NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Doctors table
        $conn->query("CREATE TABLE IF NOT EXISTS doctors (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            name VARCHAR(100) NOT NULL,
            specialization VARCHAR(100) NOT NULL,
            qualification VARCHAR(255) NOT NULL,
            experience INT NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(100) NOT NULL,
            address TEXT,
            photo VARCHAR(255),
            consultation_fee DECIMAL(10,2) DEFAULT 0.00,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Patients table
        $conn->query("CREATE TABLE IF NOT EXISTS patients (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            name VARCHAR(100) NOT NULL,
            dob DATE NOT NULL,
            gender ENUM('male', 'female', 'other') NOT NULL,
            blood_group VARCHAR(5),
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(100) NOT NULL,
            address TEXT,
            emergency_contact VARCHAR(100),
            emergency_phone VARCHAR(20),
            medical_history TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Services table
        $conn->query("CREATE TABLE IF NOT EXISTS services (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            cost DECIMAL(10,2) NOT NULL,
            duration INT DEFAULT 30,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Appointments table
        $conn->query("CREATE TABLE IF NOT EXISTS appointments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            patient_id INT NOT NULL,
            doctor_id INT NOT NULL,
            service_id INT,
            appointment_date DATE NOT NULL,
            appointment_time TIME NOT NULL,
            status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
            reason TEXT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Doctor Schedule table
        $conn->query("CREATE TABLE IF NOT EXISTS doctor_schedule (
            id INT PRIMARY KEY AUTO_INCREMENT,
            doctor_id INT NOT NULL,
            day_of_week ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            break_start TIME,
            break_end TIME,
            max_patients INT DEFAULT 20,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Insert default admin user if not exists
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT IGNORE INTO users (username, password, email, role) 
                     VALUES ('admin', '$admin_password', 'admin@hospital.com', 'admin')");

        // Insert some sample doctors
        $conn->query("INSERT IGNORE INTO users (username, password, email, role) 
                     VALUES ('doctor1', '$admin_password', 'doctor1@hospital.com', 'doctor')");
        
        $doctor_user_id = $conn->insert_id;
        if ($doctor_user_id) {
            $conn->query("INSERT IGNORE INTO doctors (user_id, name, specialization, qualification, experience, phone, email) 
                         VALUES ($doctor_user_id, 'Dr. John Doe', 'Cardiologist', 'MBBS, MD', 10, '+91-9876543210', 'doctor1@hospital.com')");
        }

        // Insert some default services
        $services = [
            ['General Consultation', 'Regular medical consultation with our experienced doctors', 500.00],
            ['Emergency Care', '24/7 emergency medical services', 2000.00],
            ['Laboratory Tests', 'Comprehensive medical tests and diagnostics', 1000.00],
            ['X-Ray', 'Digital X-ray services', 800.00],
            ['Vaccination', 'Various vaccination services', 300.00]
        ];

        foreach ($services as $service) {
            $name = $conn->real_escape_string($service[0]);
            $description = $conn->real_escape_string($service[1]);
            $cost = $service[2];
            
            $conn->query("INSERT IGNORE INTO services (name, description, cost) 
                         VALUES ('$name', '$description', $cost)");
        }

        echo "Database tables created and initialized successfully!";
        
    } catch (Exception $e) {
        error_log("Database Initialization Error: " . $e->getMessage());
        die("Error initializing database: " . $e->getMessage());
    }
}

// Create tables
createTables();
?>
