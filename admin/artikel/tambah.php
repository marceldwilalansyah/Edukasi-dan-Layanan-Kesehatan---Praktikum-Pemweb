<?php

require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");

$categories = mysqli_query(
    $koneksi,
    "SELECT * FROM categories ORDER BY category_name"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Tambah Artikel</title>

    <link rel="stylesheet"
          href="../../assets/css/style.css">

</head>

<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">

    <a class="back-button" href="index.php">
        ← Kembali ke Artikel
    </a>

    <h1>Tambah Artikel</h1>

    <form action="proses_tambah.php" method="POST">

        <label for="title">
            Judul Artikel
        </label>

        <input
            type="text"
            id="title"
            name="title"
            placeholder="Masukkan judul artikel"
            required
        >


        <label for="category_id">
            Kategori
        </label>

        <select
            name="category_id"
            id="category_id"
            required
        >

            <option value="">
                -- Pilih Kategori --
            </option>

            <?php while ($category = mysqli_fetch_assoc($categories)): ?>

                <option value="<?= $category["category_id"] ?>">
                    <?= htmlspecialchars($category["category_name"]) ?>
                </option>

            <?php endwhile; ?>

            <option value="lainnya">
                + Lainnya
            </option>

        </select>


        <!-- Form kategori baru -->
        <div
            id="kategoriBaruContainer"
            style="display: none; margin-top: 15px;"
        >

            <label for="category_name">
                Nama Kategori Baru
            </label>

            <input
                type="text"
                id="category_name"
                name="category_name"
                placeholder="Contoh: Kesehatan Remaja"
            >

            <small>
                Kategori ini akan disimpan dan dapat digunakan
                kembali pada artikel berikutnya.
            </small>

        </div>


        <label for="content">
            Isi Artikel
        </label>

        <textarea
            id="content"
            name="content"
            rows="10"
            placeholder="Tulis isi artikel..."
            required
        ></textarea>


        <label for="status">
            Status
        </label>

        <select name="status" id="status">

            <option value="draft">
                Draft
            </option>

            <option value="terbit">
                Terbit
            </option>

        </select>


        <div class="form-actions">

            <a
                class="button secondary"
                href="index.php"
            >
                Batal
            </a>

            <button
                type="submit"
                class="button"
            >
                Simpan Artikel
            </button>

        </div>

    </form>

</div>

<script>

const categorySelect = document.getElementById("category_id");
const kategoriBaruContainer =
    document.getElementById("kategoriBaruContainer");
const categoryName =
    document.getElementById("category_name");

categorySelect.addEventListener("change", function () {

    if (this.value === "lainnya") {

        kategoriBaruContainer.style.display = "block";

        categoryName.required = true;

        categoryName.focus();

    } else {

        kategoriBaruContainer.style.display = "none";

        categoryName.required = false;

        categoryName.value = "";

    }

});

</script>

<?php require_once "../../include/footer.php"; ?>

</body>

</html>