<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/database.php';

try {
    $conn = getDBConnection();
    
    // Fetch active services
    $sql = "SELECT * FROM services WHERE status = 'active' ORDER BY name";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    
} catch (Exception $e) {
    error_log("Services page error: " . $e->getMessage());
    $error = "Error loading services: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .hero-section {
            background-color: #f8f9fa;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .service-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .service-card:hover {
            transform: translateY(-5px);
        }
        .service-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        .service-price {
            font-size: 1.25rem;
            font-weight: bold;
            color: #198754;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/public_navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 mb-4">Our Services</h1>
            <p class="lead">Comprehensive healthcare services tailored to your needs</p>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section">
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($services)): ?>
                <div class="alert alert-info text-center">
                    No services are currently available. Please check back later.
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($services as $service): ?>
                        <div class="col-md-4">
                            <div class="card service-card h-100">
                                <div class="card-body text-center p-4">
                                    <i class="bi <?php echo htmlspecialchars($service['icon']); ?> service-icon"></i>
                                    <h4 class="card-title mb-3"><?php echo htmlspecialchars($service['name']); ?></h4>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($service['description']); ?></p>
                                    <div class="service-price">
                                        <?php 
                                        if (isset($service['cost']) && $service['cost'] !== null) {
                                            echo '$' . number_format((float)$service['cost'], 2);
                                        } else {
                                            echo 'Price on request';
                                        }
                                        ?>
                                    </div>
                                    <a href="appointments.php" class="btn btn-primary">Book Now</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-light py-5">
        <div class="container text-center">
            <h2 class="mb-4">Need Medical Assistance?</h2>
            <p class="lead mb-4">Our team of healthcare professionals is available 24/7 to help you.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="appointments.php" class="btn btn-primary btn-lg px-4">Book Appointment</a>
                <a href="doctors.php" class="btn btn-outline-primary btn-lg px-4">Our Doctors</a>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
