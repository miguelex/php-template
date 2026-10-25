<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\DatabaseException;
use PDO;
use PDOException;
use PDOStatement;

/**
 * ActiveRecord base — PHP 8.2, PDO, prepared statements.
 *
 * Uso:
 *   class Post extends ActiveRecord {
 *       protected static string $table = 'posts';
 *       protected static array $columns = ['id', 'title', 'body', 'created_at'];
 *   }
 *
 *   $post = Post::find(1);
 *   $posts = Post::all();
 *   $post->title = 'Nuevo título';
 *   $post->save();
 *   $post->delete();
 */
abstract class ActiveRecord
{
    // ── Configuración por modelo (sobreescribir en hijos) ────────────────────

    protected static string $table   = '';

    /** @var list<string> Columnas que existen en la BD (incluir 'id') */
    protected static array $columns  = [];

    // ── Conexión compartida ───────────────────────────────────────────────────

    private static ?PDO $db = null;

    // ── Alertas / mensajes de validación ─────────────────────────────────────

    /** @var array<string, list<string>> */
    protected static array $alerts = [];

    public int|null $id = null;

    // ── Conexión ─────────────────────────────────────────────────────────────

    public static function setDB(PDO $pdo): void
    {
        self::$db = $pdo;
    }

    protected static function db(): PDO
    {
        if (!self::$db instanceof \PDO) {
            throw new DatabaseException('No database connection set. Call ActiveRecord::setDB($pdo) first.');
        }

        return self::$db;
    }

    // ── Constructor ───────────────────────────────────────────────────────────

    /** @param array<string, mixed> $data */
    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    // ── Alertas ───────────────────────────────────────────────────────────────

    public static function addAlert(string $type, string $message): void
    {
        static::$alerts[$type][] = $message;
    }

    /** @return array<string, list<string>> */
    public static function getAlerts(): array
    {
        return static::$alerts;
    }

    public static function clearAlerts(): void
    {
        static::$alerts = [];
    }

    /** @return array<string, list<string>> Override in child to add validation */
    public function validate(): array
    {
        static::clearAlerts();

        return static::$alerts;
    }

    // ── Hydration ─────────────────────────────────────────────────────────────

    /** @param array<string, mixed> $data */
    public function fill(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function save(): bool
    {
        return $this->id === null ? $this->insert() : $this->update();
    }

    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }

        $table = static::$table;
        $stmt  = self::db()->prepare("DELETE FROM {$table} WHERE id = :id");

