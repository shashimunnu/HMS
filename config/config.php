<?php
// Prevent multiple inclusions
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);

// Error reporting configuration
if (!defined('DISPLAY_ERRORS')) define('DISPLAY_ERRORS', true);
if (DISPLAY_ERRORS) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Base URL Configuration
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$server_name = $_SERVER['SERVER_NAME'];
$port = $_SERVER['SERVER_PORT'];
$port = ($protocol === 'http' && $port != '80') || ($protocol === 'https' && $port != '443') ? ':' . $port : '';
if (!defined('BASE_URL')) define('BASE_URL', $protocol . '://' . $server_name . $port . '/HMS');

// Path configurations
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__)); // Parent directory of config folder
if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', BASE_PATH . '/uploads');
if (!defined('LOG_PATH')) define('LOG_PATH', BASE_PATH . '/logs');

// Create necessary directories if they don't exist
$directories = [UPLOAD_PATH, LOG_PATH];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Database configuration
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'hms_db');

// Session configuration
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 3600); // 1 hour
if (!defined('SESSION_NAME')) define('SESSION_NAME', 'HMS_SESSION');

// Time zone configuration
date_default_timezone_set('UTC'); // Using UTC as default timezone

// Initialize session with secure settings
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configure session cookie parameters
        $cookie_params = [
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        
        session_set_cookie_params($cookie_params);
        session_name(SESSION_NAME);
        
        // Start session
        session_start();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        } else if (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// Application constants
if (!defined('SITE_NAME')) define('SITE_NAME', 'Hospital Management System');
if (!defined('SITE_EMAIL')) define('SITE_EMAIL', 'admin@hospital.com');
if (!defined('SITE_PHONE')) define('SITE_PHONE', '+91-9876543210');
if (!defined('SITE_ADDRESS')) define('SITE_ADDRESS', '123 Hospital Street, Medical Center, City - 123456');

// File upload configurations
if (!defined('MAX_FILE_SIZE')) define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
if (!defined('ALLOWED_IMAGE_TYPES')) define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
if (!defined('ALLOWED_DOC_TYPES')) define('ALLOWED_DOC_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Initialize session
init_session();
}
?>
