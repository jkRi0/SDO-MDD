<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/auth.php';

$cfg = base_config();

$requiredRole = (string)($_GET['role'] ?? $_POST['role'] ?? '');
$returnTo = (string)($_GET['return_to'] ?? $_POST['return_to'] ?? '');
$insufficientRole = (bool)($_GET['insufficient_role'] ?? $_POST['insufficient_role'] ?? false);

$requiredRole = strtolower(trim($requiredRole));
if (!in_array($requiredRole, ['', 'admin', 'medical', 'dental'], true)) {
    $requiredRole = '';
}

$returnTo = ltrim(trim($returnTo), '/');
// Basic security check for returnTo
if ($returnTo !== '' && (str_contains($returnTo, '..') || str_starts_with($returnTo, 'http'))) {
    $returnTo = '';
}

$error = null;
if ($insufficientRole) {
    $error = "Your account does not have permission to access the " . ucfirst($requiredRole) . " area.";
}

$user = current_user();
$mustLogoutToSwitch = false;
if ($user && $requiredRole !== '' && !has_role($requiredRole)) {
    $mustLogoutToSwitch = true;
    $error = "You're currently logged in as " . ucfirst((string)($user['role'] ?? '')) . ". Please logout to switch to " . ucfirst($requiredRole) . ".";
}

if ($user && !$mustLogoutToSwitch) {
    redirect('/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } elseif (attempt_login($username, $password)) {
        if ($returnTo !== '') {
            redirect('/' . $returnTo);
        }
        
        $user = current_user();
        if ($user['role'] === 'admin') {
            redirect('/admin/index.php');
        } elseif ($user['role'] === 'medical') {
            redirect('/medical/index.php');
        } elseif ($user['role'] === 'dental') {
            redirect('/dental/index.php');
        }
        redirect('/');
    } else {
        $error = 'Invalid username or password.';
    }
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - <?= e($cfg['app_name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= e(asset('public/assets/css/styles.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">
  <?php if ($error): ?>
    <div class="toast-stack-top-center">
      <div class="toast toast-flash toast-flash--error" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3200">
        <div class="d-flex">
          <div class="toast-body">
            <div class="toast-flash__row">
              <i class="bi bi-slash-circle-fill toast-flash__icon" aria-hidden="true"></i>
              <div class="toast-flash__text">
                <div class="toast-flash__title">Error:</div>
                <div class="toast-flash__message"><?= e($error) ?></div>
              </div>
            </div>
          </div>
          <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>
  <?php endif; ?>
  <div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: 100vh; padding: 2rem 0;">
    <div class="text-center mb-4">
      <img src="<?= e(asset('public/assets/sdo-logo.png')) ?>" alt="Logo" style="width: 100px; height: 100px; margin-bottom: 1.5rem; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
      <h1 class="h4 fw-bold text-primary mb-1"><?= e($cfg['app_name']) ?></h1>
      <p class="text-secondary mb-0">School Health Section</p>
    </div>

    <div class="card shadow-lg border-0" style="width: 100%; max-width: 420px; border-radius: 20px; overflow: hidden;">
      <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
          <h2 class="h4 fw-bold mb-1">Welcome Back</h2>
          <p class="small text-muted">Please enter your credentials to continue</p>
        </div>

        <?php if ($mustLogoutToSwitch): ?>
          <div class="d-grid gap-3">
            <a class="btn btn-primary fw-bold" href="<?= url('/auth/logout.php') ?>" style="border-radius: 12px; padding: 0.85rem; font-size: 1rem;">
              Logout
            </a>
            <a href="<?= url('/') ?>" class="btn btn-link btn-sm text-secondary text-decoration-none">
              <i class="bi bi-arrow-left me-1"></i> Back to Home
            </a>
          </div>
        <?php else: ?>
          <form method="post" autocomplete="off">
            <input type="hidden" name="role" value="<?= e($requiredRole) ?>">
            <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
            
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">Username</label>
              <div class="input-group custom-input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-person text-muted"></i>
                </span>
                <input class="form-control bg-light border-start-0" name="username" value="<?= e((string)($_POST['username'] ?? '')) ?>" style="padding: 0.75rem;" placeholder="Enter your username" required autofocus>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label small fw-bold text-secondary">Password</label>
              <div class="input-group custom-input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-lock text-muted"></i>
                </span>
                <input class="form-control bg-light border-start-0" id="passwordInput" name="password" type="password" style="padding: 0.75rem;" placeholder="Enter your password" required>
                <button class="btn bg-light border-start-0" type="button" id="togglePassword" aria-label="Show password">
                  <i class="bi bi-eye text-muted" aria-hidden="true"></i>
                </button>
              </div>
            </div>

            <div class="d-grid gap-3">
              <button class="btn btn-primary fw-bold" type="submit" style="border-radius: 12px; padding: 0.85rem; font-size: 1rem; transition: transform 0.2s;">
                Sign In
              </button>
              <a href="<?= url('/') ?>" class="btn btn-link btn-sm text-secondary text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Back to Home
              </a>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </div>
    
    <div class="mt-5 text-center text-muted small">
      &copy; <?= date('Y') ?> Deped SDO Cabuyao. All rights reserved.
    </div>
  </div>

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

  <script>
    (function(){
      var input = document.getElementById('passwordInput');
      var btn = document.getElementById('togglePassword');
      if (!input || !btn) return;

      btn.addEventListener('click', function(){
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        var icon = btn.querySelector('i');
        if (icon) {
          icon.classList.remove(show ? 'bi-eye' : 'bi-eye-slash');
          icon.classList.add(show ? 'bi-eye-slash' : 'bi-eye');
        }
      });
    })();
  </script>
</body>
</html>
