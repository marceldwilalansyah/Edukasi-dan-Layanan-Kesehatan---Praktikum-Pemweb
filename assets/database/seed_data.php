<?php

/*
 * DATA CONTOH SEHATKU (opsional)
 * Mengisi: 4 akun perawat (nakes) + 10 artikel terbit.
 *
 * Cara pakai (pilih salah satu):
 *   - Terminal : php assets/database/seed_data.php
 *   - Browser  : http://localhost/sehatku/assets/database/seed_data.php
 *
 * Aman dijalankan berulang kali: data yang sudah ada dilewati.
 * Hapus file ini setelah dipakai.
 */

require_once __DIR__ . "/../../koneksi.php";

if (PHP_SAPI !== "cli") {
    header("Content-Type: text/plain; charset=utf-8");
}

const PASSWORD_PERAWAT = "perawat123";

/* ---------- Data perawat ---------- */

$perawat = [
    [
        "username"     => "perawat.rina",
        "nama"         => "Ns. Rina Kusuma, S.Kep",
        "spesialisasi" => "Perawat Umum",
        "bio"          => "Berpengalaman 8 tahun di layanan kesehatan primer. Membantu keluhan ringan sehari-hari, perawatan luka, dan edukasi pola hidup sehat.",
    ],
    [
        "username"     => "perawat.budi",
        "nama"         => "Ns. Budi Santoso, S.Kep",
        "spesialisasi" => "Perawat Komunitas",
        "bio"          => "Fokus pada pencegahan penyakit dan edukasi kesehatan keluarga, termasuk pengelolaan tekanan darah dan gula darah di rumah.",
    ],
    [
        "username"     => "perawat.dewi",
        "nama"         => "Ns. Dewi Lestari, S.Kep",
        "spesialisasi" => "Perawat Ibu dan Anak",
        "bio"          => "Mendampingi ibu hamil, ibu menyusui, serta tumbuh kembang anak. Siap berdiskusi soal gizi anak dan imunisasi.",
    ],
    [
        "username"     => "perawat.fauzi",
        "nama"         => "Ns. Ahmad Fauzi, S.Kep",
        "spesialisasi" => "Perawat Gawat Darurat",
        "bio"          => "Berpengalaman di unit gawat darurat. Memberi arahan pertolongan pertama dan kapan sebaiknya segera ke fasilitas kesehatan.",
    ],
];

/* ---------- Data artikel ---------- */

