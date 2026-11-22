-- ==========================================
-- UPDATE DATABASE UNTUK MODUL 7 - 9
-- Jalankan file ini jika database `sehatku` sudah dibuat dari modul 1 - 6.
-- (Kalau membuat database baru, cukup import db_sehatku.sql saja.)
-- ==========================================

-- Kolom profil yang dipakai kode tetapi belum ada di skema awal.
-- Sintaks IF NOT EXISTS didukung MariaDB (XAMPP). Di MySQL biasa, hapus
-- baris kolom yang sudah ada lalu jalankan sisanya.
ALTER TABLE user_profiles
    ADD COLUMN IF NOT EXISTS tinggi DECIMAL(5,2) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS berat DECIMAL(5,2) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS tanggal_lahir DATE DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS spesialisasi VARCHAR(150) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS bio TEXT DEFAULT NULL;

-- ==========================================
-- TABEL MODUL 7: KONSULTASI
-- ==========================================

-- Jadwal praktik yang dibuat tenaga kesehatan (nakes)
CREATE TABLE IF NOT EXISTS jadwal_praktik (
    jadwal_id INT AUTO_INCREMENT PRIMARY KEY,
    nakes_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    status ENUM('tersedia', 'dibooking') NOT NULL DEFAULT 'tersedia',
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_jadwal_nakes_tanggal (nakes_id, tanggal),

    CONSTRAINT fk_jadwal_nakes
        FOREIGN KEY (nakes_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- Permintaan konsultasi (booking) dari pengguna ke nakes.
-- tanggal & jam disalin dari jadwal supaya riwayat tetap utuh
-- walaupun nakes menghapus jadwalnya.
CREATE TABLE IF NOT EXISTS konsultasi (
    konsultasi_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nakes_id INT NOT NULL,
    jadwal_id INT DEFAULT NULL,
    tanggal_konsultasi DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    keluhan TEXT NOT NULL,
    status ENUM('menunggu', 'dikonfirmasi', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'menunggu',
    catatan_nakes TEXT DEFAULT NULL,
    dibatalkan_oleh ENUM('pengguna', 'nakes') DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_konsultasi_nakes_status (nakes_id, status),
    INDEX idx_konsultasi_user (user_id, status),

    CONSTRAINT fk_konsultasi_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_konsultasi_nakes
        FOREIGN KEY (nakes_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_konsultasi_jadwal
        FOREIGN KEY (jadwal_id)
        REFERENCES jadwal_praktik(jadwal_id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==========================================
-- TABEL MODUL 8: NOTIFIKASI & PENGINGAT
-- ==========================================

CREATE TABLE IF NOT EXISTS notifikasi (
    notifikasi_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    jenis ENUM('booking', 'konsultasi', 'badge', 'sistem') NOT NULL DEFAULT 'sistem',
    judul VARCHAR(150) NOT NULL,
    pesan TEXT NOT NULL,
    tautan VARCHAR(255) DEFAULT NULL,
    sudah_dibaca TINYINT(1) NOT NULL DEFAULT 0,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_notifikasi_user (user_id, sudah_dibaca, dibuat_pada),

    CONSTRAINT fk_notifikasi_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- Satu pengingat per jenis untuk tiap pengguna
CREATE TABLE IF NOT EXISTS pengingat (
    pengingat_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    jenis ENUM('air', 'tidur', 'olahraga') NOT NULL,
    jam TIME NOT NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    terakhir_ditutup DATE DEFAULT NULL,

    UNIQUE KEY unik_user_jenis (user_id, jenis),

    CONSTRAINT fk_pengingat_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;
