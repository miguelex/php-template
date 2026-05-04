<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Contrato base para todos los repositorios.
 *
 * Implementar en src/Repositories/ para desacoplar
 * el acceso a datos de la lógica de negocio.
 *
 * Cuándo usar Repository vs ActiveRecord:
 *  - ActiveRecord  → proyectos pequeños/medianos, CRUD directo
 *  - Repository    → lógica de dominio compleja, testabilidad,
 *                    múltiples fuentes de datos, proyectos grandes
 */
interface RepositoryInterface
{
    public function find(int $id): ?object;

    /** @return list<object> */
    public function findAll(): array;

    public function save(object $entity): bool;

    public function delete(int $id): bool;
}
