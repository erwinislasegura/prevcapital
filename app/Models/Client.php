<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Client
{
    public static function all(string $search = '', ?bool $active = null): array
    {
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(c.name LIKE :search OR c.company LIKE :search OR c.email LIKE :search OR c.tax_id LIKE :search OR c.phone LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        if ($active !== null) {
            $where[] = 'c.status = :status';
            $params['status'] = $active ? 1 : 0;
        }
        $sql = 'SELECT c.*, COUNT(q.id) AS quote_count, COALESCE(SUM(q.total), 0) AS quoted_total
                FROM clients c LEFT JOIN quotes q ON q.client_id = c.id';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' GROUP BY c.id ORDER BY c.company, c.name';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public static function active(): array
    {
        return self::all('', true);
    }

    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT c.*, COUNT(q.id) AS quote_count, COALESCE(SUM(q.total), 0) AS quoted_total
             FROM clients c LEFT JOIN quotes q ON q.client_id = c.id WHERE c.id = :id GROUP BY c.id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public static function findMatching(string $email, string $company): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM clients WHERE LOWER(email) = :email AND LOWER(company) = :company LIMIT 1');
        $statement->execute(['email' => mb_strtolower(trim($email)), 'company' => mb_strtolower(trim($company))]);
        return $statement->fetch() ?: null;
    }

    public static function duplicateExists(string $email, string $company, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM clients WHERE LOWER(email) = :email AND LOWER(company) = :company';
        $params = ['email' => mb_strtolower(trim($email)), 'company' => mb_strtolower(trim($company))];
        if ($exceptId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $exceptId;
        }
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn() > 0;
    }

    public static function create(array $data, ?int $userId): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO clients (name, company, email, phone, tax_id, address, notes, status, created_by, updated_by)
             VALUES (:name, :company, :email, :phone, :tax_id, :address, :notes, :status, :created_by, :updated_by)'
        );
        $statement->execute($data + ['created_by' => $userId, 'updated_by' => $userId]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data, ?int $userId): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE clients SET name=:name, company=:company, email=:email, phone=:phone, tax_id=:tax_id, address=:address, notes=:notes, status=:status, updated_by=:updated_by WHERE id=:id'
        );
        $statement->execute($data + ['updated_by' => $userId, 'id' => $id]);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM clients WHERE id = :id')->execute(['id' => $id]);
    }

    public static function count(?bool $active = null): int
    {
        if ($active === null) return (int) Database::connection()->query('SELECT COUNT(*) FROM clients')->fetchColumn();
        $statement = Database::connection()->prepare('SELECT COUNT(*) FROM clients WHERE status = :status');
        $statement->execute(['status' => $active ? 1 : 0]);
        return (int) $statement->fetchColumn();
    }
}
