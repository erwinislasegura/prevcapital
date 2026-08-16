<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class QuoteAttachment
{
    public static function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO quote_attachments (quote_id, original_name, stored_name, mime_type, file_size, created_by)
             VALUES (:quote_id, :original_name, :stored_name, :mime_type, :file_size, :created_by)'
        );
        $statement->execute($data);
        return (int) Database::connection()->lastInsertId();
    }

    public static function forQuote(int $quoteId): array
    {
        $statement = Database::connection()->prepare('SELECT * FROM quote_attachments WHERE quote_id = :quote_id ORDER BY created_at, id');
        $statement->execute(['quote_id' => $quoteId]);
        return $statement->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM quote_attachments WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM quote_attachments WHERE id = :id')->execute(['id' => $id]);
    }
}
