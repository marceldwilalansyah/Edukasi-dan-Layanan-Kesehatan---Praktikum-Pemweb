<?php

require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");


$query = "
    SELECT
        a.article_id,
        a.title,
        a.status,
        a.created_at,
        c.category_name,
        u.username AS author
    FROM articles a

    JOIN categories c
        ON a.category_id = c.category_id

    JOIN users u
        ON a.author_id = u.user_id

    ORDER BY a.created_at DESC
";

$result = mysqli_query($koneksi, $query);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Kelola Artikel</title>

    <link rel="stylesheet"
          href="../../assets/css/style.css">

</head>

<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">

    <a class="back-button" href="../dashboard.php">← Kembali ke Dashboard</a>

    <h1>Kelola Artikel</h1>

    <a class="button"
       href="tambah.php">
        + Tambah Artikel
    </a>

    <table
        border="1"
        cellpadding="10"
        cellspacing="0"
        width="100%"
        style="margin-top:20px;"
    >

        <tr>

            <th>Judul</th>
            <th>Kategori</th>
            <th>Penulis</th>
            <th>Status</th>
            <th>Aksi</th>

        </tr>


        <?php while ($article = mysqli_fetch_assoc($result)): ?>

            <tr>

                <td>
                    <?= htmlspecialchars($article["title"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $article["category_name"]
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $article["author"]
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $article["status"]
                    ) ?>
                </td>

                <td>

                    <a
                        href="edit.php?id=<?= $article["article_id"] ?>"
                    >
                        Edit
                    </a>

                    |

                    <a
                        href="hapus.php?id=<?= $article["article_id"] ?>"
                        onclick="return confirm('Hapus artikel?')"
                    >
                        Hapus
                    </a>

                </td>

            </tr>

        <?php endwhile; ?>

    </table>

</div>

<?php require_once "../../include/footer.php"; ?>
</body>

</html>