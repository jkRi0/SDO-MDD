<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/auth.php';

require_login('admin', 'admin/edit_user.php');
$cfg = base_config();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('/admin/index.php');
}

$error = null;
$success = null;

$stmt = db()->prepare('SELECT id, username, fullname, role FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$userToEdit = $stmt->fetch();

if (!$userToEdit) {
    redirect('/admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim((string)($_POST['fullname'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $role = (string)($_POST['role'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($fullname === '' || $username === '' || $role === '') {
        $error = "Full Name, Username, and Role are required.";
    } else {
        try {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $update = db()->prepare('UPDATE users SET fullname = ?, username = ?, role = ?, password_hash = ? WHERE id = ?');
                $update->execute([$fullname, $username, $role, $hash, $id]);
            } else {
                $update = db()->prepare('UPDATE users SET fullname = ?, username = ?, role = ? WHERE id = ?');
                $update->execute([$fullname, $username, $role, $id]);
            }
            $success = "User updated successfully.";
            
            // Refresh data
            $stmt->execute([$id]);
            $userToEdit = $stmt->fetch();
        } catch (Throwable $e) {
            $error = "Error updating user: " . $e->getMessage();
        }
    }
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit User - <?= e($cfg['app_name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(asset('public/assets/css/styles.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">
  <header class="appbar">
    <div class="container py-3">
      <div class="d-flex justify-content-between align-items-center">
        <div class="appbar-inner">
          <div class="appbar-title">
            <div class="brand h5 mb-0">Edit User</div>
            <div class="small text-white-50">Admin Dashboard</div>
          </div>
        </div>
        <a href="<?= url('/admin/index.php') ?>" class="btn btn-sm btn-light">Back to List</a>
      </div>
    </div>
  </header>

  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
          <div class="card-body p-4">
            <form method="post">
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="fullname" class="form-control" value="<?= e($userToEdit['fullname']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?= e($userToEdit['username']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                  <option value="medical" <?= $userToEdit['role'] === 'medical' ? 'selected' : '' ?>>Medical Staff</option>
                  <option value="dental" <?= $userToEdit['role'] === 'dental' ? 'selected' : '' ?>>Dental Staff</option>
                  <option value="admin" <?= $userToEdit['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                </select>
              </div>
              <div class="mb-4">
                <label class="form-label">New Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-control">
              </div>
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?= url('/admin/index.php') ?>" class="btn btn-link text-secondary">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
