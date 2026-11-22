<?php
require_once "../include/auth_check.php";
require_once "../koneksi.php";

cekRoleSalahSatu(["pengguna", "nakes"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = (int) $_SESSION["user_id"];

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE notifikasi SET sudah_dibaca = 1 WHERE user_id = ? AND sudah_dibaca = 0"
    );
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
}

header("Location: index.php?pesan=" . urlencode("Semua notifikasi ditandai sudah dibaca."));
exit;
