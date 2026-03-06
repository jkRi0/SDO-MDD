<?php

declare(strict_types=1);

$cfg = require __DIR__ . '/../config/app.php';

session_name($cfg['session_name']);

// Set session cookie path to the application subfolder
$docRoot = str_replace('\\', '/', (string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
$appRoot = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: '');
$basePath = '/';
if ($docRoot !== '' && $appRoot !== '' && str_starts_with($appRoot, $docRoot)) {
    $basePath = '/' . ltrim(substr($appRoot, strlen($docRoot)), '/');
    $basePath = rtrim($basePath, '/') ?: '/';
}

session_set_cookie_params([
    'path' => $basePath,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

require __DIR__ . '/helpers.php';
require __DIR__ . '/db.php';
require __DIR__ . '/db_init.php';

// Auto-initialize and seed database
try {
    $pdo = db();
    sync_database($pdo);
} catch (Throwable $e) {
    // Silently fail or log error if database connection isn't ready yet
}
