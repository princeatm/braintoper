<?php
/**
 * User Model
 */

namespace App\Models;

class User extends BaseModel
{
    protected string $table = 'users';

    /**
     * Find by login code
     */
    public function findByLoginCode(string $loginCode): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE login_code = ?");
        $stmt->execute([strtoupper($loginCode)]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create new user
     */
    public function create(string $loginCode, string $role, string $pinHash): int
    {
        return $this->insert([
            'login_code' => strtoupper($loginCode),
            'role' => $role,
            'pin_hash' => $pinHash,
        ]);
    }

    /**
     * Update last login
     */
    public function updateLastLogin(int $userId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    /**
     * Check if user exists
     */
    public function exists(string $loginCode): bool
    {
        return $this->findByLoginCode($loginCode) !== null;
    }

    /**
     * Get user with role
     */
    public function getUserWithRole(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*, 
                   s.first_name as student_first_name, s.last_name as student_last_name,
                   t.first_name as teacher_first_name, t.last_name as teacher_last_name,
                   a.first_name as admin_first_name, a.last_name as admin_last_name,
                   sa.first_name as superadmin_first_name, sa.last_name as superadmin_last_name
            FROM {$this->table} u
            LEFT JOIN students s ON u.id = s.user_id
            LEFT JOIN teachers t ON u.id = t.user_id
            LEFT JOIN admins a ON u.id = a.user_id
            LEFT JOIN super_admins sa ON u.id = sa.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }
}
