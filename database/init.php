<?php
/**
 * Database Initialization
 * Creates tables and default superadmin user
 */

// Load environment
// Support Railway's native environment variables
$_ENV['DB_HOST'] = getenv('DATABASE_URL_HOST') ?: (getenv('DB_HOST') ?: 'localhost');
$_ENV['DB_PORT'] = getenv('DATABASE_URL_PORT') ?: (getenv('DB_PORT') ?: 3306);
$_ENV['DB_NAME'] = getenv('DATABASE_URL_DATABASE') ?: (getenv('DB_NAME') ?: 'braintoper');
$_ENV['DB_USER'] = getenv('DATABASE_URL_USER') ?: (getenv('DB_USER') ?: 'root');
$_ENV['DB_PASS'] = getenv('DATABASE_URL_PASSWORD') ?: (getenv('DB_PASS') ?: '');

// Load additional config from .env if it exists
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env');
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            // Skip database vars
            if (!in_array(trim($key), ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'])) {
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
}

require_once __DIR__ . '/../../app/Autoloader.php';
\App\Autoloader::register();

use App\Helpers\Database;
use App\Helpers\Migration;

// Initialize database
$dbConfig = include __DIR__ . '/../../config/database.php';
Database::init($dbConfig);
$pdo = Database::connect();

try {
    // Run migrations to create tables
    echo "📝 Running migrations...\n";
    $migration = new Migration($pdo);
    $migration->runAll();
    echo "✅ Database tables created successfully\n\n";

    // Create default superadmin user
    echo "👤 Creating default superadmin user...\n";
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE login_code = ?");
    $stmt->execute(['superadmin']);
    $exists = $stmt->fetch();
    
    if (!$exists) {
        // PIN: 1234 hashed
        $pinHash = password_hash('1234', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (login_code, role, pin_hash, is_active) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute(['superadmin', 'superadmin', $pinHash, true]);
        echo "✅ Default superadmin user created\n";
        echo "   📝 Login Code: superadmin\n";
        echo "   🔐 PIN: 1234\n";
        echo "   ⚠️  IMPORTANT: Change this PIN immediately in production!\n";
    } else {
        echo "✅ Superadmin user already exists\n";
    }
    
    echo "\n✅ Database initialization completed successfully!\n";
    
} catch (Exception $e) {
    echo "⚠️  Warning: " . $e->getMessage() . "\n";
    echo "This may be okay if the database is already initialized.\n";
}
