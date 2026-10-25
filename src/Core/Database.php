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
        if (self::$instance instanceof \PDO) {
            return self::$instance;
        }

        $driver   = $_ENV['DB_CONNECTION'] ?? 'mysql';
        $username = $_ENV['DB_USERNAME']   ?? '';
        $password = $_ENV['DB_PASSWORD']   ?? '';

        $dsn = self::buildDsn($driver);

        // SQLite no usa credenciales.
        if ($driver === 'sqlite') {
            $username = '';
            $password = '';
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            self::$instance = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // No exponer credenciales en producción
            $message = ($_ENV['APP_DEBUG'] ?? 'false') === 'true'
                ? $e->getMessage()
                : 'Database connection failed.';

            throw new DatabaseException($message, (int) $e->getCode(), $e);
        }

        return self::$instance;
    }

    /**
     * Construye el DSN PDO según el driver configurado (DB_CONNECTION).
     * Soporta mysql, sqlite y pgsql.
     */
    private static function buildDsn(string $driver): string
    {
        $host     = $_ENV['DB_HOST']     ?? '127.0.0.1';
        $database = $_ENV['DB_DATABASE'] ?? '';

        return match ($driver) {
            'sqlite' => 'sqlite:' . ($database),
            'pgsql'  => sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $host,
                $_ENV['DB_PORT'] ?? '5432',
                $database,
            ),
            default  => sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $host,
                $_ENV['DB_PORT'] ?? '3306',
                $database,
                $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            ),
        };
    }
}
