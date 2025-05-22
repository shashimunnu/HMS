<?php
session_start();
include '../config/db_connect.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
    $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

    if (empty($name) || empty($email) || empty($message)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Save to database
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        
        if ($stmt->execute()) {
            $success_message = "Thank you for your message. We will get back to you soon!";
            // Clear form data
            $_POST = array();
        } else {
            $error_message = "Error sending message. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .contact-icon {
            font-size: 2rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        .contact-info-card {
            height: 100%;
            transition: transform 0.3s ease;
        }
        .contact-info-card:hover {
            transform: translateY(-5px);
        }
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../assets/images/hospital-contact.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 50px;
        }
        .map-container {
            height: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <?php include '../includes/public_navbar.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4">Contact Us</h1>
            <p class="lead">We're Here to Help You</p>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Contact Information -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card contact-info-card shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-geo-alt contact-icon"></i>
                        <h4>Location</h4>
                        <p class="mb-0">Seva Hospital </p>
                        <p class="mb-0">Noida</p>
                        <p class="mb-0">UP, 843207</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card contact-info-card shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-telephone contact-icon"></i>
                        <h4>Phone</h4>
                        <p class="mb-0">Emergency: (+91) 911-1234-765</p>
                        <p class="mb-0">Reception:  123-4567-654</p>
                        <p class="mb-0">Appointments: (+91) 987234-5678</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card contact-info-card shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-envelope contact-icon"></i>
                        <h4>Email</h4>
                        <p class="mb-0">info@hospital.com</p>
                        <p class="mb-0">appointments@hospital.com</p>
                        <p class="mb-0">support@hospital.com</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Contact Form -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Send Us a Message</h3>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $success_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required><?php 
                                    echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; 
                                ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Find Us</h3>
                        <div class="map-container">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=your-map-embed-url"
                                width="100%" 
                                height="100%" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy">
                            </iframe>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Getting Here</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="bi bi-car-front text-primary me-2"></i>
                                    Free parking available on premises
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-bus-front text-primary me-2"></i>
                                    Bus routes: 18, 63, 62
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-train-front text-primary me-2"></i>
                                    Nearest metro station: sector-62 (5 min walk)
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="card bg-danger text-white mt-5">
            <div class="card-body text-center py-4">
                <h3><i class="bi bi-exclamation-triangle me-2"></i>Emergency?</h3>
                <p class="lead mb-0">Call our 24/7 Emergency Hotline</p>
                <h2 class="display-4 mb-0">91-8756453432</h2>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>
