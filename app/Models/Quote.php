<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Quote
{
    public static function all(string $status = '', string $search = ''): array
    {
        $where = [];
        $params = [];
        if (in_array($status, ['draft', 'sent', 'accepted', 'rejected'], true)) {
            $where[] = 'q.status = :status';
            $params['status'] = $status;
        }
        if ($search !== '') {
            $where[] = '(q.quote_number LIKE :search OR q.client_name LIKE :search OR q.company LIKE :search OR q.email LIKE :search OR q.subject LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        $sql = 'SELECT q.*, u.name AS creator_name FROM quotes q LEFT JOIN users u ON u.id = q.created_by';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY q.created_at DESC';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT q.*, u.name AS creator_name FROM quotes q LEFT JOIN users u ON u.id = q.created_by WHERE q.id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $quote = $statement->fetch();
        return $quote ? self::withRelations($quote) : null;
    }

    public static function findByToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) return null;
        $statement = Database::connection()->prepare('SELECT q.*, u.name AS creator_name FROM quotes q LEFT JOIN users u ON u.id = q.created_by WHERE q.public_token = :token LIMIT 1');
        $statement->execute(['token' => $token]);
        $quote = $statement->fetch();
        return $quote ? self::withRelations($quote) : null;
    }

    private static function withRelations(array $quote): array
    {
        $items = Database::connection()->prepare('SELECT * FROM quote_items WHERE quote_id = :id ORDER BY sort_order, id');
        $items->execute(['id' => $quote['id']]);
        $events = Database::connection()->prepare('SELECT * FROM quote_events WHERE quote_id = :id ORDER BY created_at DESC, id DESC');
        $events->execute(['id' => $quote['id']]);
        $quote['items'] = $items->fetchAll();
        $quote['events'] = $events->fetchAll();
        return $quote;
    }

    public static function create(array $data, array $items, ?int $userId): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $token = bin2hex(random_bytes(32));
            $data += ['public_token' => $token, 'created_by' => $userId, 'updated_by' => $userId];
            $statement = $pdo->prepare(
                'INSERT INTO quotes (quote_number, public_token, client_id, client_name, company, email, phone, tax_id, address, subject, issue_date, valid_until, currency, subtotal, tax_rate, tax_amount, total, notes, terms, created_by, updated_by)
                 VALUES (:quote_number, :public_token, :client_id, :client_name, :company, :email, :phone, :tax_id, :address, :subject, :issue_date, :valid_until, :currency, :subtotal, :tax_rate, :tax_amount, :total, :notes, :terms, :created_by, :updated_by)'
            );
            $data['quote_number'] = 'TMP-' . substr($token, 0, 12);
            $statement->execute($data);
            $id = (int) $pdo->lastInsertId();
            $number = 'PC-' . date('Y') . '-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
            $pdo->prepare('UPDATE quotes SET quote_number = :number WHERE id = :id')->execute(['number' => $number, 'id' => $id]);
            self::syncItems($pdo, $id, $items);
            self::addEventWithConnection($pdo, $id, 'created', 'Cotización creada en el panel.');
            $pdo->commit();
            return $id;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }

    public static function update(int $id, array $data, array $items, ?int $userId): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $data += ['updated_by' => $userId, 'id' => $id];
            $statement = $pdo->prepare(
                'UPDATE quotes SET client_id=:client_id, client_name=:client_name, company=:company, email=:email, phone=:phone, tax_id=:tax_id, address=:address, subject=:subject, issue_date=:issue_date, valid_until=:valid_until, currency=:currency, subtotal=:subtotal, tax_rate=:tax_rate, tax_amount=:tax_amount, total=:total, notes=:notes, terms=:terms, updated_by=:updated_by WHERE id=:id'
            );
            $statement->execute($data);
            self::syncItems($pdo, $id, $items);
            self::addEventWithConnection($pdo, $id, 'updated', 'Cotización actualizada en el panel.');
            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }

    private static function syncItems(\PDO $pdo, int $quoteId, array $items): void
    {
        $pdo->prepare('DELETE FROM quote_items WHERE quote_id = :id')->execute(['id' => $quoteId]);
        $insert = $pdo->prepare('INSERT INTO quote_items (quote_id, description, detail, quantity, unit_price, total, sort_order) VALUES (:quote_id, :description, :detail, :quantity, :unit_price, :total, :sort_order)');
        foreach ($items as $position => $item) {
            $insert->execute($item + ['quote_id' => $quoteId, 'sort_order' => $position]);
        }
    }

    public static function markSent(int $id): void
    {
        Database::connection()->prepare("UPDATE quotes SET status = 'sent', sent_at = NOW() WHERE id = :id AND status NOT IN ('accepted','rejected')")->execute(['id' => $id]);
        self::addEvent($id, 'sent', 'Cotización enviada por correo electrónico.');
    }

    public static function attachClient(int $quoteId, int $clientId, ?int $userId): void
    {
        Database::connection()->prepare('UPDATE quotes SET client_id = :client_id, updated_by = :updated_by WHERE id = :id')->execute([
            'client_id' => $clientId,
            'updated_by' => $userId,
            'id' => $quoteId,
        ]);
        self::addEvent($quoteId, 'client_attached', 'Datos vinculados a la cartera de clientes.');
    }

    public static function respond(int $id, string $decision, ?string $reason): bool
    {
        if (!in_array($decision, ['accepted', 'rejected'], true)) return false;
        $pdo = Database::connection();
        $field = $decision === 'accepted' ? 'accepted_at' : 'rejected_at';
        $statement = $pdo->prepare("UPDATE quotes SET status = :status, {$field} = NOW(), rejection_reason = :reason WHERE id = :id AND status = 'sent'");
        $statement->execute(['status' => $decision, 'reason' => $decision === 'rejected' ? $reason : null, 'id' => $id]);
        if ($statement->rowCount() < 1) return false;
        self::addEvent($id, $decision, $decision === 'accepted' ? 'Cotización aceptada por el cliente.' : 'Cotización rechazada por el cliente.' . ($reason ? ' Motivo: ' . $reason : ''));
        return true;
    }

    public static function addEvent(int $quoteId, string $type, ?string $details = null): void
    {
        self::addEventWithConnection(Database::connection(), $quoteId, $type, $details);
    }

    private static function addEventWithConnection(\PDO $pdo, int $quoteId, string $type, ?string $details): void
    {
        $statement = $pdo->prepare('INSERT INTO quote_events (quote_id, event_type, details, ip_address, user_agent) VALUES (:quote_id, :event_type, :details, :ip_address, :user_agent)');
        $statement->execute([
            'quote_id' => $quoteId,
            'event_type' => $type,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM quotes WHERE id = :id')->execute(['id' => $id]);
    }

    public static function count(?string $status = null): int
    {
        if ($status === null) return (int) Database::connection()->query('SELECT COUNT(*) FROM quotes')->fetchColumn();
        $statement = Database::connection()->prepare('SELECT COUNT(*) FROM quotes WHERE status = :status');
        $statement->execute(['status' => $status]);
        return (int) $statement->fetchColumn();
    }

    public static function recent(int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));
        return Database::connection()->query("SELECT id, quote_number, company, total, status, created_at FROM quotes ORDER BY created_at DESC LIMIT {$limit}")->fetchAll();
    }
}
