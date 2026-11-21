<?php

require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");


$username = trim($_POST["username"]);
$password = $_POST["password"];
$nama_lengkap = trim($_POST["nama_lengkap"]);
$role_id = (int) $_POST["role_id"];

//cek username sudah ada
$query = "
    SELECT user_id
    FROM users
    WHERE username = ?
";

$stmt = mysqli_prepare($koneksi, $query);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $username
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {

    die("Username sudah digunakan.");

}

//password hash
$password_hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

//insert user
$query = "
    INSERT INTO users
    (role_id, username, password)
    VALUES (?, ?, ?)
";

$stmt = mysqli_prepare($koneksi, $query);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $role_id,
    $username,
    $password_hash
);

mysqli_stmt_execute($stmt);

$user_id = mysqli_insert_id($koneksi);

//insert profile
$query = "
    INSERT INTO user_profiles
    (user_id, nama_lengkap)
    VALUES (?, ?)
";

$stmt = mysqli_prepare($koneksi, $query);

mysqli_stmt_bind_param(
    $stmt,
    "is",
    $user_id,
    $nama_lengkap
);

mysqli_stmt_execute($stmt);


header("Location: index.php");
exit;