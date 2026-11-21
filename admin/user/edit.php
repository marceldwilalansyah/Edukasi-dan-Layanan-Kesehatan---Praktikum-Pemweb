<?php

require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");

$id = (int) $_GET["id"];


$query = "
    SELECT
        u.user_id,
        u.username,
        u.status,
        u.role_id,
        p.nama_lengkap
    FROM users u

    LEFT JOIN user_profiles p
        ON u.user_id = p.user_id

    WHERE u.user_id = ?
";

$stmt = mysqli_prepare($koneksi, $query);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);


if (!$user) {
    die("Pengguna tidak ditemukan.");
}


$roles = mysqli_query(
    $koneksi,
    "SELECT * FROM roles ORDER BY role_id"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Edit Pengguna</title>

    <link rel="stylesheet"
          href="../../assets/css/style.css">

</head>

<body>

<?php require_once "../../include/header.php"; ?>

<div class="container auth-container">

    <a class="back-button" href="index.php">← Kembali ke Pengguna</a>

    <h1>Edit Pengguna</h1>

    <form action="proses_edit.php" method="POST">

        <input
            type="hidden"
            name="user_id"
            value="<?= $user["user_id"] ?>"
        >


        <label>Username</label>

        <input
            type="text"
            name="username"
            value="<?= htmlspecialchars($user["username"]) ?>"
            required
        >


        <label>Nama Lengkap</label>

        <input
            type="text"
            name="nama_lengkap"
            value="<?= htmlspecialchars($user["nama_lengkap"] ?? "") ?>"
        >


        <label>Password Baru</label>

        <input
            type="password"
            name="password"
            placeholder="Kosongkan jika tidak ingin mengubah"
        >


        <label>Role</label>

        <select name="role_id">

            <?php while ($role = mysqli_fetch_assoc($roles)): ?>

                <option
                    value="<?= $role["role_id"] ?>"
                    <?= $role["role_id"] == $user["role_id"] ? "selected" : "" ?>
                >
                    <?= htmlspecialchars($role["role_name"]) ?>
                </option>

            <?php endwhile; ?>

        </select>


        <label>Status</label>

        <select name="status">

            <option
                value="aktif"
                <?= $user["status"] === "aktif" ? "selected" : "" ?>
            >
                Aktif
            </option>

            <option
                value="nonaktif"
                <?= $user["status"] === "nonaktif" ? "selected" : "" ?>
            >
                Nonaktif
            </option>

        </select>


        <button type="submit">
            Simpan Perubahan
        </button>

    </form>

    <a href="index.php">
        Kembali
    </a>

</div>

<?php require_once "../../include/footer.php"; ?>
</body>

</html>