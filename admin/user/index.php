<?php

require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");


$query = "
    SELECT
        u.user_id,
        u.username,
        u.status,
        u.created_at,
        r.role_name,
        p.nama_lengkap
    FROM users u

    JOIN roles r
        ON u.role_id = r.role_id

    LEFT JOIN user_profiles p
        ON u.user_id = p.user_id

    ORDER BY u.user_id DESC
";

$result = mysqli_query($koneksi, $query);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Kelola Pengguna</title>

    <link rel="stylesheet"
          href="../../assets/css/style.css">

</head>

<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">

    <a class="back-button" href="../dashboard.php">← Kembali ke Dashboard</a>

    <h1>Kelola Pengguna</h1>

    <a class="button"
       href="tambah.php">
        + Tambah Pengguna
    </a>

    <table border="1"
           cellpadding="10"
           cellspacing="0"
           width="100%"
           style="margin-top:20px;">

        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Nama</th>
            <th>Role</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        <?php while ($user = mysqli_fetch_assoc($result)): ?>

            <tr>

                <td>
                    <?= $user["user_id"] ?>
                </td>

                <td>
                    <?= htmlspecialchars($user["username"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $user["nama_lengkap"] ?? "-"
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars($user["role_name"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($user["status"]) ?>
                </td>

                <td>

                    <a href="edit.php?id=<?= $user["user_id"] ?>">
                        Edit
                    </a>

                    <?php if ($user["user_id"] != $_SESSION["user_id"]): ?>

                        |
                        <a
                            href="hapus.php?id=<?= $user["user_id"] ?>"
                            onclick="return confirm('Yakin ingin menghapus pengguna ini?')"
                        >
                            Hapus
                        </a>

                    <?php endif; ?>

                </td>

            </tr>

        <?php endwhile; ?>

    </table>

    <br>

    <a href="../dashboard.php">
        Kembali
    </a>

</div>

<?php require_once "../../include/footer.php"; ?>
</body>

</html>