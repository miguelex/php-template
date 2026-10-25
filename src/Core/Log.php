<?php

declare(strict_types=1);

namespace App\Core;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Punto de acceso al logger (PSR-3) de la aplicación.
 *
 * Por defecto escribe en storage/logs/app.log. Se puede sustituir el logger
 * (p. ej. en tests o para añadir canales) con Log::set().
 *
 *   Log::info('Usuario creado', ['id' => $user->id]);
 *   Log::error('Fallo al enviar email', ['exception' => $e]);
 *   Log::logger()->pushHandler(...); // acceso directo al Monolog subyacente
 */
final class Log
{
    private static ?LoggerInterface $logger = null;

    public static function set(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    public static function logger(): LoggerInterface
    {
        if (!self::$logger instanceof \Psr\Log\LoggerInterface) {
            $logger  = new Logger('app');
            $logPath = (defined('BASE_PATH') ? BASE_PATH : getcwd()) . '/storage/logs/app.log';
            $logger->pushHandler(new StreamHandler($logPath, Level::Debug));

            self::$logger = $logger;
        }

        return self::$logger;
    }

    /** @param array<string, mixed> $context */
    public static function info(string $message, array $context = []): void
    {
        self::logger()->info($message, $context);
    }

    /** @param array<string, mixed> $context */
    public static function warning(string $message, array $context = []): void
    {
        self::logger()->warning($message, $context);
    }

    /** @param array<string, mixed> $context */
    public static function error(string $message, array $context = []): void
    {
        self::logger()->error($message, $context);
    }
}
