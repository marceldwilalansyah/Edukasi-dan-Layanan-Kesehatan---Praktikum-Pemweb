<?php

require_once "../include/auth_check.php";
require_once "../koneksi.php";

cekRole("admin");

$result = mysqli_query(
    $koneksi,
    "SELECT * FROM categories ORDER BY category_id DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Kategori Artikel</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

</head>

<body>

<?php require_once "../include/header.php"; ?>

<div class="container">

    <a class="back-button" href="../admin/dashboard.php">← Kembali ke Dashboard</a>

    <h1>Kategori Artikel</h1>

    <a class="button"
       href="tambah.php">
        + Tambah Kategori
    </a>

    <table
        border="1"
        cellpadding="10"
        cellspacing="0"
        width="100%"
        style="margin-top:20px;"
    >

        <tr>

            <th>ID</th>
            <th>Nama Kategori</th>
            <th>Aksi</th>

        </tr>


        <?php while ($category = mysqli_fetch_assoc($result)): ?>

            <tr>

                <td>
                    <?= $category["category_id"] ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $category["category_name"]
                    ) ?>
                </td>

                <td>

                    <a href="edit.php?id=<?= $category["category_id"] ?>">
                        Edit
                    </a>

                    |

                    <a
                        href="hapus.php?id=<?= $category["category_id"] ?>"
                        onclick="return confirm('Hapus kategori?')"
                    >
                        Hapus
                    </a>

                </td>

            </tr>

        <?php endwhile; ?>

    </table>

</div>

<?php require_once "../include/footer.php"; ?>
</body>

</html>