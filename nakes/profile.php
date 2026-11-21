<?php

require_once "../include/auth_check.php";
require_once "../koneksi.php";

cekRole("nakes");


$user_id = $_SESSION["user_id"];


$query = "
    SELECT
        u.username,
        p.nama_lengkap,
        p.spesialisasi,
        p.bio
    FROM users u

    LEFT JOIN user_profiles p
        ON u.user_id = p.user_id

    WHERE u.user_id = ?
";

$stmt = mysqli_prepare(
    $koneksi,
    $query
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Profil Nakes</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

</head>

<body>

<?php require_once "../include/header.php"; ?>

<div class="container auth-container">

    <a class="back-button" href="dashboard.php">← Kembali ke Dashboard</a>

    <h1>Profil Nakes</h1>

    <p>
        Username:
        <?= htmlspecialchars($user["username"]) ?>
    </p>

    <p>
        Nama:
        <?= htmlspecialchars(
            $user["nama_lengkap"] ?? "-"
        ) ?>
    </p>

    <p>
        Spesialisasi:
        <?= htmlspecialchars(
            $user["spesialisasi"] ?? "-"
        ) ?>
    </p>

    <p>
        Bio:
        <?= nl2br(
            htmlspecialchars(
                $user["bio"] ?? "-"
            )
        ) ?>
    </p>

    <p>
        Data spesialisasi dan bio dikelola oleh admin.
    </p>

    <a href="dashboard.php">
        Kembali
    </a>

</div>

<?php require_once "../include/footer.php"; ?>
</body>

</html>