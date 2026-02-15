<?php
/**
 * Exam Answer Model
 */

namespace App\Models;

class ExamAnswer extends BaseModel
{
    protected string $table = 'exam_answers';

    /**
     * Save or update answer
     */
    public function saveAnswer(int $attemptId, int $questionId, ?int $optionId, bool $isSkipped = false): bool
    {
        $existing = $this->findBy([
            'exam_attempt_id' => $attemptId,
            'question_id' => $questionId,
        ]);

        if ($existing) {
            $stmt = $this->pdo->prepare("
                UPDATE {$this->table}
                SET selected_option_id = ?, is_skipped = ?, answered_at = CURRENT_TIMESTAMP
                WHERE exam_attempt_id = ? AND question_id = ?
            ");
            return $stmt->execute([$optionId, $isSkipped ? 1 : 0, $attemptId, $questionId]);
        }

        return (bool)$this->insert([
            'exam_attempt_id' => $attemptId,
            'question_id' => $questionId,
            'selected_option_id' => $optionId,
            'is_skipped' => $isSkipped ? 1 : 0,
        ]);
    }

    /**
     * Get all answers for attempt
     */
    public function getByAttemptId(int $attemptId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ea.*, o.option_letter, opt.is_correct
            FROM {$this->table} ea
            LEFT JOIN options o ON ea.selected_option_id = o.id
            LEFT JOIN options opt ON ea.question_id = opt.question_id AND opt.is_correct = TRUE
            WHERE ea.exam_attempt_id = ?
            ORDER BY ea.id
        ");
        $stmt->execute([$attemptId]);
        return $stmt->fetchAll();
    }

    /**
     * Count answered questions
     */
    public function countAnswered(int $attemptId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM {$this->table}
            WHERE exam_attempt_id = ? AND is_skipped = FALSE
        ");
        $stmt->execute([$attemptId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Count skipped questions
     */
    public function countSkipped(int $attemptId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM {$this->table}
            WHERE exam_attempt_id = ? AND is_skipped = TRUE
        ");
        $stmt->execute([$attemptId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Count correct answers
     */
    public function countCorrect(int $attemptId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM {$this->table} ea
            JOIN options o ON ea.selected_option_id = o.id
            WHERE ea.exam_attempt_id = ? AND o.is_correct = TRUE
        ");
        $stmt->execute([$attemptId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
