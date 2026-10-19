<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require __DIR__ . '/config.php';
startAppSession();

if (($_SESSION['role'] ?? null) === 'admin') {
    header('Location: index.php');
    exit;
}
if (($_SESSION['role'] ?? null) === 'siswa') {
    header('Location: siswa.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    } else {
        try {
            $statement = database()->prepare(
                "SELECT id, nama, password_hash
                 FROM users
                 WHERE username = :username AND role = 'admin' AND aktif = 1
                 LIMIT 1"
            );
            $statement->execute(['username' => trim((string) ($_POST['username'] ?? ''))]);
            $user = $statement->fetch();

            if ($user && password_verify((string) ($_POST['password'] ?? ''), $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = 'admin';
                unset($_SESSION['siswa_id']);
                header('Location: index.php');
                exit;
            }
            $error = 'Username atau password admin salah.';
        } catch (PDOException $exception) {
            error_log('InternTrack admin login error: ' . $exception->getMessage());
            $error = 'Database belum siap. Impor database.sql lalu jalankan php seed.php.';
        }
    }
}

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin | InternTrack</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-page">
<main class="auth-card">
    <a class="auth-brand" href="admin_login.php"><span class="brand-mark">I</span> interntrack</a>
    <span class="eyebrow">ADMINISTRATOR</span>
    <h1>Login admin</h1>
    <p class="auth-description">Masuk untuk mengelola data dan monitoring kegiatan PKL.</p>
    <?php if ($error !== null): ?><div class="alert alert-error" role="alert"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="simple-form">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <label>Username<input type="text" name="username" autocomplete="username" required autofocus></label>
        <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
        <button class="button button-primary button-full" type="submit">Masuk sebagai admin</button>
    </form>
    <a class="auth-back" href="login.php">Login sebagai siswa</a>
</main>
</body>
</html>
