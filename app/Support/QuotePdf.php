<?php

declare(strict_types=1);

namespace App\Support;

final class QuotePdf
{
    private const WIDTH = 595.28;
    private const HEIGHT = 841.89;
    private const NAVY = [0.027, 0.094, 0.149];
    private const TEAL = [0.000, 0.725, 0.710];
    private const TEXT = [0.075, 0.145, 0.188];
    private const MUTED = [0.380, 0.450, 0.490];
    private const LIGHT = [0.945, 0.965, 0.970];

    public static function render(array $quote): string
    {
        $items = array_values($quote['items'] ?? []);
        $chunks = [array_splice($items, 0, 7)];
        foreach (array_chunk($items, 9) as $chunk) $chunks[] = $chunk;
        if ($chunks[0] === [] && count($chunks) > 1) array_shift($chunks);
        $pages = [];
        $totalPages = count($chunks);
        foreach ($chunks as $index => $chunk) {
            $pages[] = self::page($quote, $chunk, $index + 1, $totalPages, $index === 0);
        }
        return self::document($pages);
    }

    private static function page(array $quote, array $items, int $page, int $totalPages, bool $first): string
    {
        $out = '';
        self::rect($out, 0, 742, self::WIDTH, 100, self::NAVY);
        self::rect($out, 0, 738, self::WIDTH, 4, self::TEAL);
        $out .= "q 130 0 0 48 38 768 cm /Im1 Do Q\n";
        self::text($out, 552, 805, 'COTIZACIÓN', 10, true, [1, 1, 1], 'right');
        self::text($out, 552, 782, (string) ($quote['quote_number'] ?? ''), 18, true, [1, 1, 1], 'right');
        self::text($out, 552, 762, 'Prevención que protege la continuidad operacional', 8.5, false, [0.75, 0.86, 0.88], 'right');

        $y = 710;
        if ($first) {
            self::text($out, 38, $y, self::truncate((string) ($quote['subject'] ?? 'Propuesta de servicios'), 54), 18, true, self::NAVY);
            $y -= 30;
            self::rect($out, 38, $y - 72, 519, 76, self::LIGHT);
            self::text($out, 52, $y - 12, 'CLIENTE', 8, true, self::TEAL);
            self::text($out, 52, $y - 31, self::truncate((string) ($quote['company'] ?? ''), 42), 12, true, self::TEXT);
            self::text($out, 52, $y - 49, (string) ($quote['client_name'] ?? ''), 9, false, self::MUTED);
            self::text($out, 52, $y - 64, (string) ($quote['email'] ?? ''), 8.5, false, self::MUTED);
            self::text($out, 368, $y - 12, 'EMISIÓN', 8, true, self::TEAL);
            self::text($out, 368, $y - 31, self::date((string) ($quote['issue_date'] ?? '')), 9.5, true, self::TEXT);
            self::text($out, 458, $y - 12, 'VÁLIDA HASTA', 8, true, self::TEAL);
            self::text($out, 458, $y - 31, self::date((string) ($quote['valid_until'] ?? '')), 9.5, true, self::TEXT);
            if (!empty($quote['tax_id'])) self::text($out, 368, $y - 53, 'RUT: ' . $quote['tax_id'], 8.5, false, self::MUTED);
            $y -= 105;
        } else {
            self::text($out, 38, $y, self::truncate('Continuación · ' . (string) ($quote['subject'] ?? ''), 72), 13, true, self::NAVY);
            $y -= 35;
        }

        self::rect($out, 38, $y - 26, 519, 28, self::TEAL);
        self::text($out, 50, $y - 16, 'SERVICIO / DESCRIPCIÓN', 8, true, [1, 1, 1]);
        self::text($out, 407, $y - 16, 'CANT.', 8, true, [1, 1, 1], 'right');
        self::text($out, 479, $y - 16, 'VALOR', 8, true, [1, 1, 1], 'right');
        self::text($out, 547, $y - 16, 'TOTAL', 8, true, [1, 1, 1], 'right');
        $y -= 31;

        foreach ($items as $position => $item) {
            $height = empty($item['detail']) ? 43 : 56;
            if ($position % 2 === 1) self::rect($out, 38, $y - $height + 5, 519, $height, [0.975, 0.982, 0.985]);
            self::text($out, 50, $y - 10, self::truncate((string) ($item['description'] ?? ''), 57), 9.2, true, self::TEXT);
            if (!empty($item['detail'])) {
                $lines = self::wrap((string) $item['detail'], 63, 2);
                foreach ($lines as $lineIndex => $line) self::text($out, 50, $y - 26 - ($lineIndex * 11), $line, 7.8, false, self::MUTED);
            }
            self::text($out, 407, $y - 10, self::quantity($item['quantity'] ?? 1), 8.8, false, self::TEXT, 'right');
            self::text($out, 479, $y - 10, self::money($item['unit_price'] ?? 0), 8.8, false, self::TEXT, 'right');
            self::text($out, 547, $y - 10, self::money($item['total'] ?? 0), 8.8, true, self::TEXT, 'right');
            self::line($out, 38, $y - $height + 5, 557, $y - $height + 5, [0.86, 0.89, 0.90]);
            $y -= $height;
        }

        if ($page === $totalPages) {
            $y -= 8;
            $totalsY = max(190, $y - 10);
            self::rect($out, 357, $totalsY - 88, 200, 98, self::LIGHT);
            self::text($out, 373, $totalsY - 12, 'Subtotal', 9, false, self::MUTED);
            self::text($out, 541, $totalsY - 12, self::money($quote['subtotal'] ?? 0), 9, true, self::TEXT, 'right');
            self::text($out, 373, $totalsY - 35, 'IVA ' . self::quantity($quote['tax_rate'] ?? 19) . '%', 9, false, self::MUTED);
            self::text($out, 541, $totalsY - 35, self::money($quote['tax_amount'] ?? 0), 9, true, self::TEXT, 'right');
            self::line($out, 373, $totalsY - 51, 541, $totalsY - 51, self::TEAL, 1.2);
            self::text($out, 373, $totalsY - 75, 'TOTAL', 11, true, self::NAVY);
            self::text($out, 541, $totalsY - 75, self::money($quote['total'] ?? 0), 14, true, self::NAVY, 'right');

            $notes = trim((string) ($quote['notes'] ?? ''));
            $terms = trim((string) ($quote['terms'] ?? ''));
            $leftY = $totalsY;
            if ($notes !== '') {
                self::text($out, 38, $leftY, 'OBSERVACIONES', 8, true, self::TEAL);
                foreach (self::wrap($notes, 65, 4) as $i => $line) self::text($out, 38, $leftY - 15 - ($i * 11), $line, 7.8, false, self::MUTED);
                $leftY -= 65;
            }
            if ($terms !== '') {
                self::text($out, 38, $leftY, 'CONDICIONES', 8, true, self::TEAL);
                foreach (self::wrap($terms, 65, 4) as $i => $line) self::text($out, 38, $leftY - 15 - ($i * 11), $line, 7.8, false, self::MUTED);
            }
        }

        self::rect($out, 0, 0, self::WIDTH, 54, self::NAVY);
        self::text($out, 38, 32, 'PREVCAPITAL', 8.5, true, self::TEAL);
        self::text($out, 38, 17, 'contacto@prevcapital.cl  ·  prevcapital.cl', 7.8, false, [0.78, 0.86, 0.88]);
        self::text($out, 557, 24, 'Página ' . $page . ' de ' . $totalPages, 7.8, false, [0.78, 0.86, 0.88], 'right');
        return $out;
    }

