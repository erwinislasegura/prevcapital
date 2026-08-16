<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\MarketingCampaign;
use RuntimeException;

final class MarketingQueue
{
    public static function processNext(): array
    {
        if (!EmailDeliverability::readyForMarketing()) {
            throw new RuntimeException('Envío detenido: configure SMTP autenticado y publique registros SPF, DKIM y DMARC válidos.');
        }
        $recipient = MarketingCampaign::acquireNext();
        if (!$recipient) {
            return ['status' => 'idle', 'message' => 'No hay correos pendientes o todavía no se cumplen los 10 minutos entre envíos.'];
        }
        $unsubscribeUrl = absolute_url('/correo/desuscribir?token=' . urlencode((string) $recipient['unsubscribe_token']));
        $html = EmailTemplate::render((string) $recipient['html_content'], $recipient, $unsubscribeUrl, true);
        $text = EmailTemplate::render((string) $recipient['text_content'], $recipient, $unsubscribeUrl, false);
        $subject = EmailTemplate::render((string) $recipient['subject'], $recipient, $unsubscribeUrl, false);
        try {
            if (!Mailer::sendMarketing((string) $recipient['email'], $subject, $html, $text, $unsubscribeUrl)) {
                throw new RuntimeException('El servidor no confirmó el envío.');
            }
            $messageId = bin2hex(random_bytes(16));
            MarketingCampaign::markSent((int) $recipient['id'], (int) $recipient['campaign_id'], $messageId);
            return ['status' => 'sent', 'message' => 'Correo enviado a ' . $recipient['email'] . '.', 'recipient' => $recipient['email']];
        } catch (\Throwable $exception) {
            MarketingCampaign::markFailed((int) $recipient['id'], (int) $recipient['campaign_id'], (int) $recipient['attempts'], $exception->getMessage());
            throw $exception;
        }
    }
}
