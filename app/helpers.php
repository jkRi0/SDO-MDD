<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function base_path(): string
{
    static $basePath = null;
    if (is_string($basePath)) {
        return $basePath;
    }

    $docRoot = str_replace('\\', '/', (string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $appRoot = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: '');

    if ($docRoot !== '' && $appRoot !== '' && str_starts_with($appRoot, $docRoot)) {
        $basePath = '/' . ltrim(substr($appRoot, strlen($docRoot)), '/');
        $basePath = rtrim($basePath, '/');
        return $basePath;
    }

    $basePath = '';
    return $basePath;
}

function url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    $base = base_path();
    if ($base === '') {
        return $path;
    }
    return $base . $path;
}

function asset(string $path): string
{
    return url($path);
}

function redirect(string $path): never
{
    $location = preg_match('/^https?:\/\//i', $path) ? $path : url($path);
    header('Location: ' . $location);
    exit;
}

function base_config(): array
{
    return require __DIR__ . '/../config/app.php';
}

function set_flash(string $key, string $value): void
{
    $_SESSION['flash'][$key] = $value;
}

function get_flash(string $key): ?string
{
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}
