<?php
/**
 * Security Helper Functions
 * CSRF tokens, password hashing, input validation
 */

namespace App\Helpers;

use Exception;

class Security
{
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken(string $token): bool
    {
        if (empty($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    /**
     * Hash password/PIN using PHP's password_hash
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify password/PIN
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate random PIN
     */
    public static function generatePIN(): string
    {
        return str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Sanitize input string
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate login code format
     */
    public static function validateLoginCode(string $code): array
    {
        $code = strtoupper(trim($code));

        // Student: STU-XX-XXXX
        if (preg_match('/^STU-\d{2}-\d{4}$/', $code)) {
            return ['type' => 'student', 'code' => $code];
        }

        // Teacher: TEA-XX-XXXX
        if (preg_match('/^TEA-\d{2}-\d{4}$/', $code)) {
            return ['type' => 'teacher', 'code' => $code];
        }

        // Admin: AD-XX-XXX
        if (preg_match('/^AD-\d{2}-\d{3}$/', $code)) {
            return ['type' => 'admin', 'code' => $code];
        }

        // Super Admin: SUPAD-XX-XXXX
        if (preg_match('/^SUPAD-\d{2}-\d{4}$/', $code)) {
            return ['type' => 'superadmin', 'code' => $code];
        }

        return ['type' => null, 'code' => $code];
    }

    /**
     * Rate limit check
     */
    public static function checkRateLimit(string $key, int $attempts, int $window): bool
    {
        $cacheFile = __DIR__ . '/../../storage/cache/' . md5($key) . '.cache';

        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data['expires'] > time()) {
                if ($data['count'] >= $attempts) {
                    return false;
                }
                $data['count']++;
                file_put_contents($cacheFile, json_encode($data));
                return true;
            }
        }

        file_put_contents($cacheFile, json_encode([
            'count' => 1,
            'expires' => time() + $window
        ]));

        return true;
    }
}
