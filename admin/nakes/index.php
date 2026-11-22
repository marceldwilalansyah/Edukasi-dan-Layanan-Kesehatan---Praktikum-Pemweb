<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";
require_once "../../include/fungsi.php";

cekRole("admin");

$query = "
    SELECT
        u.user_id,
        u.username,
        u.status,
        p.nama_lengkap,
        p.spesialisasi,
        (SELECT COUNT(*) FROM jadwal_praktik j
            WHERE j.nakes_id = u.user_id AND j.tanggal >= CURDATE()) AS jadwal_mendatang,
        (SELECT COUNT(*) FROM konsultasi k
            WHERE k.nakes_id = u.user_id) AS total_konsultasi
    FROM users u
    JOIN roles r ON u.role_id = r.role_id
    LEFT JOIN user_profiles p ON u.user_id = p.user_id
    WHERE r.role_name = 'nakes'
    ORDER BY p.nama_lengkap, u.username
";

$daftar_nakes = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tenaga Kesehatan - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="../dashboard.php">← Kembali ke Dashboard</a>

    <h1>Kelola Tenaga Kesehatan</h1>
    <p>Akun, spesialisasi, dan bio tenaga kesehatan yang tampil di menu Konsultasi.</p>

    <?php if (isset($_GET["pesan"])): ?>
        <div class="alert success"><?= e($_GET["pesan"]) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET["error"])): ?>
        <div class="alert error"><?= e($_GET["error"]) ?></div>
    <?php endif; ?>

    <div class="page-actions">
        <a class="button" href="tambah.php">+ Tambah Tenaga Kesehatan</a>
    </div>

    <div class="card">
        <?php if (mysqli_num_rows($daftar_nakes) === 0): ?>
            <div class="empty-state">Belum ada tenaga kesehatan. Tambahkan yang pertama.</div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table width="100%">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Spesialisasi</th>
                            <th>Jadwal Mendatang</th>
                            <th>Konsultasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($nakes = mysqli_fetch_assoc($daftar_nakes)): ?>
                            <tr>
                                <td><?= e(namaTampil($nakes["nama_lengkap"], $nakes["username"])) ?></td>
                                <td><?= e($nakes["username"]) ?></td>
                                <td><?= e($nakes["spesialisasi"] ?: "-") ?></td>
                                <td><?= (int) $nakes["jadwal_mendatang"] ?></td>
                                <td><?= (int) $nakes["total_konsultasi"] ?></td>
                                <td>
                                    <?php if ($nakes["status"] === "aktif"): ?>
                                        <span class="pill pill-selesai">Aktif</span>
                                    <?php else: ?>
                                        <span class="pill pill-dibatalkan">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="form-actions" style="margin: 0;">
                                        <a class="button secondary small-button" href="edit.php?id=<?= (int) $nakes["user_id"] ?>">Edit</a>

                                        <form class="inline-form" action="hapus.php" method="POST">
                                            <input type="hidden" name="user_id" value="<?= (int) $nakes["user_id"] ?>">
                                            <button type="submit" class="button danger small-button"
                                                data-confirm="Hapus tenaga kesehatan ini? Jadwal dan riwayat konsultasinya ikut terhapus.">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>
