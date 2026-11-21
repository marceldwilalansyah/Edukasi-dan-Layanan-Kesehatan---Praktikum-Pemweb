<?php

require_once "../include/auth_check.php";

cekRole("admin");

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

</head>

<body>

<?php require_once "../include/header.php"; ?>

<div class="container">

    <h1>Dashboard Admin</h1>

    <p>
        Selamat datang,
        <?= htmlspecialchars($_SESSION["username"]) ?>
    </p>

    <hr>

    <a class="button"
       href="user/index.php">
        Kelola Pengguna
    </a>

    <a class="button"
       href="../kategori/index.php">
        Kelola Kategori
    </a>

    <a class="button"
       href="artikel/index.php">
        Kelola Artikel
    </a>

    <a class="button danger"
       href="../autentikasi/logout.php">
        Logout
    </a>

</div>

<?php require_once "../include/footer.php"; ?>
</body>

</html>