        return $stmt->execute([':id' => $this->id]);
    }

    // ── Queries ───────────────────────────────────────────────────────────────

    /** @return list<static> */
    public static function all(string $orderBy = 'id DESC'): array
    {
        $table   = static::$table;
        $orderBy = static::assertOrderBy($orderBy);
        $stmt    = self::db()->prepare("SELECT * FROM {$table} ORDER BY {$orderBy}");
        $stmt->execute();

        return self::hydrate($stmt);
    }

    public static function find(int $id): static|null
    {
        $table = static::$table;
        $stmt  = self::db()->prepare("SELECT * FROM {$table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? self::fromArray($row) : null;
    }

    /**
     * @return list<static>
     */
    public static function where(string $column, mixed $value): array
    {
        $table  = static::$table;
        $column = static::assertColumn($column);
        $stmt   = self::db()->prepare("SELECT * FROM {$table} WHERE {$column} = :value");
        $stmt->execute([':value' => $value]);

        return self::hydrate($stmt);
    }

    public static function findBy(string $column, mixed $value): static|null
    {
        $results = static::where($column, $value);

        return $results[0] ?? null;
    }

    /**
     * @param array<string, mixed> $conditions
     * @return list<static>
     */
    public static function whereMany(array $conditions): array
    {
        $table  = static::$table;
        $wheres = [];
        $params = [];

        foreach ($conditions as $column => $value) {
            $column               = static::assertColumn($column);
            $wheres[]             = "{$column} = :{$column}";
            $params[":{$column}"] = $value;
        }

        $sql  = "SELECT * FROM {$table} WHERE " . implode(' AND ', $wheres);
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return self::hydrate($stmt);
    }

    /** @return list<static> */
    public static function limit(int $n, string $orderBy = 'id DESC'): array
    {
        $table   = static::$table;
        $orderBy = static::assertOrderBy($orderBy);
        $stmt    = self::db()->prepare("SELECT * FROM {$table} ORDER BY {$orderBy} LIMIT :limit");
        $stmt->bindValue(':limit', $n, PDO::PARAM_INT);
        $stmt->execute();

        return self::hydrate($stmt);
    }

    public static function count(): int
    {
        $table = static::$table;
        $stmt  = self::db()->prepare("SELECT COUNT(*) FROM {$table}");
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    // ── Validación de identificadores SQL ──────────────────────────────────────

    /**
     * Valida un nombre de columna contra la whitelist del modelo y el formato.
     * Evita inyección SQL vía identificadores (que no pueden parametrizarse).
     */
    protected static function assertColumn(string $column): string
    {
        if (static::$columns !== [] && !in_array($column, static::$columns, true)) {
            throw new DatabaseException(
                "Unknown column '{$column}' for " . static::class . '.',
            );
        }

        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column) !== 1) {
            throw new DatabaseException("Invalid column name '{$column}'.");
        }

        return $column;
    }

    /**
     * Valida una cláusula ORDER BY. Acepta "col", "col ASC", "col DESC"
     * y listas separadas por comas.
     */
    protected static function assertOrderBy(string $orderBy): string
    {
        foreach (explode(',', $orderBy) as $part) {
            $part = trim($part);

            if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\s+(ASC|DESC))?$/i', $part) !== 1) {
                throw new DatabaseException("Invalid ORDER BY clause '{$orderBy}'.");
            }
        }

        return $orderBy;
    }

    // ── Privados ──────────────────────────────────────────────────────────────

    private function insert(): bool
    {
        $attrs   = $this->getAttributes();
        $columns = implode(', ', array_keys($attrs));
        $placeholders = implode(', ', array_map(fn(string $k): string => ":{$k}", array_keys($attrs)));

        $table = static::$table;
        $stmt  = self::db()->prepare(
            "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})",
        );

        $result = $stmt->execute($this->prefixKeys($attrs));

        if ($result) {
            $this->id = (int) self::db()->lastInsertId();
        }

        return $result;
    }

    private function update(): bool
    {
        $attrs  = $this->getAttributes();
        $sets   = implode(', ', array_map(fn(string $k): string => "{$k} = :{$k}", array_keys($attrs)));
        $params = $this->prefixKeys($attrs);
        $params[':id'] = $this->id;

        $table = static::$table;
        $stmt  = self::db()->prepare(
            "UPDATE {$table} SET {$sets} WHERE id = :id",
        );

        return $stmt->execute($params);
    }

    /** @return array<string, mixed> */
    private function getAttributes(): array
    {
        $attrs = [];

        foreach (static::$columns as $column) {
            if ($column === 'id') {
                continue;
            }

            $attrs[$column] = $this->$column ?? null;
        }

        return $attrs;
    }

    /**
     * Añade prefijo ':' a las claves para PDO.
     *
     * @param  array<string, mixed> $attrs
     * @return array<string, mixed>
     */
    private function prefixKeys(array $attrs): array
    {
        $result = [];

        foreach ($attrs as $key => $value) {
            $result[":{$key}"] = $value;
        }

        return $result;
    }

    /** @return list<static> */
    private static function hydrate(PDOStatement $stmt): array
    {
        $rows    = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $objects = [];

        foreach ($rows as $row) {
            $objects[] = self::fromArray($row);
        }

        return $objects;
    }

    /** @param array<string, mixed> $row */
    private static function fromArray(array $row): static
    {
        /** @phpstan-ignore new.static */
        $obj = new static();
        $obj->fill($row);

        return $obj;
    }
}
