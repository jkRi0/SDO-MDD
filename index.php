<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/auth.php';

$cfg = base_config();
$user = current_user();

$flashSuccess = get_flash('success');
$flashError = get_flash('error');

$logoFsPath = __DIR__ . '/public/assets/sdo-logo.png';
$hasLogo = is_file($logoFsPath);

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($cfg['app_name']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= e(asset('public/assets/css/styles.css')) ?>" rel="stylesheet">
</head>
<body>
  <header class="appbar">
    <div class="container py-3">
      <div class="row align-items-center">
        <!-- Logo & Title -->
        <div class="col-12 col-md">
          <div class="d-flex align-items-center gap-3">
            <?php if ($hasLogo): ?>
              <img src="<?= e(asset('public/assets/sdo-logo.png')) ?>" alt="Logo" style="width: 48px; height: 48px; flex-shrink: 0;">
            <?php endif; ?>
            <div class="overflow-hidden">
              <div class="brand h5 mb-0 text-white fw-bold lh-1"><?= e($cfg['app_name']) ?></div>
              <div class="small text-white-50 lh-1 mt-1">School Health Section</div>
            </div>
          </div>
        </div>

        <!-- Header Actions -->
        <div class="col-12 col-md-auto mt-2 mt-md-0">
          <div class="d-flex align-items-center gap-2 justify-content-md-end">
            <?php if ($user): ?>
              <div class="d-none d-lg-block text-end me-2">
                <div class="small fw-bold text-white lh-1"><?= e($user['fullname']) ?></div>
                <div class="text-white-50 lh-1 mt-1" style="font-size: 0.7rem;"><?= ucfirst($user['role']) ?></div>
              </div>
              <?php if ($user['role'] === 'admin'): ?>
                <a href="<?= url('/admin/index.php') ?>" class="btn btn-light btn-sm fw-bold px-3" style="border-radius: 8px;">Admin</a>
              <?php endif; ?>
              <a href="<?= url('/auth/logout.php') ?>" class="btn btn-outline-light btn-sm px-3" style="border-radius: 8px;">Logout</a>
            <?php else: ?>
              <a href="<?= url('/auth/login.php') ?>" class="btn btn-light btn-sm fw-bold px-4" style="border-radius: 8px;">Admin Login</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="container page py-4 py-md-5">

    <?php if ($flashSuccess || $flashError): ?>
      <div class="toast-stack-top-center">
        <?php if ($flashSuccess): ?>
          <div class="toast toast-flash toast-flash--success" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2200">
            <div class="d-flex">
              <div class="toast-body">
                <div class="toast-flash__row">
                  <i class="bi bi-check-circle-fill toast-flash__icon" aria-hidden="true"></i>
                  <div class="toast-flash__text">
                    <div class="toast-flash__title">Success:</div>
                    <div class="toast-flash__message"><?= e($flashSuccess) ?></div>
                  </div>
                </div>
              </div>
              <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
          <div class="toast toast-flash toast-flash--error" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
            <div class="d-flex">
              <div class="toast-body">
                <div class="toast-flash__row">
                  <i class="bi bi-slash-circle-fill toast-flash__icon" aria-hidden="true"></i>
                  <div class="toast-flash__text">
                    <div class="toast-flash__title">Error:</div>
                    <div class="toast-flash__message"><?= e($flashError) ?></div>
                  </div>
                </div>
              </div>
              <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="row g-3">
      <?php if ($user && $user['role'] === 'admin'): ?>
      <div class="col-12">
        <a class="tile tile--hero" href="<?= e(url('admin/index.php')) ?>">
          <div class="tile-row tile-row--center h-100">
            <div class="tile-icon tile-icon--admin"><i class="bi bi-shield-lock"></i></div>
            <div class="tile-body">
              <div class="tile-title">Admin Dashboard</div>
              <div class="tile-sub">Manage accounts and system settings</div>
            </div>
            <div class="tile-cta d-none d-sm-flex">Open Admin <i class="bi bi-arrow-right ms-2"></i></div>
          </div>
        </a>
      </div>
      <?php endif; ?>

      <div class="col-12">
        <a class="tile tile--hero" href="<?= e(url('patient-entry/index.php')) ?>">
          <div class="tile-row tile-row--center">
            <div class="tile-icon tile-icon--primary">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" style="width:22px; height:22px; display:block;">
                <path d="M15 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" fill="none" stroke="currentColor" stroke-width="2"/>
                <path d="M9 11a4 4 0 1 0 0-8a4 4 0 0 0 0 8z" fill="none" stroke="currentColor" stroke-width="2"/>
                <path d="M19 8v6" fill="none" stroke="currentColor" stroke-width="2"/>
                <path d="M16 11h6" fill="none" stroke="currentColor" stroke-width="2"/>
              </svg>
            </div>
            <div class="tile-body">
              <div class="tile-title">Patient Entry</div>
              <div class="tile-sub">Create a new patient record</div>
            </div>
            <div class="tile-cta">Open form <i class="bi bi-arrow-right"></i></div>
          </div>
        </a>
      </div>

      <div class="col-12 col-md-6">
        <a class="tile tile--square" href="<?= e(url('medical/index.php')) ?>">
          <div class="tile-row tile-row--center">
            <div class="tile-icon tile-icon--medical"><i class="bi bi-clipboard2-pulse"></i></div>
            <div class="tile-body">
              <div class="tile-title">Medical</div>
              <div class="tile-sub">Dashboard (login required)</div>
            </div>
            <div class="tile-cta">Open dashboard <i class="bi bi-arrow-right"></i></div>
          </div>
        </a>
      </div>

      <div class="col-12 col-md-6">
        <a class="tile tile--square" href="<?= e(url('dental/index.php')) ?>">
          <div class="tile-row tile-row--center">
            <div class="tile-icon tile-icon--dental">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" style="width:22px; height:22px; display:block;">
                <path d="M8 2.75c1.2-.55 2.5-.75 4-.75s2.8.2 4 .75c2.3 1.1 4 3.4 4 6.2c0 2.4-.8 4.4-1.7 6c-.6 1.1-1.2 2.1-1.4 3.2l-.2 1.3c-.2 1.4-1.4 2.5-2.8 2.5c-1.5 0-2.7-1.2-2.7-2.7v-2.2c0-.6-.5-1.1-1.2-1.1s-1.2.5-1.2 1.1v2.2c0 1.5-1.2 2.7-2.7 2.7c-1.4 0-2.6-1.1-2.8-2.5l-.2-1.3c-.2-1.1-.8-2.1-1.4-3.2c-.9-1.6-1.7-3.6-1.7-6c0-2.8 1.7-5.1 4-6.2Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 9c.9-.7 1.9-1 3-1s2.1.3 3 1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="tile-body">
              <div class="tile-title">Dental</div>
              <div class="tile-sub">Dashboard (login required)</div>
            </div>
            <div class="tile-cta">Open dashboard <i class="bi bi-arrow-right"></i></div>
          </div>
        </a>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function(){
      if (!window.bootstrap) return;
      var toasts = document.querySelectorAll('.toast');
      toasts.forEach(function(el){
        try { new bootstrap.Toast(el).show(); } catch (e) {}
      });
    })();
  </script>
</body>
</html>
