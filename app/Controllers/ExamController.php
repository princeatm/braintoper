<?php
/**
 * Exam Controller
 * Handles exam taking, auto-save, submission
 */

namespace App\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\ExamResult;
use App\Models\Question;
use App\Models\Option;
use App\Helpers\Security;
use App\Helpers\Logger;
use App\Middleware\AuthMiddleware;
use App\Middleware\CSRFMiddleware;

class ExamController
{
    private Exam $examModel;
    private ExamAttempt $attemptModel;
    private ExamAnswer $answerModel;
    private ExamResult $resultModel;
    private Student $studentModel;
    private Question $questionModel;

    public function __construct()
    {
        $this->examModel = new Exam();
        $this->attemptModel = new ExamAttempt();
        $this->answerModel = new ExamAnswer();
        $this->resultModel = new ExamResult();
        $this->studentModel = new Student();
        $this->questionModel = new Question();
    }

    /**
     * Start exam
     */
    public function startExam(): void
    {
        AuthMiddleware::requireRole('student');
        
        $examCode = Security::sanitize($_POST['exam_code'] ?? '');
        
        if (empty($examCode)) {
            die(json_encode(['error' => 'Exam code required']));
        }

        // Find exam by code
        $exam = $this->examModel->findByExamCode($examCode);
        if (!$exam) {
            die(json_encode(['error' => 'Exam not found']));
        }

        // Get student info
        $student = $this->studentModel->findByUserId($_SESSION['user_id']);
        if (!$student) {
            die(json_encode(['error' => 'Student profile not found']));
        }

        // Check if student already took exam
        $existingAttempt = $this->attemptModel->findBy([
            'exam_id' => $exam['id'],
            'student_id' => $student['id'],
        ]);

        if ($existingAttempt && $existingAttempt['is_completed']) {
            Logger::audit($_SESSION['user_id'], 'EXAM_START', $exam['title'], 'Already completed', false);
            die(json_encode(['error' => 'You have already completed this exam']));
        }

        // Create or get attempt
        $attempt = $this->attemptModel->getOrCreate($exam['id'], $student['id'], $_SESSION['user_id']);

        // Get questions for student
        $questions = $this->examModel->getQuestionsForStudent(
            $exam['id'],
            $student['id'],
            $exam['randomize_questions']
        );

        Logger::audit($_SESSION['user_id'], 'EXAM_START', $exam['title'], 'Exam started');

        header('Content-Type: application/json');
        die(json_encode([
            'success' => true,
            'attempt_id' => $attempt['id'],
            'exam' => $exam,
            'questions' => $questions,
            'duration_seconds' => $exam['duration_minutes'] * 60,
        ]));
    }

    /**
     * Get exam page
     */
    public function showExam(): void
    {
        AuthMiddleware::requireRole('student');
        
        $examCode = Security::sanitize($_GET['code'] ?? '');
        
        if (empty($examCode)) {
            header('Location: /dashboard/student');
            exit;
        }

        $exam = $this->examModel->findByExamCode($examCode);
        if (!$exam || $exam['status'] !== 'published') {
            die('Exam not found or not available');
        }

        $csrfToken = Security::generateCSRFToken();
        include __DIR__ . '/../Views/exam/take-exam.php';
    }

    /**
     * Auto-save answer
     */
    public function saveAnswer(): void
    {
        AuthMiddleware::requireRole('student');
        header('Content-Type: application/json');

        $attemptId = (int)($_POST['attempt_id'] ?? 0);
        $questionId = (int)($_POST['question_id'] ?? 0);
        $optionId = (int)($_POST['option_id'] ?? 0) ?: null;
        $isSkipped = (bool)($_POST['is_skipped'] ?? false);

        if (!$attemptId || !$questionId) {
            die(json_encode(['error' => 'Invalid parameters']));
        }

        try {
            $this->answerModel->saveAnswer($attemptId, $questionId, $optionId, $isSkipped);
            die(json_encode(['success' => true]));
        } catch (\Exception $e) {
            Logger::error('Error saving answer', $e);
            die(json_encode(['error' => 'Failed to save answer']));
        }
    }

