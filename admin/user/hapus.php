<?php

require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");

$id = (int) $_GET["id"];


if ($id === (int) $_SESSION["user_id"]) {

    die("Admin tidak dapat menghapus akun sendiri.");

}


$query = "
    DELETE FROM users
    WHERE user_id = ?
";

$stmt = mysqli_prepare(
    $koneksi,
    $query
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);


header("Location: index.php");
exit;