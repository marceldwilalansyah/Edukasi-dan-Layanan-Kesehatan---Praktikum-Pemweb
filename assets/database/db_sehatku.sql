-- 1. Tabel Roles (admin, nakes, pengguna)
CREATE TABLE IF NOT EXISTS roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

INSERT IGNORE INTO roles (role_name) VALUES
('admin'),
('nakes'),
('pengguna');

-- 2. Tabel Users
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_user_role
        FOREIGN KEY (role_id)
        REFERENCES roles(role_id)
);

-- 3. Tabel User Profiles (dibutuhkan otomatis saat register)
CREATE TABLE IF NOT EXISTS user_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    nama_lengkap VARCHAR(150) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    no_hp VARCHAR(20) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_profile_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- 4. Tabel Categories
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

INSERT IGNORE INTO categories (category_name) VALUES
('Pola Hidup Sehat'),
('Nutrisi'),
('Aktivitas Fisik'),
('Kesehatan Mental'),
('Tidur'),
('Pencegahan Penyakit');

-- 5. Tabel Articles
CREATE TABLE IF NOT EXISTS articles (
    article_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    author_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('draft', 'terbit') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_article_category
        FOREIGN KEY (category_id)
        REFERENCES categories(category_id),

    CONSTRAINT fk_article_author
        FOREIGN KEY (author_id)
        REFERENCES users(user_id)
);

-- ==========================================
-- TABEL MODUL 4: BOOKMARK ARTIKEL
-- ==========================================
CREATE TABLE IF NOT EXISTS bookmark_artikel (
    bookmark_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    article_id INT NOT NULL,
    tanggal_simpan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unik_user_artikel (user_id, article_id),

    CONSTRAINT fk_bookmark_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_bookmark_artikel
        FOREIGN KEY (article_id)
        REFERENCES articles(article_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================================
-- TABEL MODUL 5: RIWAYAT KALKULATOR (BMI & AIR)
-- ==========================================
CREATE TABLE IF NOT EXISTS riwayat_kalkulator (
    kalkulator_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    berat_badan DECIMAL(5,2) NOT NULL,
    tinggi_badan DECIMAL(5,2) NOT NULL,
    nilai_bmi DECIMAL(5,2) NOT NULL,
    kategori_bmi VARCHAR(50) NOT NULL,
    kebutuhan_air DECIMAL(4,2) NOT NULL,
    tanggal_hitung TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_kalkulator_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================================
-- TABEL MODUL 6: CATATAN KESEHATAN HARIAN
-- ==========================================
CREATE TABLE IF NOT EXISTS catatan_kesehatan (
    catatan_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tanggal_catatan DATE NOT NULL,
    durasi_tidur DECIMAL(4,1) NOT NULL,
    aktivitas_fisik VARCHAR(150) NOT NULL,
    asupan_air DECIMAL(4,2) NOT NULL,
    keluhan TEXT DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_catatan_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kuis (
    kuis_id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pertanyaan_kuis (
    pertanyaan_id INT AUTO_INCREMENT PRIMARY KEY,
    kuis_id INT NOT NULL,
    pertanyaan TEXT NOT NULL,
    opsi_a VARCHAR(255) NOT NULL,
    opsi_b VARCHAR(255) NOT NULL,
    opsi_c VARCHAR(255) NOT NULL,
    opsi_d VARCHAR(255) NOT NULL,
    jawaban_benar ENUM('A', 'B', 'C', 'D') NOT NULL,
    pembahasan TEXT DEFAULT NULL,
    FOREIGN KEY (kuis_id) REFERENCES kuis(kuis_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS riwayat_kuis (
    riwayat_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    kuis_id INT NOT NULL,
    skor INT NOT NULL,
    jumlah_benar INT NOT NULL,
    total_soal INT NOT NULL,
    tanggal_kerjakan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kuis_id) REFERENCES kuis(kuis_id) ON DELETE CASCADE
);

-- Contoh data awal untuk pengujian
INSERT INTO kuis (judul, deskripsi) VALUES
('Kuis Gaya Hidup Sehat', 'Uji pemahaman dasar Anda tentang pola makan, tidur, dan hidrasi harian.');

INSERT INTO pertanyaan_kuis (kuis_id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban_benar, pembahasan) VALUES
(1, 'Berapa jam durasi tidur ideal bagi orang dewasa per malam?', '3 - 4 jam', '5 - 6 jam', '7 - 9 jam', '11 - 12 jam', 'C', 'Orang dewasa membutuhkan 7–9 jam tidur berkualitas setiap malam untuk pemulihan fisik dan fungsi otak.'),
(1, 'Berapa rata-rata anjuran konsumsi air putih harian untuk orang dewasa?', 'Sekitar 2 liter (8 gelas)', '0.5 liter', '5 liter', 'Tidak perlu jika sudah minum kopi', 'A', 'Kebutuhan cairan rata-rata orang dewasa adalah sekitar 2 liter atau 8 gelas air putih per hari.'),
(1, 'Aktivitas fisik ringan seperti jalan cepat sebaiknya dilakukan minimal berapa menit per minggu?', '10 menit', '30 menit', '150 menit', '500 menit', 'C', 'WHO menganjurkan aktivitas fisik intensitas sedang minimal 150 menit per minggu untuk menjaga kesehatan jantung.');

INSERT IGNORE INTO kuis (kuis_id, judul, deskripsi) VALUES
(1, 'Kuis Gaya Hidup Sehat', 'Uji pemahaman dasar Anda tentang pola makan, tidur, dan hidrasi harian.');

INSERT IGNORE INTO pertanyaan_kuis (pertanyaan_id, kuis_id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban_benar, pembahasan) VALUES
(1, 1, 'Berapa jam durasi tidur ideal bagi orang dewasa per malam?', '3 - 4 jam', '5 - 6 jam', '7 - 9 jam', '11 - 12 jam', 'C', 'Orang dewasa membutuhkan 7–9 jam tidur berkualitas setiap malam untuk pemulihan fisik dan fungsi otak.'),
(2, 1, 'Berapa rata-rata anjuran konsumsi air putih harian untuk orang dewasa?', 'Sekitar 2 liter (8 gelas)', '0.5 liter', '5 liter', 'Tidak perlu jika sudah minum kopi', 'A', 'Kebutuhan cairan rata-rata orang dewasa adalah sekitar 2 liter atau 8 gelas air putih per hari.'),
(3, 1, 'Aktivitas fisik ringan seperti jalan cepat sebaiknya dilakukan minimal berapa menit per minggu?', '10 menit', '30 menit', '150 menit', '500 menit', 'C', 'WHO menganjurkan aktivitas fisik intensitas sedang minimal 150 menit per minggu untuk menjaga kesehatan jantung.');