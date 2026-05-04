<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;
use App\Models\User;

/**
 * Repositorio de usuarios.
 *
 * Extiende BaseRepository para añadir queries específicas
 * del dominio de usuarios sin mezclarlas con el modelo.
 *
 * Uso en un controller:
 *   $repo = new UserRepository(Database::connect());
 *   $user = $repo->findByEmail('test@example.com');
 */
final class UserRepository extends BaseRepository
{
    protected string $table       = 'users';
    protected string $entityClass = User::class;

    // ── Queries específicas del dominio ───────────────────────────────────────

    public function findByEmail(string $email): ?User
    {
        /** @var User|null */
        return $this->findOneBy('email', $email);
    }

    public function findByToken(string $token): ?User
    {
        /** @var User|null */
        return $this->findOneBy('token', $token);
    }

    /** @return list<User> */
    public function findConfirmed(): array
    {
        /** @var list<User> */
        return $this->findWhere(['confirmed' => 1], 'name ASC');
    }

    /** @return list<User> */
    public function findPending(): array
    {
        /** @var list<User> */
        return $this->findWhere(['confirmed' => 0], 'created_at DESC');
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE email = :email",
        );
        $stmt->execute([':email' => $email]);

        return (int) $stmt->fetchColumn() > 0;
    }

    // ── Hydration / Extraction ────────────────────────────────────────────────

    /** @param array<string, mixed> $row */
    protected function hydrate(array $row): User
    {
        $user = new User();
        $user->fill($row);

        return $user;
    }

    /** @return array<string, mixed> */
    protected function extract(object $entity): array
    {
        /** @var User $entity */
        return [
            'id'         => $entity->id,
            'name'       => $entity->name,
            'email'      => $entity->email,
            'password'   => $entity->password,
            'token'      => $entity->token,
            'confirmed'  => $entity->confirmed,
            'created_at' => $entity->created_at,
        ];
    }
}
