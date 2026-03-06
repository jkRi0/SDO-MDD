<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/auth.php';

require_login('medical', 'medical/index.php');
$cfg = base_config();

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Medical Dashboard - <?= e($cfg['app_name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(asset('public/assets/css/styles.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">
  <header class="appbar">
    <div class="container py-3">
      <div class="row align-items-center">
        <!-- Logo & Title -->
        <div class="col-12 col-md">
          <div class="d-flex align-items-center gap-3">
            <img src="<?= e(asset('public/assets/sdo-logo.png')) ?>" alt="Logo" style="width: 48px; height: 48px; flex-shrink: 0;">
            <div class="overflow-hidden">
              <div class="brand h5 mb-0 text-white fw-bold lh-1">Medical Dashboard</div>
              <div class="small text-white-50 lh-1 mt-1">School Health Section</div>
            </div>
          </div>
        </div>
        <!-- Header Actions -->
        <div class="col-12 col-md-auto mt-2 mt-md-0">
          <div class="d-flex align-items-center gap-2 justify-content-md-end">
            <div class="d-none d-lg-block text-end me-2">
              <div class="small fw-bold text-white lh-1"><?= e(current_user()['fullname']) ?></div>
              <div class="text-white-50 lh-1 mt-1" style="font-size: 0.7rem;">Medical Staff</div>
            </div>
            <a href="<?= url('/') ?>" class="btn btn-light btn-sm fw-bold px-3" style="border-radius: 8px;">Home</a>
            <a href="<?= url('/auth/logout.php') ?>" class="btn btn-outline-light btn-sm px-3" style="border-radius: 8px;">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="container py-4 py-md-5">
    <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
      <div class="card-body p-4">
        <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 12px;">
          <i class="bi bi-info-circle me-2"></i>
          Dashboard is temporary. Next: medical encounter records, search, and reports.
        </div>
        <a class="btn btn-outline-primary px-4" href="<?= e(url('/')) ?>" style="border-radius: 10px;">
          <i class="bi bi-arrow-left me-2"></i>Back to Home
        </a>
      </div>
    </div>
  </main>
</body>
</html>
