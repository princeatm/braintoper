<?php
/**
 * Option Model
 */

namespace App\Models;

class Option extends BaseModel
{
    protected string $table = 'options';

    /**
     * Get by question ID
     */
    public function getByQuestionId(int $questionId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE question_id = ?
            ORDER BY option_letter
        ");
        $stmt->execute([$questionId]);
        return $stmt->fetchAll();
    }

    /**
     * Create option
     */
    public function create(array $data): int
    {
        return $this->insert($data);
    }

    /**
     * Get correct answer for question
     */
    public function getCorrectAnswer(int $questionId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE question_id = ? AND is_correct = TRUE
        ");
        $stmt->execute([$questionId]);
        return $stmt->fetch() ?: null;
    }
}
