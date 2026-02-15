<?php
/**
 * Teacher Dashboard Controller
 */

namespace App\Controllers;

use App\Models\Teacher;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Subject;
use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\Arm;
use App\Helpers\Security;
use App\Helpers\Utils;
use App\Middleware\AuthMiddleware;

class TeacherDashboardController
{
    private Teacher $teacherModel;
    private Exam $examModel;
    private ExamResult $resultModel;
    private Subject $subjectModel;

    public function __construct()
    {
        $this->teacherModel = new Teacher();
        $this->examModel = new Exam();
        $this->resultModel = new ExamResult();
        $this->subjectModel = new Subject();
    }

    /**
     * Show dashboard
     */
    public function show(): void
    {
        AuthMiddleware::requireRole('teacher');

        $teacher = $this->teacherModel->findByUserId($_SESSION['user_id']);
        if (!$teacher) {
            header('Location: /');
            exit;
        }

        $subjects = $this->subjectModel->getByTeacherId($teacher['id']);
        $exams = $this->examModel->getByTeacherId($teacher['id']);

        $csrfToken = Security::generateCSRFToken();
        include __DIR__ . '/../Views/dashboard/teacher.php';
    }

    /**
     * Show exam creation page
     */
    public function showCreateExam(): void
    {
        AuthMiddleware::requireRole('teacher');

        $teacher = $this->teacherModel->findByUserId($_SESSION['user_id']);
        $subjects = $this->subjectModel->getByTeacherId($teacher['id']);
        
        $classModel = new ClassModel();
        $gradeModel = new Grade();
        $armModel = new Arm();

        $classes = $classModel->getAllCached();
        $grades = $gradeModel->getAllCached();
        $arms = $armModel->getAllCached();

        $csrfToken = Security::generateCSRFToken();
        include __DIR__ . '/../Views/exam/create-exam.php';
    }

    /**
     * Create exam
     */
    public function createExam(): void
    {
        AuthMiddleware::requireRole('teacher');
        
        header('Content-Type: application/json');

        $teacher = $this->teacherModel->findByUserId($_SESSION['user_id']);

        $data = [
            'teacher_id' => $teacher['id'],
            'subject_id' => (int)($_POST['subject_id'] ?? 0),
            'title' => Security::sanitize($_POST['title'] ?? ''),
            'description' => Security::sanitize($_POST['description'] ?? ''),
            'exam_code' => Security::sanitize($_POST['exam_code'] ?? '') ?: Utils::generateExamCode(),
            'class_id' => (int)($_POST['class_id'] ?? 0),
            'grade_id' => (int)($_POST['grade_id'] ?? 0),
            'arm_id' => (int)($_POST['arm_id'] ?? 0),
            'duration_minutes' => (int)($_POST['duration_minutes'] ?? 60),
            'total_marks' => (int)($_POST['total_marks'] ?? 100),
            'passing_marks' => (int)($_POST['passing_marks'] ?? 50),
        ];

        if (!$data['subject_id'] || !$data['title'] || !$data['class_id'] || !$data['grade_id']) {
            die(json_encode(['error' => 'All required fields must be filled']));
        }

        try {
            $examId = $this->examModel->create($data);
            die(json_encode([
                'success' => true,
                'exam_id' => $examId,
                'message' => 'Exam created successfully'
            ]));
        } catch (\Exception $e) {
            die(json_encode(['error' => 'Failed to create exam: ' . $e->getMessage()]));
        }
    }

    /**
     * Get leaderboard
     */
    public function getLeaderboard(): void
    {
        AuthMiddleware::requireRole('teacher');
        header('Content-Type: application/json');

        $examId = (int)($_GET['exam_id'] ?? 0);
        $academicGroupId = (int)($_GET['academic_group_id'] ?? 0);

        if (!$examId) {
            die(json_encode(['error' => 'Invalid exam ID']));
        }

        try {
            $leaderboard = $this->resultModel->getLeaderboard($examId, $academicGroupId ?: null);
            die(json_encode([
                'success' => true,
                'leaderboard' => $leaderboard
            ]));
        } catch (\Exception $e) {
            die(json_encode(['error' => 'Failed to fetch leaderboard']));
        }
    }
}
