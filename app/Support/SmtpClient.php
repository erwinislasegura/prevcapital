<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class SmtpClient
{
    public static function send(array $config, string $from, string $to, string $message): bool
    {
        $host = trim((string) ($config['host'] ?? ''));
        $port = (int) ($config['port'] ?? 587);
        $encryption = mb_strtolower(trim((string) ($config['encryption'] ?? 'tls')));
        $timeout = max(5, min((int) ($config['timeout'] ?? 20), 60));
        if ($host === '') {
            throw new RuntimeException('MAIL_HOST no está configurado.');
        }
        $transport = $encryption === 'ssl' ? 'ssl' : 'tcp';
        $context = stream_context_create(['ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'peer_name' => $host,
        ]]);
        $socket = @stream_socket_client("{$transport}://{$host}:{$port}", $errorNumber, $errorMessage, $timeout, STREAM_CLIENT_CONNECT, $context);
        if (!is_resource($socket)) {
            throw new RuntimeException('No fue posible conectar con el servidor SMTP: ' . $errorMessage);
        }
        stream_set_timeout($socket, $timeout);
        try {
            self::expect($socket, [220]);
            $hostname = preg_replace('/[^a-z0-9.-]/i', '', (string) ($_SERVER['SERVER_NAME'] ?? 'prevcapital.cl')) ?: 'prevcapital.cl';
            self::command($socket, 'EHLO ' . $hostname, [250]);
            if ($encryption === 'tls') {
                self::command($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('El servidor SMTP no pudo iniciar una conexión TLS segura.');
                }
                self::command($socket, 'EHLO ' . $hostname, [250]);
            }
            $username = (string) ($config['username'] ?? '');
            $password = (string) ($config['password'] ?? '');
            if ($username !== '') {
                self::command($socket, 'AUTH LOGIN', [334]);
                self::command($socket, base64_encode($username), [334]);
                self::command($socket, base64_encode($password), [235]);
            }
            self::command($socket, 'MAIL FROM:<' . $from . '>', [250]);
            self::command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            self::command($socket, 'DATA', [354]);
            $payload = preg_replace('/(?m)^\./', '..', str_replace(["\r\n", "\r"], "\n", $message)) ?? $message;
            fwrite($socket, str_replace("\n", "\r\n", $payload) . "\r\n.\r\n");
            self::expect($socket, [250]);
            fwrite($socket, "QUIT\r\n");
            return true;
        } finally {
            fclose($socket);
        }
    }

    private static function command($socket, string $command, array $expected): void
    {
        fwrite($socket, $command . "\r\n");
        self::expect($socket, $expected);
    }

    private static function expect($socket, array $expected): void
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $expected, true)) {
            $safe = preg_replace('/\s+/', ' ', trim($response)) ?: 'respuesta desconocida';
            throw new RuntimeException('SMTP rechazó la operación (' . $code . '): ' . mb_substr($safe, 0, 300));
        }
    }
}
