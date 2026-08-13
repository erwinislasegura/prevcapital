<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\Database;
use RuntimeException;

final class Schema
{
    public static function install(): void
    {
        $path = APP_ROOT . '/database/schema.sql';
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException('No fue posible leer el esquema de instalación.');
        }

        $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [];
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement !== '') {
                Database::connection()->exec($statement);
            }
        }
    }
}
