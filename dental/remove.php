<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/auth.php';

require_login('dental', 'dental/index.php');

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
if ($id > 0) {
    try {
        $stmt = db()->prepare('DELETE FROM patients WHERE id = ?');
        $stmt->execute([$id]);
    } catch (Throwable $e) {
    }
}

redirect('/dental/index.php');
