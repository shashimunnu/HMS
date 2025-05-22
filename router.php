<?php
session_start();

// Base path configuration
define('BASE_PATH', __DIR__);
define('BASE_URL', '/HMS');

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_middleware.php';

// Get the request URI and remove base path
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = parse_url(BASE_URL, PHP_URL_PATH);
$request_uri = substr($request_uri, strlen($base_path));
$request_uri = trim($request_uri, '/');

// Default route
if (empty($request_uri)) {
    $request_uri = 'home';
}

// Define routes and their corresponding files
$routes = [
    // Public routes
    'home' => 'public/index.php',
    'doctors' => 'public/doctors.php',
    'services' => 'public/services.php',
    'contact' => 'public/contact.php',
    'about' => 'public/about.php',
    'appointments' => 'public/appointments.php',
    
    // Authentication routes
    'login' => 'auth/login.php',
    'register' => 'auth/register.php',
    'logout' => 'auth/logout.php',
    'forgot-password' => 'auth/forgot-password.php',
    'reset-password' => 'auth/reset-password.php',
    
    // Admin routes
    'admin' => 'admin/dashboard.php',
    'admin/dashboard' => 'admin/dashboard.php',
    'admin/doctors' => 'admin/doctors.php',
    'admin/patients' => 'admin/patients.php',
    'admin/appointments' => 'admin/appointments.php',
    'admin/services' => 'admin/services.php',
    'admin/users' => 'admin/users.php',
    'admin/settings' => 'admin/settings.php',
    
    // Doctor routes
    'doctor' => 'doctor/dashboard.php',
    'doctor/appointments' => 'doctor/appointments.php',
    'doctor/schedule' => 'doctor/schedule.php',
    'doctor/patients' => 'doctor/patients.php',
    'doctor/profile' => 'doctor/profile.php',
    
    // Patient routes
    'patient' => 'patient/dashboard.php',
    'patient/appointments' => 'patient/appointments.php',
    'patient/book-appointment' => 'patient/book-appointment.php',
    'patient/medical-history' => 'patient/medical-history.php',
    'patient/profile' => 'patient/profile.php',
    
    // API routes
    'api/appointments' => 'api/appointments.php',
    'api/doctors' => 'api/doctors.php',
    'api/patients' => 'api/patients.php',
    'admin/api/users/add' => 'admin/api/users/add.php',
    'admin/api/users/get' => 'admin/api/users/get.php',
    'admin/api/users/update' => 'admin/api/users/update.php',
    'admin/api/users/delete' => 'admin/api/users/delete.php',
    'admin/api/users/list' => 'admin/api/users/list.php',
    'modules/doctors' => 'modules/doctors/index.php',
    'modules/patients' => 'modules/patients/index.php',
    'modules/services' => 'modules/services/index.php',
    'modules/appointments' => 'modules/appointments/index.php',
    'modules/users' => 'modules/users/index.php'
];

// Check if route exists
if (!isset($routes[$request_uri])) {
    header("HTTP/1.0 404 Not Found");
    include BASE_PATH . '/includes/404.php';
    exit;
}

// Define route access rules
$route_access = [
    'public' => [], // No roles required
    'auth' => ['login', 'register', 'forgot-password', 'reset-password'], // No auth required
    'admin' => ['admin'], // Admin only
    'doctor' => ['doctor'], // Doctor only
    'patient' => ['patient'], // Patient only
    'api' => ['api/appointments', 'api/doctors', 'api/patients'], // Based on endpoint
    'modules' => ['admin']  // Only admin can access modules
];

// Check authentication and authorization
$route_parts = explode('/', $request_uri);
$route_base = $route_parts[0];

// Handle authentication routes
if (in_array($request_uri, $route_access['auth'])) {
    if ($request_uri !== 'logout') {
        redirectIfLoggedIn();
    }
}
// Handle admin routes
elseif (strpos($request_uri, 'admin') === 0) {
    requireAuth(['admin']);
}
// Handle doctor routes
elseif (strpos($request_uri, 'doctor') === 0) {
    requireAuth(['doctor']);
}
// Handle patient routes
elseif (strpos($request_uri, 'patient') === 0) {
    requireAuth(['patient']);
}
// Handle API routes
elseif (strpos($request_uri, 'api') === 0) {
    // API authentication will be handled by individual endpoints
    // as they may have different requirements
}
// Handle module routes
elseif (strpos($request_uri, 'modules') === 0) {
    requireAuth(['admin']);
}

// Load the requested file
$file_path = BASE_PATH . '/' . $routes[$request_uri];
if (file_exists($file_path)) {
    include $file_path;
} else {
    header("HTTP/1.0 404 Not Found");
    include BASE_PATH . '/includes/404.php';
}
?>
