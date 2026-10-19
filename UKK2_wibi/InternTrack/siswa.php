<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require __DIR__ . '/config.php';
requireStudent();
$db = database();
$error = null;
$success = null;
$siswaId = (int) $_SESSION['siswa_id'];

$placementQuery = $db->prepare(
    "SELECT x.id, x.tanggal_mulai, x.tanggal_selesai, x.status,
            p.nama AS perusahaan, b.nama AS pembimbing
     FROM penempatan x
     JOIN perusahaan p ON p.id = x.perusahaan_id
     LEFT JOIN pembimbing b ON b.id = x.pembimbing_id
     WHERE x.siswa_id = :siswa_id AND x.status IN ('berjalan', 'direncanakan')
     ORDER BY x.tanggal_mulai DESC
     LIMIT 1"
);
$placementQuery->execute(['siswa_id' => $siswaId]);
$placement = $placementQuery->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    } elseif (!$placement) {
        $error = 'Akun ini belum memiliki penempatan PKL aktif.';
    } else {
        $formType = $_POST['form_type'] ?? '';
        $date = (string) ($_POST['tanggal'] ?? '');
        if (!in_array($formType, ['jurnal', 'absensi'], true) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $error = 'Lengkapi formulir dengan data yang benar.';
        } else {
            try {
                if ($formType === 'jurnal') {
                    $activity = trim((string) ($_POST['kegiatan'] ?? ''));
                    if ($activity === '') {
                        throw new InvalidArgumentException('Isi kegiatan jurnal terlebih dahulu.');
                    }
                    $statement = $db->prepare(
                        "INSERT INTO jurnal (penempatan_id, tanggal, kegiatan, hasil, kendala)
                         VALUES (:penempatan_id, :tanggal, :kegiatan, :hasil, :kendala)"
                    );
                    $statement->execute([
                        'penempatan_id' => $placement['id'],
                        'tanggal' => $date,
                        'kegiatan' => $activity,
                        'hasil' => trim((string) ($_POST['hasil'] ?? '')) ?: null,
                        'kendala' => trim((string) ($_POST['kendala'] ?? '')) ?: null,
                    ]);
                } else {
                    $status = (string) ($_POST['status'] ?? 'hadir');
                    if (!in_array($status, ['hadir', 'izin', 'sakit', 'alpa'], true)) {
                        throw new InvalidArgumentException('Status absensi tidak valid.');
                    }
                    $statement = $db->prepare(
                        'INSERT INTO absensi (penempatan_id, tanggal, jam_masuk, jam_pulang, status, keterangan)
                         VALUES (:penempatan_id, :tanggal, :jam_masuk, :jam_pulang, :status, :keterangan)'
                    );
                    $statement->execute([
                        'penempatan_id' => $placement['id'],
                        'tanggal' => $date,
                        'jam_masuk' => trim((string) ($_POST['jam_masuk'] ?? '')) ?: null,
                        'jam_pulang' => trim((string) ($_POST['jam_pulang'] ?? '')) ?: null,
                        'status' => $status,
                        'keterangan' => trim((string) ($_POST['keterangan'] ?? '')) ?: null,
                    ]);
                }
                $success = $formType === 'jurnal' ? 'Jurnal berhasil disimpan.' : 'Absensi berhasil disimpan.';
            } catch (InvalidArgumentException $exception) {
                $error = $exception->getMessage();
            } catch (PDOException $exception) {
                error_log('InternTrack student submission error: ' . $exception->getMessage());
                $error = $exception->getCode() === '23000'
                    ? 'Data untuk tanggal tersebut sudah pernah diisi.'
                    : 'Data gagal disimpan. Periksa data dan coba lagi.';
            }
        }
    }
}

$journals = $db->prepare(
    'SELECT tanggal, kegiatan, hasil, status_validasi, catatan_pembimbing
     FROM jurnal j JOIN penempatan x ON x.id = j.penempatan_id
     WHERE x.siswa_id = :siswa_id ORDER BY j.tanggal DESC LIMIT 10'
);
$journals->execute(['siswa_id' => $siswaId]);
$journalRows = $journals->fetchAll();
$attendance = $db->prepare(
    'SELECT a.tanggal, a.jam_masuk, a.jam_pulang, a.status
     FROM absensi a JOIN penempatan x ON x.id = a.penempatan_id
     WHERE x.siswa_id = :siswa_id ORDER BY a.tanggal DESC LIMIT 10'
);
$attendance->execute(['siswa_id' => $siswaId]);
$attendanceRows = $attendance->fetchAll();

