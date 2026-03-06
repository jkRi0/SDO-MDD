<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/auth.php';

require_login('admin', 'admin/index.php');
$cfg = base_config();

$error = null;
$success = null;

// Handle user deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0 && $id !== current_user()['id']) {
        try {
            $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $success = "User deleted successfully.";
        } catch (Throwable $e) {
            $error = "Error deleting user: " . $e->getMessage();
        }
    }
}

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $fullname = trim((string)($_POST['fullname'] ?? ''));
    $role = (string)($_POST['role'] ?? 'medical');

    if ($username === '' || $password === '' || $fullname === '') {
        $error = "All fields are required.";
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('INSERT INTO users (username, password_hash, fullname, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $hash, $fullname, $role]);
            $success = "User created successfully.";
        } catch (Throwable $e) {
            $error = "Error creating user: " . $e->getMessage();
        }
    }
}

$users = [];
try {
    $users = db()->query('SELECT id, username, fullname, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
} catch (Throwable $e) {
    $error = "Error fetching users: " . $e->getMessage();
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard - <?= e($cfg['app_name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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
              <div class="brand h5 mb-0 text-white fw-bold lh-1">Admin Dashboard</div>
              <div class="small text-white-50 lh-1 mt-1">School Health Section</div>
            </div>
          </div>
        </div>
        <!-- Header Actions -->
        <div class="col-12 col-md-auto mt-2 mt-md-0">
          <div class="d-flex align-items-center gap-2 justify-content-md-end">
            <div class="d-none d-lg-block text-end me-2">
              <div class="small fw-bold text-white lh-1"><?= e(current_user()['fullname']) ?></div>
              <div class="text-white-50 lh-1 mt-1" style="font-size: 0.7rem;">Administrator</div>
            </div>
            <a href="<?= url('/') ?>" class="btn btn-light btn-sm fw-bold px-3" style="border-radius: 8px;">Home</a>
            <a href="<?= url('/auth/logout.php') ?>" class="btn btn-outline-light btn-sm px-3" style="border-radius: 8px;">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="container py-4">
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- Create User Form -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
          <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold">Create New Account</h5>
          </div>
          <div class="card-body p-4">
            <form method="post">
              <input type="hidden" name="create_user" value="1">
              <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Full Name</label>
                <input type="text" name="fullname" class="form-control" style="border-radius: 10px; padding: 0.6rem 1rem;" placeholder="e.g. John Doe" required>
              </div>
              <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Username</label>
                <input type="text" name="username" class="form-control" style="border-radius: 10px; padding: 0.6rem 1rem;" placeholder="username" required>
              </div>
              <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Password</label>
                <input type="password" name="password" class="form-control" style="border-radius: 10px; padding: 0.6rem 1rem;" placeholder="••••••••" required>
              </div>
              <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Role</label>
                <select name="role" class="form-select" style="border-radius: 10px; padding: 0.6rem 1rem;">
                  <option value="medical">Medical Staff</option>
                  <option value="dental">Dental Staff</option>
                  <option value="admin">Administrator</option>
                </select>
              </div>
              <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary fw-bold" style="border-radius: 10px; padding: 0.75rem;">Create Account</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- User List -->
      <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
          <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold">Manage Accounts</h5>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">User</th>
                  <th class="py-3 text-secondary small fw-bold text-uppercase">Role</th>
                  <th class="py-3 text-secondary small fw-bold text-uppercase">Created</th>
                  <th class="text-end pe-4 py-3 text-secondary small fw-bold text-uppercase">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $user): ?>
                  <tr>
                    <td class="ps-4 py-3">
                      <div class="fw-bold text-dark"><?= e($user['fullname']) ?></div>
                      <div class="small text-secondary">@<?= e($user['username']) ?></div>
                    </td>
                    <td class="py-3">
                      <span class="badge rounded-pill bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'medical' ? 'primary' : 'info') ?>" style="font-weight: 600; padding: 0.4em 0.8em;">
                        <?= ucfirst($user['role']) ?>
                      </span>
                    </td>
                    <td class="small text-secondary py-3">
                      <?= date('M d, Y', strtotime($user['created_at'])) ?>
                    </td>
                    <td class="text-end pe-4 py-3">
                      <div class="action-container">
                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-white py-2 px-3" style="border-right: 1px solid #dee2e6 !important;" title="Edit">
                          <i class="bi bi-pencil text-primary"></i>
                        </a>
                        <?php if ($user['id'] !== current_user()['id']): ?>
                          <a href="?delete=<?= $user['id'] ?>" class="btn btn-sm btn-white py-2 px-3" onclick="return confirm('Delete this account?')" title="Delete">
                            <i class="bi bi-trash text-danger"></i>
                          </a>
                        <?php else: ?>
                          <span class="btn btn-sm btn-light py-2 px-3 disabled text-secondary" style="background: #f8fafc; font-size: 0.75rem; font-weight: 600;">You</span>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
