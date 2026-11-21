<?php

require_once "../include/auth_check.php";
require_once "../koneksi.php";

cekRole("admin");

$id = (int) $_POST["category_id"];

$name = trim($_POST["category_name"]);


$query = "
    UPDATE categories
    SET category_name = ?
    WHERE category_id = ?
";

$stmt = mysqli_prepare(
    $koneksi,
    $query
);

mysqli_stmt_bind_param(
    $stmt,
    "si",
    $name,
    $id
);

mysqli_stmt_execute($stmt);


header("Location: index.php");
exit;