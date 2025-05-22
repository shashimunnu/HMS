<?php
require_once __DIR__ . '/../config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /HMS/index.php');
    exit();
}

$error = '';
$username = '';
$debug_info = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        try {
            $conn = getDBConnection();
            $debug_info .= "Database connection successful. ";
            
            // Get user details - removed status field from query
            $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                error_log("Failed to prepare statement: " . $conn->error);
                throw new Exception("Database error occurred");
            }
            $debug_info .= "Statement prepared. ";
            
            if (!$stmt->bind_param("s", $username)) {
                error_log("Failed to bind parameters: " . $stmt->error);
                throw new Exception("Database error occurred");
            }
            $debug_info .= "Parameters bound. ";
            
            if (!$stmt->execute()) {
                error_log("Failed to execute statement: " . $stmt->error);
                throw new Exception("Database error occurred");
            }
            $debug_info .= "Query executed. ";
            
            $result = $stmt->get_result();
            $debug_info .= "Result fetched. ";
            
            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $debug_info .= "User found. ";
                
                if (password_verify($password, $user['password'])) {
                    $debug_info .= "Password verified. ";
                    
                    // Clear any existing session data
                    session_regenerate_id(true);
                    $_SESSION = array();
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    
                    $debug_info .= "Session variables set. ";
                    
                    // Log successful login
                    error_log("Successful login for user: " . $username);
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header('Location: /HMS/admin/dashboard.php');
                    } else {
                        header('Location: /HMS/index.php');
                    }
                    exit();
                } else {
                    error_log("Failed login attempt - invalid password for user: " . $username);
                    $error = "Invalid username or password";
                    $debug_info .= "Password verification failed. ";
                }
            } else {
                error_log("Failed login attempt - user not found: " . $username);
                $error = "Invalid username or password";
                $debug_info .= "User not found. ";
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Login error for user '" . $username . "': " . $e->getMessage());
            $error = "An error occurred. Please try again later.";
            $debug_info .= "Exception: " . $e->getMessage();
        }
    }
}

// Debug information
error_log("Debug Info: " . $debug_info);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0">
                            <i class="bi bi-hospital"></i> HMS Login
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($username); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please enter your username</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="invalid-feedback">Please enter your password</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3">
                        <a href="/HMS/index.php" class="text-decoration-none">
                            <i class="bi bi-arrow-left"></i> Back to Home
                        </a>
                    </div>
                </div>
                <?php if (!empty($debug_info)): ?>
                    <div class="mt-3 alert alert-info">
                        <small>Debug: <?php echo htmlspecialchars($debug_info); ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
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
