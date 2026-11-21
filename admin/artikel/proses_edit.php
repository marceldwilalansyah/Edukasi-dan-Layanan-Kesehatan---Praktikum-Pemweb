<?php

require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");


$article_id = (int) $_POST["article_id"];

$title = trim($_POST["title"]);

$category_id = (int) $_POST["category_id"];

$content = trim($_POST["content"]);

$status = $_POST["status"];


$query = "
    UPDATE articles
    SET
        category_id = ?,
        title = ?,
        content = ?,
        status = ?
    WHERE article_id = ?
";

$stmt = mysqli_prepare(
    $koneksi,
    $query
);

mysqli_stmt_bind_param(
    $stmt,
    "isssi",
    $category_id,
    $title,
    $content,
    $status,
    $article_id
);

mysqli_stmt_execute($stmt);


header("Location: index.php");
exit;