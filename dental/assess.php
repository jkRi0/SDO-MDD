<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/auth.php';

require_login('dental', 'dental/assess.php');

$cfg = base_config();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    redirect('/dental/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = db()->prepare('UPDATE patients SET dental_checked = 1, dental_checked_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    } catch (Throwable $e) {
    }
    redirect('/dental/index.php');
}

try {
    $stmt = db()->prepare('SELECT id, fullname, entry_date, school, level FROM patients WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $patient = null;
}

if (!$patient) {
    redirect('/dental/index.php');
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dental Assessment - <?= e($cfg['app_name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(asset('public/assets/css/styles.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">
  <header class="appbar">
    <div class="container py-3">
      <div class="row align-items-center">
        <div class="col-12 col-md">
          <div class="d-flex align-items-center gap-3">
            <img src="<?= e(asset('public/assets/sdo-logo.png')) ?>" alt="Logo" style="width: 48px; height: 48px; flex-shrink: 0;">
            <div class="overflow-hidden">
              <div class="brand h5 mb-0 text-white fw-bold lh-1">Dental Assessment</div>
              <div class="small text-white-50 lh-1 mt-1">School Health Section</div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-auto mt-2 mt-md-0">
          <div class="d-flex align-items-center gap-2 justify-content-md-end">
            <a href="<?= url('/dental/index.php') ?>" class="btn btn-light btn-sm fw-bold px-3" style="border-radius: 8px;">Back</a>
            <a href="<?= url('/auth/logout.php') ?>" class="btn btn-outline-light btn-sm px-3" style="border-radius: 8px;">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="container py-4 py-md-5" style="max-width: 980px;">
    <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
      <div class="card-body p-4">
        <div class="mb-3">
          <div class="h5 mb-1"><?= e((string)$patient['fullname']) ?></div>
          <div class="text-secondary small">
            <?= e((string)$patient['level']) ?> · <?= e((string)$patient['school']) ?> · <?= e((string)$patient['entry_date']) ?>
          </div>
        </div>

        <form method="post">
          <input type="hidden" name="id" value="<?= (int)$patient['id'] ?>">
          <div class="alert alert-info border-0" style="border-radius: 12px;">
            This is a placeholder page. You'll provide the Dental assessment form fields later.
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Mark as Dental Checked</button>
            <a class="btn btn-outline-secondary" href="<?= url('/dental/index.php') ?>">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</body>
</html>
