<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Router;

/**
 * Middleware de autenticación por sesión.
 *
 * Uso en rutas protegidas:
 *   $router->use(AuthMiddleware::check(...));
 *
 * O por ruta individual en el controller:
 *   AuthMiddleware::require($router);
 *
 * O como middleware selectivo:
 *   $router->get('/dashboard', function(Router $r) {
 *       AuthMiddleware::require($r);
 *       // lógica protegida
 *   });
 */
final class AuthMiddleware
{
    private const SESSION_KEY = 'user_id';

    /**
     * Verificar autenticación. Redirige a /login si no hay sesión.
     * Usar como callable en $router->use()
     */
    public static function check(Router $router): void
    {
        self::startSession();

        if (!self::isAuthenticated()) {
            $router->redirect('/login');
        }
    }

    /**
     * Verificar autenticación en un punto concreto del controller.
     * Redirige si no hay sesión activa.
     */
    public static function require(Router $router, string $redirectTo = '/login'): void
    {
        self::startSession();

        if (!self::isAuthenticated()) {
            $router->redirect($redirectTo);
        }
    }

    /**
     * Redirigir a home si ya está autenticado (para login/registro).
     */
    public static function guest(Router $router, string $redirectTo = '/'): void
    {
        self::startSession();

        if (self::isAuthenticated()) {
            $router->redirect($redirectTo);
        }
    }

    // ── Gestión de sesión ─────────────────────────────────────────────────────

    /**
     * @param array<string, mixed> $data Datos del usuario a guardar en sesión
     */
    public static function login(array $data): void
    {
        self::startSession();
        session_regenerate_id(true); // Prevenir session fixation

        foreach ($data as $key => $value) {
            $_SESSION[$key] = $value;
        }

        $_SESSION[self::SESSION_KEY] = $data['id'] ?? null;
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly'],
            );
        }

        session_destroy();
    }

    public static function isAuthenticated(): bool
    {
        return !empty($_SESSION[self::SESSION_KEY]);
    }

    public static function userId(): int|null
    {
        return isset($_SESSION[self::SESSION_KEY])
            ? (int) $_SESSION[self::SESSION_KEY]
            : null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function user(): array
    {
        return $_SESSION ?? [];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    // ── Privados ──────────────────────────────────────────────────────────────

    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
