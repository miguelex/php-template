<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Ejecutor de migraciones.
 * Gestiona qué migraciones se han ejecutado y cuáles quedan pendientes.
 *
 * Usado desde bin/console (migrate, rollback, status).
 */
final class Migrator
{
    private const TABLE = 'migrations';

    public function __construct(
        private readonly PDO $db,
        private readonly string $migrationsPath,
    ) {
        $this->ensureMigrationsTable();
    }

    // ── Comandos principales ──────────────────────────────────────────────────

    public function migrate(): void
    {
        $pending = $this->getPending();

        if (empty($pending)) {
            echo "  Nothing to migrate.\n";

            return;
        }

        foreach ($pending as $file) {
            $this->runUp($file);
        }
    }

    public function rollback(int $steps = 1): void
    {
        $ran = $this->getRan();

        if (empty($ran)) {
            echo "  Nothing to rollback.\n";

            return;
        }

        $toRollback = array_slice(array_reverse($ran), 0, $steps);

        foreach ($toRollback as $migration) {
            $this->runDown($migration);
        }
    }

    public function status(): void
    {
        $ran     = $this->getRan();
        $all     = $this->getAll();
        $pending = array_diff($all, $ran);

        echo "\n  Migration Status\n";
        echo "  " . str_repeat('─', 60) . "\n";

        foreach ($all as $file) {
            $name   = pathinfo($file, PATHINFO_FILENAME);
            $status = in_array($file, $ran, true) ? '✓ Ran' : '○ Pending';
            printf("  %-10s %s\n", $status, $name);
        }

        echo "\n";
        printf("  Ran: %d  |  Pending: %d\n\n", count($ran), count($pending));
    }

    public function fresh(): void
    {
        $ran = array_reverse($this->getRan());

        foreach ($ran as $migration) {
            $this->runDown($migration);
        }

        $this->migrate();
    }

    // ── Privados ──────────────────────────────────────────────────────────────

    private function runUp(string $file): void
    {
        $name = pathinfo($file, PATHINFO_FILENAME);
        echo "  Migrating: {$name}\n";

        require_once $this->migrationsPath . '/' . $file;

        $class     = $this->classNameFromFile($file);
        $migration = new $class($this->db);
        $migration->up();

        $this->db->prepare(
            "INSERT INTO " . self::TABLE . " (migration, ran_at) VALUES (:migration, NOW())",
        )->execute([':migration' => $file]);

        echo "  Migrated:  {$name} ✓\n";
    }

    private function runDown(string $file): void
    {
        $name = pathinfo($file, PATHINFO_FILENAME);
        echo "  Rolling back: {$name}\n";

        require_once $this->migrationsPath . '/' . $file;

        $class     = $this->classNameFromFile($file);
        $migration = new $class($this->db);
        $migration->down();

        $this->db->prepare(
            "DELETE FROM " . self::TABLE . " WHERE migration = :migration",
        )->execute([':migration' => $file]);

        echo "  Rolled back: {$name} ✓\n";
    }

    /** @return list<string> */
    private function getPending(): array
    {
        return array_values(array_diff($this->getAll(), $this->getRan()));
    }

    /** @return list<string> */
    private function getRan(): array
    {
        $stmt = $this->db->query(
            "SELECT migration FROM " . self::TABLE . " ORDER BY ran_at ASC",
        );

        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    /** @return list<string> */
    private function getAll(): array
    {
        $files = glob($this->migrationsPath . '/*.php') ?: [];

        return array_map('basename', $files);
    }

    private function classNameFromFile(string $file): string
    {
        // 2024_01_15_120000_create_users_table.php → CreateUsersTable
        $name = pathinfo($file, PATHINFO_FILENAME);

        // Eliminar el prefijo de timestamp
        $name = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $name) ?? $name;

        // snake_case → PascalCase
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }

    private function ensureMigrationsTable(): void
    {
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS " . self::TABLE . " (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                migration  VARCHAR(255) NOT NULL UNIQUE,
                ran_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
        );
    }
}
