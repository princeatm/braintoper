<?php
/**
 * Exam Result Model
 */

namespace App\Models;

class ExamResult extends BaseModel
{
    protected string $table = 'exam_results';

    /**
     * Create result from attempt
     */
    public function createFromAttempt(int $attemptId, int $examId, int $studentId, int $totalQuestions, int $correctAnswers, int $skipped, int $totalMarks, int $obtainedMarks): int
    {
        $percentage = ($obtainedMarks / $totalMarks) * 100;
        
        // Determine grade (can be customized)
        $grade = match(true) {
            $percentage >= 90 => 'A',
            $percentage >= 80 => 'B',
            $percentage >= 70 => 'C',
            $percentage >= 60 => 'D',
            default => 'F'
        };

        // Get passing marks from exam
        $stmt = $this->pdo->prepare("SELECT passing_marks FROM exams WHERE id = ?");
        $stmt->execute([$examId]);
        $exam = $stmt->fetch();
        $isPassed = $obtainedMarks >= ($exam['passing_marks'] ?? 50);

        return $this->insert([
            'exam_attempt_id' => $attemptId,
            'exam_id' => $examId,
            'student_id' => $studentId,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'skipped' => $skipped,
            'total_marks' => $totalMarks,
            'obtained_marks' => $obtainedMarks,
            'percentage' => round($percentage, 2),
            'is_passed' => $isPassed,
            'grade' => $grade,
        ]);
    }

    /**
     * Get result for attempt
     */
    public function getByAttemptId(int $attemptId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE exam_attempt_id = ?");
        $stmt->execute([$attemptId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get results for student in exam
     */
    public function getStudentExamResult(int $studentId, int $examId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE student_id = ? AND exam_id = ?
        ");
        $stmt->execute([$studentId, $examId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get statistics for exam
     */
    public function getExamStatistics(int $examId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_attempts,
                AVG(percentage) as avg_percentage,
                MAX(obtained_marks) as highest_score,
                MIN(obtained_marks) as lowest_score,
                SUM(CASE WHEN is_passed THEN 1 ELSE 0 END) as passed_count
            FROM {$this->table}
            WHERE exam_id = ?
        ");
        $stmt->execute([$examId]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Get leaderboard for exam
     */
    public function getLeaderboard(int $examId, ?int $academicGroupId = null, int $limit = 50): array
    {
        $query = "
            SELECT er.*, s.first_name, s.last_name, s.academic_group_id, u.login_code
            FROM {$this->table} er
            JOIN students s ON er.student_id = s.id
            JOIN users u ON s.user_id = u.id
            WHERE er.exam_id = ?
        ";

        $params = [$examId];

        if ($academicGroupId) {
            $query .= " AND s.academic_group_id = ?";
            $params[] = $academicGroupId;
        }

        $query .= " ORDER BY er.obtained_marks DESC, er.graded_at ASC LIMIT ?";
        $params[] = $limit;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
