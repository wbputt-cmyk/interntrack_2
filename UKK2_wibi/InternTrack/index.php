<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/auth.php';
requireAdmin();

$modules = [
    'siswa' => [
        'label' => 'Data Siswa',
        'table' => 'siswa',
        'query' => 'SELECT nis, nama, kelas, jurusan, no_telepon FROM siswa ORDER BY nama LIMIT 100',
        'columns' => ['nis' => 'NIS', 'nama' => 'Nama siswa', 'kelas' => 'Kelas', 'jurusan' => 'Jurusan', 'no_telepon' => 'Telepon'],
    ],
    'perusahaan' => [
        'label' => 'Perusahaan',
        'table' => 'perusahaan',
        'query' => 'SELECT nama, bidang_usaha, kota, telepon, email FROM perusahaan ORDER BY nama LIMIT 100',
        'columns' => ['nama' => 'Nama perusahaan', 'bidang_usaha' => 'Bidang usaha', 'kota' => 'Kota', 'telepon' => 'Telepon', 'email' => 'Email'],
    ],
    'pembimbing' => [
        'label' => 'Pembimbing',
        'table' => 'pembimbing',
        'query' => 'SELECT b.nama, p.nama AS perusahaan, b.jabatan, b.no_telepon, b.email FROM pembimbing b JOIN perusahaan p ON p.id = b.perusahaan_id ORDER BY b.nama LIMIT 100',
        'columns' => ['nama' => 'Nama pembimbing', 'perusahaan' => 'Perusahaan', 'jabatan' => 'Jabatan', 'no_telepon' => 'Telepon', 'email' => 'Email'],
    ],
    'penempatan' => [
        'label' => 'Penempatan PKL',
        'table' => 'penempatan',
        'query' => 'SELECT s.nama AS siswa, p.nama AS perusahaan, b.nama AS pembimbing, x.tanggal_mulai, x.tanggal_selesai, x.status FROM penempatan x JOIN siswa s ON s.id = x.siswa_id JOIN perusahaan p ON p.id = x.perusahaan_id LEFT JOIN pembimbing b ON b.id = x.pembimbing_id ORDER BY x.tanggal_mulai DESC LIMIT 100',
        'columns' => ['siswa' => 'Siswa', 'perusahaan' => 'Perusahaan', 'pembimbing' => 'Pembimbing', 'tanggal_mulai' => 'Mulai', 'tanggal_selesai' => 'Selesai', 'status' => 'Status'],
    ],
    'jurnal' => [
        'label' => 'Jurnal Kegiatan',
        'table' => 'jurnal',
        'query' => 'SELECT s.nama AS siswa, j.tanggal, j.kegiatan, j.status_validasi FROM jurnal j JOIN penempatan x ON x.id = j.penempatan_id JOIN siswa s ON s.id = x.siswa_id ORDER BY j.tanggal DESC LIMIT 100',
        'columns' => ['siswa' => 'Siswa', 'tanggal' => 'Tanggal', 'kegiatan' => 'Kegiatan', 'status_validasi' => 'Validasi'],
    ],
    'absensi' => [
        'label' => 'Absensi',
        'table' => 'absensi',
        'query' => 'SELECT s.nama AS siswa, a.tanggal, a.jam_masuk, a.jam_pulang, a.status FROM absensi a JOIN penempatan x ON x.id = a.penempatan_id JOIN siswa s ON s.id = x.siswa_id ORDER BY a.tanggal DESC LIMIT 100',
        'columns' => ['siswa' => 'Siswa', 'tanggal' => 'Tanggal', 'jam_masuk' => 'Jam masuk', 'jam_pulang' => 'Jam pulang', 'status' => 'Status'],
    ],
    'monitoring' => [
        'label' => 'Monitoring',
        'table' => 'monitoring',
        'query' => 'SELECT s.nama AS siswa, m.tanggal, m.metode, m.catatan, u.nama AS petugas FROM monitoring m JOIN penempatan x ON x.id = m.penempatan_id JOIN siswa s ON s.id = x.siswa_id LEFT JOIN users u ON u.id = m.user_id ORDER BY m.tanggal DESC LIMIT 100',
        'columns' => ['siswa' => 'Siswa', 'tanggal' => 'Tanggal', 'metode' => 'Metode', 'catatan' => 'Catatan', 'petugas' => 'Petugas'],
    ],
];

