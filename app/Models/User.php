<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    public static function all(): array
    {
        $sql = "SELECT u.id, u.name, u.email, u.status, u.last_login_at, u.created_at,
                       GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ', ') AS role_names,
                       GROUP_CONCAT(DISTINCT r.slug) AS role_slugs
                FROM users u
                LEFT JOIN user_roles ur ON ur.user_id = u.id
                LEFT JOIN roles r ON r.id = ur.role_id
                GROUP BY u.id ORDER BY u.created_at DESC";
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function recent(int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));
        return Database::connection()->query("SELECT id, name, email, status, created_at FROM users ORDER BY created_at DESC LIMIT {$limit}")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT id, name, email, status, last_login_at, created_at FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public static function roleIds(int $id): array
    {
        $statement = Database::connection()->prepare('SELECT role_id FROM user_roles WHERE user_id = :id');
        $statement->execute(['id' => $id]);
        return array_map('intval', array_column($statement->fetchAll(), 'role_id'));
    }

    public static function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';
        $params = ['email' => mb_strtolower(trim($email))];
        if ($exceptId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $exceptId;
        }
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn() > 0;
    }

    public static function create(array $data, array $roleIds): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $statement = $pdo->prepare('INSERT INTO users (name, email, password, status) VALUES (:name, :email, :password, :status)');
            $statement->execute($data);
            $id = (int) $pdo->lastInsertId();
            self::syncRoles($id, $roleIds, $pdo);
            $pdo->commit();
            return $id;
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public static function update(int $id, array $data, array $roleIds): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $sql = 'UPDATE users SET name = :name, email = :email, status = :status';
            if (!empty($data['password'])) {
                $sql .= ', password = :password';
            } else {
                unset($data['password']);
            }
            $sql .= ' WHERE id = :id';
            $statement = $pdo->prepare($sql);
            $statement->execute($data + ['id' => $id]);
            self::syncRoles($id, $roleIds, $pdo);
            $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private static function syncRoles(int $userId, array $roleIds, PDO $pdo): void
    {
        $pdo->prepare('DELETE FROM user_roles WHERE user_id = :id')->execute(['id' => $userId]);
        $insert = $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)');
        foreach ($roleIds as $roleId) {
            $insert->execute(['user_id' => $userId, 'role_id' => $roleId]);
        }
    }

    public static function validRoleIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $statement = Database::connection()->prepare("SELECT id FROM roles WHERE id IN ({$placeholders})");
        $statement->execute($ids);
        return array_map('intval', array_column($statement->fetchAll(), 'id'));
    }

    public static function toggle(int $id): void
    {
        Database::connection()->prepare('UPDATE users SET status = IF(status = 1, 0, 1) WHERE id = :id')->execute(['id' => $id]);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $id]);
    }

    public static function count(?bool $active = null): int
    {
        $sql = 'SELECT COUNT(*) FROM users';
        if ($active !== null) {
            $sql .= ' WHERE status = ' . ($active ? '1' : '0');
        }
        return (int) Database::connection()->query($sql)->fetchColumn();
    }

    public static function isSuperadmin(int $id): bool
    {
        $statement = Database::connection()->prepare("SELECT COUNT(*) FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = :id AND r.slug = 'superadmin'");
        $statement->execute(['id' => $id]);
        return (int) $statement->fetchColumn() > 0;
    }

    public static function superadminCount(): int
    {
        return (int) Database::connection()->query("SELECT COUNT(DISTINCT ur.user_id) FROM user_roles ur JOIN roles r ON r.id = ur.role_id JOIN users u ON u.id = ur.user_id WHERE r.slug = 'superadmin' AND u.status = 1")->fetchColumn();
    }
}
