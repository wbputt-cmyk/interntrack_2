<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
startAppSession();
$redirect = ($_SESSION['role'] ?? null) === 'admin' ? 'admin_login.php' : 'login.php';
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $parameters = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $parameters['path'],
        'domain' => $parameters['domain'],
        'secure' => $parameters['secure'],
        'httponly' => $parameters['httponly'],
        'samesite' => $parameters['samesite'] ?? 'Lax',
    ]);
}

session_destroy();
header('Location: ' . $redirect);
exit;
