<?php

require_once "../include/auth_check.php";
require_once "../koneksi.php";

cekRole("admin");

$id = (int) $_GET["id"];


$query = "
    SELECT *
    FROM categories
    WHERE category_id = ?
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

$category = mysqli_fetch_assoc($result);

if (!$category) {
    die("Kategori tidak ditemukan.");
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Edit Kategori</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

</head>

<body>

<?php require_once "../include/header.php"; ?>

<div class="container auth-container">

    <a class="back-button" href="index.php">← Kembali ke Kategori</a>

    <h1>Edit Kategori</h1>

    <form action="proses_edit.php" method="POST">

        <input
            type="hidden"
            name="category_id"
            value="<?= $category["category_id"] ?>"
        >

        <label>Nama Kategori</label>

        <input
            type="text"
            name="category_name"
            value="<?= htmlspecialchars(
                $category["category_name"]
            ) ?>"
            required
        >

        <button type="submit">
            Simpan
        </button>

    </form>

</div>

<?php require_once "../include/footer.php"; ?>
</body>

</html>