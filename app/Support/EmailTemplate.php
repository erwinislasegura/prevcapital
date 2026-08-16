<?php

declare(strict_types=1);

namespace App\Support;

final class EmailTemplate
{
    public static function sanitize(string $html): string
    {
        $html = preg_replace('#<(script|iframe|object|embed|form|input|button)[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('#<(script|iframe|object|embed|form|input|button)[^>]*/?>#is', '', $html) ?? '';
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? '';
        return trim($html);
    }

    public static function render(string $template, array $recipient, string $unsubscribeUrl, bool $escapeValues = true): string
    {
        $values = [
            '{{nombre}}' => trim((string) ($recipient['name'] ?? '')) ?: 'equipo',
            '{{empresa}}' => trim((string) ($recipient['company'] ?? '')),
            '{{correo}}' => trim((string) ($recipient['email'] ?? '')),
            '{{unsubscribe_url}}' => $unsubscribeUrl,
        ];
        if ($escapeValues) {
            $values = array_map(static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), $values);
        }
        return strtr($template, $values);
    }

    public static function contentWarnings(string $subject, string $html): array
    {
        $warnings = [];
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');
        if (mb_strlen($plain) < 80) $warnings[] = 'El contenido es muy breve; agregue información útil y contextual.';
        if (substr_count($html, '<a ') > 5) $warnings[] = 'Hay demasiados enlaces; mantenga solo los llamados a la acción necesarios.';
        if (substr_count($html, '<img ') > 4) $warnings[] = 'Hay muchas imágenes; priorice texto legible para evitar filtros de spam.';
        if ($subject === mb_strtoupper($subject) && preg_match('/\pL/u', $subject)) $warnings[] = 'Evite escribir todo el asunto en mayúsculas.';
        if (preg_match('/\b(gratis|urgente|gana dinero|oferta imperdible|haz clic ahora)\b/iu', $subject . ' ' . $plain)) $warnings[] = 'Revise expresiones promocionales que suelen activar filtros de spam.';
        if (!str_contains($html, '{{unsubscribe_url}}')) $warnings[] = 'La plantilla debe incluir el enlace {{unsubscribe_url}}.';
        return $warnings;
    }
}
