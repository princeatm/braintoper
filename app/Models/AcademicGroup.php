<?php
/**
 * Academic Group Model
 */

namespace App\Models;

class AcademicGroup extends BaseModel
{
    protected string $table = 'academic_groups';

    /**
     * Get by class, grade, arm
     */
    public function getByClassGradeArm(int $classId, int $gradeId, int $armId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE class_id = ? AND grade_id = ? AND arm_id = ?
        ");
        $stmt->execute([$classId, $gradeId, $armId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get or create group
     */
    public function getOrCreate(int $classId, int $gradeId, int $armId): int
    {
        $existing = $this->getByClassGradeArm($classId, $gradeId, $armId);
        
        if ($existing) {
            return $existing['id'];
        }

        // Get class, grade, arm names
        $stmt = $this->pdo->prepare("SELECT name FROM classes WHERE id = ?");
        $stmt->execute([$classId]);
        $class = $stmt->fetch();

        $stmt = $this->pdo->prepare("SELECT name FROM grades WHERE id = ?");
        $stmt->execute([$gradeId]);
        $grade = $stmt->fetch();

        $stmt = $this->pdo->prepare("SELECT name FROM arms WHERE id = ?");
        $stmt->execute([$armId]);
        $arm = $stmt->fetch();

        $code = $class['name'] . $grade['name'] . $arm['name'];

        return $this->insert([
            'class_id' => $classId,
            'grade_id' => $gradeId,
            'arm_id' => $armId,
            'code' => $code,
        ]);
    }
}
