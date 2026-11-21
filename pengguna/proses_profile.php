<?php

require_once "../include/auth_check.php";
require_once "../koneksi.php";

cekRole("pengguna");


$user_id = $_SESSION["user_id"];

$nama_lengkap = trim($_POST["nama_lengkap"]);
$tinggi = $_POST["tinggi"] !== ""
    ? (float) $_POST["tinggi"]
    : null;

$berat = $_POST["berat"] !== ""
    ? (float) $_POST["berat"]
    : null;

$tanggal_lahir = $_POST["tanggal_lahir"] !== ""
    ? $_POST["tanggal_lahir"]
    : null;

$password = $_POST["password"];

//update profile
$query = "
    UPDATE user_profiles
    SET
        nama_lengkap = ?,
        tinggi = ?,
        berat = ?,
        tanggal_lahir = ?
    WHERE user_id = ?
";

$stmt = mysqli_prepare(
    $koneksi,
    $query
);

mysqli_stmt_bind_param(
    $stmt,
    "sddsi",
    $nama_lengkap,
    $tinggi,
    $berat,
    $tanggal_lahir,
    $user_id
);

mysqli_stmt_execute($stmt);


//update password
if ($password !== "") {

    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $query = "
        UPDATE users
        SET password = ?
        WHERE user_id = ?
    ";

    $stmt = mysqli_prepare(
        $koneksi,
        $query
    );

    mysqli_stmt_bind_param(
        $stmt,
        "si",
        $password_hash,
        $user_id
    );

    mysqli_stmt_execute($stmt);
}


header("Location: profile.php");
exit;