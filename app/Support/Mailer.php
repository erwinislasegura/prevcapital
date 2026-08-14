<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\Config;

final class Mailer
{
    public static function sendQuote(array $quote, string $pdf): bool
    {
        $mail = (array) Config::get('app.mail', []);
        $fromAddress = (string) ($mail['from_address'] ?? 'contacto@prevcapital.cl');
        $fromName = (string) ($mail['from_name'] ?? 'PrevCapital');
        $replyTo = (string) ($mail['reply_to'] ?? $fromAddress);
        $boundary = 'PC-' . bin2hex(random_bytes(12));
        $quoteUrl = absolute_url('/cotizacion?token=' . urlencode((string) $quote['public_token']));
        $subject = 'Cotización ' . $quote['quote_number'] . ' · PrevCapital';
        $html = self::quoteHtml($quote, $quoteUrl);
        $filename = 'Cotizacion-' . preg_replace('/[^A-Za-z0-9-]/', '-', (string) $quote['quote_number']) . '.pdf';
        $headers = [
            'MIME-Version: 1.0',
            'From: ' . self::encoded($fromName) . ' <' . $fromAddress . '>',
            'Reply-To: ' . $replyTo,
            'Content-Type: multipart/mixed; boundary="' . $boundary . '"',
        ];
        $body = '--' . $boundary . "\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($html)) . "\r\n";
        $body .= '--' . $boundary . "\r\n";
        $body .= 'Content-Type: application/pdf; name="' . $filename . '"' . "\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= 'Content-Disposition: attachment; filename="' . $filename . '"' . "\r\n\r\n";
        $body .= chunk_split(base64_encode($pdf)) . "\r\n--" . $boundary . "--\r\n";
        return mail((string) $quote['email'], self::encoded($subject), $body, implode("\r\n", $headers));
    }

    public static function sendDecisionNotification(array $quote, string $decision): bool
    {
        $mail = (array) Config::get('app.mail', []);
        $to = (string) ($mail['notification_address'] ?? 'contacto@prevcapital.cl');
        $from = (string) ($mail['from_address'] ?? 'contacto@prevcapital.cl');
        $label = $decision === 'accepted' ? 'aceptada' : 'rechazada';
        $subject = 'Cotización ' . $quote['quote_number'] . ' ' . $label;
        $body = '<p>La cotización <strong>' . e($quote['quote_number']) . '</strong> fue ' . $label . ' por ' . e($quote['company']) . '.</p>';
        if ($decision === 'rejected' && !empty($quote['rejection_reason'])) $body .= '<p>Motivo: ' . e($quote['rejection_reason']) . '</p>';
        $headers = ['MIME-Version: 1.0', 'Content-Type: text/html; charset=UTF-8', 'From: PrevCapital <' . $from . '>'];
        return mail($to, self::encoded($subject), $body, implode("\r\n", $headers));
    }

    private static function quoteHtml(array $quote, string $quoteUrl): string
    {
        $discount = (float) ($quote['discount_amount'] ?? 0) > 0
            ? '<span style="display:block;margin-top:7px;color:#087a77;font-size:13px">Descuento aplicado: -' . money_clp($quote['discount_amount']) . '</span>'
            : '';
        return '<!doctype html><html lang="es"><body style="margin:0;background:#eef3f5;font-family:Arial,sans-serif;color:#132530">'
            . '<div style="max-width:640px;margin:24px auto;background:#fff;border-top:6px solid #00b9b5">'
            . '<div style="padding:28px 34px;background:#071826;color:#fff"><h1 style="margin:0;font-size:24px">PREVCAPITAL</h1><p style="margin:7px 0 0;color:#9cdedb">Prevención que protege su operación</p></div>'
            . '<div style="padding:34px"><p style="color:#00a8a5;font-weight:bold">COTIZACIÓN ' . e($quote['quote_number']) . '</p>'
            . '<h2 style="font-size:22px;color:#071826">Hola, ' . e($quote['client_name']) . '</h2>'
            . '<p>Adjuntamos la propuesta para <strong>' . e($quote['subject']) . '</strong>, preparada para ' . e($quote['company']) . '.</p>'
            . '<div style="margin:24px 0;padding:18px;background:#eef6f6"><span>Total de la propuesta</span><strong style="display:block;margin-top:6px;font-size:25px;color:#071826">' . money_clp($quote['total']) . '</strong>' . $discount . '</div>'
            . '<p>Puede revisar la cotización completa y registrar su aceptación o rechazo en el siguiente enlace:</p>'
            . '<p style="margin:28px 0"><a href="' . e($quoteUrl) . '" style="display:inline-block;padding:14px 22px;background:#00b9b5;color:#071826;text-decoration:none;font-weight:bold">Revisar y responder cotización</a></p>'
            . '<p style="font-size:13px;color:#60727b">La propuesta es válida hasta el ' . e(date('d/m/Y', strtotime((string) $quote['valid_until']))) . '.</p></div>'
            . '<div style="padding:18px 34px;background:#071826;color:#b9cbd1;font-size:12px">contacto@prevcapital.cl · prevcapital.cl</div></div></body></html>';
    }

    private static function encoded(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }
}