    /**
     * Track user action (tab switch, focus loss, etc.)
     */
    public function trackAction(): void
    {
        AuthMiddleware::requireRole('student');
        header('Content-Type: application/json');

        $attemptId = (int)($_POST['attempt_id'] ?? 0);
        $action = Security::sanitize($_POST['action'] ?? '');

        if (!$attemptId) {
            die(json_encode(['error' => 'Invalid attempt ID']));
        }

        try {
            match($action) {
                'tab_switch' => $this->attemptModel->trackTabSwitch($attemptId),
                'focus_lost' => $this->attemptModel->trackFocusLost($attemptId),
                default => null,
            };

            die(json_encode(['success' => true]));
        } catch (\Exception $e) {
            Logger::error('Error tracking action', $e);
            die(json_encode(['error' => 'Failed to track action']));
        }
    }

    /**
     * Submit exam
     */
    public function submitExam(): void
    {
        AuthMiddleware::requireRole('student');
        CSRFMiddleware::verify();
        header('Content-Type: application/json');

        $attemptId = (int)($_POST['attempt_id'] ?? 0);
        
        if (!$attemptId) {
            die(json_encode(['error' => 'Invalid attempt ID']));
        }

        try {
            $this->attemptModel->beginTransaction();

            // Get attempt
            $attempt = $this->attemptModel->find($attemptId);
            if (!$attempt || $attempt['is_completed']) {
                throw new \Exception('Invalid or already completed attempt');
            }

            // Submit attempt
            $this->attemptModel->submit($attemptId);

            // Calculate results
            $totalQuestions = $this->questionModel->count(['exam_id' => $attempt['exam_id']]);
            $correctAnswers = $this->answerModel->countCorrect($attemptId);
            $skipped = $this->answerModel->countSkipped($attemptId);

            // Get exam for marks
            $exam = $this->examModel->find($attempt['exam_id']);
            $marksPerQuestion = $exam['total_marks'] / $totalQuestions;
            $obtainedMarks = round($correctAnswers * $marksPerQuestion);

            // Create result
            $this->resultModel->createFromAttempt(
                $attemptId,
                $attempt['exam_id'],
                $attempt['student_id'],
                $totalQuestions,
                $correctAnswers,
                $skipped,
                $exam['total_marks'],
                $obtainedMarks
            );

            $this->attemptModel->commit();

            Logger::audit(
                $_SESSION['user_id'],
                'EXAM_SUBMIT',
                $exam['title'],
                "Score: $obtainedMarks/$exam[total_marks]"
            );

            die(json_encode([
                'success' => true,
                'message' => 'Exam submitted successfully'
            ]));
        } catch (\Exception $e) {
            $this->attemptModel->rollback();
            Logger::error('Error submitting exam', $e);
            die(json_encode(['error' => 'Failed to submit exam: ' . $e->getMessage()]));
        }
    }

    /**
     * Auto-submit exam on timer end
     */
    public function autoSubmitExam(): void
    {
        AuthMiddleware::requireRole('student');
        header('Content-Type: application/json');

        $attemptId = (int)($_POST['attempt_id'] ?? 0);
        $reason = Security::sanitize($_POST['reason'] ?? 'Timer ended');

        if (!$attemptId) {
            die(json_encode(['error' => 'Invalid attempt ID']));
        }

        try {
            $this->attemptModel->beginTransaction();

            $attempt = $this->attemptModel->find($attemptId);
            if (!$attempt || $attempt['is_completed']) {
                throw new \Exception('Invalid or already completed attempt');
            }

            // Auto-submit
            $this->attemptModel->autoSubmit($attemptId, $reason);

            // Calculate results
            $totalQuestions = $this->questionModel->count(['exam_id' => $attempt['exam_id']]);
            $correctAnswers = $this->answerModel->countCorrect($attemptId);
            $skipped = $this->answerModel->countSkipped($attemptId);

            $exam = $this->examModel->find($attempt['exam_id']);
            $marksPerQuestion = $exam['total_marks'] / $totalQuestions;
            $obtainedMarks = round($correctAnswers * $marksPerQuestion);

            $this->resultModel->createFromAttempt(
                $attemptId,
                $attempt['exam_id'],
                $attempt['student_id'],
                $totalQuestions,
                $correctAnswers,
                $skipped,
                $exam['total_marks'],
                $obtainedMarks
            );

            $this->attemptModel->commit();

            Logger::audit($_SESSION['user_id'], 'EXAM_AUTO_SUBMIT', '', $reason);

            die(json_encode(['success' => true]));
        } catch (\Exception $e) {
            $this->attemptModel->rollback();
            Logger::error('Error auto-submitting exam', $e);
            die(json_encode(['error' => 'Failed to submit exam']));
        }
    }
}
