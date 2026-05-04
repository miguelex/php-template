<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\ActiveRecord;

/**
 * Modelo de ejemplo — eliminar o adaptar al proyecto real.
 *
 * CREATE TABLE users (
 *   id         INT AUTO_INCREMENT PRIMARY KEY,
 *   name       VARCHAR(100) NOT NULL,
 *   email      VARCHAR(150) NOT NULL UNIQUE,
 *   password   VARCHAR(255) NOT NULL,
 *   token      VARCHAR(64)  DEFAULT NULL,
 *   confirmed  TINYINT(1)   DEFAULT 0,
 *   created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
 * );
 */
final class User extends ActiveRecord
{
    protected static string $table = 'users';

    /** @var list<string> */
    protected static array $columns = [
        'id', 'name', 'email', 'password', 'token', 'confirmed', 'created_at',
    ];

    public string $name       = '';
    public string $email      = '';
    public string $password   = '';
    public string $token      = '';
    public int $confirmed     = 0;
    public string $created_at = '';

    // ── Validación ────────────────────────────────────────────────────────────

    /** @return array<string, list<string>> */
    public function validate(): array
    {
        static::clearAlerts();

        if (empty($this->name)) {
            static::addAlert('error', 'El nombre es obligatorio.');
        }

        if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            static::addAlert('error', 'El email no es válido.');
        }

        if (strlen($this->password) < 8) {
            static::addAlert('error', 'La contraseña debe tener al menos 8 caracteres.');
        }

        return static::getAlerts();
    }

    // ── Métodos específicos ───────────────────────────────────────────────────

    public function hashPassword(): void
    {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function verifyPassword(string $plain): bool
    {
        return password_verify($plain, $this->password);
    }

    public function generateToken(): void
    {
        $this->token = bin2hex(random_bytes(32));
    }

    public function confirm(): bool
    {
        $this->confirmed = 1;
        $this->token     = '';

        return $this->save();
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed === 1;
    }
}
