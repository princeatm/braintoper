<?php
/**
 * Question Model
 */

namespace App\Models;

class Question extends BaseModel
{
    protected string $table = 'questions';

    /**
     * Get by exam ID
     */
    public function getByExamId(int $examId, bool $includeOptions = true): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE exam_id = ?
            ORDER BY order_position
        ");
        $stmt->execute([$examId]);
        $questions = $stmt->fetchAll();

        if ($includeOptions) {
            foreach ($questions as &$question) {
                $stmt = $this->pdo->prepare("
                    SELECT id, option_letter, option_text, is_correct
                    FROM options
                    WHERE question_id = ?
                    ORDER BY option_letter
                ");
                $stmt->execute([$question['id']]);
                $question['options'] = $stmt->fetchAll();
            }
        }

        return $questions;
    }

    /**
     * Create question
     */
    public function create(array $data): int
    {
        return $this->insert($data);
    }

    /**
     * Get total questions in exam
     */
    public function getTotalByExamId(int $examId): int
    {
        return $this->count(['exam_id' => $examId]);
    }

    /**
     * Get total questions by subject
     */
    public function getTotalBySubjectId(int $subjectId): int
    {
        return $this->count(['subject_id' => $subjectId]);
    }
}
