<?php
/**
 * Application Configuration
 */

return [
    'name' => 'BrainToper',
    'version' => '1.0.0',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => $_ENV['APP_DEBUG'] ?? false,
    'url' => $_ENV['APP_URL'] ?? 'https://braintoper.com',
    'timezone' => 'UTC',
    
    // Session configuration
    'session' => [
        'lifetime' => 3600,
        'cookie_name' => 'BRAINTOPER_SESS',
        'cookie_httponly' => true,
        'cookie_secure' => true,
        'cookie_samesite' => 'Strict',
        'cookie_path' => '/',
        'cookie_domain' => $_ENV['SESSION_DOMAIN'] ?? '',
    ],
    
    // Security
    'csrf' => [
        'enabled' => true,
        'token_name' => '_csrf_token',
        'header_name' => 'X-CSRF-Token',
    ],
    
    // Rate limiting
    'rate_limit' => [
        'login_attempts' => 5,
        'login_window' => 900, // 15 minutes
    ],
    
    // File uploads
    'uploads' => [
        'max_size' => 5242880, // 5MB
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
        'upload_dir' => __DIR__ . '/../storage/uploads/questions',
    ],
    
    // SMS Gateway
    'sms' => [
        'enabled' => $_ENV['SMS_ENABLED'] ?? false,
        'gateway' => $_ENV['SMS_GATEWAY'] ?? 'twilio',
        'api_key' => $_ENV['SMS_API_KEY'] ?? '',
        'api_secret' => $_ENV['SMS_API_SECRET'] ?? '',
        'from' => $_ENV['SMS_FROM'] ?? '',
    ],
    
    // Email Configuration
    'email' => [
        'enabled' => $_ENV['EMAIL_ENABLED'] ?? false,
        'driver' => $_ENV['MAIL_DRIVER'] ?? 'smtp',
        'host' => $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io',
        'port' => $_ENV['MAIL_PORT'] ?? 465,
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'from' => $_ENV['MAIL_FROM'] ?? 'noreply@braintoper.com',
        'from_name' => 'BrainToper',
    ],
    
    // WebSocket Server
    'websocket' => [
        'enabled' => $_ENV['WEBSOCKET_ENABLED'] ?? true,
        'host' => $_ENV['WEBSOCKET_HOST'] ?? 'localhost',
        'port' => $_ENV['WEBSOCKET_PORT'] ?? 8080,
        'protocol' => $_ENV['WEBSOCKET_PROTOCOL'] ?? 'ws',
    ],
];
