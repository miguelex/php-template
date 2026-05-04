<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Clase base para migraciones.
 *
 * Cada migración extiende esta clase e implementa up() y down().
 * Nombrar los ficheros como: YYYY_MM_DD_HHMMSS_descripcion.php
 *
 * Ejemplo:
 *   class CreateUsersTable extends Migration {
 *       public function up(): void {
 *           $this->execute("CREATE TABLE users (...)");
 *       }
 *       public function down(): void {
 *           $this->execute("DROP TABLE IF EXISTS users");
 *       }
 *   }
 */
abstract class Migration
{
    public function __construct(
        protected readonly PDO $db,
    ) {}

    abstract public function up(): void;

    abstract public function down(): void;

    // ── Helpers para las migraciones ──────────────────────────────────────────

    protected function execute(string $sql): void
    {
        $this->db->exec($sql);
    }

    protected function tableExists(string $table): bool
    {
        try {
            $this->db->query("SELECT 1 FROM {$table} LIMIT 1");

            return true;
        } catch (\PDOException) {
            return false;
        }
    }

    protected function columnExists(string $table, string $column): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM information_schema.columns
             WHERE table_name = :table AND column_name = :column",
        );
        $stmt->execute([':table' => $table, ':column' => $column]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
