<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$kuis_id = isset($_POST["kuis_id"]) ? (int) $_POST["kuis_id"] : 0;

if ($kuis_id > 0) {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM kuis WHERE kuis_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $kuis_id);
    mysqli_stmt_execute($stmt);
    $pesan = "Kuis berhasil dihapus.";
} else {
    $pesan = "Data kuis tidak valid.";
}

header("Location: index.php?pesan=" . urlencode($pesan));
exit;