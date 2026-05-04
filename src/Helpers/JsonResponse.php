<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Helper para respuestas JSON (modo API).
 *
 * Uso en controllers:
 *   JsonResponse::ok(['users' => $users]);
 *   JsonResponse::created(['id' => $id]);
 *   JsonResponse::error('Not found', 404);
 *   JsonResponse::validationError($alerts);
 */
final class JsonResponse
{
    public static function ok(mixed $data = null, string $message = 'OK'): never
    {
        self::send(200, $message, $data);
    }

    public static function created(mixed $data = null, string $message = 'Created'): never
    {
        self::send(201, $message, $data);
    }

    public static function noContent(): never
    {
        http_response_code(204);
        exit;
    }

    public static function error(string $message, int $status = 400, mixed $data = null): never
    {
        self::send($status, $message, $data, false);
    }

    public static function notFound(string $message = 'Not found'): never
    {
        self::error($message, 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): never
    {
        self::error($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): never
    {
        self::error($message, 403);
    }

    public static function serverError(string $message = 'Server error'): never
    {
        self::error($message, 500);
    }

    /**
     * @param array<string, list<string>> $errors
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): never
    {
        self::send(422, $message, ['errors' => $errors], false);
    }

    // ── Privados ──────────────────────────────────────────────────────────────

    private static function send(int $status, string $message, mixed $data, bool $success = true): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        $payload = ['success' => $success, 'message' => $message];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
