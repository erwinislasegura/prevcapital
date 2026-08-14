<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function base_url_path(): string
{
    $configured = (string) Config::get('app.base_url', '');
    if ($configured !== '') {
        return rtrim((string) parse_url($configured, PHP_URL_PATH), '/');
    }
    $directory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    return $directory === '/' ? '' : rtrim($directory, '/');
}

function url(string $path = ''): string
{
    $configured = (string) Config::get('app.base_url', '');
    $base = $configured !== '' ? $configured : base_url_path();
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url($path);
}

function absolute_url(string $path = ''): string
{
    $generated = url($path);
    if (preg_match('#^https?://#i', $generated)) {
        return $generated;
    }
    $https = ($_SERVER['HTTPS'] ?? '') !== '' && ($_SERVER['HTTPS'] ?? '') !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    return $scheme . '://' . $host . '/' . ltrim($generated, '/');
}

function money_clp(float|int|string $amount): string
{
    return '$' . number_format((float) $amount, 0, ',', '.');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function flash(string $key, mixed $value = null): mixed
{
    if (func_num_args() === 2) {
        $_SESSION['_flash_new'][$key] = $value;
        return $value;
    }
    return $_SESSION['_flash_old'][$key] ?? null;
}

function old(string $key, mixed $default = ''): mixed
{
    $values = flash('old') ?: [];
    return $values[$key] ?? $default;
}

function slugify(string $value): string
{
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = preg_replace('/[^a-zA-Z0-9]+/', '-', $value) ?: '';
    return trim(mb_strtolower($value), '-');
}

function selected(mixed $value, mixed $expected): string
{
    return (string) $value === (string) $expected ? 'selected' : '';
}

function checked(bool $condition): string
{
    return $condition ? 'checked' : '';
}
