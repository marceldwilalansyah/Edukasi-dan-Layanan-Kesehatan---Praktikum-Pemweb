<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";
require_once "../../include/fungsi.php";

cekRole("pengguna");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$id      = isset($_POST["konsultasi_id"]) ? (int) $_POST["konsultasi_id"] : 0;

mysqli_begin_transaction($koneksi);

try {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT konsultasi_id, nakes_id, jadwal_id, status, tanggal_konsultasi, jam_mulai, jam_selesai
         FROM konsultasi
         WHERE konsultasi_id = ? AND user_id = ?
         FOR UPDATE"
    );
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
    mysqli_stmt_execute($stmt);
    $k = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$k || !in_array($k["status"], ["menunggu", "dikonfirmasi"], true)) {
        mysqli_rollback($koneksi);
        header("Location: index.php?error=" . urlencode("Booking ini tidak bisa dibatalkan."));
        exit;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE konsultasi SET status = 'dibatalkan', dibatalkan_oleh = 'pengguna' WHERE konsultasi_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    
    if ($k["jadwal_id"] !== null) {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE jadwal_praktik SET status = 'tersedia' WHERE jadwal_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $k["jadwal_id"]);
        mysqli_stmt_execute($stmt);
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT COALESCE(NULLIF(p.nama_lengkap, ''), u.username) AS nama
         FROM users u LEFT JOIN user_profiles p ON p.user_id = u.user_id
         WHERE u.user_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $pemesan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    buatNotifikasi(
        $koneksi,
        (int) $k["nakes_id"],
        "booking",
        "Booking dibatalkan pasien",
        ($pemesan["nama"] ?? "Pengguna") . " membatalkan konsultasi pada "
            . tanggalIndo($k["tanggal_konsultasi"]) . ", "
            . rentangJam($k["jam_mulai"], $k["jam_selesai"]) . ". Jadwal dibuka kembali.",
        "nakes/konsultasi/detail.php?id=" . $id
    );

    mysqli_commit($koneksi);
} catch (mysqli_sql_exception $ex) {
    mysqli_rollback($koneksi);
    header("Location: index.php?error=" . urlencode("Terjadi kesalahan, coba lagi."));
    exit;
}

header("Location: index.php?pesan=" . urlencode("Booking berhasil dibatalkan."));
exit;
