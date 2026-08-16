<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\Config;
use RuntimeException;

final class Mailer
{
    public static function sendQuote(array $quote, string $pdf): bool
    {
        $quoteUrl = absolute_url('/cotizacion?token=' . urlencode((string) $quote['public_token']));
        $subject = 'Cotización ' . $quote['quote_number'] . ' · PrevCapital';
        $attachments = [[
            'name' => 'Cotizacion-' . preg_replace('/[^A-Za-z0-9-]/', '-', (string) $quote['quote_number']) . '.pdf',
            'mime' => 'application/pdf',
            'content' => $pdf,
        ]];
        foreach ($quote['attachments'] ?? [] as $attachment) {
            $path = QuoteAttachmentStorage::absolutePath($attachment);
            if (is_file($path)) {
                $attachments[] = [
                    'name' => (string) $attachment['original_name'],
                    'mime' => (string) $attachment['mime_type'],
                    'path' => $path,
                ];
            }
        }
        return self::deliver(
            (string) $quote['email'],
            $subject,
            self::quoteHtml($quote, $quoteUrl),
            'Hola, ' . $quote['client_name'] . ".\n\nAdjuntamos la cotización " . $quote['quote_number'] . ' para ' . $quote['subject'] . ".\n\nRevise y responda en: " . $quoteUrl,
            $attachments
        );
    }

    public static function sendMarketing(string $to, string $subject, string $html, string $text, string $unsubscribeUrl): bool
    {
        return self::deliver($to, $subject, $html, $text, [], [
            'List-Unsubscribe' => '<' . $unsubscribeUrl . '>, <mailto:' . self::fromAddress() . '?subject=Desuscribir>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            'Precedence' => 'bulk',
        ]);
    }

    public static function sendDecisionNotification(array $quote, string $decision): bool
    {
        $mail = self::config();
        $to = (string) ($mail['notification_address'] ?? self::fromAddress());
        $label = $decision === 'accepted' ? 'aceptada' : 'rechazada';
        $subject = 'Cotización ' . $quote['quote_number'] . ' ' . $label;
        $body = '<p>La cotización <strong>' . e($quote['quote_number']) . '</strong> fue ' . $label . ' por ' . e($quote['company']) . '.</p>';
        if ($decision === 'rejected' && !empty($quote['rejection_reason'])) $body .= '<p>Motivo: ' . e($quote['rejection_reason']) . '</p>';
        return self::deliver($to, $subject, $body, strip_tags($body));
    }

    public static function smtpConfigured(): bool
    {
        $mail = self::config();
        return ($mail['transport'] ?? 'mail') === 'smtp'
            && trim((string) ($mail['host'] ?? '')) !== ''
            && trim((string) ($mail['username'] ?? '')) !== ''
            && trim((string) ($mail['password'] ?? '')) !== '';
    }

    public static function fromAddress(): string
    {
        return (string) (self::config()['from_address'] ?? 'contacto@prevcapital.cl');
    }

    private static function deliver(string $to, string $subject, string $html, string $text, array $attachments = [], array $extraHeaders = []): bool
    {
        $mail = self::config();
        $fromAddress = self::fromAddress();
        $fromName = (string) ($mail['from_name'] ?? 'PrevCapital');
        $replyTo = (string) ($mail['reply_to'] ?? $fromAddress);
        foreach ([$to, $fromAddress, $replyTo] as $address) {
            if (!filter_var($address, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $address)) {
                throw new RuntimeException('La configuración contiene una dirección de correo inválida.');
            }
        }
        $subject = self::headerValue($subject);
        $mixed = 'MIX-' . bin2hex(random_bytes(12));
        $alternative = 'ALT-' . bin2hex(random_bytes(12));
        $headers = [
            'Date' => date(DATE_RFC2822),
            'Message-ID' => '<' . bin2hex(random_bytes(16)) . '@' . self::messageDomain($fromAddress) . '>',
            'MIME-Version' => '1.0',
            'From' => self::encoded($fromName) . ' <' . $fromAddress . '>',
            'Reply-To' => $replyTo,
            'X-Mailer' => 'PrevCapital Mailer',
            'Content-Type' => 'multipart/mixed; boundary="' . $mixed . '"',
        ] + $extraHeaders;

        $body = '--' . $mixed . "\r\n";
        $body .= 'Content-Type: multipart/alternative; boundary="' . $alternative . '"' . "\r\n\r\n";
        $body .= '--' . $alternative . "\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($text)) . "\r\n";
        $body .= '--' . $alternative . "\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($html)) . "\r\n--" . $alternative . "--\r\n";
        foreach ($attachments as $attachment) {
            $name = self::attachmentName((string) ($attachment['name'] ?? 'adjunto'));
            $content = isset($attachment['content']) ? (string) $attachment['content'] : @file_get_contents((string) ($attachment['path'] ?? ''));
            if ($content === false) {
                throw new RuntimeException('No fue posible leer el adjunto ' . $name . '.');
            }
            $mime = preg_replace('/[^A-Za-z0-9.+\/-]/', '', (string) ($attachment['mime'] ?? 'application/octet-stream')) ?: 'application/octet-stream';
            $body .= '--' . $mixed . "\r\nContent-Type: {$mime}; name=\"{$name}\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"{$name}\"\r\n\r\n";
            $body .= chunk_split(base64_encode($content)) . "\r\n";
        }
        $body .= '--' . $mixed . "--\r\n";
        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = self::headerValue((string) $name) . ': ' . self::headerValue((string) $value);
        }

