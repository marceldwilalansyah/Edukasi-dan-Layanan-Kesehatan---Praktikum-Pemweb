<?php

require_once "session.php";

if (!isset($_SESSION["user_id"])) {

    header("Location: autentikasi/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Sehatku</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

</head>

<body>

<?php require_once "include/header.php"; ?>

<div class="container">

    <h1>Sehatku</h1>

    <p>
        Selamat datang,
        <strong>
            <?= htmlspecialchars($_SESSION["username"]) ?>
        </strong>
    </p>

    <p>
        Role:
        <strong>
            <?= htmlspecialchars($_SESSION["role_name"]) ?>
        </strong>
    </p>


    <?php if ($_SESSION["role_name"] === "admin"): ?>

        <a class="button"
           href="admin/dashboard.php">
            Dashboard Admin
        </a>

    <?php elseif ($_SESSION["role_name"] === "nakes"): ?>

        <a class="button"
           href="nakes/dashboard.php">
            Dashboard Nakes
        </a>

    <?php elseif ($_SESSION["role_name"] === "pengguna"): ?>

        <a class="button"
           href="pengguna/dashboard.php">
            Dashboard Pengguna
        </a>

    <?php endif; ?>


    <a class="button danger"
       href="autentikasi/logout.php">
        Logout
    </a>

</div>

<?php require_once "include/footer.php"; ?>
</body>

</html>