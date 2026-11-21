<?php

require_once "../include/auth_check.php";
require_once "../koneksi.php";

cekRole("pengguna");


$user_id = $_SESSION["user_id"];


$query = "
    SELECT
        u.username,
        p.nama_lengkap,
        p.tinggi,
        p.berat,
        p.tanggal_lahir
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

    <title>Profil Saya</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

</head>

<body>

<?php require_once "../include/header.php"; ?>

<div class="container auth-container">

    <a class="back-button" href="dashboard.php">← Kembali ke Dashboard</a>

    <h1>Profil Saya</h1>

    <form action="proses_profile.php" method="POST">

        <label>Username</label>

        <input
            type="text"
            value="<?= htmlspecialchars($user["username"]) ?>"
            disabled
        >


        <label>Nama Lengkap</label>

        <input
            type="text"
            name="nama_lengkap"
            value="<?= htmlspecialchars($user["nama_lengkap"] ?? "") ?>"
        >


        <label>Tinggi</label>

        <input
            type="number"
            step="0.01"
            name="tinggi"
            value="<?= htmlspecialchars($user["tinggi"] ?? "") ?>"
        >


        <label>Berat</label>

        <input
            type="number"
            step="0.01"
            name="berat"
            value="<?= htmlspecialchars($user["berat"] ?? "") ?>"
        >


        <label>Tanggal Lahir</label>

        <input
            type="date"
            name="tanggal_lahir"
            value="<?= htmlspecialchars($user["tanggal_lahir"] ?? "") ?>"
        >


        <label>Password Baru</label>

        <input
            type="password"
            name="password"
            placeholder="Kosongkan jika tidak ingin mengubah"
        >


        <button type="submit">
            Simpan
        </button>

    </form>

</div>

<?php require_once "../include/footer.php"; ?>
</body>

</html>