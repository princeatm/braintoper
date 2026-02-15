<?php
/**
 * Authentication Middleware
 */

namespace App\Middleware;

use App\Helpers\Logger;

class AuthMiddleware
{
    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user
     */
    public static function getCurrentUser(): ?array
    {
        if (!self::isAuthenticated()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'role' => $_SESSION['role'] ?? null,
            'login_code' => $_SESSION['login_code'] ?? null,
        ];
    }

    /**
     * Require authentication
     */
    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            Logger::info('Unauthorized access attempt', [
                'ip' => $_SERVER['REMOTE_ADDR'],
                'uri' => $_SERVER['REQUEST_URI'],
            ]);
            header('Location: /');
            exit;
        }
    }

    /**
     * Require guest (not authenticated)
     */
    public static function requireGuest(): void
    {
        if (self::isAuthenticated()) {
            $role = $_SESSION['role'];
            $redirects = [
                'student' => '/dashboard/student',
                'teacher' => '/dashboard/teacher',
                'admin' => '/dashboard/admin',
                'superadmin' => '/dashboard/superadmin',
            ];
            header('Location: ' . ($redirects[$role] ?? '/'));
            exit;
        }
    }

    /**
     * Require specific role
     */
    public static function requireRole(string|array $roles): void
    {
        self::requireAuth();

        $requiredRoles = is_array($roles) ? $roles : [$roles];
        $userRole = $_SESSION['role'] ?? null;

        if (!in_array($userRole, $requiredRoles)) {
            Logger::audit(
                $_SESSION['user_id'],
                'UNAUTHORIZED_ACCESS',
                implode(',', $requiredRoles),
                'Attempted to access with insufficient permissions',
                false
            );
            http_response_code(403);
            die('Access Denied');
        }
    }
}
