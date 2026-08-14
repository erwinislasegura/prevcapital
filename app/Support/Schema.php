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

        self::upgradeExistingInstallations();
    }

    private static function upgradeExistingInstallations(): void
    {
        $pdo = Database::connection();
        if (!self::columnExists('quotes', 'client_id')) {
            $pdo->exec('ALTER TABLE quotes ADD COLUMN client_id BIGINT UNSIGNED NULL AFTER public_token');
        }
        if (!self::indexExists('quotes', 'idx_quotes_client_id')) {
            $pdo->exec('ALTER TABLE quotes ADD INDEX idx_quotes_client_id (client_id)');
        }
        if (!self::constraintExists('quotes', 'fk_quotes_client')) {
            $pdo->exec('ALTER TABLE quotes ADD CONSTRAINT fk_quotes_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL');
        }
        if (!self::columnExists('quotes', 'discount_type')) {
            $pdo->exec("ALTER TABLE quotes ADD COLUMN discount_type ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage' AFTER subtotal");
        }
        if (!self::columnExists('quotes', 'discount_value')) {
            $pdo->exec('ALTER TABLE quotes ADD COLUMN discount_value DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER discount_type');
        }
        if (!self::columnExists('quotes', 'discount_amount')) {
            $pdo->exec('ALTER TABLE quotes ADD COLUMN discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER discount_value');
        }
        if (!self::columnExists('contact_inquiries', 'worker_count')) {
            $pdo->exec('ALTER TABLE contact_inquiries ADD COLUMN worker_count INT UNSIGNED NULL AFTER phone');
        }
    }

    private static function columnExists(string $table, string $column): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column'
        );
        $statement->execute(['table' => $table, 'column' => $column]);
        return (int) $statement->fetchColumn() > 0;
    }

    private static function indexExists(string $table, string $index): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND INDEX_NAME = :index_name'
        );
        $statement->execute(['table' => $table, 'index_name' => $index]);
        return (int) $statement->fetchColumn() > 0;
    }

    private static function constraintExists(string $table, string $constraint): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = :table AND CONSTRAINT_NAME = :constraint_name'
        );
        $statement->execute(['table' => $table, 'constraint_name' => $constraint]);
        return (int) $statement->fetchColumn() > 0;
    }
}
