<?php

require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");

$roles = mysqli_query(
    $koneksi,
    "SELECT * FROM roles ORDER BY role_id"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Tambah Pengguna</title>

    <link rel="stylesheet"
          href="../../assets/css/style.css">

</head>

<body>

<?php require_once "../../include/header.php"; ?>

<div class="container auth-container">

    <a class="back-button" href="index.php">← Kembali ke Pengguna</a>

    <h1>Tambah Pengguna</h1>

    <form action="proses_tambah.php" method="POST">

        <label>Username</label>

        <input
            type="text"
            name="username"
            required
        >


        <label>Password</label>

        <input
            type="password"
            name="password"
            required
        >


        <label>Nama Lengkap</label>

        <input
            type="text"
            name="nama_lengkap"
        >


        <label>Role</label>

        <select name="role_id" required>

            <?php while ($role = mysqli_fetch_assoc($roles)): ?>

                <option value="<?= $role["role_id"] ?>">
                    <?= htmlspecialchars($role["role_name"]) ?>
                </option>

            <?php endwhile; ?>

        </select>


        <button type="submit">
            Simpan
        </button>

    </form>

    <a href="index.php">
        Kembali
    </a>

</div>

<?php require_once "../../include/footer.php"; ?>
</body>

</html>