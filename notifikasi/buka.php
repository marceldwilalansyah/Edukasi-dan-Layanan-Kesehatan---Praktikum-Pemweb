<?php
require_once "../include/auth_check.php";
require_once "../koneksi.php";

cekRoleSalahSatu(["pengguna", "nakes"]);

$baseUrl  = "/sehatku";
$user_id  = (int) $_SESSION["user_id"];
$id       = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT tautan FROM notifikasi WHERE notifikasi_id = ? AND user_id = ?"
);
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
mysqli_stmt_execute($stmt);
$n = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$n) {
    header("Location: index.php");
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE notifikasi SET sudah_dibaca = 1 WHERE notifikasi_id = ? AND user_id = ?"
);
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
mysqli_stmt_execute($stmt);

$tautan = (string) $n["tautan"];

if ($tautan === "" || preg_match('#^[a-z]+:|^/|\\\\|\.\.#i', $tautan)) {
    header("Location: index.php");
} else {
    header("Location: " . $baseUrl . "/" . $tautan);
}
exit;
