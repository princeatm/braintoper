<?php
/**
 * Public entry point
 * index.php - Main application bootstrap
 */

// Set error reporting
if (getenv('APP_DEBUG') === 'true' || getenv('APP_DEBUG') === '1') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
}

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env');
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Define core constants
define('APP_PATH', dirname(__DIR__));
define('APP_NAME', 'BrainToper');
define('APP_VERSION', '1.0.0');

// Autoloader
require_once APP_PATH . '/app/Autoloader.php';
\App\Autoloader::register();

// Start session with secure settings
$config = include APP_PATH . '/config/app.php';
session_set_cookie_params([
    'lifetime' => $config['session']['lifetime'],
    'path' => $config['session']['cookie_path'],
    'domain' => $config['session']['cookie_domain'],
    'secure' => $config['session']['cookie_secure'],
    'httponly' => $config['session']['cookie_httponly'],
    'samesite' => $config['session']['cookie_samesite'],
]);
session_name($config['session']['cookie_name']);
session_start();

// Initialize database
$dbConfig = include APP_PATH . '/config/database.php';
\App\Helpers\Database::init($dbConfig);

// CSRF protection on POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    \App\Middleware\CSRFMiddleware::verify();
}

// Create router and register routes
$router = new \App\Router();

// Auth routes
$router->add('GET', '/', 'AuthController', 'showLogin');
$router->add('POST', '/auth/login', 'AuthController', 'handleLogin');
$router->add('GET', '/auth/register-student', 'AuthController', 'showStudentRegistration');
$router->add('POST', '/auth/register-student', 'AuthController', 'handleStudentRegistration');
$router->add('GET', '/auth/logout', 'AuthController', 'logout');
$router->add('POST', '/auth/check-session', 'AuthController', 'checkSession');

// Exam routes
$router->add('GET', '/exam/{code}', 'ExamController', 'showExam');
$router->add('POST', '/exam/start', 'ExamController', 'startExam');
$router->add('POST', '/exam/save-answer', 'ExamController', 'saveAnswer');
$router->add('POST', '/exam/track-action', 'ExamController', 'trackAction');
$router->add('POST', '/exam/submit', 'ExamController', 'submitExam');
$router->add('POST', '/exam/auto-submit', 'ExamController', 'autoSubmitExam');

// Student Dashboard routes
$router->add('GET', '/dashboard/student', 'StudentDashboardController', 'show');
$router->add('GET', '/api/student/exam-result', 'StudentDashboardController', 'getResult');

// Teacher Dashboard routes
$router->add('GET', '/dashboard/teacher', 'TeacherDashboardController', 'show');
$router->add('GET', '/exam/create', 'TeacherDashboardController', 'showCreateExam');
$router->add('POST', '/exam/create', 'TeacherDashboardController', 'createExam');
$router->add('GET', '/api/teacher/leaderboard', 'TeacherDashboardController', 'getLeaderboard');

// Admin Dashboard routes
$router->add('GET', '/dashboard/admin', 'AdminDashboardController', 'show');
$router->add('GET', '/api/admin/students', 'AdminDashboardController', 'manageStudents');
$router->add('GET', '/api/admin/teachers', 'AdminDashboardController', 'manageTeachers');

// Dispatch the request
try {
    $router->dispatch();
} catch (\Exception $e) {
    \App\Helpers\Logger::error('Application error', $e);
    header('HTTP/1.0 500 Internal Server Error');
    if (getenv('APP_DEBUG') === 'true') {
        die('Error: ' . $e->getMessage());
    }
    die('An error occurred. Please try again later.');
}
