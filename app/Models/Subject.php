<?php
/**
 * Subject Model
 */

namespace App\Models;

class Subject extends BaseModel
{
    protected string $table = 'subjects';

    /**
     * Get all active subjects
     */
    public function getAllActive(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE is_active = TRUE
            ORDER BY name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get subjects by teacher
     */
    public function getByTeacherId(int $teacherId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT s.* FROM {$this->table} s
            JOIN teacher_subjects ts ON s.id = ts.subject_id
            WHERE ts.teacher_id = ?
            ORDER BY s.name ASC
        ");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }
}
