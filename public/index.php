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

// Load environment variables - support both .env files and native environment
// First, load from actual environment (Railway uses native env vars)
$dbHost = getenv('DATABASE_URL_HOST') ?: (getenv('DB_HOST') ?: 'localhost');
$dbPort = getenv('DATABASE_URL_PORT') ?: (getenv('DB_PORT') ?: 3306);
$dbName = getenv('DATABASE_URL_DATABASE') ?: (getenv('DB_NAME') ?: 'braintoper');
$dbUser = getenv('DATABASE_URL_USER') ?: (getenv('DB_USER') ?: 'root');
$dbPass = getenv('DATABASE_URL_PASSWORD') ?: (getenv('DB_PASS') ?: '');

// Set into $_ENV for compatibility
$_ENV['DB_HOST'] = $dbHost;
$_ENV['DB_PORT'] = $dbPort;
$_ENV['DB_NAME'] = $dbName;
$_ENV['DB_USER'] = $dbUser;
$_ENV['DB_PASS'] = $dbPass;

// Load additional config from .env if it exists
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env');
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Skip database vars as we already set them above
            if (!in_array($key, ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'])) {
                $_ENV[$key] = $value;
            }
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
$dbConnected = false;

try {
    \App\Helpers\Database::init($dbConfig);
    $dbConnected = true;
} catch (Exception $e) {
    // Database connection failed - app will show error page but won't crash
    error_log("Database connection failed: " . $e->getMessage());
    // Set a flag in $_SERVER so routes can know database is unavailable
    $_SERVER['DB_UNAVAILABLE'] = true;
}

// CSRF protection on POST requests
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        \App\Middleware\CSRFMiddleware::verify();
    }
} catch (Exception $e) {
    error_log("CSRF check failed: " . $e->getMessage());
    // Don't crash on CSRF check failure
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

// Dispatch the request with comprehensive error handling
try {
    $router->dispatch();
} catch (\Exception $e) {
    error_log("Router dispatch error: " . $e->getMessage() . " - " . $e->getFile() . ":" . $e->getLine());
    
    // Try to set proper HTTP status
    if (strpos($e->getMessage(), 'Route not found') !== false) {
        http_response_code(404);
        header('Content-Type: text/html');
        echo '<h1>404 - Page Not Found</h1><p>The requested page does not exist.</p>';
    } else {
        http_response_code(500);
        header('Content-Type: text/html');
        if (getenv('APP_DEBUG') === 'true' || getenv('APP_DEBUG') === '1') {
            echo '<h1>500 - Server Error</h1>';
            echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p>File: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
        } else {
            echo '<h1>500 - Server Error</h1><p>An error occurred. Please try again later.</p>';
        }
    }
}
