<?php
/**
 * Notification Model
 */

namespace App\Models;

class Notification extends BaseModel
{
    protected string $table = 'notifications';

    /**
     * Create notification
     */
    public function createNotification(int $userId, string $type, string $title, string $message, string $method = 'sms'): int
    {
        return $this->insert([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'notification_method' => $method,
            'status' => 'pending',
        ]);
    }

    /**
     * Mark as sent
     */
    public function markAsSent(int $notificationId): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table}
            SET status = 'sent', sent_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        return $stmt->execute([$notificationId]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(int $notificationId): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table}
            SET status = 'failed'
            WHERE id = ?
        ");
        return $stmt->execute([$notificationId]);
    }

    /**
     * Get pending notifications
     */
    public function getPending(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE status = 'pending'
            ORDER BY created_at ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
