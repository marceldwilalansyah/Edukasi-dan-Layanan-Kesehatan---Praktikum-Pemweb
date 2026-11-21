<?php

require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("pengguna");


$id = (int) $_GET["id"];


$query = "
    SELECT
        a.title,
        a.content,
        a.created_at,
        c.category_name,
        u.username AS author
    FROM articles a

    JOIN categories c
        ON a.category_id = c.category_id

    JOIN users u
        ON a.author_id = u.user_id

    WHERE a.article_id = ?
    AND a.status = 'terbit'
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

$result = mysqli_stmt_get_result($stmt);

$article = mysqli_fetch_assoc($result);


if (!$article) {
    die("Artikel tidak ditemukan.");
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>
        <?= htmlspecialchars($article["title"]) ?>
    </title>

    <link rel="stylesheet"
          href="../../assets/css/style.css">

</head>

<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">

    <a class="back-button" href="index.php">← Kembali ke Artikel</a>

    <h1>
        <?= htmlspecialchars($article["title"]) ?>
    </h1>


    <p>

        Kategori:
        <strong>
            <?= htmlspecialchars(
                $article["category_name"]
            ) ?>
        </strong>

        <br>

        Penulis:
        <strong>
            <?= htmlspecialchars(
                $article["author"]
            ) ?>
        </strong>

        <br>

        Tanggal:
        <?= htmlspecialchars(
            $article["created_at"]
        ) ?>

    </p>


    <hr>


    <div>

        <?= nl2br(
            htmlspecialchars($article["content"])
        ) ?>

    </div>


    <br>

    <a href="index.php">
        Kembali ke Artikel
    </a>

</div>

<?php require_once "../../include/footer.php"; ?>
</body>

</html>