<?php

require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");

$title = trim($_POST["title"] ?? "");
$category_id = $_POST["category_id"] ?? "";
$category_name = trim($_POST["category_name"] ?? "");
$content = trim($_POST["content"] ?? "");
$status = $_POST["status"] ?? "draft";

$author_id = $_SESSION["user_id"];

if ($title === "" || $content === "") {

    die("Judul dan isi artikel wajib diisi.");

}


if ($category_id === "lainnya") {

    if ($category_name === "") {

        die("Nama kategori baru wajib diisi.");

    }

    $check = mysqli_prepare(
        $koneksi,
        "SELECT category_id
         FROM categories
         WHERE category_name = ?"
    );

    mysqli_stmt_bind_param(
        $check,
        "s",
        $category_name
    );

    mysqli_stmt_execute($check);

    $result = mysqli_stmt_get_result($check);

    $existing = mysqli_fetch_assoc($result);


    if ($existing) {

        $category_id = $existing["category_id"];

    } else {

        $insertCategory = mysqli_prepare(
            $koneksi,
            "INSERT INTO categories (category_name)
             VALUES (?)"
        );

        mysqli_stmt_bind_param(
            $insertCategory,
            "s",
            $category_name
        );

        mysqli_stmt_execute($insertCategory);

        $category_id = mysqli_insert_id($koneksi);

    }

}


$category_id = (int) $category_id;


$query = "
    INSERT INTO articles
    (
        category_id,
        author_id,
        title,
        content,
        status
    )
    VALUES (?, ?, ?, ?, ?)
";

$stmt = mysqli_prepare(
    $koneksi,
    $query
);

mysqli_stmt_bind_param(
    $stmt,
    "iisss",
    $category_id,
    $author_id,
    $title,
    $content,
    $status
);

mysqli_stmt_execute($stmt);

header("Location: index.php");

exit;