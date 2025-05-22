<?php
session_start();
include '../config/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Facilities - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .facility-image {
            height: 250px;
            object-fit: cover;
            width: 100%;
        }
        .facility-card {
            transition: transform 0.3s ease;
        }
        .facility-card:hover {
            transform: translateY(-5px);
        }
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../assets/images/hospital-interior.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 50px;
        }
        .stats-section {
            background: #f8f9fa;
            padding: 50px 0;
            margin: 50px 0;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <?php include '../includes/public_navbar.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4">State-of-the-Art Facilities</h1>
            <p class="lead">Experience World-Class Healthcare Infrastructure</p>
        </div>
    </div>

    <div class="container">
        <!-- Main Facilities -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card facility-card shadow">
                    <img src="../assets/images/operation-theater.jpg" class="facility-image" alt="Operation Theater">
                    <div class="card-body">
                        <h4>Operation Theaters</h4>
                        <p>State-of-the-art operation theaters equipped with the latest surgical technology.</p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Advanced surgical equipment</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Sterile environment</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Modern monitoring systems</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card facility-card shadow">
                    <img src="../assets/images/icu.jpg" class="facility-image" alt="ICU">
                    <div class="card-body">
                        <h4>Intensive Care Unit</h4>
                        <p>24/7 monitored ICU facility with advanced life support systems.</p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Advanced monitoring</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Specialized ICU staff</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Modern life support</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card facility-card shadow">
                    <img src="../assets/images/diagnostic.jpg" class="facility-image" alt="Diagnostic Center">
                    <div class="card-body">
                        <h4>Diagnostic Center</h4>
                        <p>Comprehensive diagnostic facilities with modern equipment.</p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Advanced imaging</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Quick results</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Expert analysis</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="stats-section text-center">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-number">100+</div>
                    <p>Hospital Beds</p>
                </div>
                <div class="col-md-3">
                    <div class="stat-number">10</div>
                    <p>Operation Theaters</p>
                </div>
                <div class="col-md-3">
                    <div class="stat-number">20</div>
                    <p>ICU Units</p>
                </div>
                <div class="col-md-3">
                    <div class="stat-number">50+</div>
                    <p>Medical Equipment</p>
                </div>
            </div>
        </div>

        <!-- Additional Facilities -->
        <h2 class="text-center mb-4">Additional Facilities</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h5><i class="bi bi-building text-primary me-2"></i>Patient Rooms</h5>
                        <p>Comfortable and well-equipped patient rooms designed for recovery and comfort.</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Private and semi-private rooms</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>24/7 nursing care</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Modern amenities</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h5><i class="bi bi-prescription2 text-primary me-2"></i>Pharmacy</h5>
                        <p>24/7 pharmacy service with a wide range of medications.</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>24/7 service</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Wide range of medicines</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Expert pharmacists</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h5><i class="bi bi-cup-hot text-primary me-2"></i>Cafeteria</h5>
                        <p>Clean and hygienic cafeteria serving nutritious meals.</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Healthy menu options</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Special dietary meals</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Clean environment</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h5><i class="bi bi-p-circle text-primary me-2"></i>Parking</h5>
                        <p>Spacious parking facility with 24/7 security.</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Ample parking space</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>24/7 security</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Easy access</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Virtual Tour Section -->
        <div class="text-center mt-5 mb-5">
            <h2>Take a Virtual Tour</h2>
            <p class="lead">Experience our facilities from the comfort of your home</p>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#tourModal">
                <i class="bi bi-play-circle me-2"></i>Start Virtual Tour
            </button>
        </div>
    </div>

    <!-- Virtual Tour Modal -->
    <div class="modal fade" id="tourModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Virtual Hospital Tour</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="ratio ratio-16x9">
                        <iframe src="https://www.youtube.com/embed/your-video-id" title="Virtual Hospital Tour" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
