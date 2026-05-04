<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Utilidades HTML / output.
 * Reemplaza el funciones.php global con métodos tipados y seguros.
 */
final class Html
{
    /**
     * Escapa HTML para output seguro en vistas.
     * Usar siempre al mostrar datos del usuario.
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Vuelca una variable de forma legible (solo en desarrollo).
     */
    public static function dump(mixed $variable, bool $exit = true): void
    {
        if (($_ENV['APP_DEBUG'] ?? 'false') !== 'true') {
            return;
        }

        echo '<pre style="background:#1e293b;color:#e2e8f0;padding:1rem;border-radius:6px;overflow:auto;font-size:0.875rem">';
        var_dump($variable);
        echo '</pre>';

        if ($exit) {
            exit;
        }
    }

    /**
     * Muestra alertas en vistas.
     *
     * @param array<string, list<string>> $alerts
     */
    public static function alerts(array $alerts): string
    {
        if (empty($alerts)) {
            return '';
        }

        $colors = [
            'error'   => '#fee2e2;color:#991b1b',
            'success' => '#dcfce7;color:#166534',
            'warning' => '#fef3c7;color:#92400e',
            'info'    => '#dbeafe;color:#1e40af',
        ];

        $html = '';

        foreach ($alerts as $type => $messages) {
            $style = $colors[$type] ?? $colors['info'];

            foreach ($messages as $message) {
                $msg  = self::e($message);
                $html .= "<div style='background:{$style};padding:.75rem 1rem;border-radius:6px;margin-bottom:.5rem'>{$msg}</div>";
            }
        }

        return $html;
    }

    /**
     * Genera un token CSRF y lo guarda en sesión.
     */
    public static function csrfField(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        $token = self::e($_SESSION['_csrf_token']);

        return "<input type='hidden' name='_csrf_token' value='{$token}'>";
    }

    /**
     * Verifica el token CSRF. Lanza excepción si es inválido.
     */
    public static function verifyCsrf(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token    = $_POST['_csrf_token'] ?? '';
        $expected = $_SESSION['_csrf_token'] ?? '';

        if (!hash_equals($expected, $token)) {
            http_response_code(419);

            throw new \RuntimeException('CSRF token mismatch.');
        }
    }
}
