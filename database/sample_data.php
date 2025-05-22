<?php
include dirname(__FILE__) . '/../config/db_connect.php';

// Sample data for doctors with their user accounts
$doctors = [
    [
        'username' => 'dr.smith',
        'password' => 'doctor123',
        'name' => 'Dr. John Smith',
        'specialization' => 'Cardiologist',
        'phone' => '555-0101',
        'email' => 'dr.smith@hospital.com'
    ],
    [
        'username' => 'dr.jones',
        'password' => 'doctor123',
        'name' => 'Dr. Sarah Jones',
        'specialization' => 'Pediatrician',
        'phone' => '555-0102',
        'email' => 'dr.jones@hospital.com'
    ],
    [
        'username' => 'dr.wilson',
        'password' => 'doctor123',
        'name' => 'Dr. David Wilson',
        'specialization' => 'Orthopedic Surgeon',
        'phone' => '555-0103',
        'email' => 'dr.wilson@hospital.com'
    ],
    [
        'username' => 'dr.patel',
        'password' => 'doctor123',
        'name' => 'Dr. Priya Patel',
        'specialization' => 'Neurologist',
        'phone' => '555-0104',
        'email' => 'dr.patel@hospital.com'
    ]
];

// Sample data for patients
$patients = [
    [
        'name' => 'Michael Brown',
        'dob' => '1985-03-15',
        'gender' => 'Male',
        'phone' => '555-0201',
        'email' => 'michael.b@email.com',
        'address' => '123 Oak Street, Cityville',
        'blood_group' => 'O+'
    ],
    [
        'name' => 'Emily Davis',
        'dob' => '1990-07-22',
        'gender' => 'Female',
        'phone' => '555-0202',
        'email' => 'emily.d@email.com',
        'address' => '456 Pine Avenue, Townsburg',
        'blood_group' => 'A-'
    ],
    [
        'name' => 'James Wilson',
        'dob' => '1978-11-30',
        'gender' => 'Male',
        'phone' => '555-0203',
        'email' => 'james.w@email.com',
        'address' => '789 Maple Road, Villageton',
        'blood_group' => 'B+'
    ],
    [
        'name' => 'Sophia Chen',
        'dob' => '1995-04-18',
        'gender' => 'Female',
        'phone' => '555-0204',
        'email' => 'sophia.c@email.com',
        'address' => '321 Elm Court, Hamletville',
        'blood_group' => 'AB+'
    ],
    [
        'name' => 'Robert Taylor',
        'dob' => '1982-09-25',
        'gender' => 'Male',
        'phone' => '555-0205',
        'email' => 'robert.t@email.com',
        'address' => '654 Birch Lane, Boroughtown',
        'blood_group' => 'O-'
    ]
];

echo "Starting to populate the database with sample data...<br>";

// Insert doctors and their user accounts
foreach ($doctors as $doctor) {
    // First create user account
    $password_hash = password_hash($doctor['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'doctor')");
    $stmt->bind_param("ss", $doctor['username'], $password_hash);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        // Then create doctor profile
        $stmt = $conn->prepare("INSERT INTO doctors (user_id, name, specialization, phone, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $doctor['name'], $doctor['specialization'], $doctor['phone'], $doctor['email']);
        
        if ($stmt->execute()) {
            echo "Added doctor: " . $doctor['name'] . "<br>";
        } else {
            echo "Error adding doctor: " . $doctor['name'] . "<br>";
        }
    } else {
        echo "Error creating user account for: " . $doctor['name'] . "<br>";
    }
}

// Insert patients
foreach ($patients as $patient) {
    $stmt = $conn->prepare("INSERT INTO patients (name, dob, gender, phone, email, address, blood_group) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", 
        $patient['name'],
        $patient['dob'],
        $patient['gender'],
        $patient['phone'],
        $patient['email'],
        $patient['address'],
        $patient['blood_group']
    );
    
    if ($stmt->execute()) {
        echo "Added patient: " . $patient['name'] . "<br>";
    } else {
        echo "Error adding patient: " . $patient['name'] . "<br>";
    }
}

// Create some sample appointments
$doctors_result = $conn->query("SELECT id FROM doctors");
$doctor_ids = [];
while ($row = $doctors_result->fetch_assoc()) {
    $doctor_ids[] = $row['id'];
}

$patients_result = $conn->query("SELECT id FROM patients");
$patient_ids = [];
while ($row = $patients_result->fetch_assoc()) {
    $patient_ids[] = $row['id'];
}

// Create appointments for the next 7 days
for ($i = 0; $i < 10; $i++) {
    $doctor_id = $doctor_ids[array_rand($doctor_ids)];
    $patient_id = $patient_ids[array_rand($patient_ids)];
    $date = date('Y-m-d', strtotime('+' . rand(1, 7) . ' days'));
    $time = sprintf("%02d:00:00", rand(9, 16)); // Appointments between 9 AM and 4 PM
    $status = ['Scheduled', 'Completed', 'Cancelled'][rand(0, 2)];
    
    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $patient_id, $doctor_id, $date, $time, $status);
    
    if ($stmt->execute()) {
        echo "Added appointment for date: " . $date . "<br>";
    } else {
        echo "Error adding appointment for date: " . $date . "<br>";
    }
}

echo "Sample data population completed!<br>";
echo "<br>Doctor login credentials:<br>";
foreach ($doctors as $doctor) {
    echo "Username: " . $doctor['username'] . ", Password: " . $doctor['password'] . "<br>";
}

$conn->close();
?>
