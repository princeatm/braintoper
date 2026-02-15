<?php
/**
 * Audit Log Model
 */

namespace App\Models;

class AuditLog extends BaseModel
{
    protected string $table = 'audit_logs';

    /**
     * Log action
     */
    public function log(int $userId, string $action, string $resource, string $details = '', bool $success = true): int
    {
        return $this->insert([
            'user_id' => $userId,
            'action' => $action,
            'resource' => $resource,
            'details' => $details,
            'success' => $success ? 1 : 0,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);
    }

    /**
     * Get logs for user
     */
    public function getByUserId(int $userId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get logs by action
     */
    public function getByAction(string $action, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE action = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$action, $limit]);
        return $stmt->fetchAll();
    }
}
