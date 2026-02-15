<?php
/**
 * CSRF Protection Middleware
 */

namespace App\Middleware;

use App\Helpers\Security;

class CSRFMiddleware
{
    /**
     * Verify CSRF token on POST requests
     */
    public static function verify(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!Security::verifyCSRFToken($token)) {
            http_response_code(403);
            die(json_encode(['error' => 'CSRF token validation failed']));
        }
    }
}
