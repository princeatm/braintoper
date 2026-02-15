<?php
/**
 * Exam Attempt Model
 */

namespace App\Models;

class ExamAttempt extends BaseModel
{
    protected string $table = 'exam_attempts';

    /**
     * Get or create attempt
     */
    public function getOrCreate(int $examId, int $studentId, int $userId): ?array
    {
        $attempt = $this->findBy([
            'exam_id' => $examId,
            'student_id' => $studentId,
        ]);

        if ($attempt) {
            return $attempt;
        }

        $attempId = $this->insert([
            'exam_id' => $examId,
            'student_id' => $studentId,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);

        return $this->find($attempId);
    }

    /**
     * Submit exam
     */
    public function submit(int $attemptId): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET submitted_at = CURRENT_TIMESTAMP, is_completed = TRUE
            WHERE id = ?
        ");
        return $stmt->execute([$attemptId]);
    }

    /**
     * Auto submit exam
     */
    public function autoSubmit(int $attemptId, string $reason): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET submitted_at = CURRENT_TIMESTAMP, is_completed = TRUE, 
                auto_submitted = TRUE, auto_submit_reason = ?
            WHERE id = ?
        ");
        return $stmt->execute([$reason, $attemptId]);
    }

    /**
     * Track focus lost
     */
    public function trackFocusLost(int $attemptId): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET focus_lost_count = focus_lost_count + 1
            WHERE id = ?
        ");
        return $stmt->execute([$attemptId]);
    }

    /**
     * Track tab switch
     */
    public function trackTabSwitch(int $attemptId): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET browser_tabs_switched = browser_tabs_switched + 1
            WHERE id = ?
        ");
        return $stmt->execute([$attemptId]);
    }

    /**
     * Get active attempt by student
     */
    public function getActiveAttempt(int $studentId, int $examId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE student_id = ? AND exam_id = ? AND is_completed = FALSE
        ");
        $stmt->execute([$studentId, $examId]);
        return $stmt->fetch() ?: null;
    }
}
