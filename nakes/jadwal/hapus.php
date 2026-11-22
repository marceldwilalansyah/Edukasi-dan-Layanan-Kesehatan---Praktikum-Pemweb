<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("nakes");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$nakes_id  = (int) $_SESSION["user_id"];
$jadwal_id = isset($_POST["jadwal_id"]) ? (int) $_POST["jadwal_id"] : 0;


$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM jadwal_praktik
     WHERE jadwal_id = ? AND nakes_id = ? AND status = 'tersedia'"
);
mysqli_stmt_bind_param($stmt, "ii", $jadwal_id, $nakes_id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    header("Location: index.php?pesan=" . urlencode("Jadwal berhasil dihapus."));
} else {
    header("Location: index.php?error=" . urlencode("Jadwal tidak bisa dihapus karena sudah dibooking atau tidak ditemukan."));
}
exit;
