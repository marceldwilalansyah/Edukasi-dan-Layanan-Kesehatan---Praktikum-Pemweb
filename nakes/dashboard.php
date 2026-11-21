<?php

require_once "../include/auth_check.php";

cekRole("nakes");

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Dashboard Nakes</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

</head>

<body>

<?php require_once "../include/header.php"; ?>

<div class="container">

    <h1>Dashboard Tenaga Kesehatan</h1>

    <p>
        Selamat datang,
        <?= htmlspecialchars($_SESSION["username"]) ?>
    </p>

    <a class="button"
       href="profile.php">
        Profil Saya
    </a>

    <a class="button danger"
       href="../autentikasi/logout.php">
        Logout
    </a>

</div>

<?php require_once "../include/footer.php"; ?>
</body>

</html>