function h(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Siswa | InternTrack</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="student-shell">
    <header class="student-header">
        <a class="auth-brand" href="siswa.php"><span class="brand-mark">I</span> interntrack</a>
        <div class="student-user"><span><?= h($_SESSION['nama'] ?? 'Siswa') ?></span><a class="button button-secondary" href="logout.php">Keluar</a></div>
    </header>
    <section class="welcome-panel student-welcome">
        <div><span class="eyebrow">PORTAL SISWA</span><h2>Halo, <?= h($_SESSION['nama'] ?? 'Siswa') ?>! 👋</h2><p>Lihat penempatan PKL, catat kehadiran, dan isi jurnal kegiatanmu.</p></div>
        <div class="welcome-art" aria-hidden="true"><span>PKL</span><i></i><b></b></div>
    </section>
    <?php if ($error !== null): ?><div class="alert alert-error" role="alert"><?= h($error) ?></div><?php endif; ?>
    <?php if ($success !== null): ?><div class="alert alert-success" role="status"><?= h($success) ?></div><?php endif; ?>
    <section class="content-card placement-card">
        <div class="card-heading"><div><h2>Penempatan PKL</h2><p>Informasi perusahaan dan pembimbingmu</p></div></div>
        <?php if (!$placement): ?>
            <div class="empty-state"><strong>Belum ada penempatan PKL aktif</strong><p>Hubungi guru atau admin sekolah untuk informasi penempatan.</p></div>
        <?php else: ?>
            <div class="placement-details">
                <div><small>PERUSAHAAN</small><strong><?= h($placement['perusahaan']) ?></strong></div>
                <div><small>PEMBIMBING</small><strong><?= h($placement['pembimbing']) ?></strong></div>
                <div><small>PERIODE</small><strong><?= h($placement['tanggal_mulai']) ?> — <?= h($placement['tanggal_selesai']) ?></strong></div>
                <div><small>STATUS</small><strong><?= h(ucfirst($placement['status'])) ?></strong></div>
            </div>
        <?php endif; ?>
    </section>
    <?php if ($placement): ?>
        <div class="student-forms">
            <section class="content-card form-card">
                <div class="card-heading"><div><h2>Isi jurnal kegiatan</h2><p>Catat kegiatan harian PKL</p></div></div>
                <form method="post" class="simple-form">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                    <input type="hidden" name="form_type" value="jurnal">
                    <label>Tanggal<input type="date" name="tanggal" value="<?= h(date('Y-m-d')) ?>" required></label>
                    <label>Kegiatan<textarea name="kegiatan" rows="3" required></textarea></label>
                    <label>Hasil kegiatan<textarea name="hasil" rows="2"></textarea></label>
                    <label>Kendala<textarea name="kendala" rows="2"></textarea></label>
                    <button class="button button-primary" type="submit">Simpan jurnal</button>
                </form>
            </section>
            <section class="content-card form-card">
                <div class="card-heading"><div><h2>Isi absensi</h2><p>Catat kehadiran harianmu</p></div></div>
                <form method="post" class="simple-form">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                    <input type="hidden" name="form_type" value="absensi">
                    <label>Tanggal<input type="date" name="tanggal" value="<?= h(date('Y-m-d')) ?>" required></label>
                    <label>Status
                        <select name="status"><option value="hadir">Hadir</option><option value="izin">Izin</option><option value="sakit">Sakit</option><option value="alpa">Alpa</option></select>
                    </label>
                    <div class="form-row">
                        <label>Jam masuk<input type="time" name="jam_masuk"></label>
                        <label>Jam pulang<input type="time" name="jam_pulang"></label>
                    </div>
                    <label>Keterangan<textarea name="keterangan" rows="2"></textarea></label>
                    <button class="button button-primary" type="submit">Simpan absensi</button>
                </form>
            </section>
        </div>
        <div class="student-forms">
            <section class="content-card">
                <div class="card-heading"><div><h2>Jurnal saya</h2><p>10 jurnal terbaru</p></div></div>
                <?php if ($journalRows === []): ?><div class="empty-state"><strong>Belum ada jurnal</strong></div><?php else: ?>
                    <div class="table-wrap"><table><thead><tr><th>TANGGAL</th><th>KEGIATAN</th><th>STATUS</th><th>CATATAN PEMBIMBING</th></tr></thead><tbody>
                    <?php foreach ($journalRows as $row): ?><tr><td><?= h($row['tanggal']) ?></td><td><?= h($row['kegiatan']) ?></td><td><?= h(ucfirst($row['status_validasi'])) ?></td><td><?= h($row['catatan_pembimbing']) ?></td></tr><?php endforeach; ?>
                    </tbody></table></div>
                <?php endif; ?>
            </section>
            <section class="content-card">
                <div class="card-heading"><div><h2>Absensi saya</h2><p>10 catatan kehadiran terbaru</p></div></div>
                <?php if ($attendanceRows === []): ?><div class="empty-state"><strong>Belum ada catatan absensi</strong></div><?php else: ?>
                    <div class="table-wrap"><table><thead><tr><th>TANGGAL</th><th>MASUK</th><th>PULANG</th><th>STATUS</th></tr></thead><tbody>
                    <?php foreach ($attendanceRows as $row): ?><tr><td><?= h($row['tanggal']) ?></td><td><?= h($row['jam_masuk']) ?></td><td><?= h($row['jam_pulang']) ?></td><td><?= h(ucfirst($row['status'])) ?></td></tr><?php endforeach; ?>
                    </tbody></table></div>
                <?php endif; ?>
            </section>
        </div>
    <?php endif; ?>
    <footer class="page-footer">InternTrack <span>•</span> Sistem Monitoring Kegiatan PKL Siswa</footer>
</main>
</body>
</html>
