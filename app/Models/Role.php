<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Role
{
    public static function all(): array
    {
        $sql = 'SELECT r.*, COUNT(DISTINCT ur.user_id) AS user_count, COUNT(DISTINCT rp.permission_id) AS permission_count
                FROM roles r
                LEFT JOIN user_roles ur ON ur.role_id = r.id
                LEFT JOIN role_permissions rp ON rp.role_id = r.id
                GROUP BY r.id ORDER BY r.is_system DESC, r.name';
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM roles WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public static function permissionIds(int $id): array
    {
        $statement = Database::connection()->prepare('SELECT permission_id FROM role_permissions WHERE role_id = :id');
        $statement->execute(['id' => $id]);
        return array_map('intval', array_column($statement->fetchAll(), 'permission_id'));
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM roles WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $exceptId;
        }
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn() > 0;
    }

    public static function create(array $data, array $permissionIds): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $statement = $pdo->prepare('INSERT INTO roles (name, slug, description) VALUES (:name, :slug, :description)');
            $statement->execute($data);
            $id = (int) $pdo->lastInsertId();
            self::syncPermissions($id, $permissionIds, $pdo);
            $pdo->commit();
            return $id;
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public static function update(int $id, array $data, array $permissionIds): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $statement = $pdo->prepare('UPDATE roles SET name = :name, slug = :slug, description = :description WHERE id = :id');
            $statement->execute($data + ['id' => $id]);
            self::syncPermissions($id, $permissionIds, $pdo);
            $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private static function syncPermissions(int $roleId, array $permissionIds, PDO $pdo): void
    {
        $pdo->prepare('DELETE FROM role_permissions WHERE role_id = :id')->execute(['id' => $roleId]);
        $insert = $pdo->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)');
        foreach ($permissionIds as $permissionId) {
            $insert->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
        }
    }

    public static function delete(int $id): bool
    {
        $role = self::find($id);
        if (!$role || (int) $role['is_system'] === 1 || self::assignedUserCount($id) > 0) {
            return false;
        }
        $statement = Database::connection()->prepare('DELETE FROM roles WHERE id = :id');
        return $statement->execute(['id' => $id]);
    }

    public static function count(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM roles')->fetchColumn();
    }

    public static function assignedUserCount(int $id): int
    {
        $statement = Database::connection()->prepare('SELECT COUNT(*) FROM user_roles WHERE role_id = :id');
        $statement->execute(['id' => $id]);
        return (int) $statement->fetchColumn();
    }
}
