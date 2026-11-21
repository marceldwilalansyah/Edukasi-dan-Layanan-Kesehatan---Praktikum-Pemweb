<?php
require_once "../include/auth_check.php";
cekRole("pengguna");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengguna - Sehatku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php require_once "../include/header.php"; ?>

<div class="container">
    <h1>Dashboard Pengguna</h1>

    <p>
        Halo, <strong><?= htmlspecialchars($_SESSION["username"]) ?></strong>
    </p>

    <div class="page-actions" style="flex-wrap: wrap; gap: 10px;">
        <a class="button" href="profile.php">Profil Saya</a>
        <a class="button" href="artikel/index.php">Artikel Edukasi</a>
        <a class="button danger" href="../autentikasi/logout.php">Logout</a>
    </div>
</div>

<?php require_once "../include/footer.php"; ?>
</body>
</html>