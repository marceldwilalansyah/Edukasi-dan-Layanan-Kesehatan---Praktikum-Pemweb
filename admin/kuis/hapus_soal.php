<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$pertanyaan_id = isset($_POST["pertanyaan_id"]) ? (int) $_POST["pertanyaan_id"] : 0;
$kuis_id       = isset($_POST["kuis_id"]) ? (int) $_POST["kuis_id"] : 0;

if ($pertanyaan_id > 0) {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM pertanyaan_kuis WHERE pertanyaan_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $pertanyaan_id);
    mysqli_stmt_execute($stmt);
    $pesan = "Pertanyaan berhasil dihapus.";
} else {
    $pesan = "Soal tidak valid.";
}

header("Location: soal.php?kuis_id=" . $kuis_id . "&pesan=" . urlencode($pesan));
exit;