<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";
require_once "../../include/fungsi.php";

cekRole("nakes");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$nakes_id = (int) $_SESSION["user_id"];
$id       = isset($_POST["konsultasi_id"]) ? (int) $_POST["konsultasi_id"] : 0;
$aksi     = $_POST["aksi"] ?? "";
$catatan  = trim($_POST["catatan"] ?? "");

function kembaliKeDetail($id, $jenis, $teks)
{
    header("Location: detail.php?id=" . $id . "&" . $jenis . "=" . urlencode($teks));
    exit;
}

if (!in_array($aksi, ["konfirmasi", "selesai", "batal"], true)) {
    kembaliKeDetail($id, "error", "Aksi tidak dikenal.");
}

if (($aksi === "selesai" || $aksi === "batal") && $catatan === "") {
    kembaliKeDetail($id, "error", "Catatan wajib diisi untuk aksi ini.");
}

mysqli_begin_transaction($koneksi);

try {
    // Kunci baris supaya tidak bentrok kalau pasien membatalkan di saat yang sama
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT konsultasi_id, user_id, jadwal_id, status, tanggal_konsultasi, jam_mulai, jam_selesai
         FROM konsultasi
         WHERE konsultasi_id = ? AND nakes_id = ?
         FOR UPDATE"
    );
    mysqli_stmt_bind_param($stmt, "ii", $id, $nakes_id);
    mysqli_stmt_execute($stmt);
    $k = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$k) {
        mysqli_rollback($koneksi);
        header("Location: index.php");
        exit;
    }

    // Alur status: menunggu -> dikonfirmasi -> selesai; batal dari menunggu atau dikonfirmasi
    $boleh = [
        "konfirmasi" => ["menunggu"],
        "selesai"    => ["dikonfirmasi"],
        "batal"      => ["menunggu", "dikonfirmasi"],
    ];

    if (!in_array($k["status"], $boleh[$aksi], true)) {
        mysqli_rollback($koneksi);
        kembaliKeDetail($id, "error", "Status konsultasi sudah berubah, aksi tidak bisa dilakukan.");
    }

    $status_baru = ["konfirmasi" => "dikonfirmasi", "selesai" => "selesai", "batal" => "dibatalkan"][$aksi];
    $catatan_db  = $catatan !== "" ? $catatan : null;

    if ($aksi === "batal") {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE konsultasi
             SET status = ?, catatan_nakes = ?, dibatalkan_oleh = 'nakes'
             WHERE konsultasi_id = ?"
        );
    } else {
        // Saat konfirmasi tanpa catatan, catatan lama (kalau ada) dibiarkan
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE konsultasi
             SET status = ?, catatan_nakes = COALESCE(?, catatan_nakes)
             WHERE konsultasi_id = ?"
        );
    }
    mysqli_stmt_bind_param($stmt, "ssi", $status_baru, $catatan_db, $id);
    mysqli_stmt_execute($stmt);

    // Dibatalkan: slot jadwal dibuka lagi supaya bisa dipilih pengguna lain
    if ($aksi === "batal" && $k["jadwal_id"] !== null) {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE jadwal_praktik SET status = 'tersedia' WHERE jadwal_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $k["jadwal_id"]);
        mysqli_stmt_execute($stmt);
    }

    // Beri tahu pengguna
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT COALESCE(NULLIF(nama_lengkap, ''), (SELECT username FROM users WHERE user_id = ?)) AS nama
         FROM user_profiles WHERE user_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $nakes_id, $nakes_id);
    mysqli_stmt_execute($stmt);
    $baris     = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $nama_nakes = $baris["nama"] ?? $_SESSION["username"];

    $waktu = tanggalIndo($k["tanggal_konsultasi"]) . ", " . rentangJam($k["jam_mulai"], $k["jam_selesai"]);

    $judul_notif = [
        "konfirmasi" => "Konsultasi dikonfirmasi",
        "selesai"    => "Konsultasi selesai",
        "batal"      => "Konsultasi dibatalkan",
    ][$aksi];

    $pesan_notif = [
        "konfirmasi" => "$nama_nakes menerima permintaan konsultasimu pada $waktu.",
        "selesai"    => "Konsultasimu dengan $nama_nakes pada $waktu sudah selesai. Lihat catatannya.",
        "batal"      => "$nama_nakes membatalkan konsultasi pada $waktu. Kamu bisa memilih jadwal lain.",
    ][$aksi];

    buatNotifikasi(
        $koneksi,
        (int) $k["user_id"],
        "konsultasi",
        $judul_notif,
        $pesan_notif,
        "pengguna/konsultasi/index.php#k" . $id
    );

    mysqli_commit($koneksi);
} catch (mysqli_sql_exception $ex) {
    mysqli_rollback($koneksi);
    kembaliKeDetail($id, "error", "Terjadi kesalahan, coba lagi.");
}

$pesan = [
    "konfirmasi" => "Konsultasi dikonfirmasi. Pasien sudah diberi tahu.",
    "selesai"    => "Konsultasi ditandai selesai.",
    "batal"      => "Konsultasi dibatalkan dan jadwal dibuka kembali.",
][$aksi];

kembaliKeDetail($id, "pesan", $pesan);
