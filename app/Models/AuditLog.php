<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Database;

final class AuditLog
{
    public static function record(string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address)
             VALUES (:user_id, :action, :entity_type, :entity_id, :details, :ip_address)'
        );
        $statement->execute([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    public static function recent(int $limit = 6): array
    {
        $limit = max(1, min($limit, 20));
        $sql = "SELECT a.*, u.name AS user_name FROM audit_logs a
                LEFT JOIN users u ON u.id = a.user_id
                ORDER BY a.created_at DESC LIMIT {$limit}";
        return Database::connection()->query($sql)->fetchAll();
    }
}
