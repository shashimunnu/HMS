<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db_connect.php';

function setupDatabase() {
    global $conn;
    
    try {
        // Create tables
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

        // Medical Records table
        $conn->query("CREATE TABLE IF NOT EXISTS medical_records (
            id INT PRIMARY KEY AUTO_INCREMENT,
            patient_id INT NOT NULL,
            doctor_id INT NOT NULL,
            appointment_id INT,
            diagnosis TEXT,
            prescription TEXT,
            notes TEXT,
            next_visit_date DATE,
            status ENUM('active', 'archived') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
            FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Contact Messages table
        $conn->query("CREATE TABLE IF NOT EXISTS contact_messages (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            subject VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('new', 'read', 'replied') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Insert default admin user if not exists
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT IGNORE INTO users (username, password, email, role) 
                     VALUES ('admin', '$admin_password', 'admin@hospital.com', 'admin')");

        // Insert sample doctors
        $doctors = [
            ['Dr. John Doe', 'Cardiologist', 'MBBS, MD (Cardiology)', 10, '+91-9876543210', 'john.doe@hospital.com'],
            ['Dr. Jane Smith', 'Pediatrician', 'MBBS, MD (Pediatrics)', 8, '+91-9876543211', 'jane.smith@hospital.com'],
            ['Dr. Mike Wilson', 'Orthopedic', 'MBBS, MS (Orthopedics)', 12, '+91-9876543212', 'mike.wilson@hospital.com'],
            ['Dr. Sarah Johnson', 'Dermatologist', 'MBBS, MD (Dermatology)', 6, '+91-9876543213', 'sarah.johnson@hospital.com']
        ];

        foreach ($doctors as $doctor) {
            // Create user account for doctor
            $username = strtolower(str_replace(' ', '.', $doctor[0]));
            $email = $doctor[5];
            $password = password_hash('doctor123', PASSWORD_DEFAULT);
            
            $conn->query("INSERT IGNORE INTO users (username, password, email, role) 
                         VALUES ('$username', '$password', '$email', 'doctor')");
            
            $user_id = $conn->insert_id;
            if ($user_id) {
                $conn->query("INSERT IGNORE INTO doctors (user_id, name, specialization, qualification, experience, phone, email) 
                             VALUES ($user_id, '{$doctor[0]}', '{$doctor[1]}', '{$doctor[2]}', {$doctor[3]}, '{$doctor[4]}', '{$doctor[5]}')");
            }
        }

        // Insert sample services
        $services = [
            ['General Consultation', 'Regular medical consultation with our experienced doctors', 500.00, 30],
            ['Emergency Care', '24/7 emergency medical services', 2000.00, 60],
            ['Laboratory Tests', 'Comprehensive medical tests and diagnostics', 1000.00, 45],
            ['X-Ray', 'Digital X-ray services', 800.00, 30],
            ['Vaccination', 'Various vaccination services', 300.00, 15],
            ['Physical Therapy', 'Rehabilitation and physical therapy sessions', 700.00, 45],
            ['Dental Care', 'Complete dental care services', 1000.00, 60],
            ['Eye Examination', 'Comprehensive eye check-up', 600.00, 30]
        ];

        foreach ($services as $service) {
            $name = $conn->real_escape_string($service[0]);
            $description = $conn->real_escape_string($service[1]);
            $cost = $service[2];
            $duration = $service[3];
            
            $conn->query("INSERT IGNORE INTO services (name, description, cost, duration) 
                         VALUES ('$name', '$description', $cost, $duration)");
        }

        // Set up doctor schedules
        $doctor_ids = $conn->query("SELECT id FROM doctors WHERE status = 'active'");
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        while ($doctor = $doctor_ids->fetch_assoc()) {
            foreach ($days as $day) {
                $conn->query("INSERT IGNORE INTO doctor_schedule (doctor_id, day_of_week, start_time, end_time, break_start, break_end) 
                             VALUES ({$doctor['id']}, '$day', '09:00:00', '17:00:00', '13:00:00', '14:00:00')");
            }
        }

        echo "Database setup completed successfully!";
        
    } catch (Exception $e) {
        error_log("Database Setup Error: " . $e->getMessage());
        die("Error setting up database: " . $e->getMessage());
    }
}

// Run the setup
setupDatabase();
?>
