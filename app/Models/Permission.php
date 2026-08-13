<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Permission
{
    public static function grouped(): array
    {
        $permissions = Database::connection()->query('SELECT * FROM permissions ORDER BY module, id')->fetchAll();
        $grouped = [];
        foreach ($permissions as $permission) {
            $grouped[$permission['module']][] = $permission;
        }
        return $grouped;
    }

    public static function validIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $statement = Database::connection()->prepare("SELECT id FROM permissions WHERE id IN ({$placeholders})");
        $statement->execute($ids);
        return array_map('intval', array_column($statement->fetchAll(), 'id'));
    }
}
