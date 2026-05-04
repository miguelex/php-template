<?php

declare(strict_types=1);

namespace App\Core;

use Dotenv\Dotenv;

/**
 * Bootstrap de la aplicación.
 * Se invoca una sola vez desde public/index.php.
 */
final class App
{
    public static function boot(): void
    {
        self::loadEnvironment();
        self::configurePhp();
        self::connectDatabase();
    }

    private static function loadEnvironment(): void
    {
        $dotenv = Dotenv::createImmutable(BASE_PATH);
        $dotenv->safeLoad();

        // Variables requeridas (ajustar según el proyecto)
        // $dotenv->required(['APP_ENV', 'DB_HOST', 'DB_DATABASE']);
    }

    private static function configurePhp(): void
    {
        $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

        error_reporting($debug ? E_ALL : 0);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');

        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Madrid');
    }

    private static function connectDatabase(): void
    {
        // Solo conectar si hay configuración de BD
        if (empty($_ENV['DB_DATABASE']) || empty($_ENV['DB_HOST'])) {
            return;
        }

        try {
            $pdo = Database::connect();
            ActiveRecord::setDB($pdo);
        } catch (\App\Exceptions\DatabaseException $e) {
            // En desarrollo/testing sin BD disponible, continuar sin conexión
            if (($_ENV['APP_ENV'] ?? 'local') === 'production') {
                throw $e;
            }
        }
    }
}
