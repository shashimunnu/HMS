<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/database.php';

try {
    $conn = getDBConnection();
    
    // Fetch active doctors with their details
    $sql = "SELECT d.*, 
            COUNT(DISTINCT a.id) as appointment_count 
            FROM doctors d 
            LEFT JOIN appointments a ON d.id = a.doctor_id 
            WHERE d.status = 'active'
            GROUP BY d.id 
            ORDER BY d.name";
            
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    
} catch (Exception $e) {
    error_log("Doctors page error: " . $e->getMessage());
    $error = "Error loading doctors: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Doctors - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .doctor-card {
            transition: transform 0.3s ease;
            height: 100%;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        .doctor-specialization {
            color: #0d6efd;
            font-weight: 500;
        }
        .doctor-stats {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .doctor-contact {
            font-size: 0.9rem;
        }
        .hero-section {
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
            color: white;
            padding: 4rem 0;
        }
    </style>
</head>
<body>
    <?php include '../includes/public_navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-3">Our Medical Experts</h1>
                    <p class="lead mb-4">Meet our team of experienced and dedicated healthcare professionals</p>
                    <div class="input-group mb-3 w-75 mx-auto">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="doctorSearch" class="form-control border-start-0" 
                               placeholder="Search by name or specialization...">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctors Section -->
    <section class="py-5">
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($doctors)): ?>
                <div class="alert alert-info text-center" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    No Doctors Found<br>
                    <small>Please try adjusting your search criteria.</small>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($doctors as $doctor): ?>
                        <div class="col-md-6 col-lg-4 doctor-item">
                            <div class="card doctor-card h-100">
                                <div class="card-body p-4">
                                    <div class="text-center mb-4">
                                        <div class="display-3 text-primary mb-3">
                                            <i class="bi bi-person-circle"></i>
                                        </div>
                                        <h4 class="card-title"><?php echo htmlspecialchars($doctor['name']); ?></h4>
                                        <p class="doctor-specialization mb-2">
                                            <i class="bi bi-star-fill me-2"></i>
                                            <?php echo htmlspecialchars($doctor['specialization']); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="doctor-contact mb-3">
                                        <?php if (!empty($doctor['phone'])): ?>
                                            <p class="mb-2">
                                                <i class="bi bi-telephone me-2"></i>
                                                <?php echo htmlspecialchars($doctor['phone']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($doctor['email'])): ?>
                                            <p class="mb-2">
                                                <i class="bi bi-envelope me-2"></i>
                                                <?php echo htmlspecialchars($doctor['email']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($doctor['address'])): ?>
                                            <p class="mb-2">
                                                <i class="bi bi-geo-alt me-2"></i>
                                                <?php echo htmlspecialchars($doctor['address']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="doctor-stats d-flex justify-content-between align-items-center mb-4">
                                        <span>
                                            <i class="bi bi-calendar-check me-2"></i>
                                            <?php echo $doctor['appointment_count']; ?> Appointments
                                        </span>
                                        <span>
                                            <i class="bi bi-currency-dollar me-1"></i>
                                            <?php 
                                            if ($doctor['consultation_fee'] !== null) {
                                                echo '$' . number_format($doctor['consultation_fee'], 2);
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <div class="text-center">
                                        <a href="appointments.php?doctor=<?php echo $doctor['id']; ?>" 
                                           class="btn btn-primary px-4">
                                            <i class="bi bi-calendar-plus me-2"></i>
                                            Book Appointment
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('doctorSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const doctorCards = document.querySelectorAll('.doctor-item');
            
            doctorCards.forEach(card => {
                const name = card.querySelector('.card-title').textContent.toLowerCase();
                const specialization = card.querySelector('.doctor-specialization').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || specialization.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
