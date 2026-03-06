<?php

declare(strict_types=1);

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function has_role(string $role): bool
{
    $user = current_user();
    if (!$user) return false;
    
    // Admin has access to everything
    if (($user['role'] ?? '') === 'admin') return true;
    
    return ($user['role'] ?? '') === $role;
}

function require_login(?string $requiredRole = null, ?string $returnTo = null): void
{
    $returnTo = is_string($returnTo) ? ltrim($returnTo, '/') : null;

    // Check session timeout (30 minutes)
    $timeout = 1800;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        logout();
        $q = ['timeout' => 1];
        if ($requiredRole !== null) $q['role'] = $requiredRole;
        if ($returnTo !== null) $q['return_to'] = $returnTo;
        redirect('/auth/login.php?' . http_build_query($q));
    }
    $_SESSION['last_activity'] = time();

    if (!is_logged_in()) {
        $q = [];
        if ($requiredRole !== null) {
            $q[] = 'role=' . rawurlencode($requiredRole);
        }
        if ($returnTo !== null && $returnTo !== '') {
            $q[] = 'return_to=' . rawurlencode($returnTo);
        }
        $qs = $q ? ('?' . implode('&', $q)) : '';
        redirect('/auth/login.php' . $qs);
    }

    if ($requiredRole !== null && !has_role($requiredRole)) {
        // If logged in but doesn't have the required role for this specific area
        $q = ['role' => $requiredRole, 'insufficient_role' => 1];
        if ($returnTo !== null && $returnTo !== '') {
            $q['return_to'] = $returnTo;
        }
        redirect('/auth/login.php?' . http_build_query($q));
    }
}

function attempt_login(string $username, string $password): bool
{
    $cfg = base_config();

    try {
        $stmt = db()->prepare('SELECT id, username, password_hash, fullname, role FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if (is_array($user) && isset($user['password_hash']) && password_verify($password, (string)$user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'username' => (string)$user['username'],
                'fullname' => (string)$user['fullname'],
                'role' => (string)$user['role'],
            ];
            $_SESSION['last_activity'] = time();
            return true;
        }
    } catch (Throwable $e) {
    }

    return false;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