$artikel = [
    [
        "kategori" => "Nutrisi",
        "judul"   => "Panduan Gizi Seimbang: Isi Piringku untuk Sehari-hari",
        "isi"     => <<<'TXT'
Gizi seimbang berarti tubuh mendapat zat gizi dalam jumlah yang sesuai kebutuhan, tidak kurang dan tidak berlebihan. Cara paling mudah mempraktikkannya adalah dengan konsep "Isi Piringku".

Dalam satu kali makan, bayangkan piring terbagi menjadi beberapa bagian:
- Sekitar separuh piring diisi sayur dan buah.
- Sisanya diisi makanan pokok (nasi, jagung, umbi) dan lauk pauk (ikan, telur, tahu, tempe, daging tanpa lemak).

Variasikan menu setiap hari agar tubuh mendapat beragam vitamin dan mineral. Pilih cara memasak yang lebih ringan seperti merebus, mengukus, atau memanggang dibanding terlalu sering menggoreng.

Jangan lupa sarapan, karena sarapan membantu menjaga energi dan konsentrasi di awal hari. Mulailah dari perubahan kecil, misalnya menambah satu porsi sayur di setiap makan siang.
TXT,
    ],
    [
        "kategori" => "Nutrisi",
        "judul"   => "Mengenal Gula, Garam, dan Lemak: Batas Konsumsi Harian",
        "isi"     => <<<'TXT'
Gula, garam, dan lemak dibutuhkan tubuh, tetapi jumlah yang berlebihan dalam jangka panjang meningkatkan risiko diabetes, tekanan darah tinggi, dan penyakit jantung.

Pedoman sederhana yang sering dipakai di Indonesia dikenal dengan "Gula 4, Garam 1, Lemak 5":
- Gula: maksimal 4 sendok makan per hari (sekitar 50 gram).
- Garam: maksimal 1 sendok teh per hari (sekitar 5 gram).
- Lemak atau minyak: maksimal 5 sendok makan per hari (sekitar 67 gram).

Perlu diingat, takaran ini sudah termasuk gula, garam, dan lemak yang tersembunyi dalam makanan olahan, minuman kemasan, camilan, dan makanan cepat saji. Biasakan membaca label informasi nilai gizi, dan kurangi minuman manis dengan menggantinya dengan air putih.
TXT,
    ],
    [
        "kategori" => "Nutrisi",
        "judul"   => "Minum Air Putih: Berapa Banyak yang Dibutuhkan Tubuh?",
        "isi"     => <<<'TXT'
Sekitar 60 persen tubuh orang dewasa terdiri dari air. Air membantu mengatur suhu tubuh, melancarkan pencernaan, dan membuang sisa metabolisme.

Kebutuhan cairan rata-rata orang dewasa sekitar 2 liter atau 8 gelas per hari. Angka ini bisa lebih tinggi saat cuaca panas, setelah berolahraga, atau ketika sedang demam. Kalkulator di SehatKu memperkirakan kebutuhan Anda berdasarkan berat badan.

Tanda tubuh mulai kekurangan cairan antara lain rasa haus, mulut kering, urine berwarna pekat, pusing, dan mudah lelah.

Tips menjaga hidrasi:
- Bawa botol minum ke mana pun Anda pergi.
- Minum segelas air setiap bangun tidur.
- Atur pengingat minum air di menu Pengingat SehatKu.
- Perbanyak buah dan sayur yang kaya air, seperti semangka dan mentimun.
TXT,
    ],
    [
        "kategori" => "Tidur",
        "judul"   => "Tips Tidur Nyenyak: Kebiasaan Kecil yang Berdampak Besar",
        "isi"     => <<<'TXT'
Orang dewasa umumnya membutuhkan 7 sampai 9 jam tidur berkualitas setiap malam. Tidur yang cukup membantu pemulihan tubuh, menjaga daya tahan, dan membuat otak lebih fokus keesokan harinya.

Beberapa kebiasaan yang membantu tidur lebih nyenyak:
- Tidur dan bangun pada jam yang sama setiap hari, termasuk akhir pekan.
- Hindari kafein (kopi, teh, minuman energi) pada sore dan malam hari.
- Jauhkan ponsel dan layar lain setidaknya 30 menit sebelum tidur.
- Buat kamar gelap, sejuk, dan tenang.
- Hindari makan besar dan olahraga berat tepat sebelum tidur.

Jika Anda sulit tidur terus-menerus lebih dari beberapa minggu, atau sering terbangun dengan rasa sangat lelah, sebaiknya konsultasikan dengan tenaga kesehatan.
TXT,
    ],
    [
        "kategori" => "Aktivitas Fisik",
        "judul"   => "150 Menit Seminggu: Cara Mudah Memulai Rutinitas Olahraga",
        "isi"     => <<<'TXT'
Organisasi Kesehatan Dunia (WHO) menganjurkan orang dewasa melakukan aktivitas fisik intensitas sedang minimal 150 menit per minggu. Jika dibagi, itu hanya sekitar 30 menit selama 5 hari.

Aktivitas intensitas sedang adalah yang membuat napas lebih cepat tetapi Anda masih bisa berbicara, misalnya jalan cepat, bersepeda santai, atau berenang ringan.

Cara memulai tanpa merasa berat:
- Mulai dari 10 menit per hari, lalu tambah bertahap.
- Pilih aktivitas yang Anda sukai agar mudah konsisten.
- Manfaatkan keseharian: naik tangga, berjalan ke warung, atau turun satu halte lebih awal.
- Lakukan pemanasan sebelum dan pendinginan sesudah berolahraga.
- Catat aktivitas Anda di menu Tracker SehatKu supaya progres terlihat.

Jika memiliki penyakit jantung atau kondisi kesehatan tertentu, konsultasikan jenis olahraga yang aman dengan tenaga kesehatan.
TXT,
    ],
    [
        "kategori" => "Kesehatan Mental",
        "judul"   => "Mengelola Stres Sehari-hari dengan Cara Sederhana",
        "isi"     => <<<'TXT'
Stres adalah respons wajar terhadap tekanan, tetapi bila berlangsung lama dapat memengaruhi tidur, nafsu makan, suasana hati, dan kesehatan fisik.

Beberapa cara sederhana untuk mengelolanya:
- Napas dalam: tarik napas perlahan 4 hitungan, tahan sebentar, lalu hembuskan 6 hitungan. Ulangi beberapa kali.
- Bergerak: jalan kaki ringan sering membantu menenangkan pikiran.
- Batasi beban: tulis daftar tugas dan kerjakan satu per satu.
- Terhubung dengan orang lain: bercerita kepada teman atau keluarga yang Anda percaya.
- Beri waktu untuk hal yang Anda nikmati, walau hanya 15 menit.

Jika perasaan cemas atau sedih terasa menetap, mengganggu aktivitas, atau muncul pikiran untuk menyakiti diri sendiri, segera cari bantuan dari tenaga kesehatan atau layanan kesehatan jiwa terdekat. Meminta bantuan adalah langkah yang kuat.
TXT,
    ],
    [
        "kategori" => "Pencegahan Penyakit",
        "judul"   => "Cuci Tangan Pakai Sabun: Langkah Kecil Pencegah Banyak Penyakit",
        "isi"     => <<<'TXT'
Mencuci tangan dengan sabun dan air mengalir adalah salah satu cara paling efektif untuk mencegah diare, infeksi saluran napas, dan penyakit menular lainnya.

Cuci tangan pada waktu-waktu penting:
- Sebelum makan dan menyiapkan makanan
- Setelah buang air besar atau kecil
- Setelah batuk, bersin, atau memegang hewan
- Setelah dari luar rumah atau memegang benda yang banyak disentuh orang

Lakukan selama 40 sampai 60 detik dengan langkah berikut: basahi tangan, sabuni, gosok telapak tangan, punggung tangan, sela-sela jari, ujung jari, dan ibu jari, lalu bilas dan keringkan dengan kain atau tisu bersih.

Bila sabun dan air tidak tersedia, pembersih tangan berbahan alkohol dapat menjadi alternatif sementara.
TXT,
    ],
    [
        "kategori" => "Pencegahan Penyakit",
        "judul"   => "Cek Kesehatan Berkala: Apa Saja yang Perlu Diperiksa?",
        "isi"     => <<<'TXT'
Banyak penyakit, seperti tekanan darah tinggi dan diabetes, pada awalnya tidak menimbulkan gejala. Pemeriksaan berkala membantu menemukannya lebih dini sehingga penanganannya lebih mudah.

Pemeriksaan dasar yang umum dianjurkan untuk orang dewasa:
- Tekanan darah
- Berat badan, tinggi badan, dan lingkar perut (untuk menilai BMI)
- Gula darah
- Kolesterol

Frekuensi dan jenis pemeriksaan tergantung usia, riwayat keluarga, dan kondisi masing-masing. Tanyakan kepada tenaga kesehatan jadwal yang sesuai untuk Anda.

Anda juga bisa memantau diri sendiri di rumah dengan kalkulator BMI dan catatan harian di SehatKu, lalu membawa catatan tersebut saat berkonsultasi.
TXT,
    ],
    [
        "kategori" => "Pola Hidup Sehat",
        "judul"   => "Membangun Kebiasaan Sehat Tanpa Merasa Terbebani",
        "isi"     => <<<'TXT'
Mengubah gaya hidup sekaligus sering berakhir dengan menyerah di minggu kedua. Kuncinya adalah mulai kecil dan konsisten.

Beberapa prinsip yang bisa dicoba:
- Pilih satu kebiasaan dahulu, misalnya minum 2 liter air per hari atau tidur sebelum jam 23.00.
- Kaitkan dengan rutinitas yang sudah ada, misalnya peregangan setelah sikat gigi.
- Catat kemajuan harian. Melihat rangkaian hari yang berhasil (streak) bisa menjadi motivasi.
- Jangan terlalu keras pada diri sendiri. Jika satu hari terlewat, lanjutkan keesokan harinya.
- Rayakan pencapaian kecil.

Gunakan fitur Tracker dan Pengingat di SehatKu untuk membantu membentuk kebiasaan baru. Perubahan kecil yang dilakukan terus-menerus biasanya lebih bertahan dibanding perubahan besar yang sebentar.
TXT,
    ],
    [
        "kategori" => "Pola Hidup Sehat",
        "judul"   => "Mengurangi Kelelahan Mata dengan Aturan 20-20-20",
        "isi"     => <<<'TXT'
Terlalu lama menatap layar ponsel atau komputer dapat menyebabkan mata kering, pegal, penglihatan buram sementara, hingga sakit kepala.

Aturan 20-20-20 adalah cara mudah untuk mengistirahatkan mata: setiap 20 menit, alihkan pandangan ke objek sejauh sekitar 20 kaki (6 meter) selama 20 detik.

Tips tambahan:
- Atur kecerahan layar agar sesuai dengan cahaya ruangan.
- Jaga jarak pandang yang nyaman, sekitar sepanjang lengan.
- Berkedip lebih sering agar mata tetap lembap.
- Beristirahatlah dari layar secara berkala dan hindari layar sebelum tidur.

Bila keluhan mata terus berlanjut, periksakan diri ke tenaga kesehatan atau dokter mata.
TXT,
    ],
];