$moduleForms = [
    'siswa' => [
        ['nis', 'NIS', 'text', true],
        ['nama', 'Nama siswa', 'text', true],
        ['kelas', 'Kelas', 'text', true],
        ['jurusan', 'Jurusan', 'text', true],
        ['no_telepon', 'Nomor telepon', 'text', false],
        ['alamat', 'Alamat', 'textarea', false],
        ['username', 'Username login siswa', 'text', true],
        ['email', 'Email akun', 'email', true],
        ['password', 'Password awal', 'password', true],
    ],
    'perusahaan' => [
        ['nama', 'Nama perusahaan', 'text', true],
        ['bidang_usaha', 'Bidang usaha', 'text', false],
        ['alamat', 'Alamat', 'textarea', true],
        ['kota', 'Kota', 'text', false],
        ['telepon', 'Telepon', 'text', false],
        ['email', 'Email', 'email', false],
    ],
    'pembimbing' => [
        ['perusahaan_id', 'Perusahaan', 'perusahaan', true],
        ['nama', 'Nama pembimbing', 'text', true],
        ['jabatan', 'Jabatan', 'text', false],
        ['no_telepon', 'Telepon', 'text', false],
        ['email', 'Email', 'email', false],
    ],
    'penempatan' => [
        ['siswa_id', 'Siswa', 'siswa', true],
        ['perusahaan_id', 'Perusahaan', 'perusahaan', true],
        ['pembimbing_id', 'Pembimbing', 'pembimbing', false],
        ['tanggal_mulai', 'Tanggal mulai', 'date', true],
        ['tanggal_selesai', 'Tanggal selesai', 'date', true],
        ['status', 'Status', 'status_penempatan', true],
        ['catatan', 'Catatan', 'textarea', false],
    ],
    'jurnal' => [
        ['penempatan_id', 'Siswa / penempatan', 'penempatan', true],
        ['tanggal', 'Tanggal', 'date', true],
        ['kegiatan', 'Kegiatan', 'textarea', true],
        ['hasil', 'Hasil', 'textarea', false],
        ['kendala', 'Kendala', 'textarea', false],
        ['status_validasi', 'Status validasi', 'status_validasi', true],
        ['catatan_pembimbing', 'Catatan pembimbing', 'textarea', false],
    ],
    'absensi' => [
        ['penempatan_id', 'Siswa / penempatan', 'penempatan', true],
        ['tanggal', 'Tanggal', 'date', true],
        ['jam_masuk', 'Jam masuk', 'time', false],
        ['jam_pulang', 'Jam pulang', 'time', false],
        ['status', 'Status kehadiran', 'status_absensi', true],
        ['keterangan', 'Keterangan', 'textarea', false],
    ],
    'monitoring' => [
        ['penempatan_id', 'Siswa / penempatan', 'penempatan', true],
        ['tanggal', 'Tanggal', 'date', true],
        ['metode', 'Metode monitoring', 'metode_monitoring', true],
        ['catatan', 'Catatan monitoring', 'textarea', true],
        ['evaluasi', 'Evaluasi', 'textarea', false],
        ['tindak_lanjut', 'Tindak lanjut', 'textarea', false],
    ],
];

$requestedPage = $_GET['page'] ?? 'dashboard';
$currentPage = isset($modules[$requestedPage]) || $requestedPage === 'dashboard'
    ? $requestedPage
    : 'dashboard';
$db = null;
$connectionError = null;
$stats = [];
$rows = [];
$latestJournals = [];
$formError = null;
$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
$selectOptions = [];

