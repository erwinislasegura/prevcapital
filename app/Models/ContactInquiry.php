<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ContactInquiry
{
    public static function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO contact_inquiries (name, company, email, phone, worker_count, service, message, source, ip_address, user_agent)
             VALUES (:name, :company, :email, :phone, :worker_count, :service, :message, :source, :ip_address, :user_agent)'
        );
        $statement->execute($data);
        return (int) Database::connection()->lastInsertId();
    }

    public static function all(string $status = '', string $search = ''): array
    {
        $where = [];
        $params = [];
        if (in_array($status, ['new', 'contacted', 'closed'], true)) {
            $where[] = 'c.status = :status';
            $params['status'] = $status;
        }
        if ($search !== '') {
            $where[] = '(c.name LIKE :search OR c.company LIKE :search OR c.email LIKE :search OR c.service LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        $sql = 'SELECT c.*, u.name AS reviewer_name FROM contact_inquiries c LEFT JOIN users u ON u.id = c.reviewed_by';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY c.created_at DESC';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT c.*, u.name AS reviewer_name FROM contact_inquiries c LEFT JOIN users u ON u.id = c.reviewed_by WHERE c.id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public static function updateStatus(int $id, string $status, ?int $reviewedBy): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE contact_inquiries SET status = :status, reviewed_by = :reviewed_by, reviewed_at = NOW() WHERE id = :id'
        );
        $statement->execute(['status' => $status, 'reviewed_by' => $reviewedBy, 'id' => $id]);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM contact_inquiries WHERE id = :id')->execute(['id' => $id]);
    }

    public static function count(?string $status = null): int
    {
        if ($status === null) return (int) Database::connection()->query('SELECT COUNT(*) FROM contact_inquiries')->fetchColumn();
        $statement = Database::connection()->prepare('SELECT COUNT(*) FROM contact_inquiries WHERE status = :status');
        $statement->execute(['status' => $status]);
        return (int) $statement->fetchColumn();
    }

    public static function recent(int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));
        return Database::connection()->query("SELECT id, name, company, service, status, created_at FROM contact_inquiries ORDER BY created_at DESC LIMIT {$limit}")->fetchAll();
    }
}
