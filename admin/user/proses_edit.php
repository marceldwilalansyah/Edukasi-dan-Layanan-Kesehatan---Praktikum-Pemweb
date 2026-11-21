<?php

require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");


$user_id = (int) $_POST["user_id"];
$username = trim($_POST["username"]);
$nama_lengkap = trim($_POST["nama_lengkap"]);
$password = $_POST["password"];
$role_id = (int) $_POST["role_id"];
$status = $_POST["status"];

//update user
if ($password !== "") {

    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $query = "
        UPDATE users
        SET
            username = ?,
            password = ?,
            role_id = ?,
            status = ?
        WHERE user_id = ?
    ";

    $stmt = mysqli_prepare(
        $koneksi,
        $query
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssisi",
        $username,
        $password_hash,
        $role_id,
        $status,
        $user_id
    );

} else {

    $query = "
        UPDATE users
        SET
            username = ?,
            role_id = ?,
            status = ?
        WHERE user_id = ?
    ";

    $stmt = mysqli_prepare(
        $koneksi,
        $query
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sisi",
        $username,
        $role_id,
        $status,
        $user_id
    );
}

mysqli_stmt_execute($stmt);

//update profile
$query = "
    UPDATE user_profiles
    SET nama_lengkap = ?
    WHERE user_id = ?
";

$stmt = mysqli_prepare(
    $koneksi,
    $query
);

mysqli_stmt_bind_param(
    $stmt,
    "si",
    $nama_lengkap,
    $user_id
);

mysqli_stmt_execute($stmt);


header("Location: index.php");
exit;