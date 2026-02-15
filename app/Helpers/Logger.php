<?php
/**
 * Logger Helper
 * Audit logging for all user actions
 */

namespace App\Helpers;

class Logger
{
    private const LOG_DIR = __DIR__ . '/../../storage/logs';

    /**
     * Log message to file
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        $logFile = self::LOG_DIR . '/' . date('Y-m-d') . '.log';
        
        $logEntry = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            !empty($context) ? json_encode($context) : ''
        );

        error_log($logEntry, 3, $logFile);
    }

    /**
     * Log audit event
     */
    public static function audit(int $userId, string $action, string $resource, string $details, bool $success = true): void
    {
        self::log('audit', sprintf(
            'User %d - %s - %s',
            $userId,
            $action,
            $resource
        ), [
            'user_id' => $userId,
            'action' => $action,
            'resource' => $resource,
            'details' => $details,
            'success' => $success,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);
    }

    /**
     * Log error
     */
    public static function error(string $message, \Throwable $exception = null): void
    {
        self::log('error', $message, [
            'file' => $exception?->getFile(),
            'line' => $exception?->getLine(),
            'trace' => $exception?->getTraceAsString(),
        ]);
    }

    /**
     * Log info
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    /**
     * Log warning
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }
}
