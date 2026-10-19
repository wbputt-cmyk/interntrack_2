<?php
declare(strict_types=1);

function startAppSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

function requireStudent(): void
{
    startAppSession();
    if (($_SESSION['role'] ?? null) !== 'siswa' || !isset($_SESSION['siswa_id'])) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin(): void
{
    startAppSession();
    if (($_SESSION['role'] ?? null) !== 'admin' || !isset($_SESSION['user_id'])) {
        header('Location: admin_login.php');
        exit;
    }
}