try {
    $db = database();

    if ($currentPage !== 'dashboard' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $formError = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
        } else {
            $fields = $moduleForms[$currentPage];
            $values = [];
            foreach ($fields as [$name, $label, $type, $required]) {
                $value = trim((string) ($_POST[$name] ?? ''));
                if ($required && $value === '') {
                    $formError = 'Mohon lengkapi kolom ' . $label . '.';
                    break;
                }
                if ($type === 'password' && $value !== '' && strlen($value) < 8) {
                    $formError = 'Password awal minimal 8 karakter.';
                    break;
                }
                if ($type === 'email' && $value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                    $formError = 'Format email tidak valid.';
                    break;
                }
                $values[$name] = $value === '' ? null : $value;
            }

            if ($formError === null) {
                try {
                    $db->beginTransaction();
                    if ($currentPage === 'siswa') {
                        $account = $db->prepare(
                            "INSERT INTO users (nama, username, email, password_hash, role)
                             VALUES (:nama, :username, :email, :password_hash, 'siswa')"
                        );
                        $account->execute([
                            'nama' => $values['nama'],
                            'username' => $values['username'],
                            'email' => $values['email'],
                            'password_hash' => password_hash((string) $values['password'], PASSWORD_DEFAULT),
                        ]);
                        $student = $db->prepare(
                            'INSERT INTO siswa (user_id, nis, nama, kelas, jurusan, no_telepon, alamat)
                             VALUES (:user_id, :nis, :nama, :kelas, :jurusan, :no_telepon, :alamat)'
                        );
                        $student->execute([
                            'user_id' => $db->lastInsertId(),
                            'nis' => $values['nis'],
                            'nama' => $values['nama'],
                            'kelas' => $values['kelas'],
                            'jurusan' => $values['jurusan'],
                            'no_telepon' => $values['no_telepon'],
                            'alamat' => $values['alamat'],
                        ]);
                    } else {
                        $columns = array_map(static fn (array $field): string => $field[0], $fields);
                        $statement = $db->prepare(sprintf(
                            'INSERT INTO `%s` (%s) VALUES (%s)',
                            $modules[$currentPage]['table'],
                            implode(', ', array_map(static fn (string $column): string => '`' . $column . '`', $columns)),
                            implode(', ', array_map(static fn (string $column): string => ':' . $column, $columns))
                        ));
                        $statement->execute(array_intersect_key($values, array_flip($columns)));
                    }
                    $db->commit();
                    $_SESSION['flash_message'] = $modules[$currentPage]['label'] . ' berhasil ditambahkan.';
                    header('Location: ?page=' . rawurlencode($currentPage));
                    exit;
                } catch (PDOException $exception) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    error_log('InternTrack insert error: ' . $exception->getMessage());
                    $formError = $exception->getCode() === '23000'
                        ? 'Data duplikat atau relasi tidak valid. Periksa kembali isian.'
                        : 'Data gagal disimpan. Periksa isian dan pastikan database sudah sesuai skema.';
                }
            }
        }
    }

    $selectQueries = [
        'siswa' => 'SELECT id, CONCAT(nis, " - ", nama) AS label FROM siswa ORDER BY nama',
        'perusahaan' => 'SELECT id, nama AS label FROM perusahaan ORDER BY nama',
        'pembimbing' => 'SELECT id, CONCAT(nama, " - ", (SELECT nama FROM perusahaan WHERE id = perusahaan_id)) AS label FROM pembimbing ORDER BY nama',
        'penempatan' => 'SELECT x.id, CONCAT(s.nama, " - ", p.nama, " (", x.tanggal_mulai, ")") AS label FROM penempatan x JOIN siswa s ON s.id = x.siswa_id JOIN perusahaan p ON p.id = x.perusahaan_id ORDER BY x.tanggal_mulai DESC',
    ];
    foreach ($selectQueries as $key => $query) {
        $selectOptions[$key] = $db->query($query)->fetchAll();
    }

    if ($currentPage === 'dashboard') {
        $stats = [
            ['label' => 'Siswa terdaftar', 'value' => (int) $db->query('SELECT COUNT(*) FROM siswa')->fetchColumn(), 'icon' => 'S'],
            ['label' => 'Perusahaan mitra', 'value' => (int) $db->query('SELECT COUNT(*) FROM perusahaan')->fetchColumn(), 'icon' => 'P'],
            ['label' => 'PKL berjalan', 'value' => (int) $db->query("SELECT COUNT(*) FROM penempatan WHERE status = 'berjalan'")->fetchColumn(), 'icon' => 'A'],
            ['label' => 'Jurnal menunggu', 'value' => (int) $db->query("SELECT COUNT(*) FROM jurnal WHERE status_validasi = 'menunggu'")->fetchColumn(), 'icon' => 'J'],
        ];
        $latestJournals = $db->query(
            'SELECT s.nama AS siswa, j.tanggal, j.kegiatan, j.status_validasi
             FROM jurnal j
             JOIN penempatan x ON x.id = j.penempatan_id
             JOIN siswa s ON s.id = x.siswa_id
             ORDER BY j.tanggal DESC, j.id DESC
             LIMIT 5'
        )->fetchAll();
    } else {
        $rows = $db->query($modules[$currentPage]['query'])->fetchAll();
    }
} catch (PDOException $exception) {
    error_log('InternTrack database error: ' . $exception->getMessage());
    $connectionError = 'Database belum dapat diakses. Periksa konfigurasi koneksi dan jalankan database.sql terlebih dahulu.';
}

