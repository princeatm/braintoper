<?php
/**
 * Teacher Model
 */

namespace App\Models;

class Teacher extends BaseModel
{
    protected string $table = 'teachers';

    /**
     * Find by user ID
     */
    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create teacher
     */
    public function create(int $userId, string $firstName, string $lastName, string $email, ?string $specialization = null): int
    {
        return $this->insert([
            'user_id' => $userId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'specialization' => $specialization,
        ]);
    }

    /**
     * Get exams count
     */
    public function getExamsCount(int $teacherId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM exams WHERE teacher_id = ?");
        $stmt->execute([$teacherId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
