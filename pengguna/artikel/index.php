<?php

require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("pengguna");


$search = isset($_GET["search"])
    ? trim($_GET["search"])
    : "";

$category_id = isset($_GET["category"])
    ? (int) $_GET["category"]
    : 0;


$categories = mysqli_query(
    $koneksi,
    "SELECT * FROM categories ORDER BY category_name"
);

//query artikel
$query = "
    SELECT
        a.article_id,
        a.title,
        a.content,
        a.created_at,
        c.category_name
    FROM articles a

    JOIN categories c
        ON a.category_id = c.category_id

    WHERE a.status = 'terbit'
";


$params = [];
$types = "";

//pencarian artikel
if ($search !== "") {

    $query .= "
        AND a.title LIKE ?
    ";

    $search_param = "%" . $search . "%";

    $params[] = $search_param;

    $types .= "s";
}

//filter kategori
if ($category_id > 0) {

    $query .= "
        AND a.category_id = ?
    ";

    $params[] = $category_id;

    $types .= "i";
}


$query .= "
    ORDER BY a.created_at DESC
";


$stmt = mysqli_prepare(
    $koneksi,
    $query
);


if (!empty($params)) {

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );
}


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Artikel Edukasi</title>

    <link rel="stylesheet"
          href="../../assets/css/style.css">

</head>

<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">

    <a class="back-button" href="../dashboard.php">← Kembali ke Dashboard</a>

    <h1>Artikel Edukasi</h1>


    <form
        method="GET"
        style="margin-bottom:20px;"
    >

        <label>Cari Artikel</label>

        <input
            type="text"
            name="search"
            value="<?= htmlspecialchars($search) ?>"
            placeholder="Masukkan judul artikel..."
        >


        <label>Filter Kategori</label>

        <select name="category">

            <option value="0">
                Semua Kategori
            </option>

            <?php while ($category = mysqli_fetch_assoc($categories)): ?>

                <option
                    value="<?= $category["category_id"] ?>"
                    <?= $category_id == $category["category_id"]
                        ? "selected"
                        : "" ?>
                >
                    <?= htmlspecialchars(
                        $category["category_name"]
                    ) ?>
                </option>

            <?php endwhile; ?>

        </select>


        <button type="submit">
            Cari
        </button>

    </form>


    <?php if (mysqli_num_rows($result) === 0): ?>

        <p>
            Tidak ada artikel ditemukan.
        </p>

    <?php endif; ?>


    <?php while ($article = mysqli_fetch_assoc($result)): ?>

        <div
            style="
                background:white;
                padding:20px;
                margin-bottom:15px;
                border-radius:10px;
            "
        >

            <h2>
                <?= htmlspecialchars(
                    $article["title"]
                ) ?>
            </h2>


            <small>

                Kategori:
                <?= htmlspecialchars(
                    $article["category_name"]
                ) ?>

                |
                
                <?= $article["created_at"] ?>

            </small>


            <p>

                <?= htmlspecialchars(
                    substr($article["content"], 0, 200)
                ) ?>...

            </p>


            <a
                href="detail.php?id=<?= $article["article_id"] ?>"
            >
                Baca Selengkapnya
            </a>

        </div>

    <?php endwhile; ?>

</div>

<?php require_once "../../include/footer.php"; ?>
</body>

</html>