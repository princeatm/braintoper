<?php
/**
 * Student Dashboard Controller
 */

namespace App\Controllers;

use App\Models\Student;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamResult;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;

class StudentDashboardController
{
    private Student $studentModel;
    private Exam $examModel;
    private ExamAttempt $attemptModel;
    private ExamResult $resultModel;

    public function __construct()
    {
        $this->studentModel = new Student();
        $this->examModel = new Exam();
        $this->attemptModel = new ExamAttempt();
        $this->resultModel = new ExamResult();
    }

    /**
     * Show dashboard
     */
    public function show(): void
    {
        AuthMiddleware::requireRole('student');

        $student = $this->studentModel->findByUserId($_SESSION['user_id']);
        if (!$student) {
            header('Location: /');
            exit;
        }

        // Get available exams for student's academic group
        $availableExams = $this->getAvailableExams($student['academic_group_id']);
        
        // Get recent attempts
        $recentAttempts = $this->getRecentAttempts($student['id']);

        $csrfToken = Security::generateCSRFToken();
        include __DIR__ . '/../Views/dashboard/student.php';
    }

    /**
     * Get available exams
     */
    private function getAvailableExams(int $academicGroupId): array
    {
        $pdo = \App\Helpers\Database::connect();
        $stmt = $pdo->prepare("
            SELECT e.*, s.name as subject_name, t.first_name, t.last_name
            FROM exams e
            JOIN subjects s ON e.subject_id = s.id
            JOIN teachers t ON e.teacher_id = t.id
            WHERE e.academic_group_id = ? AND e.status = 'published' 
            AND NOT EXISTS (
                SELECT 1 FROM exam_attempts ea
                WHERE ea.exam_id = e.id 
                AND ea.student_id = (SELECT id FROM students WHERE user_id = ?)
            )
            ORDER BY e.published_at DESC
        ");
        $stmt->execute([$academicGroupId, $_SESSION['user_id']]);
        return $stmt->fetchAll();
    }

    /**
     * Get recent attempts
     */
    private function getRecentAttempts(int $studentId): array
    {
        $pdo = \App\Helpers\Database::connect();
        $stmt = $pdo->prepare("
            SELECT ea.*, e.title, e.subject_id, s.name as subject_name,
                   CASE WHEN er.id IS NOT NULL THEN TRUE ELSE FALSE END as is_graded,
                   er.obtained_marks, er.total_marks, er.percentage
            FROM exam_attempts ea
            JOIN exams e ON ea.exam_id = e.id
            JOIN subjects s ON e.subject_id = s.id
            LEFT JOIN exam_results er ON ea.id = er.exam_attempt_id
            WHERE ea.student_id = ?
            ORDER BY ea.started_at DESC
            LIMIT 20
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Get exam result (AJAX)
     */
    public function getResult(): void
    {
        AuthMiddleware::requireRole('student');
        header('Content-Type: application/json');

        $attemptId = (int)($_GET['attempt_id'] ?? 0);

        if (!$attemptId) {
            die(json_encode(['error' => 'Invalid attempt ID']));
        }

        try {
            $result = $this->resultModel->getByAttemptId($attemptId);
            
            if (!$result) {
                die(json_encode(['error' => 'Result not found']));
            }

            // Verify student owns this attempt
            $attempt = $this->attemptModel->find($attemptId);
            $student = $this->studentModel->findByUserId($_SESSION['user_id']);

            if ($attempt['student_id'] !== $student['id']) {
                die(json_encode(['error' => 'Access denied']));
            }

            die(json_encode([
                'success' => true,
                'result' => $result
            ]));
        } catch (\Exception $e) {
            die(json_encode(['error' => 'Failed to fetch result']));
        }
    }
}