    private static function document(array $pages): string
    {
        $objects = [];
        $objects[1] = '';
        $objects[2] = '';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $logoPath = APP_ROOT . '/assets/images/logo-prevcapital-pdf.jpg';
        $logo = is_file($logoPath) ? (string) file_get_contents($logoPath) : '';
        $objects[5] = "<< /Type /XObject /Subtype /Image /Width 304 /Height 113 /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($logo) . " >>\nstream\n" . $logo . "\nendstream";
        $pageIds = [];
        foreach ($pages as $content) {
            $contentId = count($objects) + 1;
            $objects[$contentId] = '<< /Length ' . strlen($content) . ">>\nstream\n" . $content . "endstream";
            $pageId = count($objects) + 1;
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . self::WIDTH . ' ' . self::HEIGHT . '] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> /XObject << /Im1 5 0 R >> >> /Contents ' . $contentId . ' 0 R >>';
            $pageIds[] = $pageId;
        }
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $kids = implode(' ', array_map(static fn (int $id): string => $id . ' 0 R', $pageIds));
        $objects[2] = '<< /Type /Pages /Kids [' . $kids . '] /Count ' . count($pageIds) . ' >>';
        ksort($objects);
        $pdf = "%PDF-1.4\n%âãÏÓ\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $object . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= 'xref' . "\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach (array_keys($objects) as $id) $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        $pdf .= 'trailer << /Size ' . (count($objects) + 1) . ' /Root 1 0 R >>' . "\nstartxref\n" . $xref . "\n%%EOF";
        return $pdf;
    }

    private static function text(string &$out, float $x, float $y, string $text, float $size, bool $bold, array $color, string $align = 'left'): void
    {
        $encoded = self::encode($text);
        if ($align === 'right') $x -= strlen($encoded) * $size * 0.51;
        $out .= sprintf("BT /%s %.2F Tf %.3F %.3F %.3F rg 1 0 0 1 %.2F %.2F Tm (%s) Tj ET\n", $bold ? 'F2' : 'F1', $size, $color[0], $color[1], $color[2], $x, $y, self::escape($encoded));
    }

    private static function rect(string &$out, float $x, float $y, float $w, float $h, array $color): void
    {
        $out .= sprintf("%.3F %.3F %.3F rg %.2F %.2F %.2F %.2F re f\n", $color[0], $color[1], $color[2], $x, $y, $w, $h);
    }

    private static function line(string &$out, float $x1, float $y1, float $x2, float $y2, array $color, float $width = 0.5): void
    {
        $out .= sprintf("%.3F %.3F %.3F RG %.2F w %.2F %.2F m %.2F %.2F l S\n", $color[0], $color[1], $color[2], $width, $x1, $y1, $x2, $y2);
    }

    private static function encode(string $text): string
    {
        $text = str_replace(["\r", "\n"], ' ', $text);
        return iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text) ?: $text;
    }

    private static function escape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private static function wrap(string $text, int $width, int $max): array
    {
        $lines = explode("\n", wordwrap(trim(preg_replace('/\s+/', ' ', $text) ?: ''), $width, "\n", true));
        if (count($lines) > $max) {
            $lines = array_slice($lines, 0, $max);
            $lines[$max - 1] = rtrim($lines[$max - 1], ' .') . '…';
        }
        return $lines;
    }

    private static function money(float|int|string $amount): string
    {
        return '$' . number_format((float) $amount, 0, ',', '.');
    }

    private static function truncate(string $value, int $length): string
    {
        return mb_strlen($value) <= $length ? $value : rtrim(mb_substr($value, 0, $length - 1)) . '…';
    }

    private static function quantity(float|int|string $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
    }

    private static function date(string $value): string
    {
        $timestamp = strtotime($value);
        return $timestamp ? date('d/m/Y', $timestamp) : $value;
    }
}
