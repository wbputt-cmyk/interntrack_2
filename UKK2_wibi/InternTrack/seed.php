<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Jalankan file ini melalui terminal.');
}

require __DIR__ . '/config.php';

$db = database();
$db->beginTransaction();

try {
    $accountByUsername = $db->prepare('SELECT id FROM users WHERE username = :username');
    $insertAccount = $db->prepare(
        'INSERT INTO users (nama, username, email, password_hash, role)
         VALUES (:nama, :username, :email, :password_hash, :role)'
    );
    $getAccountId = static function (string $nama, string $username, string $email, string $role) use ($db, $accountByUsername, $insertAccount): int {
        $accountByUsername->execute(['username' => $username]);
        $id = $accountByUsername->fetchColumn();
        if ($id !== false) {
            return (int) $id;
        }
        $insertAccount->execute([
            'nama' => $nama,
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($role === 'admin' ? 'admin12345' : 'siswa123', PASSWORD_DEFAULT),
            'role' => $role,
        ]);

        return (int) $db->lastInsertId();
    };

    $adminUserId = $getAccountId('Administrator Demo', 'admin', 'admin@interntrack.local', 'admin');

    $students = [
        ['nis' => '2026001', 'nama' => 'Alya Putri', 'kelas' => 'XII RPL 1', 'jurusan' => 'Rekayasa Perangkat Lunak'],
        ['nis' => '2026002', 'nama' => 'Bima Pratama', 'kelas' => 'XII RPL 1', 'jurusan' => 'Rekayasa Perangkat Lunak'],
        ['nis' => '2026003', 'nama' => 'Citra Lestari', 'kelas' => 'XII TKJ 1', 'jurusan' => 'Teknik Komputer dan Jaringan'],
    ];
    $studentIds = [];
    $findStudent = $db->prepare('SELECT id FROM siswa WHERE nis = :nis');
    $insertStudent = $db->prepare(
        'INSERT INTO siswa (user_id, nis, nama, kelas, jurusan, no_telepon, alamat)
         VALUES (:user_id, :nis, :nama, :kelas, :jurusan, :no_telepon, :alamat)'
    );
    foreach ($students as $index => $student) {
        $username = 'siswa' . ($index + 1);
        $userId = $getAccountId($student['nama'], $username, $username . '@interntrack.local', 'siswa');
        $findStudent->execute(['nis' => $student['nis']]);
        $id = $findStudent->fetchColumn();
        if ($id === false) {
            $insertStudent->execute($student + [
                'user_id' => $userId,
                'no_telepon' => '08123456789' . $index,
                'alamat' => 'Bandung',
            ]);
            $id = $db->lastInsertId();
        }
        $studentIds[] = (int) $id;
    }

    $companies = [
        ['nama' => 'PT Digital Kreatif', 'bidang_usaha' => 'Pengembangan perangkat lunak', 'alamat' => 'Jl. Merdeka No. 10', 'kota' => 'Bandung', 'telepon' => '0227001001', 'email' => 'info@digitalkreatif.local'],
        ['nama' => 'CV Teknologi Nusantara', 'bidang_usaha' => 'Teknologi informasi', 'alamat' => 'Jl. Asia Afrika No. 25', 'kota' => 'Bandung', 'telepon' => '0227001002', 'email' => 'halo@teknusantara.local'],
    ];
    $companyIds = [];
    $findCompany = $db->prepare('SELECT id FROM perusahaan WHERE nama = :nama');
    $insertCompany = $db->prepare(
        'INSERT INTO perusahaan (nama, bidang_usaha, alamat, kota, telepon, email)
         VALUES (:nama, :bidang_usaha, :alamat, :kota, :telepon, :email)'
    );
    foreach ($companies as $company) {
        $findCompany->execute(['nama' => $company['nama']]);
        $id = $findCompany->fetchColumn();
        if ($id === false) {
            $insertCompany->execute($company);
            $id = $db->lastInsertId();
        }
        $companyIds[] = (int) $id;
    }

    $mentors = [
        ['perusahaan_id' => $companyIds[0], 'nama' => 'Dedi Firmansyah', 'jabatan' => 'Web Developer', 'no_telepon' => '081200000101', 'email' => 'dedi@digitalkreatif.local'],
        ['perusahaan_id' => $companyIds[1], 'nama' => 'Rina Maharani', 'jabatan' => 'IT Support', 'no_telepon' => '081200000102', 'email' => 'rina@teknusantara.local'],
    ];
    $mentorIds = [];
    $findMentor = $db->prepare('SELECT id FROM pembimbing WHERE email = :email');
    $insertMentor = $db->prepare(
        'INSERT INTO pembimbing (perusahaan_id, nama, jabatan, no_telepon, email)
         VALUES (:perusahaan_id, :nama, :jabatan, :no_telepon, :email)'
    );
    foreach ($mentors as $mentor) {
        $findMentor->execute(['email' => $mentor['email']]);
        $id = $findMentor->fetchColumn();
        if ($id === false) {
            $insertMentor->execute($mentor);
            $id = $db->lastInsertId();
        }
        $mentorIds[] = (int) $id;
    }

    $placements = [
        ['siswa_id' => $studentIds[0], 'perusahaan_id' => $companyIds[0], 'pembimbing_id' => $mentorIds[0], 'tanggal_mulai' => date('Y-m-d', strtotime('-20 days')), 'tanggal_selesai' => date('Y-m-d', strtotime('+70 days')), 'status' => 'berjalan', 'catatan' => 'Praktik pengembangan aplikasi web.'],
        ['siswa_id' => $studentIds[1], 'perusahaan_id' => $companyIds[1], 'pembimbing_id' => $mentorIds[1], 'tanggal_mulai' => date('Y-m-d', strtotime('-15 days')), 'tanggal_selesai' => date('Y-m-d', strtotime('+75 days')), 'status' => 'berjalan', 'catatan' => 'Praktik dukungan teknis dan jaringan.'],
        ['siswa_id' => $studentIds[2], 'perusahaan_id' => $companyIds[1], 'pembimbing_id' => $mentorIds[1], 'tanggal_mulai' => date('Y-m-d', strtotime('+5 days')), 'tanggal_selesai' => date('Y-m-d', strtotime('+95 days')), 'status' => 'direncanakan', 'catatan' => 'Penempatan periode berikutnya.'],
    ];
    $placementIds = [];
    $findPlacement = $db->prepare('SELECT id FROM penempatan WHERE siswa_id = :siswa_id AND perusahaan_id = :perusahaan_id AND tanggal_mulai = :tanggal_mulai');
    $insertPlacement = $db->prepare(
        'INSERT INTO penempatan (siswa_id, perusahaan_id, pembimbing_id, tanggal_mulai, tanggal_selesai, status, catatan)
         VALUES (:siswa_id, :perusahaan_id, :pembimbing_id, :tanggal_mulai, :tanggal_selesai, :status, :catatan)'
    );
    foreach ($placements as $placement) {
        $findPlacement->execute([
            'siswa_id' => $placement['siswa_id'],
            'perusahaan_id' => $placement['perusahaan_id'],
            'tanggal_mulai' => $placement['tanggal_mulai'],
        ]);
        $id = $findPlacement->fetchColumn();
        if ($id === false) {
            $insertPlacement->execute($placement);
            $id = $db->lastInsertId();
        }
        $placementIds[] = (int) $id;
    }

    $findJournal = $db->prepare('SELECT id FROM jurnal WHERE penempatan_id = :penempatan_id AND tanggal = :tanggal');
    $insertJournal = $db->prepare(
        'INSERT INTO jurnal (penempatan_id, tanggal, kegiatan, hasil, kendala, status_validasi, catatan_pembimbing)
         VALUES (:penempatan_id, :tanggal, :kegiatan, :hasil, :kendala, :status_validasi, :catatan_pembimbing)'
    );
    $journalSamples = [
        ['penempatan_id' => $placementIds[0], 'tanggal' => date('Y-m-d', strtotime('-1 day')), 'kegiatan' => 'Mempelajari struktur proyek dan membuat rancangan halaman dashboard.', 'hasil' => 'Rancangan tampilan awal selesai.', 'kendala' => 'Perlu menyesuaikan beberapa warna desain.', 'status_validasi' => 'disetujui', 'catatan_pembimbing' => 'Lanjutkan ke tahap implementasi.'],
        ['penempatan_id' => $placementIds[1], 'tanggal' => date('Y-m-d', strtotime('-1 day')), 'kegiatan' => 'Memeriksa koneksi jaringan dan mendata perangkat komputer.', 'hasil' => 'Inventaris perangkat diperbarui.', 'kendala' => null, 'status_validasi' => 'menunggu', 'catatan_pembimbing' => null],
        ['penempatan_id' => $placementIds[0], 'tanggal' => date('Y-m-d'), 'kegiatan' => 'Membuat komponen navigasi dan kartu ringkasan.', 'hasil' => 'Komponen dapat ditampilkan di halaman dashboard.', 'kendala' => null, 'status_validasi' => 'menunggu', 'catatan_pembimbing' => null],
    ];
    foreach ($journalSamples as $journal) {
        $findJournal->execute(['penempatan_id' => $journal['penempatan_id'], 'tanggal' => $journal['tanggal']]);
        if ($findJournal->fetchColumn() === false) {
            $insertJournal->execute($journal);
        }
    }

    $findAttendance = $db->prepare('SELECT id FROM absensi WHERE penempatan_id = :penempatan_id AND tanggal = :tanggal');
    $insertAttendance = $db->prepare(
        'INSERT INTO absensi (penempatan_id, tanggal, jam_masuk, jam_pulang, status, keterangan)
         VALUES (:penempatan_id, :tanggal, :jam_masuk, :jam_pulang, :status, :keterangan)'
    );
    foreach ([$placementIds[0], $placementIds[1]] as $placementId) {
        foreach ([date('Y-m-d', strtotime('-1 day')), date('Y-m-d')] as $date) {
            $findAttendance->execute(['penempatan_id' => $placementId, 'tanggal' => $date]);
            if ($findAttendance->fetchColumn() === false) {
                $insertAttendance->execute([
                    'penempatan_id' => $placementId,
                    'tanggal' => $date,
                    'jam_masuk' => '08:00:00',
                    'jam_pulang' => $date === date('Y-m-d') ? null : '16:00:00',
                    'status' => 'hadir',
                    'keterangan' => null,
                ]);
            }
        }
    }

    $findMonitoring = $db->prepare('SELECT id FROM monitoring WHERE penempatan_id = :penempatan_id AND tanggal = :tanggal');
    $insertMonitoring = $db->prepare(
        'INSERT INTO monitoring (penempatan_id, user_id, tanggal, metode, catatan, evaluasi, tindak_lanjut)
         VALUES (:penempatan_id, :user_id, :tanggal, :metode, :catatan, :evaluasi, :tindak_lanjut)'
    );
    $monitoringSamples = [
        ['penempatan_id' => $placementIds[0], 'user_id' => $adminUserId, 'tanggal' => date('Y-m-d', strtotime('-3 days')), 'metode' => 'kunjungan', 'catatan' => 'Kunjungan awal ke tempat PKL dan perkenalan dengan pembimbing.', 'evaluasi' => 'Siswa beradaptasi dengan baik.', 'tindak_lanjut' => 'Pantau jurnal mingguan.'],
        ['penempatan_id' => $placementIds[1], 'user_id' => $adminUserId, 'tanggal' => date('Y-m-d', strtotime('-2 days')), 'metode' => 'telepon', 'catatan' => 'Konfirmasi kehadiran dan progres kegiatan siswa.', 'evaluasi' => 'Kehadiran baik, kegiatan sesuai rencana.', 'tindak_lanjut' => 'Lakukan monitoring berikutnya bulan depan.'],
    ];
    foreach ($monitoringSamples as $item) {
        $findMonitoring->execute(['penempatan_id' => $item['penempatan_id'], 'tanggal' => $item['tanggal']]);
        if ($findMonitoring->fetchColumn() === false) {
            $insertMonitoring->execute($item);
        }
    }

    $db->commit();
    echo "Data contoh berhasil diisi. Login siswa: siswa1 / siswa123 (tersedia juga siswa2 dan siswa3).\n";
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, 'Gagal mengisi data contoh: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