        if (($mail['transport'] ?? 'mail') === 'smtp') {
            $message = 'To: <' . $to . ">\r\nSubject: " . self::encoded($subject) . "\r\n" . implode("\r\n", $headerLines) . "\r\n\r\n" . $body;
            return SmtpClient::send($mail, $fromAddress, $to, $message);
        }
        return mail($to, self::encoded($subject), $body, implode("\r\n", $headerLines), '-f' . $fromAddress);
    }

    private static function quoteHtml(array $quote, string $quoteUrl): string
    {
        $discount = (float) ($quote['discount_amount'] ?? 0) > 0
            ? '<span style="display:block;margin-top:7px;color:#087a77;font-size:13px">Descuento aplicado: -' . money_clp($quote['discount_amount']) . '</span>'
            : '';
        $attachmentNotice = !empty($quote['attachments']) ? '<p style="font-size:13px;color:#60727b">Este correo también incluye ' . count($quote['attachments']) . ' documento(s) complementario(s).</p>' : '';
        return '<!doctype html><html lang="es"><body style="margin:0;background:#eef3f5;font-family:Arial,sans-serif;color:#132530">'
            . '<div style="max-width:640px;margin:24px auto;background:#fff;border-top:6px solid #00b9b5">'
            . '<div style="padding:28px 34px;background:#071826;color:#fff"><h1 style="margin:0;font-size:24px">PREVCAPITAL</h1><p style="margin:7px 0 0;color:#9cdedb">Prevención que protege su operación</p></div>'
            . '<div style="padding:34px"><p style="color:#00a8a5;font-weight:bold">COTIZACIÓN ' . e($quote['quote_number']) . '</p>'
            . '<h2 style="font-size:22px;color:#071826">Hola, ' . e($quote['client_name']) . '</h2>'
            . '<p>Adjuntamos la propuesta para <strong>' . e($quote['subject']) . '</strong>, preparada para ' . e($quote['company']) . '.</p>'
            . $attachmentNotice
            . '<div style="margin:24px 0;padding:18px;background:#eef6f6"><span>Total de la propuesta</span><strong style="display:block;margin-top:6px;font-size:25px;color:#071826">' . money_clp($quote['total']) . '</strong>' . $discount . '</div>'
            . '<p>Puede revisar la cotización completa y registrar su aceptación o rechazo en el siguiente enlace:</p>'
            . '<p style="margin:28px 0"><a href="' . e($quoteUrl) . '" style="display:inline-block;padding:14px 22px;background:#00b9b5;color:#071826;text-decoration:none;font-weight:bold">Revisar y responder cotización</a></p>'
            . '<p style="font-size:13px;color:#60727b">La propuesta es válida hasta el ' . e(date('d/m/Y', strtotime((string) $quote['valid_until']))) . '.</p></div>'
            . '<div style="padding:18px 34px;background:#071826;color:#b9cbd1;font-size:12px">contacto@prevcapital.cl · prevcapital.cl</div></div></body></html>';
    }

    private static function config(): array
    {
        return (array) Config::get('app.mail', []);
    }

    private static function encoded(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private static function headerValue(string $value): string
    {
        return trim(str_replace(["\r", "\n"], '', $value));
    }

    private static function attachmentName(string $value): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', basename($value)) ?: basename($value);
        return mb_substr(preg_replace('/[^A-Za-z0-9._ -]+/', '-', $ascii) ?: 'adjunto', 0, 180);
    }

    private static function messageDomain(string $address): string
    {
        $domain = substr(strrchr($address, '@') ?: '@prevcapital.cl', 1);
        return preg_replace('/[^a-z0-9.-]/i', '', $domain) ?: 'prevcapital.cl';
    }
}
