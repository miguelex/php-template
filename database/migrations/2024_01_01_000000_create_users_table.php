<?php

declare(strict_types=1);

use App\Core\Migration;

/**
 * Migración de ejemplo — tabla users.
 * Renombrar siguiendo el patrón: YYYY_MM_DD_HHMMSS_descripcion.php
 */
class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE IF NOT EXISTS users (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                name        VARCHAR(100)  NOT NULL,
                email       VARCHAR(150)  NOT NULL UNIQUE,
                password    VARCHAR(255)  NOT NULL,
                token       VARCHAR(64)   DEFAULT NULL,
                confirmed   TINYINT(1)    NOT NULL DEFAULT 0,
                created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_token (token)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS users');
    }
}
