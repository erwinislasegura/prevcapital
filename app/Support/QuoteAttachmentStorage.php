<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\QuoteAttachment;
use RuntimeException;

final class QuoteAttachmentStorage
{
    public const MAX_FILES = 5;
    public const MAX_FILE_BYTES = 8 * 1024 * 1024;
    public const MAX_TOTAL_BYTES = 15 * 1024 * 1024;

    private const TYPES = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword', 'application/x-ole-storage', 'application/CDFV2', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
    ];

    public static function validate(array $fileBag, int $existingCount = 0): array
    {
        $files = self::normalize($fileBag);
        if (!$files) {
            return [];
        }
        $errors = [];
        if ($existingCount + count($files) > self::MAX_FILES) {
            $errors[] = 'Puede adjuntar como máximo ' . self::MAX_FILES . ' archivos por cotización.';
        }
        $total = 0;
        foreach ($files as $file) {
            if ((int) $file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'No fue posible cargar ' . self::safeName((string) $file['name']) . '.';
                continue;
            }
            $size = (int) $file['size'];
            $total += $size;
            if ($size < 1 || $size > self::MAX_FILE_BYTES) {
                $errors[] = self::safeName((string) $file['name']) . ' debe pesar menos de 8 MB.';
                continue;
            }
            $extension = mb_strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
            $mime = self::detectedMime((string) $file['tmp_name']);
            if (!isset(self::TYPES[$extension]) || !in_array($mime, self::TYPES[$extension], true)) {
                $errors[] = self::safeName((string) $file['name']) . ' no es un PDF, Word o imagen JPG, PNG o WEBP válida.';
            }
        }
        if ($total > self::MAX_TOTAL_BYTES) {
            $errors[] = 'El total de adjuntos no puede superar 15 MB para asegurar la entrega del correo.';
        }
        return array_values(array_unique($errors));
    }

    public static function store(int $quoteId, array $fileBag, ?int $userId): array
    {
        $stored = [];
        $directory = self::directory($quoteId);
        foreach (self::normalize($fileBag) as $file) {
            if ((int) $file['error'] !== UPLOAD_ERR_OK) {
                continue;
            }
            if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
                throw new RuntimeException('No fue posible crear la carpeta privada de adjuntos.');
            }
            $extension = mb_strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
            $storedName = bin2hex(random_bytes(20)) . '.' . $extension;
            $destination = $directory . '/' . $storedName;
            if (!move_uploaded_file((string) $file['tmp_name'], $destination)) {
                throw new RuntimeException('No fue posible guardar el adjunto.');
            }
            @chmod($destination, 0640);
            $record = [
                'quote_id' => $quoteId,
                'original_name' => self::safeName((string) $file['name']),
                'stored_name' => $storedName,
                'mime_type' => self::detectedMime($destination),
                'file_size' => (int) $file['size'],
                'created_by' => $userId,
            ];
            try {
                $record['id'] = QuoteAttachment::create($record);
                $stored[] = $record;
            } catch (\Throwable $exception) {
                @unlink($destination);
                throw $exception;
            }
        }
        return $stored;
    }

    public static function absolutePath(array $attachment): string
    {
        return self::directory((int) $attachment['quote_id']) . '/' . basename((string) $attachment['stored_name']);
    }

    public static function delete(array $attachment): void
    {
        $path = self::absolutePath($attachment);
        if (is_file($path)) {
            @unlink($path);
        }
        QuoteAttachment::delete((int) $attachment['id']);
    }

    public static function deleteQuoteFiles(array $attachments): void
    {
        foreach ($attachments as $attachment) {
            $path = self::absolutePath($attachment);
            if (is_file($path)) {
                @unlink($path);
            }
        }
        if ($attachments) {
            $directory = self::directory((int) $attachments[0]['quote_id']);
            if (is_dir($directory)) {
                @rmdir($directory);
            }
        }
    }

    private static function normalize(array $fileBag): array
    {
        if (!isset($fileBag['name'])) {
            return [];
        }
        $names = is_array($fileBag['name']) ? $fileBag['name'] : [$fileBag['name']];
        $files = [];
        foreach ($names as $index => $name) {
            $error = is_array($fileBag['error'] ?? null) ? ($fileBag['error'][$index] ?? UPLOAD_ERR_NO_FILE) : ($fileBag['error'] ?? UPLOAD_ERR_NO_FILE);
            if ((int) $error === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $files[] = [
                'name' => $name,
                'tmp_name' => is_array($fileBag['tmp_name'] ?? null) ? ($fileBag['tmp_name'][$index] ?? '') : ($fileBag['tmp_name'] ?? ''),
                'size' => is_array($fileBag['size'] ?? null) ? ($fileBag['size'][$index] ?? 0) : ($fileBag['size'] ?? 0),
                'error' => $error,
            ];
        }
        return $files;
    }

    private static function detectedMime(string $path): string
    {
        if ($path === '' || !is_file($path)) {
            return 'application/octet-stream';
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return (string) ($finfo->file($path) ?: 'application/octet-stream');
    }

    private static function safeName(string $name): string
    {
        $name = preg_replace('/[^\pL\pN._ -]+/u', '-', basename($name)) ?: 'adjunto';
        return mb_substr(trim($name), 0, 180);
    }

    private static function directory(int $quoteId): string
    {
        return APP_ROOT . '/storage/quote_attachments/' . max(1, $quoteId);
    }
}
