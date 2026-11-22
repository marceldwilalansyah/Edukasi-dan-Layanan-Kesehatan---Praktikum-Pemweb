# 🌿 SehatKu — Ruang Sehatmu

> *Sehat lebih mudah, informasi lebih terpercaya.*

**SehatKu** adalah aplikasi web edukasi dan monitoring kesehatan berbasis **PHP native + MySQL**. Pengguna bisa membaca artikel kesehatan, menghitung BMI, mencatat kebiasaan harian, mengerjakan kuis, mengatur pengingat, dan berkonsultasi dengan tenaga kesehatan (nakes). Aplikasi ini mendukung **SDG 3 (Kesehatan & Kesejahteraan)** dan **SDG 4 (Pendidikan)**.

---

## 📑 Daftar Isi

1. [Gambaran Umum](#1-gambaran-umum)
2. [Teknologi & Struktur Folder](#2-teknologi--struktur-folder)
3. [Instalasi](#3-instalasi)
4. [Cara Pakai: Registrasi, Login, Logout](#4-cara-pakai-registrasi-login-logout)
5. [Panduan Peran Pengguna](#5-panduan-peran-pengguna)
6. [Panduan Peran Tenaga Kesehatan (Nakes)](#6-panduan-peran-tenaga-kesehatan-nakes)
7. [Panduan Peran Admin](#7-panduan-peran-admin)
8. [Skenario Uji Coba Semua Fitur](#8-skenario-uji-coba-semua-fitur)
9. [Aturan & Perhitungan di Dalam Aplikasi](#9-aturan--perhitungan-di-dalam-aplikasi)
10. [Struktur Database](#10-struktur-database)
11. [Keamanan & Catatan Penting](#11-keamanan--catatan-penting)
12. [Troubleshooting](#12-troubleshooting)

---

## 1. Gambaran Umum

SehatKu memiliki **3 peran (role)**. Setiap peran punya dashboard dan menu sendiri, dan halaman peran lain tidak bisa dibuka (akan dialihkan ke beranda).

| Peran | Cara mendapatkan akun | Fungsi utama |
|---|---|---|
| **Pengguna** | Daftar sendiri lewat halaman Register | Artikel, kalkulator BMI, kuis, tracker harian, konsultasi, pengingat |
| **Nakes** (tenaga kesehatan) | Dibuatkan oleh Admin | Mengatur jadwal praktik dan menangani permintaan konsultasi |
| **Admin** | Dibuat manual lewat database (lihat [Instalasi](#34-membuat-akun-admin-pertama)) | Mengelola akun, nakes, artikel, kategori, dan kuis |

### Daftar fitur singkat

| Modul | Fitur | Untuk |
|---|---|---|
| Autentikasi | Register, login, logout, auto-logout 15 menit tanpa aktivitas | Semua |
| Dashboard | Progres harian, grafik 7 hari, streak, badge terbaru, konsultasi terdekat | Pengguna |
| Dashboard | Statistik akun, konsultasi, artikel terbit, artikel terpopuler | Admin |
| Dashboard | Jadwal hari ini, permintaan menunggu, jumlah slot | Nakes |
| Artikel | Cari, filter kategori, paginasi, baca detail, bookmark | Pengguna |
| Artikel & Kategori | Tambah, edit, hapus, draft/terbit | Admin |
| Kalkulator | Hitung BMI dan kebutuhan air, simpan riwayat | Pengguna |
| Tracker | Catatan tidur, aktivitas fisik, air, dan keluhan harian + filter tanggal | Pengguna |
| Kuis | Kerjakan kuis, skor, pembahasan, badge | Pengguna |
| Kuis | Buat kuis, tambah/hapus soal | Admin |
| Konsultasi | Pilih nakes, booking jadwal, batalkan booking | Pengguna |
| Konsultasi | Konfirmasi, selesaikan, atau batalkan permintaan | Nakes |
| Jadwal praktik | Tambah dan hapus slot praktik | Nakes |
| Notifikasi | Lonceng 🔔 dengan penghitung belum dibaca | Pengguna & Nakes |
| Pengingat | Pengingat minum air, tidur, olahraga | Pengguna |
| Manajemen akun | CRUD pengguna dan nakes | Admin |
| Profil | Nama, tinggi, berat, tanggal lahir, ganti password | Pengguna |

---

## 2. Teknologi & Struktur Folder

**Teknologi:** PHP (native, MySQLi + prepared statement), MySQL / MariaDB, HTML + CSS + JavaScript sederhana. Berjalan di XAMPP / Laragon / WAMP.

```
sehatku/
├── index.php                 # Beranda setelah login (mengarahkan ke dashboard sesuai peran)
├── koneksi.php               # Konfigurasi koneksi database
├── session.php               # Session + timeout 15 menit
│
├── autentikasi/              # login, register, logout (+ file proses)
├── include/
│   ├── auth_check.php        # Wajib login + fungsi cekRole()
│   ├── header.php            # Navigasi per peran, lonceng, banner pengingat
│   ├── footer.php
│   └── fungsi.php            # Fungsi bantu: tanggal, notifikasi, badge, progres, streak
│
├── admin/                    # dashboard, user/, nakes/, artikel/, kuis/
├── kategori/                 # CRUD kategori artikel (khusus admin)
├── nakes/                    # dashboard, jadwal/, konsultasi/, profile.php
├── pengguna/                 # dashboard, artikel/, kalkulator/, catatan/ (tracker),
│                             # kuis/, konsultasi/, pengingat/, profile.php
├── notifikasi/               # daftar, buka, tandai semua dibaca
│
└── assets/
    ├── css/style.css
    ├── js/script.js          # alert otomatis hilang + konfirmasi aksi berbahaya
    └── database/
        ├── db_sehatku.sql            # Skema lengkap + data awal (pakai ini untuk instalasi baru)
        ├── update_modul_7_9.sql      # Hanya untuk database lama (modul 1–6)
        └── seed_data.php             # Data contoh opsional: 4 perawat + 10 artikel
```

---

## 3. Instalasi

### 3.1 Prasyarat
- XAMPP / Laragon / WAMP (PHP 8+ dan MySQL/MariaDB)
- Browser modern

### 3.2 Letakkan folder proyek
Salin folder proyek ke `htdocs` (XAMPP) atau `www` (Laragon/WAMP) dan **pastikan namanya tepat `sehatku`**.

> ⚠️ Nama folder harus `sehatku` karena beberapa jalur di kode memakai awalan tetap `/sehatku/...`.

```
C:\xampp\htdocs\sehatku\
```

### 3.3 Buat database dan import
1. Jalankan **Apache** dan **MySQL** dari XAMPP Control Panel.
2. Buka `http://localhost/phpmyadmin`.
3. Buat database baru bernama **`sehatku`** (collation `utf8mb4_general_ci` atau `utf8mb4_unicode_ci`).
4. Pilih database `sehatku` → tab **Import** → pilih `assets/database/db_sehatku.sql` → **Go**.

File ini sudah berisi semua tabel dan data awal:
- 3 peran: `admin`, `nakes`, `pengguna`
- 6 kategori artikel: Pola Hidup Sehat, Nutrisi, Aktivitas Fisik, Kesehatan Mental, Tidur, Pencegahan Penyakit
- 1 kuis contoh: *Kuis Gaya Hidup Sehat* (3 soal)

> Pakai `update_modul_7_9.sql` **hanya** jika database lama dibuat dari versi modul 1–6 dan belum punya tabel konsultasi, notifikasi, dan pengingat. Untuk instalasi baru cukup `db_sehatku.sql`.

Konfigurasi koneksi ada di `koneksi.php`. Bawaannya cocok untuk XAMPP:

```php
$host     = "localhost";
$user     = "root";
$password = "";
$database = "sehatku";
```

### 3.4 Membuat akun admin pertama

Database **tidak menyertakan akun admin**, dan halaman Register hanya membuat akun *pengguna*. Pilih salah satu cara berikut.

**Opsi A — Naikkan akun yang sudah didaftarkan (paling mudah)**
1. Buka `http://localhost/sehatku/autentikasi/register.php` dan daftar, misalnya username `admin`.
2. Di phpMyAdmin → tab **SQL**, jalankan:

```sql
UPDATE users
SET role_id = (SELECT role_id FROM roles WHERE role_name = 'admin')
WHERE username = 'admin';
```

**Opsi B — Buat langsung dengan password ter-hash**
1. Buat hash password lewat terminal:
   ```bash
   php -r "echo password_hash('PasswordKuat123', PASSWORD_DEFAULT);"
   ```
2. Jalankan SQL ini (ganti `HASIL_HASH`):
   ```sql
   INSERT INTO users (role_id, username, password)
   SELECT role_id, 'admin', 'HASIL_HASH' FROM roles WHERE role_name = 'admin';

   INSERT INTO user_profiles (user_id, nama_lengkap)
   VALUES (LAST_INSERT_ID(), 'Administrator');
   ```

### 3.5 Mengisi data contoh: perawat dan artikel (opsional)

Supaya aplikasi langsung terlihat hidup, tersedia skrip `assets/database/seed_data.php` yang menambahkan **4 akun perawat** dan **10 artikel terbit**. Jalankan **setelah** import database (dan sebaiknya setelah akun admin dibuat, agar admin tercatat sebagai penulis artikel).

```bash
# Dari folder proyek (terminal)
php assets/database/seed_data.php
```

Atau buka lewat browser: `http://localhost/sehatku/assets/database/seed_data.php`

Skrip aman dijalankan berulang kali karena data yang sudah ada dilewati. **Hapus file ini setelah dipakai.**

**Akun perawat contoh** (password semuanya: `perawat123`, segera ganti lewat menu Nakes di akun Admin):

| Username | Nama | Spesialisasi |
|---|---|---|
| `perawat.rina` | Ns. Rina Kusuma, S.Kep | Perawat Umum |
| `perawat.budi` | Ns. Budi Santoso, S.Kep | Perawat Komunitas |
| `perawat.dewi` | Ns. Dewi Lestari, S.Kep | Perawat Ibu dan Anak |
| `perawat.fauzi` | Ns. Ahmad Fauzi, S.Kep | Perawat Gawat Darurat |

**Artikel contoh** (semua berstatus *terbit*):

| Kategori | Judul |
|---|---|
| Nutrisi | Panduan Gizi Seimbang: Isi Piringku untuk Sehari-hari |
| Nutrisi | Mengenal Gula, Garam, dan Lemak: Batas Konsumsi Harian |
| Nutrisi | Minum Air Putih: Berapa Banyak yang Dibutuhkan Tubuh? |
| Tidur | Tips Tidur Nyenyak: Kebiasaan Kecil yang Berdampak Besar |
| Aktivitas Fisik | 150 Menit Seminggu: Cara Mudah Memulai Rutinitas Olahraga |
| Kesehatan Mental | Mengelola Stres Sehari-hari dengan Cara Sederhana |
| Pencegahan Penyakit | Cuci Tangan Pakai Sabun: Langkah Kecil Pencegah Banyak Penyakit |
| Pencegahan Penyakit | Cek Kesehatan Berkala: Apa Saja yang Perlu Diperiksa? |
| Pola Hidup Sehat | Membangun Kebiasaan Sehat Tanpa Merasa Terbebani |
| Pola Hidup Sehat | Mengurangi Kelelahan Mata dengan Aturan 20-20-20 |

> Perawat contoh belum punya jadwal praktik. Agar muncul tombol booking di sisi pengguna, login sebagai perawat lalu tambah jadwal di menu **Jadwal**.

### 3.6 Buka aplikasi
```
http://localhost/sehatku/
```
Jika belum login, Anda otomatis diarahkan ke halaman Login.

---

## 4. Cara Pakai: Registrasi, Login, Logout

### 4.1 Registrasi (khusus Pengguna)
1. Di halaman Login, klik **Daftar** (atau buka `autentikasi/register.php`).
2. Isi **Username**, **Password**, dan **Konfirmasi Password**.
3. Klik **Daftar**.

Aturan validasi:
- Password dan konfirmasi harus sama.
- Password minimal **6 karakter**.
- Username harus unik.

Jika berhasil, Anda diarahkan ke halaman Login dengan pesan *"Registrasi berhasil"*. Akun otomatis berperan **pengguna**, dan profil kosongnya dibuat otomatis.

### 4.2 Login
1. Buka `autentikasi/login.php`.
2. Masukkan **Username** dan **Password**, lalu klik **Login**.
3. Setelah berhasil, Anda masuk ke **Beranda** yang menampilkan nama, peran, dan tombol menuju dashboard sesuai peran:
   - Pengguna → **Dashboard Pengguna**
   - Nakes → **Dashboard Nakes**
   - Admin → **Dashboard Admin**

Pesan galat yang mungkin muncul:

| Pesan | Penyebab |
|---|---|
| *Username atau password salah* | Kredensial tidak cocok |
| *Akun tidak aktif* | Status akun `nonaktif` (diatur Admin) |
| *Session telah berakhir. Silakan login kembali.* | Tidak ada aktivitas selama 15 menit |

### 4.3 Logout
Klik ikon **↗** di pojok kanan atas, atau tombol **Logout** di beranda/dashboard. Anda kembali ke Login dengan pesan *"Berhasil logout"*.

### 4.4 Navigasi menurut peran

| Peran | Menu di bilah atas |
|---|---|
| Pengguna | Beranda · Artikel · Kalkulator · Kuis · Tracker · Konsultasi · Pengingat (+ 🔔 Notifikasi) |
| Nakes | Dashboard · Jadwal · Konsultasi · Profil (+ 🔔 Notifikasi) |
| Admin | Dashboard · Pengguna · Nakes · Artikel · Kategori · Kuis |

Halaman **Profil Saya** (pengguna) dibuka lewat tombol di Dashboard Pengguna.

---

## 5. Panduan Peran Pengguna

> Login sebagai **pengguna** → Dashboard Pengguna.

### 5.1 Dashboard Pengguna
Berisi ringkasan kondisi hari ini:
- **Progres Hari Ini** — lingkaran persen dari 3 target: tidur ≥ 7 jam, minum ≥ 2 liter, dan olahraga tercatat.
- **Grafik 7 Hari Terakhir** — batang persentase target tercapai per hari.
- **Streak Pencatatan** 🔥 — jumlah hari berturut-turut Anda mengisi catatan.
- **Badge Terbaru** — badge dari kuis terakhir (atau ajakan mengerjakan kuis).
- **Konsultasi** — konsultasi terdekat yang aktif (atau tombol *Buat Konsultasi*).
- Tombol pintasan ke Tracker, Artikel, Kalkulator, Profil, dan Logout.

### 5.2 Melengkapi Profil
1. Dashboard → **Profil Saya**.
2. Isi **Nama Lengkap**, **Tinggi**, **Berat**, **Tanggal Lahir**.
3. Isi **Password Baru** hanya jika ingin mengganti password (kosongkan jika tidak).
4. Klik **Simpan**.

> Username tidak bisa diubah oleh pengguna. Nama lengkap akan dipakai sebagai nama tampil di konsultasi.

### 5.3 Artikel Edukasi
1. Menu **Artikel**.
2. **Cari** berdasarkan kata kunci (mencari di judul dan isi) dan/atau **filter kategori**, lalu klik **Cari**. Klik **Reset** untuk menghapus filter.
3. Daftar tampil **5 artikel per halaman** dengan navigasi halaman.
4. Klik **Baca Selengkapnya** untuk membuka detail artikel (judul, kategori, penulis, tanggal, isi).
5. Klik **☆ Simpan ke Bookmark** untuk menyimpan, atau **★ Hapus dari Bookmark** untuk melepas.
6. Lihat semua artikel tersimpan lewat tombol **★ Artikel Tersimpan (Bookmark)**.

> Hanya artikel berstatus **terbit** yang terlihat oleh pengguna.

### 5.4 Kalkulator Kesehatan (BMI & Kebutuhan Cairan)
1. Menu **Kalkulator**.
2. Isi **Berat Badan (kg)** dan **Tinggi Badan (cm)**.
3. Klik **Hitung & Simpan**.
4. Hasil menampilkan **nilai BMI**, **kategori**, dan **estimasi kebutuhan cairan per hari (liter)**.
5. Setiap perhitungan tersimpan di tabel **Riwayat Perhitungan**. Klik **Hapus** untuk membuang satu riwayat.

### 5.5 Tracker / Catatan Kesehatan Harian
**Tambah catatan**
1. Menu **Tracker** → **+ Tambah Catatan Harian**.
2. Isi **Tanggal** (bawaan hari ini), **Durasi Tidur** (jam, kelipatan 0,5), **Aktivitas Fisik** (misal *Jalan pagi 30 menit*), **Asupan Air** (liter), dan **Keluhan** (opsional).
3. Klik **Simpan Catatan**.

**Lihat, filter, ubah, hapus**
- Gunakan **Filter Rentang Tanggal** (dari/sampai) lalu **Filter**; **Reset** untuk kembali.
- Klik **Edit** untuk mengubah, **Hapus** untuk menghapus (ada konfirmasi).

> Catatan Anda langsung memengaruhi progres di dashboard, streak, dan pengingat. Mengisi aktivitas dengan kata seperti *"tidak ada"*, *"-"*, atau *"0"* **tidak** dihitung sebagai olahraga.

### 5.6 Kuis Kesehatan & Badge
1. Menu **Kuis** → pilih kuis → **Mulai Kuis**.
2. Jawab semua soal pilihan ganda (A–D). Semua soal wajib dijawab.
3. Klik kirim. Anda akan melihat:
   - Jumlah benar, **skor (0–100)**, dan **badge**
   - **Pembahasan** tiap soal beserta kunci jawaban
4. Klik **Ulangi Kuis** untuk mencoba lagi, atau **Lihat Riwayat Kuis**.
5. Semua hasil tercatat di **Riwayat Pengerjaan Kuis**.

| Skor | Badge |
|---|---|
| 80 – 100 | 🏆 Duta Sehat |
| 60 – 79 | 🥈 Pejuang Sehat |
| 0 – 59 | 🌱 Pemula Sehat |

Saat pertama kali meraih tingkat badge tertentu, Anda mendapat **notifikasi "Badge baru!"**.

### 5.7 Konsultasi dengan Nakes
**Membuat booking**
1. Menu **Konsultasi** → **+ Buat Konsultasi Baru** (atau tombol *Buat Konsultasi* di dashboard).
2. Cari nakes berdasarkan nama/spesialisasi. Setiap kartu menampilkan spesialisasi, bio, dan jumlah jadwal tersedia.
3. Klik **Lihat Jadwal & Booking** pada nakes yang punya jadwal.
4. Pilih **satu jadwal** yang tersedia, tulis **keluhan** (wajib), lalu kirim.
5. Muncul pesan *"Booking terkirim. Tunggu konfirmasi dari nakes."* dan nakes mendapat notifikasi.

**Memantau dan membatalkan**
- Halaman **Konsultasi** memisahkan **Konsultasi Aktif** (menunggu/dikonfirmasi) dan **Riwayat** (selesai/dibatalkan).
- Status: **Menunggu → Dikonfirmasi → Selesai**, atau **Dibatalkan**.
- Setelah nakes menanggapi, **catatan dari nakes** (saran, hasil, atau alasan pembatalan) tampil di kartu konsultasi.
- Klik **Batalkan Booking** pada konsultasi aktif. Jadwal dibuka kembali untuk pengguna lain dan nakes diberi tahu.

Aturan booking: tidak boleh memilih jadwal yang sudah lewat atau sudah dibooking, dan tidak boleh bertabrakan dengan konsultasi aktif Anda di jam yang sama.

### 5.8 Notifikasi 🔔
- Ikon lonceng menampilkan **jumlah notifikasi belum dibaca**.
- Klik notifikasi untuk membukanya. Otomatis ditandai dibaca dan membawa Anda ke halaman terkait.
- Klik **Tandai semua dibaca** untuk menandai semuanya sekaligus.
- Menampilkan maksimal **50 notifikasi terbaru**.

Jenis notifikasi: 📅 booking · 🩺 konsultasi · 🏅 badge · 🔔 sistem.

### 5.9 Pengingat Kebiasaan Sehat
1. Menu **Pengingat**.
2. Atur **jam** dan centang **Aktif** untuk tiap jenis, lalu **Simpan Pengaturan**.

| Jenis | Jam bawaan di form | Pesan |
|---|---|---|
| 💧 Minum air | 10:00 | Waktunya minum air. Targetmu hari ini 2 liter. |
| 🌙 Tidur | 22:00 | Waktunya bersiap tidur supaya istirahatmu cukup. |
| 🏃 Olahraga | 17:00 | Waktunya bergerak. Aktivitas ringan pun sudah cukup. |

Cara kerjanya:
- Pengingat belum aktif sebelum Anda menyimpannya dan mencentang **Aktif**.
- Setelah lewat jam yang ditentukan, **banner pengingat** muncul di bagian atas semua halaman Anda.
- Klik **Catat** untuk langsung membuat catatan, atau **✕** untuk menutupnya untuk hari ini.
- Pengingat **air** hilang otomatis setelah target 2 liter tercapai. Pengingat **olahraga** hilang setelah aktivitas dicatat.

---

## 6. Panduan Peran Tenaga Kesehatan (Nakes)

> Akun nakes **dibuat oleh Admin** (lihat [7.3](#73-kelola-tenaga-kesehatan)). Login memakai username dan password dari Admin.

### 6.1 Dashboard Nakes
- Kartu statistik: **Menunggu respons**, **Jadwal hari ini**, **Sudah dikonfirmasi**, **Slot kosong** (plus jumlah konsultasi selesai).
- **Jadwal Hari Ini** — slot praktik hari ini beserta nama pasien/statusnya.
- **Permintaan Menunggu** — hingga 5 permintaan terbaru dengan tombol **Tinjau**.

### 6.2 Mengatur Jadwal Praktik
1. Menu **Jadwal**.
2. Di **Tambah Jadwal**, isi **Tanggal**, **Jam Mulai**, **Jam Selesai** → **+ Tambah Jadwal**.
3. Aturan: harus di waktu yang akan datang, jam selesai harus lebih besar dari jam mulai, dan tidak boleh tumpang tindih dengan jadwal lain di tanggal yang sama.
4. **Jadwal Mendatang** menampilkan semua slot dengan status **Tersedia** atau **Dibooking** beserta nama pasien.
5. Slot **tersedia** bisa **Hapus**. Slot yang sudah dibooking tidak bisa dihapus. Gunakan **Lihat** untuk membuka detail konsultasinya.

### 6.3 Menangani Permintaan Konsultasi
1. Menu **Konsultasi**. Gunakan tab **Semua / Menunggu / Dikonfirmasi / Selesai / Dibatalkan**.
2. Klik **Tinjau Permintaan** (atau **Lihat Detail**) untuk membuka keluhan pasien.
3. Di bagian **Tindak Lanjut**, tulis **Catatan untuk pasien** lalu pilih aksi:

| Status saat ini | Aksi tersedia | Catatan |
|---|---|---|
| Menunggu | **Konfirmasi** | Catatan opsional |
| Menunggu | **Batalkan** | Catatan **wajib** (alasan) |
| Dikonfirmasi | **Tandai Selesai** | Catatan **wajib** (saran/hasil) |
| Dikonfirmasi | **Batalkan** | Catatan **wajib** (alasan) |

Setiap aksi otomatis mengirim **notifikasi ke pasien**. Jika dibatalkan, jadwal dibuka kembali agar bisa dipilih pengguna lain.

### 6.4 Profil & Notifikasi
- **Profil** menampilkan username, nama, spesialisasi, dan bio. Data ini **dikelola oleh Admin** (hanya bisa dilihat oleh nakes).
- 🔔 Notifikasi memberi tahu saat ada **booking baru** atau **pasien membatalkan**.

---

## 7. Panduan Peran Admin

> Login sebagai **admin** → Dashboard Admin.

### 7.1 Dashboard Admin
- **Total akun**, **Konsultasi** (dan yang menunggu respons), **Artikel terbit**.
- Grafik batang **Pengguna per Peran** dan **Status Konsultasi**.
- **Artikel Terpopuler** — 5 artikel dengan bookmark terbanyak.
- Tombol pintasan ke semua menu pengelolaan.

### 7.2 Kelola Pengguna
Menu **Pengguna** menampilkan semua akun (semua peran).
- **+ Tambah Pengguna** — isi username, password, nama lengkap, dan pilih role.
- **Edit** — ubah username, nama, role, **status (aktif/nonaktif)**, dan password baru (kosongkan jika tidak diganti).
- **Hapus** — menghapus akun (dengan konfirmasi). Admin tidak bisa menghapus akunnya sendiri.

> Mengubah status menjadi **nonaktif** membuat pengguna tidak bisa login.

### 7.3 Kelola Tenaga Kesehatan
Menu **Nakes** untuk akun yang tampil di menu Konsultasi pengguna.
1. Klik **+ Tambah Tenaga Kesehatan**.
2. Isi **Username**, **Password** (min. 6), **Nama Lengkap**, **Spesialisasi** (misal *Dokter Umum*, *Ahli Gizi*), dan **Bio**.
3. **Simpan**. Berikan username dan password tersebut kepada nakes.

Daftar nakes menampilkan spesialisasi, jumlah **jadwal mendatang**, **total konsultasi**, dan status akun.
- **Edit** — ubah nama, spesialisasi, bio, password baru, dan status (username tidak bisa diubah).
- **Hapus** — menghapus nakes **beserta jadwal dan riwayat konsultasinya**.

### 7.4 Kelola Artikel
Menu **Artikel**.
1. **+ Tambah Artikel** → isi **Judul**, pilih **Kategori**, tulis **Isi Artikel**, dan atur **Status**:
   - **Draft** — belum terlihat pengguna
   - **Terbit** — tampil di halaman Artikel pengguna
2. Ingin kategori baru? Pilih **+ Lainnya** di dropdown kategori, lalu ketik nama kategorinya. Kategori otomatis tersimpan dan dipakai ulang.
3. **Edit** untuk mengubah judul/kategori/isi/status, **Hapus** untuk menghapus (dengan konfirmasi).

### 7.5 Kelola Kategori
Menu **Kategori** → **+ Tambah Kategori**, **Edit**, atau **Hapus**.

### 7.6 Kelola Kuis
Menu **Kuis**.
1. **+ Tambah Kuis Baru** → isi **Judul** dan **Deskripsi** → *Simpan & Lanjut Tambah Soal*.
2. Di halaman **Kelola Soal**, isi: **Pertanyaan**, **Opsi A–D**, **Kunci Jawaban Benar**, dan **Pembahasan** (ditampilkan ke pengguna setelah selesai mengerjakan).
3. Klik **+ Simpan Soal**. Ulangi untuk soal berikutnya.
4. **Hapus Soal** untuk membuang satu soal. Dari daftar kuis, gunakan **Edit** (judul/deskripsi) atau **Hapus** (menghapus kuis beserta semua soalnya).

> Kuis yang belum punya soal tampil sebagai *"Soal belum tersedia"* di sisi pengguna.

---

## 8. Skenario Uji Coba Semua Fitur

Urutan ini membuat semua fitur terhubung dan bisa dicoba dalam sekali jalan:

1. **Admin** login → **Nakes** → tambah satu nakes (misal `dr.sinta`), atau pakai perawat contoh dari [3.5](#35-mengisi-data-contoh-perawat-dan-artikel-opsional).
2. **Admin** → **Artikel** → tambah artikel berstatus **Terbit** (atau pakai 10 artikel contoh); **Kuis** → tambah soal bila perlu.
3. **Nakes** login (misal `dr.sinta` atau `perawat.rina`) → **Jadwal** → tambah slot praktik di waktu mendatang.
4. **Pengguna** daftar lewat Register → login → lengkapi **Profil Saya**.
5. **Pengguna** → **Artikel**: cari, buka detail, simpan ke bookmark.
6. **Pengguna** → **Kalkulator**: hitung BMI. **Tracker**: tambah catatan hari ini. Cek dashboard: progres, grafik, dan streak ikut berubah.
7. **Pengguna** → **Kuis**: kerjakan kuis, lihat pembahasan, dan dapatkan badge (notifikasi muncul).
8. **Pengguna** → **Pengingat**: aktifkan pengingat dengan jam yang sudah lewat, lalu buka halaman lain untuk melihat banner.
9. **Pengguna** → **Konsultasi** → pilih nakes → booking slot dengan keluhan.
10. **Nakes** → 🔔 (ada booking baru) → **Konsultasi** → **Tinjau** → **Konfirmasi**.
11. **Pengguna** → 🔔 (konsultasi dikonfirmasi). Setelah itu **Nakes** → **Tandai Selesai** dengan catatan, dan pengguna melihat catatannya di halaman Konsultasi.
12. **Admin** → **Dashboard**: lihat statistik dan artikel terpopuler terisi.

---

## 9. Aturan & Perhitungan di Dalam Aplikasi

**BMI** = berat (kg) ÷ (tinggi dalam meter)²

| Nilai BMI | Kategori |
|---|---|
| < 18,5 | Kurus |
| 18,5 – 24,9 | Normal |
| 25 – 29,9 | Gemuk |
| ≥ 30 | Obesitas |

**Kebutuhan air harian** = berat badan (kg) × 0,035 liter

**Target harian** (dasar progres dan pengingat): tidur **7 jam**, air **2 liter**, dan **olahraga tercatat**. Setiap target tercapai bernilai ⅓ (≈ 33%) progres harian. Jika satu tanggal punya beberapa catatan, air **dijumlahkan**, tidur diambil yang **terbesar**, dan olahraga cukup **salah satu** tercatat.

**Streak**: jumlah hari berturut-turut yang punya minimal satu catatan. Jika hari ini belum diisi, hitungan dimulai dari kemarin.

**Sesi**: otomatis berakhir setelah **15 menit** tanpa aktivitas.

---

## 10. Struktur Database

Database `sehatku` (utf8mb4, zona waktu aplikasi `Asia/Jakarta`):

| Tabel | Fungsi |
|---|---|
| `roles` | Peran: admin, nakes, pengguna |
| `users` | Akun (username, password hash, role, status aktif/nonaktif) |
| `user_profiles` | Nama, email, no. HP, alamat, tinggi, berat, tanggal lahir, spesialisasi, bio |
| `categories` | Kategori artikel |
| `articles` | Artikel (draft/terbit) |
| `bookmark_artikel` | Artikel yang disimpan pengguna |
| `riwayat_kalkulator` | Riwayat BMI dan kebutuhan air |
| `catatan_kesehatan` | Catatan harian: tidur, aktivitas, air, keluhan |
| `kuis`, `pertanyaan_kuis`, `riwayat_kuis` | Kuis, soal, dan hasil pengerjaan |
| `jadwal_praktik` | Slot praktik nakes (tersedia/dibooking) |
| `konsultasi` | Booking konsultasi (menunggu/dikonfirmasi/selesai/dibatalkan) |
| `notifikasi` | Notifikasi (booking, konsultasi, badge, sistem) |
| `pengingat` | Pengaturan pengingat air/tidur/olahraga per pengguna |

---

## 11. Keamanan & Catatan Penting

**Sudah diterapkan**
- Password disimpan ter-hash (`password_hash` / `password_verify`).
- Query memakai *prepared statement* (mencegah SQL injection).
- Output di-escape (`htmlspecialchars`) untuk mencegah XSS.
- `session_regenerate_id` saat login dan timeout sesi 15 menit.
- Pembatasan akses per peran di setiap halaman (`cekRole`).
- Data pengguna dibatasi per akun (misalnya catatan, riwayat, dan bookmark hanya milik sendiri).

**Yang perlu diperhatikan sebelum dipakai di server publik**
- Ganti kredensial database bawaan (`root` tanpa password) di `koneksi.php`.
- Jalur `/sehatku` tertulis tetap di beberapa file, jadi folder harus bernama `sehatku`.
- Belum ada token CSRF, dan beberapa aksi hapus admin (pengguna, artikel, kategori) memakai tautan `GET` dengan konfirmasi JavaScript. Sebaiknya diubah ke `POST` + token CSRF.
- Form Tambah/Edit Pengguna oleh Admin belum memvalidasi panjang password minimal (Register dan Tambah Nakes sudah).
- Kategori yang masih dipakai artikel tidak bisa dihapus (terkunci relasi database), dan halaman bisa menampilkan galat. Pindahkan atau hapus artikelnya lebih dulu.
- Pengingat bersifat *in-app*: banner baru muncul saat pengguna membuka halaman SehatKu, bukan push notification.
- Informasi di aplikasi bersifat edukasi dan **bukan pengganti diagnosis atau saran medis profesional**.

---

## 12. Troubleshooting

| Masalah | Solusi |
|---|---|
| *Koneksi database gagal* | Pastikan MySQL berjalan, database `sehatku` sudah dibuat, dan isi `koneksi.php` sesuai |
| Halaman 404 / CSS tidak muncul | Pastikan folder bernama `sehatku` dan diakses lewat `http://localhost/sehatku/` |
| Tidak bisa login sebagai admin | Akun admin belum dibuat. Ikuti [3.4](#34-membuat-akun-admin-pertama) |
| *Akun tidak aktif* | Admin perlu mengubah status akun menjadi **aktif** di menu Pengguna/Nakes |
| Nakes tidak muncul di menu Konsultasi | Pastikan akun **aktif** dan nakes sudah membuat **jadwal** yang akan datang |
| Error tabel `konsultasi`/`notifikasi`/`pengingat` tidak ada | Import `update_modul_7_9.sql`, atau import ulang `db_sehatku.sql` pada database baru |
| Artikel/perawat contoh tidak muncul setelah seeder | Pastikan skrip menampilkan *ditambah*, bukan galat. Cek `koneksi.php` dan pastikan `db_sehatku.sql` sudah diimpor |
| Pengingat tidak muncul | Pastikan sudah **disimpan dan dicentang Aktif**, jam sudah lewat, dan target belum tercapai (untuk air/olahraga) |
| Sering diminta login ulang | Sesi habis setelah 15 menit tanpa aktivitas, ini normal |

---

<p align="center"><strong>SehatKu</strong> · SDG 3 · Kesehatan &amp; Kesejahteraan · SDG 4 · Pendidikan</p>
