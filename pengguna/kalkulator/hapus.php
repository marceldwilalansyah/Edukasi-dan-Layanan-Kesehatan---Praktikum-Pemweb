<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("pengguna");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$user_id       = $_SESSION["user_id"];
$kalkulator_id = isset($_POST["kalkulator_id"]) ? (int) $_POST["kalkulator_id"] : 0;

if ($kalkulator_id > 0) {
    $query = "DELETE FROM riwayat_kalkulator WHERE kalkulator_id = ? AND user_id = ?";
    $stmt  = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ii", $kalkulator_id, $user_id);
    mysqli_stmt_execute($stmt);
    $pesan = "Riwayat perhitungan berhasil dihapus.";
} else {
    $pesan = "Data tidak valid.";
}

header("Location: index.php?pesan=" . urlencode($pesan));
exit;