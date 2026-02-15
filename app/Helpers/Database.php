<?php
/**
 * Database Connection Class
 * Singleton pattern for PDO connection with connection pooling
 */

namespace App\Helpers;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;
    private static array $config = [];

    /**
     * Initialize database connection
     */
    public static function init(array $config): void
    {
        self::$config = $config;
    }

    /**
     * Get database connection
     */
    public static function connect(): PDO
    {
        if (self::$connection === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                    self::$config['host'],
                    self::$config['port'],
                    self::$config['database'],
                    self::$config['charset']
                );

                self::$connection = new PDO(
                    $dsn,
                    self::$config['username'],
                    self::$config['password'],
                    self::$config['options']
                );

                // Set strict mode
                self::$connection->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES'");
            } catch (PDOException $e) {
                throw new PDOException(
                    sprintf(
                        'Database connection failed: %s',
                        $e->getMessage()
                    ),
                    (int)$e->getCode()
                );
            }
        }

        return self::$connection;
    }

    /**
     * Close database connection
     */
    public static function close(): void
    {
        self::$connection = null;
    }
}
