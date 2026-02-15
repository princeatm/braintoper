<?php
/**
 * Admin Dashboard Controller
 */

namespace App\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;

class AdminDashboardController
{
    private Student $studentModel;
    private Teacher $teacherModel;
    private Exam $examModel;
    private ExamResult $resultModel;

    public function __construct()
    {
        $this->studentModel = new Student();
        $this->teacherModel = new Teacher();
        $this->examModel = new Exam();
        $this->resultModel = new ExamResult();
    }

    /**
     * Show dashboard
     */
    public function show(): void
    {
        AuthMiddleware::requireRole('admin');

        $stats = [
            'total_students' => $this->studentModel->count(['is_active' => true]),
            'total_teachers' => $this->teacherModel->count(['is_active' => true]),
            'total_exams' => $this->examModel->count(['status' => 'published']),
        ];

        $csrfToken = Security::generateCSRFToken();
        include __DIR__ . '/../Views/dashboard/admin.php';
    }

    /**
     * Manage students
     */
    public function manageStudents(): void
    {
        AuthMiddleware::requireRole('admin');
        header('Content-Type: application/json');

        $students = $this->studentModel->all(['is_active' => true]);
        die(json_encode(['success' => true, 'students' => $students]));
    }

    /**
     * Manage teachers
     */
    public function manageTeachers(): void
    {
        AuthMiddleware::requireRole('admin');
        header('Content-Type: application/json');

        $teachers = $this->teacherModel->all(['is_active' => true]);
        die(json_encode(['success' => true, 'teachers' => $teachers]));
    }
}
