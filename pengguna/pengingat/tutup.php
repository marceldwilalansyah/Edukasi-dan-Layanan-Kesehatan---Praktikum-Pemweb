<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("pengguna");

$user_id = (int) $_SESSION["user_id"];


$kembali = $_POST["kembali"] ?? "";
if (!preg_match('#^/sehatku/[A-Za-z0-9_/.\-?=&%]*$#', $kembali) || strpos($kembali, "..") !== false) {
    $kembali = "/sehatku/pengguna/dashboard.php";
}

$jenis = $_POST["jenis"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && in_array($jenis, ["air", "tidur", "olahraga"], true)) {
    $hari_ini = date("Y-m-d");

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE pengingat SET terakhir_ditutup = ? WHERE user_id = ? AND jenis = ?"
    );
    mysqli_stmt_bind_param($stmt, "sis", $hari_ini, $user_id, $jenis);
    mysqli_stmt_execute($stmt);
}

header("Location: " . $kembali);
exit;
