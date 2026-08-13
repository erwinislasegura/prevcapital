<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = Config::get('database.host');
        $port = Config::get('database.port');
        $name = Config::get('database.database');
        $charset = Config::get('database.charset', 'utf8mb4');
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        try {
            self::$connection = new PDO($dsn, Config::get('database.username'), Config::get('database.password'), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            if (Config::get('app.debug')) {
                throw $exception;
            }
            throw new RuntimeException('No fue posible conectar con la base de datos. Revise la configuración de MySQL.');
        }

        return self::$connection;
    }
}
