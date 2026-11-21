<?php

require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");

$id = (int) $_GET["id"];


$query = "
    SELECT *
    FROM articles
    WHERE article_id = ?
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


$categories = mysqli_query(
    $koneksi,
    "SELECT * FROM categories ORDER BY category_name"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Edit Artikel</title>

    <link rel="stylesheet"
          href="../../assets/css/style.css">

</head>

<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">

    <a class="back-button" href="index.php">← Kembali ke Artikel</a>

    <h1>Edit Artikel</h1>

    <form
        action="proses_edit.php"
        method="POST"
    >

        <input
            type="hidden"
            name="article_id"
            value="<?= $article["article_id"] ?>"
        >


        <label>Judul</label>

        <input
            type="text"
            name="title"
            value="<?= htmlspecialchars(
                $article["title"]
            ) ?>"
            required
        >


        <label>Kategori</label>

        <select name="category_id">

            <?php while ($category = mysqli_fetch_assoc($categories)): ?>

                <option
                    value="<?= $category["category_id"] ?>"
                    <?= $category["category_id"] == $article["category_id"]
                        ? "selected"
                        : "" ?>
                >
                    <?= htmlspecialchars(
                        $category["category_name"]
                    ) ?>
                </option>

            <?php endwhile; ?>

        </select>


        <label>Isi Artikel</label>

        <textarea
            name="content"
            required
        ><?= htmlspecialchars(
            $article["content"]
        ) ?></textarea>


        <label>Status</label>

        <select name="status">

            <option
                value="draft"
                <?= $article["status"] === "draft"
                    ? "selected"
                    : "" ?>
            >
                Draft
            </option>

            <option
                value="terbit"
                <?= $article["status"] === "terbit"
                    ? "selected"
                    : "" ?>
            >
                Terbit
            </option>

        </select>


        <button type="submit">
            Simpan Perubahan
        </button>

    </form>

</div>

<?php require_once "../../include/footer.php"; ?>
</body>

</html>