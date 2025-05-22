<?php
session_start();
require_once '../config/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Hospital - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../assets/images/hospital-hero.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 150px 0;
        }
        .feature-card {
            transition: transform 0.3s ease;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        .stats-section {
            background: #f8f9fa;
            padding: 50px 0;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .cta-section {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('../assets/images/hospital-cta.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
        }
    </style>
</head>
<body>
    <?php include '../includes/public_navbar.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-3">Welcome to Our Hospital</h1>
            <p class="lead mb-4">Providing Quality Healthcare Services with Compassion</p>
            <a href="appointments.php" class="btn btn-primary btn-lg me-3">Book Appointment</a>
            <a href="services.php" class="btn btn-outline-light btn-lg">Our Services</a>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container my-5">
        <h2 class="text-center mb-5">Why Choose Us</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card feature-card shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-heart-pulse feature-icon"></i>
                        <h4>Expert Care</h4>
                        <p>Our team of experienced healthcare professionals is dedicated to providing the best medical care.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-building feature-icon"></i>
                        <h4>Modern Facilities</h4>
                        <p>State-of-the-art medical facilities equipped with the latest technology.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-clock feature-icon"></i>
                        <h4>24/7 Service</h4>
                        <p>Round-the-clock emergency services and patient care support.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="stats-section">
        <div class="container text-center">
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="stat-number">50+</div>
                    <p>Expert Doctors</p>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-number">10K+</div>
                    <p>Happy Patients</p>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-number">30+</div>
                    <p>Specializations</p>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-number">15+</div>
                    <p>Years Experience</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Preview -->
    <div class="container my-5">
        <h2 class="text-center mb-5">Our Services</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <img src="../assets/images/emergency.jpg" class="card-img-top" alt="Emergency Care">
                    <div class="card-body">
                        <h5 class="card-title">Emergency Care</h5>
                        <p class="card-text">24/7 emergency medical services with rapid response teams.</p>
                        <a href="services.php#emergency" class="btn btn-outline-primary">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <img src="../assets/images/cardiology.jpg" class="card-img-top" alt="Cardiology">
                    <div class="card-body">
                        <h5 class="card-title">Cardiology</h5>
                        <p class="card-text">Comprehensive cardiac care with modern diagnostic facilities.</p>
                        <a href="services.php#cardiology" class="btn btn-outline-primary">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <img src="../assets/images/pediatrics.jpg" class="card-img-top" alt="Pediatrics">
                    <div class="card-body">
                        <h5 class="card-title">Pediatrics</h5>
                        <p class="card-text">Specialized care for children in a child-friendly environment.</p>
                        <a href="services.php#pediatrics" class="btn btn-outline-primary">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="services.php" class="btn btn-primary">View All Services</a>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cta-section">
        <div class="container text-center">
            <h2 class="mb-4">Need Medical Assistance?</h2>
            <p class="lead mb-4">Book an appointment with our specialists today</p>
            <a href="appointments.php" class="btn btn-primary btn-lg">Book Appointment</a>
        </div>
    </div>

    <!-- Latest News/Blog Preview -->
    <div class="container my-5">
        <h2 class="text-center mb-5">Latest News</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">New Medical Technology</h5>
                        <p class="card-text">Our hospital has acquired the latest MRI technology for better diagnosis...</p>
                        <a href="#" class="btn btn-link">Read More</a>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Posted 3 days ago</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Health Tips</h5>
                        <p class="card-text">Stay healthy this season with these important health tips from our experts...</p>
                        <a href="#" class="btn btn-link">Read More</a>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Posted 5 days ago</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Community Outreach</h5>
                        <p class="card-text">Our hospital conducted a free health camp in the local community...</p>
                        <a href="#" class="btn btn-link">Read More</a>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Posted 1 week ago</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