function escape(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function statusClass(string $status): string
{
    return match ($status) {
        'berjalan', 'disetujui', 'hadir', 'selesai' => 'status status-success',
        'menunggu', 'direncanakan', 'izin', 'sakit' => 'status status-pending',
        'revisi', 'dibatalkan', 'alpa' => 'status status-danger',
        default => 'status',
    };
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Dashboard monitoring kegiatan Praktik Kerja Lapangan siswa.">
    <title><?= $currentPage === 'dashboard' ? 'Dashboard' : escape($modules[$currentPage]['label']) ?> | InternTrack</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="index.php" aria-label="InternTrack beranda">
            <span class="brand-mark">I</span>
            <span>intern<span class="brand-light">track</span><small>PKL MANAGEMENT</small></span>
        </a>
        <div class="nav-caption">MENU UTAMA</div>
        <nav class="nav-list" aria-label="Navigasi utama">
            <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="index.php">
                <span class="nav-icon">⌂</span> Dashboard
            </a>
        </nav>
        <div class="nav-caption">KELOLA DATA</div>
        <nav class="nav-list" aria-label="Modul data">
            <?php foreach ($modules as $key => $module): ?>
                <a class="nav-link <?= $currentPage === $key ? 'active' : '' ?>" href="?page=<?= escape($key) ?>">
                    <span class="nav-icon"><?= escape(mb_substr($module['label'], 0, 1)) ?></span>
                    <?= escape($module['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-bottom">
            <div class="help-card">
                <span class="help-dot"></span>
                <strong>PKL lebih terpantau</strong>
                <p>Jurnal, absensi, dan monitoring siswa dalam satu tempat.</p>
            </div>
            <div class="sidebar-foot">InternTrack <span>•</span> Sistem Monitoring PKL</div>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <div class="breadcrumb">InternTrack <span>/</span> <?= $currentPage === 'dashboard' ? 'Dashboard' : escape($modules[$currentPage]['label']) ?></div>
                <h1><?= $currentPage === 'dashboard' ? 'Dashboard' : escape($modules[$currentPage]['label']) ?></h1>
            </div>
            <div class="topbar-right">
                <div class="today"><?= date('d M Y') ?></div>
                <div class="avatar"><?= escape(mb_substr((string) ($_SESSION['nama'] ?? 'A'), 0, 1)) ?></div>
                <div class="user-caption"><strong><?= escape($_SESSION['nama'] ?? 'Admin') ?></strong><span>Administrator</span></div>
                <a class="button button-secondary header-login" href="logout.php">Keluar</a>
            </div>
        </header>

        <div class="page-content">
            <?php if ($connectionError !== null): ?>
                <section class="alert alert-error" role="alert">
                    <strong>Koneksi database gagal</strong>
                    <p><?= escape($connectionError) ?></p>
                    <a href="README.md">Lihat petunjuk instalasi</a>
                </section>
            <?php elseif ($currentPage === 'dashboard'): ?>
                <section class="welcome-panel">
                    <div>
                        <span class="eyebrow">PRAKTIK KERJA LAPANGAN</span>
                        <h2>Halo, Admin! <span>👋</span></h2>
                        <p>Pantau progres dan aktivitas PKL siswa dari satu dashboard.</p>
                    </div>
                    <div class="welcome-art" aria-hidden="true"><span>PKL</span><i></i><b></b></div>
                </section>

                <section class="stats-grid" aria-label="Ringkasan data">
                    <?php foreach ($stats as $index => $stat): ?>
                        <article class="stat-card">
                            <div class="stat-top"><span><?= escape($stat['label']) ?></span><span class="stat-icon icon-<?= $index ?>"><?= escape($stat['icon']) ?></span></div>
                            <div class="stat-value"><?= number_format($stat['value']) ?></div>
                            <div class="stat-foot">Data terhubung ke database</div>
                        </article>
                    <?php endforeach; ?>
                </section>

                <section class="content-card">
                    <div class="card-heading">
                        <div><h2>Jurnal terbaru</h2><p>Aktivitas PKL yang baru dicatat siswa</p></div>
                        <a class="text-link" href="?page=jurnal">Lihat semua <span>→</span></a>
                    </div>
                    <?php if ($latestJournals === []): ?>
                        <div class="empty-state"><span class="empty-icon">J</span><strong>Belum ada jurnal kegiatan</strong><p>Jurnal siswa akan muncul di sini setelah tersedia di database.</p></div>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table>
                                <thead><tr><th>SISWA</th><th>TANGGAL</th><th>KEGIATAN</th><th>STATUS</th></tr></thead>
                                <tbody>
                                <?php foreach ($latestJournals as $journal): ?>
                                    <tr>
                                        <td><strong><?= escape($journal['siswa']) ?></strong></td>
                                        <td><?= escape($journal['tanggal']) ?></td>
                                        <td class="activity-cell"><?= escape($journal['kegiatan']) ?></td>
                                        <td><span class="<?= statusClass($journal['status_validasi']) ?>"><?= escape(ucfirst($journal['status_validasi'])) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="quick-grid">
                    <a class="quick-card" href="?page=absensi"><span class="quick-icon attendance">A</span><span><strong>Rekap absensi</strong><small>Lihat kehadiran siswa</small></span><b>→</b></a>
                    <a class="quick-card" href="?page=monitoring"><span class="quick-icon monitoring">M</span><span><strong>Catatan monitoring</strong><small>Lihat kunjungan pembimbing</small></span><b>→</b></a>
                </section>
            <?php else: ?>
                <?php $module = $modules[$currentPage]; ?>
                <section class="module-intro">
                    <div><span class="eyebrow">DATA PKL</span><h2><?= escape($module['label']) ?></h2><p>Tambah data lewat formulir, data tersimpan langsung ke database.</p></div>
                    <span class="record-count"><?= count($rows) ?> data ditampilkan</span>
                </section>
                <?php if ($flashMessage !== null): ?><div class="alert alert-success" role="status"><?= escape($flashMessage) ?></div><?php endif; ?>
                <?php if ($formError !== null): ?><div class="alert alert-error" role="alert"><?= escape($formError) ?></div><?php endif; ?>
                <details class="content-card add-form" open>
                    <summary><span>＋ Tambah <?= escape(strtolower($module['label'])) ?></span><small>Isi kolom berikut, lalu simpan</small></summary>
                    <form method="post" class="simple-form admin-form">
                        <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
                        <div class="form-grid">
                            <?php foreach ($moduleForms[$currentPage] as [$name, $label, $type, $required]): ?>
                                <label class="<?= $type === 'textarea' ? 'wide-field' : '' ?>">
                                    <?= escape($label) ?>
                                    <?php if (isset($selectOptions[$type])): ?>
                                        <select name="<?= escape($name) ?>" <?= $required ? 'required' : '' ?>>
                                            <option value="">Pilih <?= escape(strtolower($label)) ?></option>
                                            <?php foreach ($selectOptions[$type] as $option): ?>
                                                <option value="<?= escape($option['id']) ?>" <?= (string) ($_POST[$name] ?? '') === (string) $option['id'] ? 'selected' : '' ?>><?= escape($option['label']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php elseif (in_array($type, ['status_penempatan', 'status_validasi', 'status_absensi', 'metode_monitoring'], true)): ?>
                                        <?php
                                        $choices = match ($type) {
                                            'status_penempatan' => ['direncanakan', 'berjalan', 'selesai', 'dibatalkan'],
                                            'status_validasi' => ['menunggu', 'disetujui', 'revisi'],
                                            'status_absensi' => ['hadir', 'izin', 'sakit', 'alpa'],
                                            default => ['kunjungan', 'telepon', 'daring'],
                                        };
                                        ?>
                                        <select name="<?= escape($name) ?>" required>
                                            <?php foreach ($choices as $choice): ?>
                                                <option value="<?= escape($choice) ?>" <?= ($_POST[$name] ?? ($choice === $choices[0] ? $choice : '')) === $choice ? 'selected' : '' ?>><?= escape(ucfirst($choice)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php elseif ($type === 'textarea'): ?>
                                        <textarea name="<?= escape($name) ?>" rows="3" <?= $required ? 'required' : '' ?>><?= escape($_POST[$name] ?? '') ?></textarea>
                                    <?php else: ?>
                                        <input type="<?= escape($type) ?>" name="<?= escape($name) ?>" <?= $required ? 'required' : '' ?> <?= $type === 'password' ? 'minlength="8"' : '' ?> value="<?= $type === 'password' ? '' : escape($_POST[$name] ?? '') ?>">
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <button class="button button-primary" type="submit">Simpan data</button>
                    </form>
                </details>
                <section class="content-card">
                    <?php if ($rows === []): ?>
                        <div class="empty-state"><span class="empty-icon"><?= escape(mb_substr($module['label'], 0, 1)) ?></span><strong>Belum ada data <?= escape(strtolower($module['label'])) ?></strong><p>Data akan tampil di halaman ini setelah ditambahkan ke database.</p></div>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table>
                                <thead><tr><?php foreach ($module['columns'] as $label): ?><th><?= escape(mb_strtoupper($label)) ?></th><?php endforeach; ?></tr></thead>
                                <tbody>
                                <?php foreach ($rows as $row): ?>
                                    <tr>
                                        <?php foreach ($module['columns'] as $key => $label): ?>
                                            <td>
                                                <?php if ($key === 'status' || $key === 'status_validasi'): ?>
                                                    <span class="<?= statusClass((string) $row[$key]) ?>"><?= escape(ucfirst((string) $row[$key])) ?></span>
                                                <?php else: ?>
                                                    <?= escape($row[$key]) ?>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
                <p class="list-note">Menampilkan maksimal 100 data terbaru.</p>
            <?php endif; ?>
            <footer class="page-footer">© <?= date('Y') ?> InternTrack <span>•</span> Sistem Monitoring Kegiatan PKL Siswa</footer>
        </div>
    </main>
</div>
</body>
</html>
