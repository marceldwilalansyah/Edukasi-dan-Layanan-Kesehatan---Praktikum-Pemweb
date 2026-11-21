<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("pengguna");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$user_id    = $_SESSION["user_id"];
$catatan_id = isset($_POST["catatan_id"]) ? (int) $_POST["catatan_id"] : 0;

if ($catatan_id > 0) {
    $query = "DELETE FROM catatan_kesehatan WHERE catatan_id = ? AND user_id = ?";
    $stmt  = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ii", $catatan_id, $user_id);
    mysqli_stmt_execute($stmt);
    $pesan = "Catatan kesehatan berhasil dihapus.";
} else {
    $pesan = "Data tidak valid.";
}

header("Location: index.php?pesan=" . urlencode($pesan));
exit;