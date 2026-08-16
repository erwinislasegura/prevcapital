<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class MarketingContact
{
    public static function upsert(string $email, ?string $name, ?string $company, string $source): ?array
    {
        $email = mb_strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 180) {
            return null;
        }
        $pdo = Database::connection();
        $statement = $pdo->prepare('SELECT * FROM marketing_contacts WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $contact = $statement->fetch();
        if ($contact) {
            $pdo->prepare("UPDATE marketing_contacts SET name=COALESCE(NULLIF(:name, ''), name), company=COALESCE(NULLIF(:company, ''), company), source=:source WHERE id=:id")->execute([
                'name' => trim((string) $name), 'company' => trim((string) $company), 'source' => $source, 'id' => $contact['id'],
            ]);
            $statement->execute(['email' => $email]);
            return $statement->fetch() ?: null;
        }
        $token = bin2hex(random_bytes(32));
        $pdo->prepare("INSERT INTO marketing_contacts (email, name, company, status, source, consent_at, unsubscribe_token) VALUES (:email, :name, :company, 'subscribed', :source, NOW(), :token)")->execute([
            'email' => $email,
            'name' => trim((string) $name) ?: null,
            'company' => trim((string) $company) ?: null,
            'source' => $source,
            'token' => $token,
        ]);
        $statement->execute(['email' => $email]);
        return $statement->fetch() ?: null;
    }

    public static function sourceRows(bool $clients, bool $inquiries): array
    {
        $rows = [];
        $pdo = Database::connection();
        if ($clients) {
            foreach ($pdo->query("SELECT email, name, company FROM clients WHERE status = 1 AND email <> '' ORDER BY id")->fetchAll() as $row) {
                $row['source'] = 'Clientes';
                $rows[] = $row;
            }
        }
        if ($inquiries) {
            foreach ($pdo->query("SELECT email, name, company FROM contact_inquiries WHERE email <> '' ORDER BY id")->fetchAll() as $row) {
                $row['source'] = 'Solicitudes web';
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public static function findByToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) return null;
        $statement = Database::connection()->prepare('SELECT * FROM marketing_contacts WHERE unsubscribe_token = :token LIMIT 1');
        $statement->execute(['token' => $token]);
        return $statement->fetch() ?: null;
    }

    public static function unsubscribe(string $token): bool
    {
        $contact = self::findByToken($token);
        if (!$contact) return false;
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $campaigns = $pdo->prepare("SELECT DISTINCT campaign_id FROM marketing_campaign_recipients WHERE contact_id=:id AND status='queued'");
            $campaigns->execute(['id' => $contact['id']]);
            $campaignIds = array_map('intval', array_column($campaigns->fetchAll(), 'campaign_id'));
            $pdo->prepare("UPDATE marketing_contacts SET status='unsubscribed', unsubscribed_at=NOW() WHERE id=:id")->execute(['id' => $contact['id']]);
            $pdo->prepare("UPDATE marketing_campaign_recipients SET status='unsubscribed', updated_at=NOW() WHERE contact_id=:id AND status='queued'")->execute(['id' => $contact['id']]);
            foreach ($campaignIds as $campaignId) MarketingCampaign::refreshCampaign($campaignId);
            $pdo->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }

    public static function counts(): array
    {
        $rows = Database::connection()->query('SELECT status, COUNT(*) total FROM marketing_contacts GROUP BY status')->fetchAll();
        $counts = ['subscribed' => 0, 'unsubscribed' => 0, 'bounced' => 0];
        foreach ($rows as $row) $counts[$row['status']] = (int) $row['total'];
        return $counts;
    }
}
