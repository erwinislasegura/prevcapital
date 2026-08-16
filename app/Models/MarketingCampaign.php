<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;

final class MarketingCampaign
{
    public static function all(): array
    {
        return Database::connection()->query('SELECT c.*, u.name creator_name FROM marketing_campaigns c LEFT JOIN users u ON u.id=c.created_by ORDER BY c.created_at DESC')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT c.*, u.name creator_name FROM marketing_campaigns c LEFT JOIN users u ON u.id=c.created_by WHERE c.id=:id LIMIT 1');
        $statement->execute(['id' => $id]);
        $campaign = $statement->fetch();
        if (!$campaign) return null;
        $recipients = Database::connection()->prepare('SELECT * FROM marketing_campaign_recipients WHERE campaign_id=:id ORDER BY scheduled_at, id');
        $recipients->execute(['id' => $id]);
        $campaign['recipients'] = $recipients->fetchAll();
        return $campaign;
    }

    public static function create(array $data, array $contacts, ?int $userId): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $statement = $pdo->prepare("INSERT INTO marketing_campaigns (name, subject, html_content, text_content, status, scheduled_at, interval_minutes, total_recipients, created_by, updated_by) VALUES (:name,:subject,:html_content,:text_content,'scheduled',:scheduled_at,:interval_minutes,:total_recipients,:created_by,:updated_by)");
            $statement->execute($data + ['total_recipients' => count($contacts), 'created_by' => $userId, 'updated_by' => $userId]);
            $id = (int) $pdo->lastInsertId();
            $insert = $pdo->prepare("INSERT INTO marketing_campaign_recipients (campaign_id,contact_id,email,name,company,status,scheduled_at) VALUES (:campaign_id,:contact_id,:email,:name,:company,'queued',:scheduled_at)");
            $scheduled = new DateTimeImmutable((string) $data['scheduled_at']);
            foreach (array_values($contacts) as $position => $contact) {
                $insert->execute([
                    'campaign_id' => $id,
                    'contact_id' => $contact['id'],
                    'email' => $contact['email'],
                    'name' => $contact['name'],
                    'company' => $contact['company'],
                    'scheduled_at' => $scheduled->modify('+' . ($position * (int) $data['interval_minutes']) . ' minutes')->format('Y-m-d H:i:s'),
                ]);
            }
            $pdo->commit();
            return $id;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }

    public static function setPaused(int $id, bool $paused): void
    {
        $status = $paused ? 'paused' : 'scheduled';
        Database::connection()->prepare("UPDATE marketing_campaigns SET status=:status WHERE id=:id AND status IN ('scheduled','sending','paused')")->execute(['status' => $status, 'id' => $id]);
    }

    public static function acquireNext(): ?array
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $pdo->exec('INSERT IGNORE INTO marketing_settings (id, interval_minutes) VALUES (1,10)');
            $settings = $pdo->query('SELECT * FROM marketing_settings WHERE id=1 FOR UPDATE')->fetch();
            $interval = max(10, (int) ($settings['interval_minutes'] ?? 10));
            if (!empty($settings['last_delivery_at']) && strtotime((string) $settings['last_delivery_at']) > time() - ($interval * 60)) {
                $pdo->commit();
                return null;
            }
            $pdo->exec("UPDATE marketing_campaign_recipients SET status='queued', last_error='Envío recuperado tras bloqueo interrumpido.' WHERE status='sending' AND updated_at < DATE_SUB(NOW(), INTERVAL 20 MINUTE)");
            $recipient = $pdo->query("SELECT r.*, c.subject, c.html_content, c.text_content, c.status campaign_status, mc.unsubscribe_token, mc.status contact_status
                FROM marketing_campaign_recipients r
                JOIN marketing_campaigns c ON c.id=r.campaign_id
                JOIN marketing_contacts mc ON mc.id=r.contact_id
                WHERE r.status='queued' AND r.scheduled_at<=NOW() AND c.status IN ('scheduled','sending')
                ORDER BY r.scheduled_at,r.id LIMIT 1 FOR UPDATE")->fetch();
            if (!$recipient) {
                $pdo->commit();
                return null;
            }
            if ($recipient['contact_status'] !== 'subscribed') {
                $pdo->prepare("UPDATE marketing_campaign_recipients SET status='unsubscribed',updated_at=NOW() WHERE id=:id")->execute(['id' => $recipient['id']]);
                $pdo->commit();
                return null;
            }
            $pdo->prepare("UPDATE marketing_campaign_recipients SET status='sending',attempts=attempts+1,updated_at=NOW() WHERE id=:id")->execute(['id' => $recipient['id']]);
            $pdo->prepare("UPDATE marketing_campaigns SET status='sending' WHERE id=:id")->execute(['id' => $recipient['campaign_id']]);
            $pdo->prepare('UPDATE marketing_settings SET last_delivery_at=NOW() WHERE id=1')->execute();
            $pdo->commit();
            $recipient['attempts'] = (int) $recipient['attempts'] + 1;
            return $recipient;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }

    public static function markSent(int $recipientId, int $campaignId, string $messageId): void
    {
        $pdo = Database::connection();
        $pdo->prepare("UPDATE marketing_campaign_recipients SET status='sent',sent_at=NOW(),message_id=:message_id,last_error=NULL,updated_at=NOW() WHERE id=:id")->execute(['message_id' => $messageId, 'id' => $recipientId]);
        self::refreshCampaign($campaignId);
    }

    public static function markFailed(int $recipientId, int $campaignId, int $attempts, string $error): void
    {
        $status = $attempts < 3 ? 'queued' : 'failed';
        $delay = $attempts < 3 ? ', scheduled_at=DATE_ADD(NOW(), INTERVAL 30 MINUTE)' : '';
        Database::connection()->prepare("UPDATE marketing_campaign_recipients SET status=:status,last_error=:error,updated_at=NOW(){$delay} WHERE id=:id")->execute([
            'status' => $status, 'error' => mb_substr($error, 0, 1000), 'id' => $recipientId,
        ]);
        self::refreshCampaign($campaignId);
    }

    public static function refreshCampaign(int $campaignId): void
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare("SELECT COUNT(*) total,SUM(status='sent') sent,SUM(status='failed') failed,SUM(status IN ('queued','sending')) pending FROM marketing_campaign_recipients WHERE campaign_id=:id");
        $statement->execute(['id' => $campaignId]);
        $counts = $statement->fetch();
        $current = $pdo->prepare('SELECT status FROM marketing_campaigns WHERE id=:id LIMIT 1');
        $current->execute(['id' => $campaignId]);
        $currentStatus = (string) $current->fetchColumn();
        $status = (int) $counts['pending'] === 0 ? 'completed' : ($currentStatus === 'paused' ? 'paused' : 'sending');
        $pdo->prepare("UPDATE marketing_campaigns SET status=:status,sent_count=:sent,failed_count=:failed,completed_at=IF(:completion_status='completed',NOW(),NULL) WHERE id=:id")->execute([
            'status' => $status, 'completion_status' => $status, 'sent' => (int) $counts['sent'], 'failed' => (int) $counts['failed'], 'id' => $campaignId,
        ]);
    }
}
