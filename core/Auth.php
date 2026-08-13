<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Auth
{
    private static ?array $user = null;

    public static function attempt(string $email, string $password): bool
    {
        $statement = Database::connection()->prepare('SELECT * FROM users WHERE email = :email AND status = 1 LIMIT 1');
        $statement->execute(['email' => mb_strtolower(trim($email))]);
        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        self::$user = null;
        Database::connection()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')->execute(['id' => $user['id']]);
        return true;
    }

    public static function check(): bool
    {
        return self::id() !== null && self::user() !== null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function user(): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }
        if (self::id() === null) {
            return null;
        }

        $sql = 'SELECT u.id, u.name, u.email, u.status, u.last_login_at,
                       GROUP_CONCAT(DISTINCT r.slug) AS role_slugs,
                       GROUP_CONCAT(DISTINCT p.slug) AS permission_slugs
                FROM users u
                LEFT JOIN user_roles ur ON ur.user_id = u.id
                LEFT JOIN roles r ON r.id = ur.role_id
                LEFT JOIN role_permissions rp ON rp.role_id = r.id
                LEFT JOIN permissions p ON p.id = rp.permission_id
                WHERE u.id = :id AND u.status = 1
                GROUP BY u.id LIMIT 1';
        $statement = Database::connection()->prepare($sql);
        $statement->execute(['id' => self::id()]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            self::logout();
            return null;
        }

        $user['roles'] = array_values(array_filter(explode(',', (string) $user['role_slugs'])));
        $user['permissions'] = array_values(array_filter(explode(',', (string) $user['permission_slugs'])));
        self::$user = $user;
        return self::$user;
    }

    public static function can(string $permission): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        return in_array('superadmin', $user['roles'], true)
            || in_array($permission, $user['permissions'], true);
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        self::$user = null;
        session_regenerate_id(true);
    }
}
