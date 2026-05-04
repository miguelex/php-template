<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Repositorio base abstracto.
 * Extiende para cada entidad de dominio.
 *
 * Ejemplo:
 *   class UserRepository extends BaseRepository {
 *       protected string $table = 'users';
 *       protected string $entityClass = User::class;
 *   }
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected string $table       = '';
    protected string $entityClass = \stdClass::class;

    public function __construct(
        protected readonly PDO $db,
    ) {
    }

    // ── RepositoryInterface ───────────────────────────────────────────────────

    public function find(int $id): ?object
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1",
        );
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->hydrate($row) : null;
    }

    /** @return list<object> */
    public function findAll(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY id DESC",
        );
        $stmt->execute();

        return array_map(
            fn(array $row) => $this->hydrate($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC),
        );
    }

    public function save(object $entity): bool
    {
        $data = $this->extract($entity);

        if (isset($data['id']) && $data['id'] !== null) {
            return $this->update($data);
        }

        return $this->insert($data, $entity);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE id = :id LIMIT 1",
        );

        return $stmt->execute([':id' => $id]);
    }

    // ── Queries adicionales comunes ───────────────────────────────────────────

    /**
     * @return list<object>
     */
    public function findBy(string $column, mixed $value): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$column} = :value",
        );
        $stmt->execute([':value' => $value]);

        return array_map(
            fn(array $row) => $this->hydrate($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC),
        );
    }

    public function findOneBy(string $column, mixed $value): ?object
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1",
        );
        $stmt->execute([':value' => $value]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->hydrate($row) : null;
    }

    /**
     * @param  array<string, mixed> $criteria
     * @return list<object>
     */
    public function findWhere(array $criteria, string $orderBy = 'id DESC', ?int $limit = null): array
    {
        $wheres = [];
        $params = [];

        foreach ($criteria as $column => $value) {
            $wheres[]             = "{$column} = :{$column}";
            $params[":{$column}"] = $value;
        }

        $sql = "SELECT * FROM {$this->table}";

        if (!empty($wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $wheres);
        }

        $sql .= " ORDER BY {$orderBy}";

        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return array_map(
            fn(array $row) => $this->hydrate($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC),
        );
    }

    public function count(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table}");
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE id = :id",
        );
        $stmt->execute([':id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }

    // ── Paginación ────────────────────────────────────────────────────────────

    /**
     * @return array{data: list<object>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function paginate(int $page = 1, int $perPage = 15, string $orderBy = 'id DESC'): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $total  = $this->count();

        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} LIMIT :limit OFFSET :offset",
        );
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $data = array_map(
            fn(array $row) => $this->hydrate($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC),
        );

        return [
            'data'     => $data,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'lastPage' => (int) ceil($total / $perPage),
        ];
    }

    // ── Transacciones ─────────────────────────────────────────────────────────

    public function transaction(callable $callback): mixed
    {
        $this->db->beginTransaction();

        try {
            $result = $callback($this);
            $this->db->commit();

            return $result;
        } catch (\Throwable $e) {
            $this->db->rollBack();

            throw $e;
        }
    }

    // ── Abstractos (implementar en cada repositorio) ──────────────────────────

    /**
     * Convierte una fila de BD en una entidad de dominio.
     *
     * @param array<string, mixed> $row
     */
    abstract protected function hydrate(array $row): object;

    /**
     * Extrae los datos de una entidad para persistirla.
     *
     * @return array<string, mixed>
     */
    abstract protected function extract(object $entity): array;

    // ── Privados ──────────────────────────────────────────────────────────────

    /** @param array<string, mixed> $data */
    private function insert(array $data, object $entity): bool
    {
        unset($data['id']);

        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($k) => ":{$k}", array_keys($data)));

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})",
        );

        $prefixed = [];
        foreach ($data as $k => $v) {
            $prefixed[":{$k}"] = $v;
        }

        $result = $stmt->execute($prefixed);

        if ($result && property_exists($entity, 'id')) {
            $entity->id = (int) $this->db->lastInsertId();
        }

        return $result;
    }

    /** @param array<string, mixed> $data */
    private function update(array $data): bool
    {
        $id = $data['id'];
        unset($data['id']);

        $sets   = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($data)));
        $params = [':id' => $id];

        foreach ($data as $k => $v) {
            $params[":{$k}"] = $v;
        }

        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET {$sets} WHERE id = :id LIMIT 1",
        );

        return $stmt->execute($params);
    }
}
