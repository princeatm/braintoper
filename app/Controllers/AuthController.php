<?php
/**
 * Authentication Controller
 */

namespace App\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Admin;
use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\Arm;
use App\Models\AcademicGroup;
use App\Helpers\Security;
use App\Helpers\Logger;
use App\Middleware\AuthMiddleware;
use App\Middleware\CSRFMiddleware;

class AuthController
{
    private User $userModel;
    private Student $studentModel;
    private Teacher $teacherModel;
    private ClassModel $classModel;
    private Grade $gradeModel;
    private Arm $armModel;
    private AcademicGroup $academicGroupModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->studentModel = new Student();
        $this->teacherModel = new Teacher();
        $this->classModel = new ClassModel();
        $this->gradeModel = new Grade();
        $this->armModel = new Arm();
        $this->academicGroupModel = new AcademicGroup();
    }

    /**
     * Show login page
     */
    public function showLogin(): void
    {
        AuthMiddleware::requireGuest();
        
        $csrfToken = Security::generateCSRFToken();
        include __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * Handle login
     */
    public function handleLogin(): void
    {
        CSRFMiddleware::verify();
        
        $loginCode = Security::sanitize($_POST['login_code'] ?? '');
        $pin = $_POST['pin'] ?? '';

        // Rate limiting
        $config = include __DIR__ . '/../../config/app.php';
        if (!Security::checkRateLimit(
            'login_' . $_SERVER['REMOTE_ADDR'],
            $config['rate_limit']['login_attempts'],
            $config['rate_limit']['login_window']
        )) {
            http_response_code(429);
            die(json_encode(['error' => 'Too many login attempts. Please try again later.']));
        }

        // Validate login code format
        $codeValidation = Security::validateLoginCode($loginCode);
        if (!$codeValidation['type']) {
            Logger::warning('Invalid login code format', ['code' => $loginCode, 'ip' => $_SERVER['REMOTE_ADDR']]);
            die(json_encode(['error' => 'Invalid login code format']));
        }

        // Check if user exists
        $user = $this->userModel->findByLoginCode($loginCode);

        if (!$user) {
            // New student registration
            if ($codeValidation['type'] === 'student') {
                $this->initiateStudentRegistration($loginCode);
            }
            
            Logger::warning('User not found', ['code' => $loginCode, 'ip' => $_SERVER['REMOTE_ADDR']]);
            die(json_encode(['error' => 'User not found']));
        }

        // Verify PIN
        if (!Security::verifyPassword($pin, $user['pin_hash'])) {
            Logger::audit($user['id'], 'FAILED_LOGIN', 'authentication', 'Invalid PIN', false);
            die(json_encode(['error' => 'Invalid PIN']));
        }

        // Check if user is active
        if (!$user['is_active']) {
            Logger::audit($user['id'], 'FAILED_LOGIN', 'authentication', 'Inactive account', false);
            die(json_encode(['error' => 'Your account is inactive']));
        }

        // Successful login
        $this->startSession($user);
        Logger::audit($user['id'], 'LOGIN', 'authentication', 'Successful login');
        $this->userModel->updateLastLogin($user['id']);

        die(json_encode(['success' => true, 'redirect' => $this->getRedirectUrl($user['role'])]));
    }

    /**
     * Initiate student registration
     */
    private function initiateStudentRegistration(string $loginCode): void
    {
        die(json_encode([
            'action' => 'register_student',
            'login_code' => $loginCode,
            'redirect' => '/auth/register-student?code=' . urlencode($loginCode)
        ]));
    }

    /**
     * Show student registration page
     */
    public function showStudentRegistration(): void
    {
        AuthMiddleware::requireGuest();
        
        $loginCode = Security::sanitize($_GET['code'] ?? '');
        if (empty($loginCode)) {
            header('Location: /');
            exit;
        }

        $codeValidation = Security::validateLoginCode($loginCode);
        if ($codeValidation['type'] !== 'student') {
            header('Location: /');
            exit;
        }

        $csrfToken = Security::generateCSRFToken();
        $classes = $this->classModel->getAllCached();
        $grades = $this->gradeModel->getAllCached();
        $arms = $this->armModel->getAllCached();

        include __DIR__ . '/../Views/auth/register-student.php';
    }

    /**
     * Handle student registration
     */
    public function handleStudentRegistration(): void
    {
        CSRFMiddleware::verify();
        
        $loginCode = Security::sanitize($_POST['login_code'] ?? '');
        $firstName = Security::sanitize($_POST['first_name'] ?? '');
        $lastName = Security::sanitize($_POST['last_name'] ?? '');
        $classId = (int)($_POST['class_id'] ?? 0);
        $gradeId = (int)($_POST['grade_id'] ?? 0);
        $armId = (int)($_POST['arm_id'] ?? 0);

        // Validate inputs
        if (empty($firstName) || empty($lastName) || !$classId || !$gradeId || !$armId) {
            die(json_encode(['error' => 'All fields are required']));
        }

        // Validate code hasn't been registered
        if ($this->userModel->exists($loginCode)) {
            die(json_encode(['error' => 'Login code already registered']));
        }

        // Get academic group
        $academicGroupId = $this->academicGroupModel->getOrCreate($classId, $gradeId, $armId);

        // Generate PIN
        $pin = Security::generatePIN();
        $pinHash = Security::hashPassword($pin);

        try {
            $this->userModel->beginTransaction();

            // Create user
            $userId = $this->userModel->create($loginCode, 'student', $pinHash);

            // Create student
            $this->studentModel->create($userId, $firstName, $lastName, $classId, $gradeId, $armId, $academicGroupId);

            $this->userModel->commit();

            Logger::audit($userId, 'REGISTER', 'student_registration', "Student registered: $firstName $lastName");

            die(json_encode([
                'success' => true,
                'pin' => $pin,
                'message' => 'Registration successful! Your 4-digit PIN is shown above. Save it carefully.'
            ]));
        } catch (\Exception $e) {
            $this->userModel->rollback();
            Logger::error('Student registration error', $e);
            die(json_encode(['error' => 'Registration failed. Please try again.']));
        }
    }

    /**
     * Start session
     */
    private function startSession(array $user): void
    {
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_code'] = $user['login_code'];
        $_SESSION['last_activity'] = time();
    }

    /**
     * Get redirect URL based on role
     */
    private function getRedirectUrl(string $role): string
    {
        return match($role) {
            'student' => '/dashboard/student',
            'teacher' => '/dashboard/teacher',
            'admin' => '/dashboard/admin',
            'superadmin' => '/dashboard/superadmin',
            default => '/'
        };
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        if (AuthMiddleware::isAuthenticated()) {
            Logger::audit($_SESSION['user_id'] ?? 0, 'LOGOUT', 'authentication', 'User logged out');
        }

        session_destroy();
        header('Location: /');
        exit;
    }

    /**
     * Check session
     */
    public function checkSession(): void
    {
        header('Content-Type: application/json');
        
        if (!AuthMiddleware::isAuthenticated()) {
            die(json_encode(['authenticated' => false]));
        }

        die(json_encode([
            'authenticated' => true,
            'user_id' => $_SESSION['user_id'],
            'role' => $_SESSION['role']
        ]));
    }
}
