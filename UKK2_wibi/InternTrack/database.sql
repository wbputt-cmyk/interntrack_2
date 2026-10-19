CREATE DATABASE IF NOT EXISTS interntrack
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE interntrack;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(120) NOT NULL,
    username VARCHAR(60) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'siswa', 'guru', 'pembimbing_perusahaan') NOT NULL,
    aktif BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS siswa (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL UNIQUE,
    nis VARCHAR(30) NOT NULL UNIQUE,
    nama VARCHAR(120) NOT NULL,
    kelas VARCHAR(40) NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(25) NULL,
    alamat TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_siswa_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS perusahaan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    bidang_usaha VARCHAR(120) NULL,
    alamat TEXT NOT NULL,
    kota VARCHAR(80) NULL,
    telepon VARCHAR(25) NULL,
    email VARCHAR(150) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pembimbing (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    perusahaan_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL UNIQUE,
    nama VARCHAR(120) NOT NULL,
    jabatan VARCHAR(100) NULL,
    no_telepon VARCHAR(25) NULL,
    email VARCHAR(150) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pembimbing_perusahaan FOREIGN KEY (perusahaan_id)
        REFERENCES perusahaan(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pembimbing_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS penempatan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    siswa_id BIGINT UNSIGNED NOT NULL,
    perusahaan_id BIGINT UNSIGNED NOT NULL,
    pembimbing_id BIGINT UNSIGNED NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    status ENUM('direncanakan', 'berjalan', 'selesai', 'dibatalkan')
        NOT NULL DEFAULT 'direncanakan',
    catatan TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_penempatan_siswa FOREIGN KEY (siswa_id)
        REFERENCES siswa(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_penempatan_perusahaan FOREIGN KEY (perusahaan_id)
        REFERENCES perusahaan(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_penempatan_pembimbing FOREIGN KEY (pembimbing_id)
        REFERENCES pembimbing(id) ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_penempatan_status (status),
    INDEX idx_penempatan_tanggal (tanggal_mulai, tanggal_selesai)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS jurnal (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    penempatan_id BIGINT UNSIGNED NOT NULL,
    tanggal DATE NOT NULL,
    kegiatan TEXT NOT NULL,
    hasil TEXT NULL,
    kendala TEXT NULL,
    status_validasi ENUM('menunggu', 'disetujui', 'revisi')
        NOT NULL DEFAULT 'menunggu',
    catatan_pembimbing TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_jurnal_penempatan FOREIGN KEY (penempatan_id)
        REFERENCES penempatan(id) ON UPDATE CASCADE ON DELETE CASCADE,
    UNIQUE KEY uq_jurnal_penempatan_tanggal (penempatan_id, tanggal),
    INDEX idx_jurnal_validasi (status_validasi)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS absensi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    penempatan_id BIGINT UNSIGNED NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME NULL,
    jam_pulang TIME NULL,
    status ENUM('hadir', 'izin', 'sakit', 'alpa')
        NOT NULL DEFAULT 'hadir',
    keterangan TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_absensi_penempatan FOREIGN KEY (penempatan_id)
        REFERENCES penempatan(id) ON UPDATE CASCADE ON DELETE CASCADE,
    UNIQUE KEY uq_absensi_penempatan_tanggal (penempatan_id, tanggal),
    INDEX idx_absensi_tanggal_status (tanggal, status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS monitoring (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    penempatan_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    tanggal DATE NOT NULL,
    metode ENUM('kunjungan', 'telepon', 'daring') NOT NULL DEFAULT 'kunjungan',
    catatan TEXT NOT NULL,
    evaluasi TEXT NULL,
    tindak_lanjut TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_monitoring_penempatan FOREIGN KEY (penempatan_id)
        REFERENCES penempatan(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_monitoring_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_monitoring_tanggal (tanggal)
) ENGINE=InnoDB;
