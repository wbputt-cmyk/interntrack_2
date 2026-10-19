<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require __DIR__ . '/config.php';
startAppSession();

if (($_SESSION['role'] ?? null) === 'siswa') {
    header('Location: siswa.php');
    exit;
}
if (($_SESSION['role'] ?? null) === 'admin') {
    header('Location: index.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    } else {
        try {
            $statement = database()->prepare(
                "SELECT u.id AS user_id, u.nama, u.password_hash, s.id AS siswa_id
                 FROM users u
                 JOIN siswa s ON s.user_id = u.id
                 WHERE u.username = :username AND u.role = 'siswa' AND u.aktif = 1
                 LIMIT 1"
            );
            $statement->execute(['username' => trim((string) ($_POST['username'] ?? ''))]);
            $user = $statement->fetch();

            if ($user && password_verify((string) ($_POST['password'] ?? ''), $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['user_id'];
                $_SESSION['siswa_id'] = (int) $user['siswa_id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = 'siswa';
                header('Location: siswa.php');
                exit;
            }
            $error = 'Username atau password salah.';
        } catch (PDOException $exception) {
            error_log('InternTrack login error: ' . $exception->getMessage());
            $error = 'Database belum siap. Impor database.sql terlebih dahulu.';
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
    <title>Login Siswa | InternTrack</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-page">
<main class="auth-card">
    <a class="auth-brand" href="index.php"><span class="brand-mark">I</span> interntrack</a>
    <span class="eyebrow">PORTAL SISWA</span>
    <h1>Masuk ke akun</h1>
    <p class="auth-description">Gunakan akun yang diberikan oleh sekolah untuk melihat dan mengisi kegiatan PKL.</p>
    <?php if ($error !== null): ?><div class="alert alert-error" role="alert"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="simple-form">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <label>Username<input type="text" name="username" autocomplete="username" required autofocus></label>
        <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
        <button class="button button-primary button-full" type="submit">Masuk</button>
    </form>
    <a class="auth-back" href="admin_login.php">Login sebagai admin</a>
</main>
</body>
</html>
