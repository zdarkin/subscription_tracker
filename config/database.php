<?php
/**
 * Database Configuration & PDO Singleton
 * Provides a single PDO instance throughout the request lifecycle.
 */

require_once __DIR__ . '/env.php';

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone()    {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host    = $_ENV['DB_HOST']    ?? '127.0.0.1';
            $port    = $_ENV['DB_PORT']    ?? '3306';
            $dbname  = $_ENV['DB_NAME']    ?? 'subscription_tracker';
            $user    = $_ENV['DB_USER']    ?? 'root';
            $pass    = $_ENV['DB_PASS']    ?? '';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_FOUND_ROWS   => true,
                PDO::ATTR_TIMEOUT            => 5,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Log the raw error but surface a generic message
                error_log('[Database] Connection failed: ' . $e->getMessage());
                die(json_encode(['error' => 'Database connection failed. Please try again later.']));
            }
        }

        return self::$instance;
    }
}
