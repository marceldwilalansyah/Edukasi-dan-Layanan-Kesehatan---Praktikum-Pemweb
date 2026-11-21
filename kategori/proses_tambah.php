<?php

require_once "../include/auth_check.php";
require_once "../koneksi.php";

cekRole("admin");

$name = trim($_POST["category_name"]);


$query = "
    INSERT INTO categories (category_name)
    VALUES (?)
";

$stmt = mysqli_prepare(
    $koneksi,
    $query
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $name
);

mysqli_stmt_execute($stmt);


header("Location: index.php");
exit;