/* ---------- Fungsi bantu ---------- */

function idRole($koneksi, $nama)
{
    $stmt = mysqli_prepare($koneksi, "SELECT role_id FROM roles WHERE role_name = ?");
    mysqli_stmt_bind_param($stmt, "s", $nama);
    mysqli_stmt_execute($stmt);
    $baris = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    return $baris ? (int) $baris["role_id"] : 0;
}

function idKategori($koneksi, $nama)
{
    $stmt = mysqli_prepare($koneksi, "SELECT category_id FROM categories WHERE category_name = ?");
    mysqli_stmt_bind_param($stmt, "s", $nama);
    mysqli_stmt_execute($stmt);
    $baris = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($baris) {
        return (int) $baris["category_id"];
    }

    $stmt = mysqli_prepare($koneksi, "INSERT INTO categories (category_name) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $nama);
    mysqli_stmt_execute($stmt);

    return (int) mysqli_insert_id($koneksi);
}

/* ---------- Perawat ---------- */

echo "== Perawat (nakes) ==\n";

$role_nakes = idRole($koneksi, "nakes");

if ($role_nakes === 0) {
    die("Tabel roles belum terisi. Import db_sehatku.sql terlebih dahulu.\n");
}

$hash        = password_hash(PASSWORD_PERAWAT, PASSWORD_DEFAULT);
$id_perawat  = [];

foreach ($perawat as $p) {
    $stmt = mysqli_prepare($koneksi, "SELECT user_id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $p["username"]);
    mysqli_stmt_execute($stmt);
    $ada = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($ada) {
        $id_perawat[] = (int) $ada["user_id"];
        echo "  lewati  : {$p["username"]} (sudah ada)\n";
        continue;
    }

    mysqli_begin_transaction($koneksi);

    $stmt = mysqli_prepare($koneksi, "INSERT INTO users (role_id, username, password) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $role_nakes, $p["username"], $hash);
    mysqli_stmt_execute($stmt);
    $user_id = (int) mysqli_insert_id($koneksi);

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO user_profiles (user_id, nama_lengkap, spesialisasi, bio) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $p["nama"], $p["spesialisasi"], $p["bio"]);
    mysqli_stmt_execute($stmt);

    mysqli_commit($koneksi);

    $id_perawat[] = $user_id;
    echo "  ditambah: {$p["username"]} - {$p["nama"]}\n";
}

/* ---------- Artikel ---------- */

echo "\n== Artikel ==\n";

// Penulis: admin pertama; kalau belum ada admin, pakai perawat pertama
$role_admin = idRole($koneksi, "admin");
$stmt = mysqli_prepare($koneksi, "SELECT user_id FROM users WHERE role_id = ? ORDER BY user_id LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $role_admin);
mysqli_stmt_execute($stmt);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$author_id = $admin ? (int) $admin["user_id"] : $id_perawat[0];

if (!$admin) {
    echo "  (belum ada akun admin, penulis artikel diisi perawat pertama)\n";
}

foreach ($artikel as $a) {
    $stmt = mysqli_prepare($koneksi, "SELECT article_id FROM articles WHERE title = ?");
    mysqli_stmt_bind_param($stmt, "s", $a["judul"]);
    mysqli_stmt_execute($stmt);

    if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
        echo "  lewati  : {$a["judul"]}\n";
        continue;
    }

    $category_id = idKategori($koneksi, $a["kategori"]);
    $isi         = trim($a["isi"]);
    $status      = "terbit";

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO articles (category_id, author_id, title, content, status) VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iisss", $category_id, $author_id, $a["judul"], $isi, $status);
    mysqli_stmt_execute($stmt);

    echo "  ditambah: {$a["judul"]}\n";
}

echo "\nSelesai.\n";
echo "Login perawat: username di atas, password \"" . PASSWORD_PERAWAT . "\" (segera ganti).\n";
echo "Hapus file seed_data.php setelah dipakai.\n";
