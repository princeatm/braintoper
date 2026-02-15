<?php
/**
 * Exam Model
 */

namespace App\Models;

class Exam extends BaseModel
{
    protected string $table = 'exams';

    /**
     * Find by exam code
     */
    public function findByExamCode(string $examCode): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT e.*, t.first_name, t.last_name, s.name as subject_name,
                   c.name as class_name, g.name as grade_name, a.name as arm_name
            FROM {$this->table} e
            JOIN teachers t ON e.teacher_id = t.id
            JOIN subjects s ON e.subject_id = s.id
            JOIN classes c ON e.class_id = c.id
            JOIN grades g ON e.grade_id = g.id
            LEFT JOIN arms a ON e.arm_id = a.id
            WHERE e.exam_code = ? AND e.status = 'published'
        ");
        $stmt->execute([strtoupper($examCode)]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get exam with questions
     */
    public function getWithQuestions(int $examId): ?array
    {
        $exam = $this->find($examId);
        if (!$exam) {
            return null;
        }

        $stmt = $this->pdo->prepare("
            SELECT q.*, GROUP_CONCAT(CONCAT(o.option_letter, ':', o.option_text, ':', o.is_correct) SEPARATOR '|') as options
            FROM questions q
            LEFT JOIN options o ON q.id = o.question_id
            WHERE q.exam_id = ?
            GROUP BY q.id
            ORDER BY q.order_position
        ");
        $stmt->execute([$examId]);

        return [
            'exam' => $exam,
            'questions' => $stmt->fetchAll(),
        ];
    }

    /**
     * Get questions for student (with randomization)
     */
    public function getQuestionsForStudent(int $examId, int $studentId, bool $randomize = true): array
    {
        $stmt = $this->pdo->prepare("
            SELECT q.id, q.question_text, q.question_image, q.marks, q.difficulty
            FROM questions q
            WHERE q.exam_id = ?
            ORDER BY " . ($randomize ? "RAND()" : "q.order_position")
        );
        $stmt->execute([$examId]);
        $questions = $stmt->fetchAll();

        // Get options for each question
        foreach ($questions as &$question) {
            $stmt = $this->pdo->prepare("
                SELECT id, option_letter, option_text
                FROM options
                WHERE question_id = ?
                ORDER BY " . ($randomize ? "RAND()" : "option_letter")
            );
            $stmt->execute([$question['id']]);
            $question['options'] = $stmt->fetchAll();
        }

        return $questions;
    }

    /**
     * Get teacher exams
     */
    public function getByTeacherId(int $teacherId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT e.*, s.name as subject_name, c.name as class_name
            FROM {$this->table} e
            JOIN subjects s ON e.subject_id = s.id
            JOIN classes c ON e.class_id = c.id
            WHERE e.teacher_id = ?
            ORDER BY e.created_at DESC
        ");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    /**
     * Create exam
     */
    public function create(array $data): int
    {
        $data['exam_code'] = strtoupper($data['exam_code']);
        return $this->insert($data);
    }

    /**
     * Publish exam
     */
    public function publish(int $examId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET status = 'published', published_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$examId]);
    }

    /**
     * Unpublish exam
     */
    public function unpublish(int $examId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET status = 'draft' WHERE id = ?");
        return $stmt->execute([$examId]);
    }
}
