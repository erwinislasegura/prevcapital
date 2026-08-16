<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\Config;

final class EmailDeliverability
{
    public static function report(): array
    {
        $mail = (array) Config::get('app.mail', []);
        $from = (string) ($mail['from_address'] ?? '');
        $domain = filter_var($from, FILTER_VALIDATE_EMAIL) ? substr(strrchr($from, '@') ?: '', 1) : '';
        $selector = preg_replace('/[^a-z0-9_-]/i', '', (string) ($mail['dkim_selector'] ?? 'default')) ?: 'default';
        return [
            ['label' => 'SMTP autenticado', 'ok' => Mailer::smtpConfigured(), 'detail' => Mailer::smtpConfigured() ? 'Servidor, usuario y contraseña configurados.' : 'Configure MAIL_TRANSPORT=smtp y credenciales MAIL_* antes de activar campañas.'],
            ['label' => 'Remitente válido', 'ok' => $domain !== '', 'detail' => $from ?: 'MAIL_FROM_ADDRESS no configurado.'],
            ['label' => 'SPF', 'ok' => $domain !== '' && self::txtContains($domain, 'v=spf1'), 'detail' => $domain ? 'Registro TXT del dominio ' . $domain . '.' : 'No se pudo determinar el dominio.'],
            ['label' => 'DKIM', 'ok' => $domain !== '' && self::dkimExists($selector . '._domainkey.' . $domain), 'detail' => 'Selector revisado: ' . $selector . '. Configure MAIL_DKIM_SELECTOR si cPanel usa otro.'],
            ['label' => 'DMARC', 'ok' => $domain !== '' && self::txtContains('_dmarc.' . $domain, 'v=dmarc1'), 'detail' => $domain ? 'Registro TXT _dmarc.' . $domain . '.' : 'No se pudo determinar el dominio.'],
        ];
    }

    public static function readyForMarketing(): bool
    {
        if (!Mailer::smtpConfigured()) return false;
        $report = self::report();
        foreach ($report as $check) {
            if (in_array($check['label'], ['Remitente válido', 'SPF', 'DKIM', 'DMARC'], true) && !$check['ok']) return false;
        }
        return true;
    }

    private static function txtContains(string $host, string $needle): bool
    {
        if ($host === '' || !function_exists('dns_get_record')) return false;
        $records = @dns_get_record($host, DNS_TXT);
        if (!is_array($records)) return false;
        foreach ($records as $record) {
            $text = mb_strtolower((string) ($record['txt'] ?? ''));
            if (str_contains($text, mb_strtolower($needle))) return true;
        }
        return false;
    }

    private static function dkimExists(string $host): bool
    {
        return self::txtContains($host, 'v=dkim1') || (function_exists('checkdnsrr') && @checkdnsrr($host, 'CNAME'));
    }
}
