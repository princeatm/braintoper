<?php
/**
 * Database Initialization
 * Creates tables and default superadmin user
 */

// Load environment
if (file_exists(__DIR__ . '/../../.env')) {
    $lines = file(__DIR__ . '/../../.env');
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
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
