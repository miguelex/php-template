<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\DatabaseException;
use PDO;
use PDOException;

final class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    public static function connect(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $driver   = $_ENV['DB_CONNECTION'] ?? 'mysql';
        $host     = $_ENV['DB_HOST']       ?? '127.0.0.1';
        $port     = $_ENV['DB_PORT']       ?? '3306';
        $database = $_ENV['DB_DATABASE']   ?? '';
        $username = $_ENV['DB_USERNAME']   ?? '';
        $password = $_ENV['DB_PASSWORD']   ?? '';
        $charset  = $_ENV['DB_CHARSET']    ?? 'utf8mb4';

        $dsn = "{$driver}:host={$host};port={$port};dbname={$database};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            self::$instance = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // No exponer credenciales en producción
            $message = $_ENV['APP_DEBUG'] === 'true'
                ? $e->getMessage()
                : 'Database connection failed.';

            throw new DatabaseException($message, (int) $e->getCode(), $e);
        }

        return self::$instance;
    }
}
