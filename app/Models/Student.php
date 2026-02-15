<?php
/**
 * Student Model
 */

namespace App\Models;

class Student extends BaseModel
{
    protected string $table = 'students';

    /**
     * Find by user ID
     */
    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT s.*, ag.code as academic_group_code, 
                   c.name as class_name, g.name as grade_name, a.name as arm_name
            FROM {$this->table} s
            LEFT JOIN academic_groups ag ON s.academic_group_id = ag.id
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN grades g ON s.grade_id = g.id
            LEFT JOIN arms a ON s.arm_id = a.id
            WHERE s.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get students by academic group
     */
    public function getByAcademicGroup(int $academicGroupId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table} 
            WHERE academic_group_id = ? AND is_active = TRUE
            ORDER BY last_name, first_name
        ");
        $stmt->execute([$academicGroupId]);
        return $stmt->fetchAll();
    }

    /**
     * Create student
     */
    public function create(int $userId, string $firstName, string $lastName, int $classId, int $gradeId, int $armId, int $academicGroupId): int
    {
        return $this->insert([
            'user_id' => $userId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'class_id' => $classId,
            'grade_id' => $gradeId,
            'arm_id' => $armId,
            'academic_group_id' => $academicGroupId,
        ]);
    }

    /**
     * Get student dashboard data
     */
    public function getDashboardData(int $userId): ?array
    {
        $student = $this->findByUserId($userId);
        if (!$student) {
            return null;
        }

        $stmt = $this->pdo->prepare("
            SELECT ea.*, e.title, e.subject_id, s.name as subject_name,
                   CASE WHEN er.id IS NOT NULL THEN TRUE ELSE FALSE END as is_graded
            FROM exam_attempts ea
            JOIN exams e ON ea.exam_id = e.id
            JOIN subjects s ON e.subject_id = s.id
            LEFT JOIN exam_results er ON ea.id = er.exam_attempt_id
            WHERE ea.student_id = ?
            ORDER BY ea.started_at DESC
            LIMIT 10
        ");
        $stmt->execute([$student['id']]);

        return [
            'student' => $student,
            'recent_exams' => $stmt->fetchAll(),
        ];
    }
}
